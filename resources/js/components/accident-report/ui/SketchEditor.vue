<template>
  <div class="flex flex-col gap-y-8">
    <FormLabel>Unfallskizze <span class="font-normal text-blue-gray">optional</span></FormLabel>
    <p class="text-xs md:text-sm text-blue-gray -mt-4">Hintergrund wählen, Elemente antippen und mit dem Finger verschieben.</p>

    <div class="grid grid-cols-5 gap-6">
      <button
        v-for="(background, key) in SKETCH_BACKGROUNDS"
        :key="key"
        type="button"
        class="flex flex-col items-center gap-y-4 p-4 text-xxs md:text-xs font-medium cursor-pointer"
        :class="sketch.background === key ? 'bg-blue-primary text-white' : 'bg-light-blue text-blue-black'"
        @click="update({ background: key })"
      >
        <svg viewBox="0 0 300 300" class="w-34 h-34" v-html="background.svg"></svg>
        {{ background.label }}
      </button>
    </div>

    <div
      ref="canvas"
      class="mx-auto w-full max-w-[22.5rem] border border-blue-primary/40 touch-none select-none [&>svg]:block [&>svg]:w-full [&>svg]:h-auto"
      @pointerdown="pointerDown"
      @pointermove="pointerMove"
      @pointerup="pointerUp"
      @pointercancel="pointerUp"
      v-html="svg"
    ></div>

    <div class="flex justify-center gap-8">
      <button type="button" :class="toolClass" @click="undo">↶ Rückgängig</button>
      <button type="button" :class="toolClass" @click="clear">Alles löschen</button>
    </div>

    <div class="grid grid-cols-4 sm:grid-cols-7 gap-6">
      <button
        v-for="(element, key) in availableElements"
        :key="key"
        type="button"
        class="flex flex-col items-center gap-y-4 p-4 bg-light-blue text-blue-black text-xxs md:text-xs font-medium cursor-pointer disabled:opacity-35 disabled:cursor-default"
        :disabled="isUsedVehicle(key)"
        @click="add(key)"
      >
        <svg viewBox="-30 -40 60 80" class="w-30 h-38" v-html="elementSvg(key, vehicles)"></svg>
        {{ element.label }}
      </button>
    </div>

    <div v-if="selectedElement" class="flex flex-wrap items-center justify-center gap-6 p-8 bg-light-grey">
      <span class="w-full text-center text-xs">Ausgewählt: <b>{{ SKETCH_ELEMENTS[selectedElement.type].label }}</b></span>
      <button type="button" :class="toolClass" aria-label="Kleiner" @click="change('smaller')">−</button>
      <button type="button" :class="toolClass" aria-label="Grösser" @click="change('bigger')">+</button>
      <button type="button" :class="toolClass" aria-label="Nach links drehen" @click="change('left')">⟲</button>
      <button type="button" :class="toolClass" aria-label="Nach rechts drehen" @click="change('right')">⟳</button>
      <button type="button" :class="[toolClass, 'text-error']" @click="change('delete')">Löschen</button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import FormLabel from '../../form/FormLabel.vue';
import { SKETCH_BACKGROUNDS, SKETCH_ELEMENTS, elementSvg, sketchSvg } from '../svg.js';

// Accident sketch kit: background, vehicles A/B, signs; move with the finger, rotate and resize
const props = defineProps({
  // { background, elements: [{ type, x, y, rotation, scale }] }
  modelValue: {
    type: Object,
    required: true,
  },
  // { busA, busB }: draw A / B as a bus or as a car
  vehicles: {
    type: Object,
    required: true,
  },
  withVehicleB: {
    type: Boolean,
    default: true,
  },
});

const emit = defineEmits(['update:modelValue']);

const toolClass = 'min-w-44 h-40 px-10 bg-white border border-blue-primary/40 text-blue-black text-sm cursor-pointer';

const sketch = computed(() => props.modelValue);
const selected = ref(-1);
const history = [];
const canvas = ref(null);
let drag = null;

const svg = computed(() => sketchSvg(sketch.value, props.vehicles, { selected: selected.value, editable: true }));
const selectedElement = computed(() => sketch.value.elements[selected.value] || null);
const availableElements = computed(() => Object.fromEntries(
  Object.entries(SKETCH_ELEMENTS).filter(([key]) => key !== 'B' || props.withVehicleB),
));

const update = (changes) => emit('update:modelValue', { ...sketch.value, ...changes });
const setElements = (elements) => update({ elements });

const remember = () => {
  history.push(JSON.stringify(sketch.value.elements));
  if (history.length > 40) history.shift();
};

const isUsedVehicle = (type) => (type === 'A' || type === 'B') && sketch.value.elements.some((element) => element.type === type);

const add = (type) => {
  if (isUsedVehicle(type)) return;
  remember();
  const jitter = () => 150 + (Math.random() * 30 - 15);
  setElements([...sketch.value.elements, { type, x: jitter(), y: jitter(), rotation: 0, scale: 1 }]);
  selected.value = sketch.value.elements.length; // index of the new element
};

const undo = () => {
  if (history.length) {
    setElements(JSON.parse(history.pop()));
    selected.value = -1;
  }
};

const clear = () => {
  remember();
  setElements([]);
  selected.value = -1;
};

const change = (action) => {
  const element = selectedElement.value;
  if (!element) return;
  remember();

  if (action === 'delete') {
    setElements(sketch.value.elements.filter((_, i) => i !== selected.value));
    selected.value = -1;
    return;
  }

  const next = { ...element };
  if (action === 'left') next.rotation -= 15;
  if (action === 'right') next.rotation += 15;
  if (action === 'bigger') next.scale = Math.min(2.5, +(next.scale + 0.15).toFixed(2));
  if (action === 'smaller') next.scale = Math.max(0.4, +(next.scale - 0.15).toFixed(2));
  setElements(sketch.value.elements.map((item, i) => (i === selected.value ? next : item)));
};

// ---------------------------------------------------------------- dragging

const svgPoint = (event) => {
  const el = canvas.value.querySelector('svg');
  const point = el.createSVGPoint();
  point.x = event.clientX;
  point.y = event.clientY;
  return point.matrixTransform(el.getScreenCTM().inverse());
};

const pointerDown = (event) => {
  const target = event.target.closest('[data-index]');
  if (!target) {
    selected.value = -1;
    return;
  }
  const index = Number(target.dataset.index);
  const element = sketch.value.elements[index];
  const p = svgPoint(event);
  selected.value = index;
  remember();
  drag = { index, dx: p.x - element.x, dy: p.y - element.y, moved: false };
  canvas.value.setPointerCapture?.(event.pointerId);
  event.preventDefault();
};

const pointerMove = (event) => {
  if (!drag) return;
  const p = svgPoint(event);
  const x = Math.max(0, Math.min(300, p.x - drag.dx));
  const y = Math.max(0, Math.min(300, p.y - drag.dy));
  setElements(sketch.value.elements.map((item, i) => (i === drag.index ? { ...item, x, y } : item)));
  drag.moved = true;
  event.preventDefault();
};

const pointerUp = () => {
  if (drag && !drag.moved) history.pop(); // a tap only selects, nothing to undo
  drag = null;
};
</script>

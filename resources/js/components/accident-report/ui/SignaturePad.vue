<template>
  <div class="bg-white border border-blue-primary/40">
    <div class="flex items-center justify-between gap-x-8 px-12 py-6 text-xs md:text-sm text-blue-gray">
      <span>
        {{ label }}
        <span v-if="required" class="ml-4 px-6 py-1 bg-error text-white text-xxs font-bold">Pflicht</span>
      </span>
      <button type="button" class="text-blue-primary font-medium underline cursor-pointer" @click="clear">Löschen</button>
    </div>
    <canvas
      ref="canvas"
      :height="height"
      class="block w-full touch-none border-t border-dashed border-blue-primary/40 cursor-crosshair"
      @pointerdown="start"
      @pointermove="move"
      @pointerup="end"
      @pointercancel="end"
      @pointerleave="end"
    ></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

// Signature with the finger, model = PNG data URL or null
const props = defineProps({
  modelValue: {
    type: String,
    default: null,
  },
  label: {
    type: String,
    required: true,
  },
  required: {
    type: Boolean,
    default: false,
  },
  height: {
    type: Number,
    default: 120,
  },
});

const emit = defineEmits(['update:modelValue']);

const canvas = ref(null);
let context = null;
let drawing = false;

onMounted(() => {
  const el = canvas.value;
  el.width = el.clientWidth;
  context = el.getContext('2d');
  context.lineWidth = 2.5;
  context.lineCap = 'round';
  context.strokeStyle = '#111';

  // Back on this step: show the signature again
  if (props.modelValue) {
    const image = new Image();
    image.onload = () => context.drawImage(image, 0, 0, el.width, el.height);
    image.src = props.modelValue;
  }
});

const point = (event) => {
  const rect = canvas.value.getBoundingClientRect();
  return [event.clientX - rect.left, event.clientY - rect.top];
};

const start = (event) => {
  drawing = true;
  canvas.value.setPointerCapture?.(event.pointerId);
  context.beginPath();
  context.moveTo(...point(event));
  event.preventDefault();
};

const move = (event) => {
  if (!drawing) return;
  context.lineTo(...point(event));
  context.stroke();
  event.preventDefault();
};

const end = () => {
  if (!drawing) return;
  drawing = false;
  emit('update:modelValue', canvas.value.toDataURL('image/png'));
};

const clear = () => {
  context.clearRect(0, 0, canvas.value.width, canvas.value.height);
  emit('update:modelValue', null);
};
</script>

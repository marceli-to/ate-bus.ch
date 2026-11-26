<template>
  <div class="flex flex-col gap-y-8">
    <div class="flex flex-wrap justify-center gap-8">
      <div v-for="view in views" :key="view.title" class="text-center" :class="view.face ? 'basis-[40%] max-w-150' : 'basis-[45%] max-w-170'">
        <div class="font-bold text-xs">{{ view.title }}</div>
        <svg :viewBox="view.viewBox" class="w-full h-auto">
          <ellipse v-if="view.face" cx="60" cy="74" rx="34" ry="46" fill="#fff" stroke="#1355A1" stroke-width="1.2" />
          <g
            v-for="[name, shape] in view.parts"
            :key="name"
            class="cursor-pointer"
            :fill="modelValue.includes(name) ? '#C62828' : '#E8EFF8'"
            :stroke="modelValue.includes(name) ? '#8e1b1b' : '#1355A1'"
            stroke-width="1"
            role="button"
            :aria-pressed="modelValue.includes(name)"
            :aria-label="name"
            @click="toggle(name)"
            v-html="`<title>${name}</title>${shape}`"
          ></g>
          <g v-if="view.hint" fill="none" stroke="#1355A1" stroke-width="1" opacity="0.6" stroke-linecap="round" pointer-events="none" v-html="view.hint"></g>
          <text :x="view.face ? 6 : 8" :y="view.face ? 136 : 244" font-size="8" fill="#6b7280">{{ view.leftLabel }}</text>
          <text :x="view.face ? 114 : 112" :y="view.face ? 136 : 244" text-anchor="end" font-size="8" fill="#6b7280">{{ view.rightLabel }}</text>
        </svg>
      </div>
    </div>
    <p class="text-xs text-blue-gray text-center">rechts / links aus Sicht der verletzten Person</p>
    <div v-if="modelValue.length" class="flex flex-wrap gap-6">
      <button
        v-for="name in modelValue"
        :key="name"
        type="button"
        class="px-10 py-4 bg-error/10 border border-error text-xs cursor-pointer"
        @click="toggle(name)"
      >
        {{ name }} ✕
      </button>
    </div>
  </div>
</template>

<script setup>
import { bodyParts, FACE_PARTS, FACE_HINT_BACK, FACE_HINT_FRONT } from '../svg.js';

// Injured body parts: tap on the front, back or face; model = list of part names
const props = defineProps({
  modelValue: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(['update:modelValue']);

const views = [
  { title: 'Vorderseite', viewBox: '0 0 120 246', parts: bodyParts(false), hint: FACE_HINT_FRONT, leftLabel: 'rechts', rightLabel: 'links' },
  { title: 'Rückseite', viewBox: '0 0 120 246', parts: bodyParts(true), hint: FACE_HINT_BACK, leftLabel: 'links', rightLabel: 'rechts' },
  { title: 'Gesicht', viewBox: '0 0 120 140', parts: FACE_PARTS, face: true, leftLabel: 'rechts', rightLabel: 'links' },
];

const toggle = (name) => {
  emit('update:modelValue', props.modelValue.includes(name)
    ? props.modelValue.filter((item) => item !== name)
    : [...props.modelValue, name]);
};
</script>

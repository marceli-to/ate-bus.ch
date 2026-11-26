<template>
  <div class="border-2 px-8 pt-6 pb-4 bg-white" :class="party === 'B' ? 'border-green' : 'border-blue-primary'">
    <div class="font-bold text-center" :class="party === 'B' ? 'text-green' : 'text-blue-primary'">{{ title }}</div>
    <div
      class="cursor-crosshair touch-manipulation [&>svg]:block [&>svg]:w-full [&>svg]:h-auto [&>svg]:max-h-200 [&>svg]:mx-auto"
      role="button"
      :aria-label="`${title}: auf den ersten Aufprallpunkt tippen`"
      @click="tap"
      v-html="svg"
    ></div>
    <p v-if="hint" class="text-xs text-blue-gray text-center">{{ hint }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { impactSvg, impactPointFromEvent } from '../svg.js';

// First point of impact: tap on the vehicle sketch, a red X marks the spot
const props = defineProps({
  modelValue: {
    type: Object,
    default: null,
  },
  types: {
    type: Array,
    required: true,
  },
  title: {
    type: String,
    required: true,
  },
  party: {
    type: String,
    default: 'A',
  },
  hint: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue']);

const svg = computed(() => impactSvg(props.types, props.modelValue));

const tap = (event) => {
  const el = event.currentTarget.querySelector('svg');
  const point = el && impactPointFromEvent(event, el, props.types);
  if (point) emit('update:modelValue', point);
};
</script>

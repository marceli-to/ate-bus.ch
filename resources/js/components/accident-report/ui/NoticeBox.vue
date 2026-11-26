<template>
  <div class="border-l-4 p-12 md:p-16 space-y-8" :class="toneClass" :role="tone === 'danger' ? 'alert' : 'status'">
    <h3 v-if="title" class="font-bold text-md md:text-lg" :class="titleClass">{{ title }}</h3>
    <slot />
  </div>
</template>

<script setup>
import { computed } from 'vue';

// Coloured box: danger (call the police), warning (VBZ, waiting), success, info
const props = defineProps({
  tone: {
    type: String,
    default: 'info',
  },
  title: {
    type: String,
    default: '',
  },
});

const toneClass = computed(() => ({
  danger: 'bg-error/10 border-error',
  warning: 'bg-[#FFF4E0] border-[#E08A00]',
  success: 'bg-success/10 border-success',
  info: 'bg-light-blue border-blue-primary',
}[props.tone]));

const titleClass = computed(() => ({
  danger: 'text-error',
  warning: 'text-[#B36B00]',
  success: 'text-success',
  info: 'text-blue-primary',
}[props.tone]));
</script>

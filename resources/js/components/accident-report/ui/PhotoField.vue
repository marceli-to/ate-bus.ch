<template>
  <div class="flex flex-col gap-y-6">
    <div
      class="relative aspect-[4/3] flex flex-col items-center justify-center gap-y-6 overflow-hidden bg-light-blue border-2 border-dashed px-8 text-center"
      :class="modelValue ? 'border-success' : 'border-blue-primary/40'"
    >
      <img v-if="modelValue" :src="modelValue" :alt="label" class="absolute inset-0 w-full h-full object-cover" />
      <span v-else class="font-medium text-blue-black text-xs md:text-sm">{{ label }}</span>
      <button
        v-if="modelValue"
        type="button"
        class="absolute top-6 right-6 w-32 h-32 flex items-center justify-center bg-blue-black/80 text-white hover:bg-blue-black cursor-pointer"
        :aria-label="`${label} entfernen`"
        @click="$emit('update:modelValue', null)"
      >
        <svg class="w-14 h-14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
          <path d="M1 1l12 12M13 1L1 13" />
        </svg>
      </button>
    </div>
    <span v-if="modelValue" class="text-xs text-success font-medium">✓ {{ label }}</span>
    <PhotoButtons @photos="(photos) => $emit('update:modelValue', photos[0])" />
  </div>
</template>

<script setup>
import PhotoButtons from './PhotoButtons.vue';

// One photo (e.g. «Führerausweis Vorderseite»), model = JPEG data URL or null
defineProps({
  modelValue: {
    type: String,
    default: null,
  },
  label: {
    type: String,
    required: true,
  },
});

defineEmits(['update:modelValue']);
</script>

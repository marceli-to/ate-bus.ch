<template>
  <label :for="htmlFor" :class="['text-blue-black font-medium text-sm mb-2 block', customClass]">
    <slot />
    <span v-if="required">*</span>
  </label>
</template>

<script setup>
defineProps({
  htmlFor: {
    type: String,
    default: '',
  },
  required: {
    type: Boolean,
    default: false,
  },
  customClass: {
    type: String,
    default: '',
  },
});
</script>

<template>
  <label class="flex items-center gap-x-12 cursor-pointer select-none font-medium">
    <input
      type="checkbox"
      :checked="modelValue"
      class="w-24 h-24 shrink-0 accent-blue-primary cursor-pointer"
      @change="$emit('update:modelValue', $event.target.checked)"
    />
    <span>{{ label }}</span>
  </label>
</template>

<script setup>
// Confirmation checkbox with a boolean model («Polizei ist informiert»)
defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  label: {
    type: String,
    required: true,
  },
});

defineEmits(['update:modelValue']);
</script>

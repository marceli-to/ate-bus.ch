<template>
  <fieldset class="flex flex-col">
    <legend class="sr-only">{{ label }}</legend>
    <FormLabel
      aria-hidden="true"
      :required="required"
      :custom-class="error ? 'text-error' : ''"
    >
      {{ error || label }}
    </FormLabel>
    <div class="grid gap-8 md:gap-12" :class="columns === 4 ? 'grid-cols-2 md:grid-cols-4' : 'grid-cols-2'">
      <label
        v-for="option in options"
        :key="option.value"
        class="cursor-pointer select-none"
      >
        <input
          type="radio"
          :name="name"
          :value="option.value"
          :checked="modelValue === option.value"
          class="sr-only peer"
          @change="$emit('update:modelValue', option.value)"
        />
        <span
          class="flex items-center justify-center text-center min-h-48 px-8 py-10 font-medium transition-colors duration-200 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-blue-black"
          :class="modelValue === option.value
            ? 'bg-blue-primary text-white'
            : 'bg-light-blue text-blue-black hover:bg-blue-primary/20'"
        >
          {{ option.label }}
        </span>
      </label>
    </div>
  </fieldset>
</template>

<script setup>
import FormLabel from './FormLabel.vue';

defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  label: {
    type: String,
    required: true,
  },
  name: {
    type: String,
    required: true,
  },
  options: {
    type: Array,
    required: true,
  },
  columns: {
    type: Number,
    default: 2,
  },
  required: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: '',
  },
});

defineEmits(['update:modelValue']);
</script>

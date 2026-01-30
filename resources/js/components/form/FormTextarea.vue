<template>
  <div class="flex flex-col p-8 md:py-8 md:px-12" :class="wrapperBgClass">
    <FormLabel
      :html-for="id"
      :required="required"
      :custom-class="error ? 'text-error' : ''"
    >
      {{ error || label }}
    </FormLabel>
    <textarea
      :id="id"
      :name="name"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :rows="rows"
      class="w-full py-4 xl:py-8 border-0 text-blue-black text-xs xl:text-sm placeholder-blue-gray/50 focus:outline-none resize-none"
      :class="textareaBgClass"
      @input="$emit('update:modelValue', $event.target.value)"
      @focus="$emit('focus')"
    />
  </div>
</template>

<script setup>
import { computed, inject } from 'vue';
import FormLabel from './FormLabel.vue';

const variant = inject('formVariant', 'white');

const props = defineProps({
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
  placeholder: {
    type: String,
    default: '',
  },
  rows: {
    type: Number,
    default: 4,
  },
  required: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: '',
  },
});

defineEmits(['update:modelValue', 'focus']);

const id = computed(() => `form-${props.name}`);

const wrapperBgClass = computed(() => ({
  'bg-white': variant === 'white',
  'bg-light-blue': variant === 'light-blue',
}));

const textareaBgClass = computed(() => ({
  'bg-white': variant === 'white',
  'bg-light-blue': variant === 'light-blue',
}));
</script>

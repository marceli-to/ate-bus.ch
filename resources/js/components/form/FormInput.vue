<template>
  <div class="flex flex-col p-8 md:py-8 md:px-12" :class="wrapperBgClass">
    <FormLabel
      :html-for="id"
      :required="required"
      :custom-class="error ? 'text-error' : ''"
    >
      {{ error || label }}
    </FormLabel>
    <input
      :id="id"
      :type="type"
      :name="name"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :min="min"
      :max="max"
      :inputmode="inputmode"
      :pattern="pattern"
      class="w-full py-4 xl:py-8 border-0 text-blue-black text-xs xl:text-sm placeholder-blue-gray/50 focus:outline-none"
      :class="inputBgClass"
      @input="handleInput"
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
  type: {
    type: String,
    default: 'text',
  },
  placeholder: {
    type: String,
    default: '',
  },
  required: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  min: {
    type: String,
    default: null,
  },
  max: {
    type: String,
    default: null,
  },
  error: {
    type: String,
    default: '',
  },
  inputmode: {
    type: String,
    default: null,
  },
  pattern: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(['update:modelValue', 'focus']);

const handleInput = (event) => {
  let value = event.target.value;
  
  // Filter non-numeric characters when inputmode is numeric
  if (props.inputmode === 'numeric') {
    value = value.replace(/[^0-9]/g, '');
    event.target.value = value;
  }
  
  emit('update:modelValue', value);
};

const id = computed(() => `form-${props.name}`);

const wrapperBgClass = computed(() => ({
  'bg-white': variant === 'white',
  'bg-light-blue': variant === 'light-blue',
}));

const inputBgClass = computed(() => ({
  'bg-white': variant === 'white',
  'bg-light-blue': variant === 'light-blue',
}));
</script>

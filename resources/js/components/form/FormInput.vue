<template>
  <div class="flex flex-col bg-white p-8 md:py-8 md:px-12">
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
      class="w-full py-4 xl:py-8 bg-white border-0 text-blue-black text-xs xl:text-sm placeholder-blue-gray/50 focus:outline-none"
      @input="$emit('update:modelValue', $event.target.value)"
      @focus="$emit('focus')"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import FormLabel from './FormLabel.vue';

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
  error: {
    type: String,
    default: '',
  },
});

defineEmits(['update:modelValue', 'focus']);

const id = computed(() => `form-${props.name}`);
</script>

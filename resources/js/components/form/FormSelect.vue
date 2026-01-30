<template>
  <div class="flex flex-col bg-white py-6 px-8 md:py-8 md:px-12">
    <FormLabel
      :html-for="id"
      :required="required"
      :custom-class="error ? 'text-error' : ''"
    >
      {{ error || label }}
    </FormLabel>
    <div class="relative bg-blue-100">
      <select
        :id="id"
        :name="name"
        :value="modelValue"
        :disabled="disabled"
        class="w-full py-4 xl:py-8 bg-white border-0 text-xs xl:text-sm appearance-none cursor-pointer focus:outline-none pr-16"
        :class="[
          !modelValue ? 'text-blue-gray/50' : 'text-blue-black'
        ]"
        @change="$emit('update:modelValue', $event.target.value)"
        @focus="$emit('focus')">
        <option value="" disabled>{{ placeholder || 'Bitte wählen' }}</option>
        <option
          v-for="option in options"
          :key="option.value"
          :value="option.value"
          class="text-blue-black">
          {{ option.label }}
        </option>
      </select>
      <IconChevronDown class="absolute right-0 top-1/2 -translate-y-1/2 text-blue-black pointer-events-none w-16 h-auto" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import FormLabel from './FormLabel.vue';
import IconChevronDown from '../icons/IconChevronDown.vue';

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
  options: {
    type: Array,
    required: true,
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

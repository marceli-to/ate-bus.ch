<template>
  <fieldset class="flex flex-col">
    <legend v-if="label" class="sr-only">{{ label }}</legend>
    <FormLabel v-if="label" aria-hidden="true" :required="required">
      {{ label }}<span v-if="optional" class="font-normal text-blue-gray"> {{ optional }}</span>
    </FormLabel>
    <div class="grid gap-8 md:gap-12" :class="columnClass">
      <label
        v-for="option in options"
        :key="String(option.value)"
        class="cursor-pointer select-none"
        :class="option.wide ? 'col-span-full' : ''"
      >
        <input
          type="radio"
          :name="name"
          :checked="modelValue === option.value"
          class="sr-only peer"
          @change="$emit('update:modelValue', option.value)"
        />
        <span
          class="flex flex-col justify-center gap-y-4 h-full transition-colors duration-200 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-blue-black"
          :class="[sizeClass, alignClass, classesFor(option)]"
        >
          <span class="font-medium">{{ option.label }}</span>
          <span v-if="option.description" class="text-xs md:text-sm font-normal opacity-80">{{ option.description }}</span>
        </span>
      </label>
    </div>
  </fieldset>
</template>

<script setup>
import { computed } from 'vue';
import FormLabel from '../../form/FormLabel.vue';

// Single choice as big buttons. Values may be strings or booleans.
// Option tone «danger» / «safe» colours the selected button red / green (e.g. «Gibt es Verletzte?»).
const props = defineProps({
  modelValue: {
    type: [String, Boolean],
    default: null,
  },
  options: {
    type: Array,
    required: true,
  },
  name: {
    type: String,
    required: true,
  },
  label: {
    type: String,
    default: '',
  },
  optional: {
    type: String,
    default: '',
  },
  required: {
    type: Boolean,
    default: false,
  },
  columns: {
    type: Number,
    default: 2,
  },
  size: {
    type: String,
    default: 'md', // sm | md | lg
  },
  align: {
    type: String,
    default: 'center', // center | left
  },
});

defineEmits(['update:modelValue']);

const columnClass = computed(() => ({
  1: 'grid-cols-1',
  2: 'grid-cols-2',
  3: 'grid-cols-2 sm:grid-cols-3',
}[props.columns]));

const sizeClass = computed(() => ({
  sm: 'min-h-44 px-8 py-6 text-xs md:text-sm',
  md: 'min-h-54 px-12 py-8',
  lg: 'min-h-80 px-12 py-12 text-lg md:text-xl',
}[props.size]));

const alignClass = computed(() => (props.align === 'left' ? 'items-start text-left' : 'items-center text-center'));

const TONES = {
  danger: 'bg-error text-white',
  safe: 'bg-green text-white',
};

const classesFor = (option) => {
  if (props.modelValue !== option.value) {
    return 'bg-light-blue text-blue-black hover:bg-blue-primary/20';
  }
  return TONES[option.tone] || 'bg-blue-primary text-white';
};
</script>

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
    <div class="grid grid-cols-2 gap-8 md:gap-12">
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
          class="flex items-center justify-center text-center min-h-54 px-8 py-8 transition-colors duration-200 peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-blue-black"
          :class="classesFor(option)"
        >
          {{ option.label }}
        </span>
      </label>
    </div>
  </fieldset>
</template>

<script setup>
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
  options: {
    type: Array,
    required: true,
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

// Optionen mit «tone» (z.B. VBZ/VBG): farbig umrandet, ausgewählt voll eingefärbt
const TONES = {
  blue: {
    on: 'bg-blue-primary border-blue-primary text-white',
    off: 'bg-blue-primary/10 border-blue-primary text-blue-primary hover:bg-blue-primary/20',
  },
  green: {
    on: 'bg-green border-green text-white',
    off: 'bg-green/10 border-green text-green hover:bg-green/20',
  },
};

const classesFor = (option) => {
  const selected = props.modelValue === option.value;
  const tone = TONES[option.tone];

  if (tone) {
    return ['border-2 font-bold text-lg', selected ? tone.on : tone.off];
  }

  return ['font-medium text-md', selected ? 'bg-blue-primary text-white' : 'bg-light-blue text-blue-black hover:bg-blue-primary/20'];
};
</script>

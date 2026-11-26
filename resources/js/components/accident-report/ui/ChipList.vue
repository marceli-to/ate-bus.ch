<template>
  <div class="flex flex-col">
    <FormLabel v-if="label" :required="required">
      {{ label }}<span v-if="optional" class="font-normal text-blue-gray"> {{ optional }}</span>
    </FormLabel>
    <div class="flex flex-wrap gap-6 md:gap-8">
      <button
        v-for="option in options"
        :key="option"
        type="button"
        class="px-12 py-6 md:py-8 text-xs md:text-sm transition-colors duration-200 cursor-pointer"
        :class="modelValue.includes(option) ? 'bg-blue-primary text-white' : 'bg-light-blue text-blue-black hover:bg-blue-primary/20'"
        :aria-pressed="modelValue.includes(option)"
        @click="toggle(option)"
      >
        {{ option }}
      </button>
    </div>
  </div>
</template>

<script setup>
import FormLabel from '../../form/FormLabel.vue';

// Multiple choice as toggle buttons; the model is the array of chosen labels
const props = defineProps({
  modelValue: {
    type: Array,
    required: true,
  },
  options: {
    type: Array,
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
});

const emit = defineEmits(['update:modelValue']);

const toggle = (option) => {
  const list = props.modelValue.includes(option)
    ? props.modelValue.filter((item) => item !== option)
    : [...props.modelValue, option];
  emit('update:modelValue', list);
};
</script>

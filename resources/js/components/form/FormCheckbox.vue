<template>
  <div class="inline-flex">
    <label class="flex items-center gap-x-8 md:gap-x-12 cursor-pointer select-none">
      <input
        type="checkbox"
        :name="name"
        :value="value"
        :checked="isChecked"
        :disabled="disabled"
        class="sr-only peer"
        @change="handleChange"
      />
      <span
        class="w-16 h-16 md:w-20 md:h-20 bg-white border border-blue-black flex items-center justify-center mr-2 transition-colors peer-checked:bg-blue-black"
      >
        <svg
          v-if="isChecked"
          class="w-12 h-12 md:w-16 md:h-16 text-white"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 20 20"
          fill="currentColor"
        >
          <path
            fill-rule="evenodd"
            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
            clip-rule="evenodd"
          />
        </svg>
      </span>
      <span class="text-blue-black">{{ label }}</span>
    </label>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Boolean],
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
  value: {
    type: String,
    default: 'true',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue']);

const isChecked = computed(() => {
  return props.modelValue === props.value;
});

const handleChange = (event) => {
  if (event.target.checked) {
    emit('update:modelValue', props.value);
  } else {
    emit('update:modelValue', '');
  }
};
</script>

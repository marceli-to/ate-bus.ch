<template>
  <svg
    :width="width"
    :height="height"
    viewBox="0 0 20 12"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    :class="className"
  >
    <path
      d="M0.707031 0.707092L9.70703 9.70709L18.707 0.707092"
      stroke="currentColor"
      stroke-width="2"
    />
  </svg>
</template>

<script setup>
defineProps({
  width: {
    type: [Number, String],
    default: 20,
  },
  height: {
    type: [Number, String],
    default: 12,
  },
  className: {
    type: String,
    default: '',
  },
});
</script>

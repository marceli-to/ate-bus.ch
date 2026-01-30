<template>
  <div v-if="message" class="text-error text-sm mt-1">
    {{ message }}
  </div>
</template>

<script setup>
defineProps({
  message: {
    type: String,
    default: '',
  },
});
</script>

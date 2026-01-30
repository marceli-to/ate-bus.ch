<template>
  <div :class="classes">
    <slot />
  </div>
</template>

<script setup>
defineProps({
  classes: {
    type: String,
    default: 'grid grid-cols-1 md:grid-cols-2 gap-16 xl:gap-20',
  },
});
</script>

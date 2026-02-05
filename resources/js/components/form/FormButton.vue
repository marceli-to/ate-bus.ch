<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    class="inline-flex items-center justify-center pl-8 pr-28 py-8 md:pl-12 md:pr-36 md:py-10 font-medium transition-all duration-300 [clip-path:polygon(0%_0%,100%_0%,calc(100%-1.2rem)_100%,0%_100%)] md:[clip-path:polygon(0%_0%,100%_0%,calc(100%-1.6rem)_100%,0%_100%)] bg-blue-primary text-white hover:bg-blue-black disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed"
  >
    <span v-if="loading" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2"></span>
    <slot />
  </button>
</template>

<script setup>
defineProps({
  type: {
    type: String,
    default: 'button',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
});
</script>

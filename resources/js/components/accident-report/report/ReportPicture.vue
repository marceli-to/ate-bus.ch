<template>
  <img v-if="png" :src="png" :width="width" :height="height" :alt="alt" :style="`width:${width}px;height:${height}px;max-width:100%`" />
  <span v-else class="inline-block [&>svg]:w-full [&>svg]:h-auto" :style="`width:${width}px;max-width:100%`" v-html="svg"></span>
</template>

<script setup>
// Drawing in the report: SVG on screen, PNG in the mail (Outlook shows no SVG)
defineProps({
  svg: {
    type: String,
    required: true,
  },
  png: {
    type: String,
    default: '',
  },
  width: {
    type: Number,
    required: true,
  },
  height: {
    type: Number,
    required: true,
  },
  alt: {
    type: String,
    default: '',
  },
});
</script>

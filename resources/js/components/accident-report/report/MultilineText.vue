<template>
  <template v-for="(line, i) in lines" :key="i"><br v-if="i">{{ line }}</template>
</template>

<script setup>
import { computed } from 'vue';

// Line breaks as <br> (mail programs ignore white-space CSS)
const props = defineProps({
  text: {
    type: String,
    default: '',
  },
});

const lines = computed(() => props.text.split('\n'));
</script>

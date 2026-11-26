<template>
  <a :href="tel(number)" class="font-bold underline underline-offset-3 whitespace-nowrap">{{ number }}</a>
</template>

<script setup>
import { tel } from '../constants.js';

defineProps({
  number: {
    type: String,
    required: true,
  },
});
</script>

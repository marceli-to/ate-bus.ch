<template>
  <div class="flex justify-center p-10 bg-white border border-blue-primary/40 [&>svg]:w-200 [&>svg]:h-200" v-html="svg"></div>
</template>

<script setup>
import { computed } from 'vue';
import qrcode from 'qrcode-generator';

const props = defineProps({
  text: {
    type: String,
    required: true,
  },
});

const svg = computed(() => {
  const qr = qrcode(0, 'M');
  qr.addData(props.text);
  qr.make();
  return qr.createSvgTag({ cellSize: 4, margin: 1, scalable: true });
});
</script>

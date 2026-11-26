<template>
  <template v-if="items.length">
    <template v-for="(item, i) in items" :key="i">
      <span v-if="mail || !item.data" :style="CHIP">📎 {{ item.name }} (Anhang)</span>
      <img v-else :src="item.data" :alt="item.name" height="110" style="display:inline-block;height:110px;width:auto;margin:0 8px 8px 0;border:1px solid #d6deea" />
    </template>
  </template>
  <template v-else>–</template>
</template>

<script setup>
// Photos in the report: thumbnails on screen, «📎 name (Anhang)» in the mail (the photos are attachments)
defineProps({
  // [{ name, data }]: data = data URL, empty for photos already on the server (other party, QR flow)
  items: {
    type: Array,
    required: true,
  },
  mail: {
    type: Boolean,
    default: false,
  },
});

const CHIP = 'display:inline-block;padding:4px 8px;margin:2px;border:1px solid #ccccdd;font-size:12px';
</script>

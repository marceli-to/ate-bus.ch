<template>
  <tr v-if="label">
    <td width="36%" :style="`${CELL};width:36%;background:#f4f7fc;font-weight:bold;color:#333344`">{{ label }}</td>
    <td :style="CELL">
      <slot><MultilineText :text="value || '–'" /></slot>
    </td>
  </tr>
  <tr v-else>
    <td colspan="2" :style="`${CELL};background:#ffffff`">
      <slot><MultilineText :text="value || '–'" /></slot>
    </td>
  </tr>
</template>

<script setup>
import MultilineText from './MultilineText.vue';

// Label / value row; without a label the value spans both columns (free text, pictures)
defineProps({
  label: {
    type: String,
    default: '',
  },
  value: {
    type: String,
    default: '',
  },
});

const CELL = 'border:1px solid #d6deea;padding:6px 9px;vertical-align:top;font-size:13.5px;font-family:Arial,Helvetica,sans-serif';
</script>

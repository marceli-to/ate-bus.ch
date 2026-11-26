<template>
  <div style="margin-top:14px">
    <div :style="`background:${dark ? '#5c6b7a' : '#1355A1'};color:#ffffff;margin:0;padding:6px 10px;font-size:13px;font-weight:bold`">{{ title }}</div>
    <table width="100%" cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse">
      <tbody>
        <slot />
      </tbody>
    </table>
  </div>
</template>

<script setup>
// Section of the report. Inline styles only: the same markup becomes the mail body.
defineProps({
  title: {
    type: String,
    required: true,
  },
  // Party B / damaged object: grey heading
  dark: {
    type: Boolean,
    default: false,
  },
});
</script>

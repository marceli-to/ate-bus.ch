<template>
  <p>Danach {{ urgent ? 'sofort ' : '' }}das <b>Pikett</b> <PhoneLink :number="PHONE.standby" /> informieren.</p>
  <p class="bg-white p-8">
    Auf <b>VBZ-Linien (701–706)</b> zusätzlich die <b>VBZ-Leitstelle</b> <PhoneLink :number="PHONE.vbzControlCentre" /> informieren.
  </p>
</template>

<script setup>
import PhoneLink from './PhoneLink.vue';
import { PHONE } from '../constants.js';

// «Danach das Pikett … informieren» + VBZ control centre, used in all police boxes
defineProps({
  urgent: {
    type: Boolean,
    default: false,
  },
});
</script>

<template>
  <div class="space-y-24 md:space-y-32">
    <NoticeBox tone="success" :title="title">
      <p v-if="cardHandover">Das Büro erhält diese Vormeldung. Die vollständige Meldung kommt, wenn der Fahrgast das Formular ausfüllt.</p>
      <p v-else>Die Meldung wurde per E-Mail gesendet.</p>
    </NoticeBox>

    <NoticeBox v-if="!mailSent" tone="danger" title="E-Mail nicht gesendet">
      <p>Die Meldung ist bei uns gespeichert, aber die E-Mail konnte nicht gesendet werden.</p>
      <p v-if="state.type === 'passenger_self'">Bitte rufen Sie uns an: <PhoneLink :number="COMPANY.phone" /></p>
      <p v-else>Bitte das <b>Pikett</b> <PhoneLink :number="PHONE.standby" /> informieren.</p>
    </NoticeBox>

    <NoticeBox v-if="state.type === 'staff' && !isNearMiss(state)" tone="danger" title="Wichtig: SUVA-Nummer">
      <p>Bitte bei den behandelnden Ärztinnen und Ärzten <b>immer die SUVA-Nummer</b> bekannt geben. Du bekommst sie in den nächsten Tagen von der Personalabteilung.</p>
    </NoticeBox>

    <div class="bg-light-grey p-12 text-xs md:text-sm leading-relaxed break-words">
      <b>An:</b> {{ recipient(state, config) }}<template v-if="copy">; {{ copy }}</template><br>
      <b>Betreff:</b> {{ subject(state, config) }}
      <template v-if="state.type !== 'staff' && !cardHandover"><br><b>Anhänge:</b> {{ files.join(', ') || '–' }}</template>
    </div>

    <div class="bg-white p-12 md:p-20 shadow-sm overflow-x-auto">
      <AccidentReportDocument :state="state" :config="config" full />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import NoticeBox from './ui/NoticeBox.vue';
import PhoneLink from './ui/PhoneLink.vue';
import AccidentReportDocument from './report/AccidentReportDocument.vue';
import { useAccidentReport } from './context.js';
import { COMPANY, PHONE } from './constants.js';
import { isCardHandover, isNearMiss, usesQr } from './state.js';
import { attachments, copyTo, recipient, subject } from './report.js';

// After sending: confirmation, what was sent to whom, and the report
defineProps({
  mailSent: {
    type: Boolean,
    default: true,
  },
});

const { config, state } = useAccidentReport();

const cardHandover = computed(() => isCardHandover(state));
const copy = computed(() => (cardHandover.value ? '' : copyTo(state)));

const title = computed(() => {
  if (cardHandover.value) return '✓ Vormeldung gesendet';
  if (state.type === 'passenger_self') return '✓ Vielen Dank – Ihre Meldung ist bei uns eingegangen';
  return '✓ Meldung gesendet';
});

const files = computed(() => {
  const names = attachments(state).map((file) => file.name);
  if (usesQr(state)) names.push(...state.otherPartyPhotos);
  if (state.type === 'vehicle' && state.sketch.elements.length) names.push('Unfallskizze.png');
  return names;
});
</script>

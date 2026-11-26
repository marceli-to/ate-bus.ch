<template>
  <div class="space-y-32 md:space-y-40">
    <ReportCard :title="title">
      <template #subtitle>
        Geht an <b>{{ recipient(state, config) }}</b><template v-if="copy"> und {{ state.type === 'vehicle' ? '' : 'als Kopie an ' }}<b>{{ copy }}</b></template>.
      </template>
      <div class="bg-white p-12 md:p-20 shadow-sm overflow-x-auto">
        <AccidentReportDocument :state="state" :config="config" />
      </div>
    </ReportCard>

    <!-- Vehicle accident -->
    <ReportCard v-if="state.type === 'vehicle'" :title="bothSign ? 'Unterschriften' : 'Unterschrift'">
      <template #subtitle>
        {{ bothSign ? (state.otherKind === 'fleet' ? 'Beide Mitarbeitenden bestätigen' : 'Mitarbeiter/in und Unfallbeteiligter bestätigen') : 'Der/die Mitarbeiter/in bestätigt' }}
        die Angaben. Die Unterschrift ist <b>keine Schuldanerkennung</b>.
      </template>
      <SignaturePad v-model="state.signatureA" label="Mitarbeiter/in A – ATE Bus AG" required />
      <template v-if="bothSign">
        <div v-if="usesQr(state)" class="bg-white border border-blue-primary/40 p-12 text-xs md:text-sm text-blue-gray">
          Unfallbeteiligter B – auf eigenem Handy unterschrieben
          <img v-if="state.signatureB" :src="state.signatureB" alt="Unterschrift Unfallbeteiligter B" class="block max-h-90 mx-auto mt-6" />
          <p v-else class="mt-6">noch keine Unterschrift</p>
        </div>
        <SignaturePad
          v-else
          v-model="state.signatureB"
          :label="state.otherKind === 'fleet' ? 'Mitarbeiter/in B – ATE Bus AG' : 'Unfallbeteiligter B'"
          required
        />
      </template>
    </ReportCard>

    <!-- Passenger accident -->
    <ReportCard
      v-else-if="state.type === 'passenger' || state.type === 'passenger_self'"
      :title="state.type === 'passenger_self' ? 'Unterschrift' : 'Unterschriften'"
      subtitle="Die Abgabe dieser Unfall- / Schadenmeldung begründet weder einen Nachweis noch eine Haftung."
    >
      <SignaturePad v-model="state.passenger.signature" :label="state.type === 'passenger_self' ? 'Ihre Unterschrift (optional)' : 'Fahrgast (falls möglich)'" />
      <SignaturePad v-if="state.type === 'passenger'" v-model="state.signatureA" label="Mitarbeiter/in ATE Bus AG" required />
    </ReportCard>

    <!-- Staff accident -->
    <ReportCard v-else title="Unterschrift" subtitle="Ich bestätige, dass die Angaben vollständig und wahr sind.">
      <SignaturePad v-model="state.signatureEmployee" label="Mitarbeiter/in" required />
    </ReportCard>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import ReportCard from './ui/ReportCard.vue';
import SignaturePad from './ui/SignaturePad.vue';
import AccidentReportDocument from './report/AccidentReportDocument.vue';
import { useAccidentReport } from './context.js';
import { hasSecondVehicle, isAlone, usesQr } from './state.js';
import { copyTo, recipient } from './report.js';

// Last step: check the report and sign
const { config, state } = useAccidentReport();

const title = computed(() => ({
  passenger: 'Prüfen und unterschreiben',
  passenger_self: 'Prüfen und senden',
}[state.type] || 'Prüfen und unterschreiben'));

const copy = computed(() => copyTo(state));
const bothSign = computed(() => hasSecondVehicle(state) && !isAlone(state));
</script>

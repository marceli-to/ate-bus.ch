<template>
  <div class="space-y-32 md:space-y-40">
    <ReportCard title="Was haben die Fahrzeuge gemacht?">
      <ChipList v-model="state.maneuversA" :options="MANEUVERS" :label="`Fahrzeug A – ATE Bus AG${vA ? ` (${vA.label})` : ''}`" />
      <ChipList v-if="showB" v-model="state.maneuversB" :options="MANEUVERS" :label="labelB" :optional="together ? '' : 'soweit bekannt'" />
      <FormTextarea
        v-model="state.course"
        name="course"
        label="Hergang (kurz)"
        placeholder="Tipp: Mikrofon der Tastatur zum Diktieren benützen."
        :rows="4"
        :maxlength="5000"
      />
      <NoticeBox v-if="usesQr(state)" tone="success">
        <p>Manöver, Schaden und Aufprallpunkt von <b>Fahrzeug B</b> hat der Unfallbeteiligte auf seinem Handy erfasst.</p>
      </NoticeBox>
      <OptionButtons
        v-if="hasSecondVehicle(state) && together"
        v-model="state.causedBy"
        name="caused_by"
        label="Wer hat den Unfall verursacht?"
        :columns="3"
        size="sm"
        :options="causedByOptions"
      />
      <SketchEditor v-model="state.sketch" :vehicles="sketchVehicles" :with-vehicle-b="hasSecondVehicle(state)" />
    </ReportCard>

    <ReportCard title="Erster Aufprallpunkt" subtitle="Auf der Fahrzeugskizze auf den ersten Aufprallpunkt tippen – ein rotes X wird angezeigt. Die Fahrzeuge sind in Fahrtrichtung gezeichnet (vorne = oben).">
      <ImpactPicker v-model="state.impactA" :types="impactTypesA(state, config)" :title="`A – ${vA ? vA.label : 'ATE Bus AG'}`" />
      <ImpactPicker
        v-if="showB"
        v-model="state.impactB"
        party="B"
        :types="impactTypesB(state, config, VEHICLE_TYPES)"
        :title="impactTitleB"
        :hint="state.otherKind === 'fleet' || state.other.vehicleType ? '' : 'Art des Fahrzeugs unbekannt – beim passenden Typ tippen.'"
      />
    </ReportCard>

    <ReportCard title="Schäden">
      <div class="space-y-8">
        <ChipList v-model="state.damagesA" :options="DAMAGES" label="An Fahrzeug A (ATE)" />
        <FormInput v-model="state.damageNoteA" name="damage_note_a" label="Ergänzung" placeholder="z. B. rechts vorne" />
      </div>
      <div v-if="showB" class="space-y-8">
        <ChipList
          v-model="state.damagesB"
          :options="DAMAGES"
          :label="state.otherKind === 'fleet' ? 'An Fahrzeug B (ATE)' : 'Am fremden Fahrzeug'"
          :optional="together ? '' : 'soweit bekannt'"
        />
        <FormInput v-model="state.damageNoteB" name="damage_note_b" label="Ergänzung" />
      </div>
      <PhotoList v-model="state.photos" label="Fotos Unfallstelle und Schäden" optional="empfohlen" :max="config.maxFiles - VEHICLE_FIXED_FILES">
        <NoticeBox tone="info">
          <p>Bitte <b>mehrere Fotos</b> machen: alle <b>Schäden</b> (nah und mit etwas Abstand) und die <b>Unfallsituation</b> – Übersicht, Stellung der Fahrzeuge, Strasse und Umgebung.</p>
        </NoticeBox>
      </PhotoList>
    </ReportCard>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import FormInput from '../../form/FormInput.vue';
import FormTextarea from '../../form/FormTextarea.vue';
import ReportCard from '../ui/ReportCard.vue';
import OptionButtons from '../ui/OptionButtons.vue';
import ChipList from '../ui/ChipList.vue';
import NoticeBox from '../ui/NoticeBox.vue';
import PhotoList from '../ui/PhotoList.vue';
import ImpactPicker from '../ui/ImpactPicker.vue';
import SketchEditor from '../ui/SketchEditor.vue';
import { useAccidentReport } from '../context.js';
import { DAMAGES, MANEUVERS, VEHICLE_FIXED_FILES, VEHICLE_TYPES } from '../constants.js';
import { hasSecondVehicle, isAlone, usesQr } from '../state.js';
import { impactTypesA, impactTypesB, vehicleA, vehicleB } from '../report.js';

// Vehicle accident, step «Hergang und Schaden»
const { config, state } = useAccidentReport();

const vA = computed(() => vehicleA(state, config));
const together = computed(() => !isAlone(state));
// Vehicle B is filled in here unless the other party did it on their own phone
const showB = computed(() => hasSecondVehicle(state) && !usesQr(state));

const labelB = computed(() => {
  if (state.otherKind === 'fleet') {
    const vB = vehicleB(state, config);
    return `Fahrzeug B – ATE Bus AG${vB ? ` (${vB.label})` : ''}`;
  }
  return 'Fahrzeug B – Unfallbeteiligter';
});

const impactTitleB = computed(() => {
  if (state.otherKind === 'fleet') return `B – ${vehicleB(state, config)?.label || 'ATE Bus AG'}`;
  return `B – Unfallbeteiligter${together.value ? '' : ' (soweit bekannt)'}`;
});

const causedByOptions = computed(() => (state.otherKind === 'fleet'
  ? [{ value: 'ate', label: 'Mitarbeiter/in A' }, { value: 'other', label: 'Mitarbeiter/in B' }, { value: 'both', label: 'Beide' }]
  : [{ value: 'ate', label: 'Unser/e Mitarbeiter/in' }, { value: 'other', label: 'Unfallbeteiligter' }, { value: 'both', label: 'Beide' }]));

const sketchVehicles = computed(() => ({
  busA: state.vehicleKind === 'bus',
  busB: state.otherKind === 'fleet' && state.secondVehicleKind === 'bus',
}));
</script>

<template>
  <ReportCard v-if="isNearMiss(state)" title="Sachschaden" subtitle="Beinahe-Unfall: niemand ist verletzt.">
    <FormInput v-model="state.staffPropertyDamage" name="staff_property_damage" label="Sachschaden (falls vorhanden)" placeholder="Art des Schadens, geschätzte Kosten" />
  </ReportCard>

  <ReportCard v-else title="Verletzung">
    <div class="space-y-8">
      <FormLabel>Verletzte Körperteile <span class="font-normal text-blue-gray">antippen, mehrere möglich</span></FormLabel>
      <BodyPicker v-model="state.injuredParts" />
      <FormInput v-model="state.injuredPartsNote" name="injured_parts_note" label="Ergänzung" placeholder="z. B. Finger, Zehe" />
    </div>
    <div class="space-y-8">
      <ChipList v-model="state.injuryKinds" :options="INJURY_KINDS" label="Art der Verletzung" />
      <FormInput v-model="state.injuryNote" name="injury_note" label="Ergänzung" />
    </div>
    <FormInput v-model="state.firstAid" name="first_aid" label="Erstbehandlung durch (Arzt / Ärztin, Spital)" placeholder="Name und Ort, oder «noch nicht»" />
    <FormInput v-model="state.staffPropertyDamage" name="staff_property_damage" label="Sachschaden (falls vorhanden)" placeholder="z. B. Brille, Kleider" />
  </ReportCard>
</template>

<script setup>
import FormInput from '../../form/FormInput.vue';
import FormLabel from '../../form/FormLabel.vue';
import ReportCard from '../ui/ReportCard.vue';
import ChipList from '../ui/ChipList.vue';
import BodyPicker from '../ui/BodyPicker.vue';
import { useAccidentReport } from '../context.js';
import { INJURY_KINDS } from '../constants.js';
import { isNearMiss } from '../state.js';

// Staff accident, step «Verletzung» (or «Sachschaden» for a near miss)
const { state } = useAccidentReport();
</script>

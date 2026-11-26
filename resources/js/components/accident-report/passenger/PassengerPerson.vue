<template>
  <ReportCard :title="self ? 'Ihre Angaben' : 'Angaben zum Fahrgast'">
    <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
      <FormInput v-model="p.lastName" name="passenger_last_name" label="Familienname" />
      <FormInput v-model="p.firstName" name="passenger_first_name" label="Vorname" />
    </FormGrid>
    <FormInput v-model="p.street" name="passenger_street" label="Strasse" />
    <FormInput v-model="p.town" name="passenger_town" label="PLZ / Ort" />
    <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
      <FormInput v-model="p.phone" name="passenger_phone" label="Telefon" type="tel" placeholder="079 123 45 67" />
      <FormInput v-model="p.email" name="passenger_email" label="E-Mail (erhält Kopie)" type="email" />
    </FormGrid>
    <FormInput v-model="p.birthDate" name="passenger_birth_date" label="Geburtsdatum (optional)" type="date" :max="today()" />
    <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
      <FormInput v-model="p.occupation" name="passenger_occupation" label="Beruf (optional)" />
      <FormInput v-model="p.employer" name="passenger_employer" label="Arbeitgeber (optional)" />
    </FormGrid>
  </ReportCard>
</template>

<script setup>
import { computed } from 'vue';
import FormInput from '../../form/FormInput.vue';
import FormGrid from '../../form/FormGrid.vue';
import ReportCard from '../ui/ReportCard.vue';
import { useAccidentReport } from '../context.js';
import { today } from '../state.js';

// Passenger accident, step «Fahrgast» / «Ihre Angaben»
const { state } = useAccidentReport();

const p = state.passenger;
const self = computed(() => state.type === 'passenger_self');
</script>

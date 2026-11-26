<template>
  <ReportCard title="Angaben zur Person">
    <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
      <FormInput v-model="e.lastName" name="employee_last_name" label="Familienname" />
      <FormInput v-model="e.firstName" name="employee_first_name" label="Vorname" />
    </FormGrid>
    <OptionButtons v-model="e.role" name="employee_role" label="Funktion" size="sm" :options="ROLES" />
    <FormInput v-model="e.street" name="employee_street" label="Strasse und Hausnummer" />
    <FormInput v-model="e.town" name="employee_town" label="PLZ und Ort" placeholder="8307 Effretikon" />
    <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
      <FormInput v-model="e.phone" name="employee_phone" label="Handy" type="tel" placeholder="079 123 45 67" />
      <FormInput v-model="e.email" name="employee_email" label="E-Mail" type="email" />
    </FormGrid>
    <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
      <FormInput v-model="e.birthDate" name="employee_birth_date" label="Geburtsdatum" type="date" :max="today()" />
      <FormInput v-model="e.employedSince" name="employee_employed_since" label="Angestellt seit" type="date" :max="today()" />
    </FormGrid>
    <OptionButtons v-model="e.civilStatus" name="employee_civil_status" label="Zivilstand" :columns="3" size="sm" :options="CIVIL_STATUS" />
    <FormInput v-model="e.nationality" name="employee_nationality" label="Staatsangehörigkeit" placeholder="z. B. Schweiz" />
    <p class="text-xs md:text-sm text-blue-gray">Diese Angaben werden nur auf diesem Handy gespeichert und beim nächsten Mal vorausgefüllt.</p>
  </ReportCard>
</template>

<script setup>
import FormInput from '../../form/FormInput.vue';
import FormGrid from '../../form/FormGrid.vue';
import ReportCard from '../ui/ReportCard.vue';
import OptionButtons from '../ui/OptionButtons.vue';
import { useAccidentReport } from '../context.js';
import { CIVIL_STATUS, STAFF_ROLES } from '../constants.js';
import { today } from '../state.js';

// Staff accident, step «Person»
const { state } = useAccidentReport();

const e = state.employee;
const ROLES = STAFF_ROLES.map((value) => ({ value, label: value === 'Büro / Disposition' ? 'Büro / Dispo' : value }));
</script>

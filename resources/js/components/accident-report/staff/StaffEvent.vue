<template>
  <div class="space-y-32 md:space-y-40">
    <ReportCard title="Art des Ereignisses">
      <OptionButtons v-model="state.eventKind" name="event_kind" :columns="1" align="left" :options="STAFF_EVENT_KINDS" />
      <p class="text-xs md:text-sm text-blue-gray">
        Berufsunfall = während der Arbeit · Nichtberufsunfall = Arbeitsweg oder Freizeit · Beinahe-Unfall = es hätte passieren können, niemand ist verletzt.
      </p>
    </ReportCard>

    <ReportCard title="Wann und wo?">
      <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
        <FormInput v-model="state.eventDate" name="event_date" label="Datum" type="date" :max="today()" />
        <FormInput v-model="state.eventTime" name="event_time" label="Uhrzeit" type="time" />
      </FormGrid>
      <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
        <FormInput v-model="state.lastWorkDay" name="last_work_day" label="Letzter Arbeitstag vor dem Ereignis" type="date" :max="today()" />
        <FormInput v-model="state.workEnd" name="work_end" label="Arbeitsende an diesem Tag" type="time" />
      </FormGrid>
      <OptionButtons v-model="state.lastDuty" name="last_duty" label="Letzter Dienst" size="sm" :options="LAST_DUTY" />
      <FormInput v-if="state.lastDuty === 'vbg' || state.lastDuty === 'vbz'" v-model="state.dutyNumber" name="duty_number" label="Dienstnummer" placeholder="z. B. 1204" />
      <FormInput v-model="state.location" name="location" label="Ort" placeholder="z. B. ATE Bus AG, Bietenholzstrasse 30, Effretikon" />
      <FormInput v-model="state.spot" name="spot" label="Stelle" placeholder="z. B. Waschanlage, Tankstelle, im Bus, Treppe" />
    </ReportCard>

    <ReportCard title="Was ist passiert?">
      <FormInput v-model="state.activity" name="activity" label="Tätigkeit zur Zeit des Ereignisses" placeholder="z. B. Aussenspiegel montieren, Bus tanken, aussteigen" />
      <FormTextarea
        v-model="state.description"
        name="description"
        label="Beschreibung"
        placeholder="Was ist genau passiert? Beteiligte Gegenstände, Fahrzeuge … Tipp: Mikrofon der Tastatur zum Diktieren benützen."
        :rows="4"
        :maxlength="5000"
      />
      <PhotoList v-model="state.staffPhotos" label="Fotos oder Skizze" optional="optional" :max="config.maxFiles" />
      <FormInput v-model="state.prevention" name="prevention" label="Wie hätte man es verhindern können? (optional)" placeholder="Deine Idee hilft, dass es nicht wieder passiert" />
    </ReportCard>

    <WitnessList />
  </div>
</template>

<script setup>
import FormInput from '../../form/FormInput.vue';
import FormTextarea from '../../form/FormTextarea.vue';
import FormGrid from '../../form/FormGrid.vue';
import ReportCard from '../ui/ReportCard.vue';
import OptionButtons from '../ui/OptionButtons.vue';
import PhotoList from '../ui/PhotoList.vue';
import WitnessList from '../ui/WitnessList.vue';
import { useAccidentReport } from '../context.js';
import { LAST_DUTY, STAFF_EVENT_KINDS } from '../constants.js';
import { today } from '../state.js';

// Staff accident, step «Ereignis»
const { config, state } = useAccidentReport();
</script>

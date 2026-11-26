<template>
  <div class="space-y-24 md:space-y-32">
    <ReportCard title="Was ist passiert?" subtitle="Bitte die Art des Unfalls wählen.">
      <OptionButtons
        :model-value="null"
        name="type"
        :columns="1"
        align="left"
        :options="[
          { value: 'vehicle', label: 'Unfall Fahrzeug', description: 'Kollision, Sachschaden mit Bus oder PW' },
          { value: 'passenger', label: 'Unfall Fahrgast', description: 'Sturz oder Verletzung im Bus oder beim Ein- / Aussteigen' },
          { value: 'staff', label: 'Unfall Mitarbeiter/in', description: 'Berufs-, Nichtberufs- oder Beinahe-Unfall' },
        ]"
        @update:model-value="(type) => $emit('pick', type)"
      />
    </ReportCard>

    <div class="text-center space-y-8">
      <p class="text-xs md:text-sm text-blue-gray">Bei Verletzten zuerst Sanität <b>144</b> / Polizei <b>117</b> alarmieren.</p>
      <div class="grid grid-cols-2 gap-8 max-w-[22rem] mx-auto">
        <a :href="tel(PHONE.ambulance)" class="py-12 bg-error text-white font-bold text-center">144 Sanität</a>
        <a :href="tel(PHONE.police)" class="py-12 bg-error text-white font-bold text-center">117 Polizei</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import ReportCard from './ui/ReportCard.vue';
import OptionButtons from './ui/OptionButtons.vue';
import { PHONE, tel } from './constants.js';

// Staff page start: which kind of accident
defineEmits(['pick']);
</script>

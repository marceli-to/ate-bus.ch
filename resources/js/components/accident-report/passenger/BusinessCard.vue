<template>
  <ReportCard
    title="Visitenkarte abgeben"
    subtitle="Dem Fahrgast die Visitenkarte «Unfallmeldung Fahrgast» geben. Er füllt das Formular zu Hause selbst aus – inkl. Angaben zur Fahrt."
  >
    <!-- What the card looks like -->
    <div class="flex items-center gap-10 max-w-[22.5rem] mx-auto aspect-[85/55] p-12 bg-white border border-dark-grey shadow-md">
      <div class="flex-1 flex flex-col gap-y-4 text-[11px] text-blue-gray">
        <img :src="logo" alt="ATE Bus AG" class="w-90" />
        <b class="text-sm text-blue-primary">Unfall im Bus?</b>
        <span>Bitte melden Sie uns den Unfall online. Scannen Sie den QR-Code oder öffnen Sie <u>ate-bus.ch/unfall-fahrgast</u></span>
        <small class="text-[9px]">ATE Bus AG · 052 355 12 00 · info@ate-bus.ch</small>
      </div>
      <div class="w-110 [&>div]:p-0 [&>div]:border-0 [&_svg]:!w-110 [&_svg]:!h-110">
        <QrCode :text="config.passengerUrl" />
      </div>
    </div>

    <CheckField v-model="state.cardHandedOver" label="Visitenkarte abgegeben" />
    <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
      <FormInput v-model="state.cardPassengerName" name="card_passenger_name" label="Name Fahrgast" placeholder="Name, Vorname" />
      <FormInput v-model="state.cardPassengerPhone" name="card_passenger_phone" label="Handy Fahrgast" type="tel" placeholder="079 123 45 67" />
    </FormGrid>
    <p class="text-xs md:text-sm text-blue-gray">Für die Zuordnung und damit wir den Fahrgast erreichen, falls er das Formular nicht ausfüllt.</p>
  </ReportCard>
</template>

<script setup>
import FormInput from '../../form/FormInput.vue';
import FormGrid from '../../form/FormGrid.vue';
import ReportCard from '../ui/ReportCard.vue';
import CheckField from '../ui/CheckField.vue';
import QrCode from '../ui/QrCode.vue';
import logo from '../report/ate-logo.jpg';
import { useAccidentReport } from '../context.js';

// Passenger accident, no time on site: hand over the business card (pre-report to the office)
const { config, state } = useAccidentReport();
</script>

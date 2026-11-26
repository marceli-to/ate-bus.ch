<template>
  <div class="space-y-32 md:space-y-40">
    <ReportCard title="Wann und wo?">
      <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
        <FormInput v-model="state.date" name="date" label="Datum" type="date" :max="today()" />
        <FormInput v-model="state.time" name="time" label="Zeit" type="time" />
      </FormGrid>

      <LinePicker />

      <OptionButtons v-model="state.placeType" name="place_type" label="Unfallort" :columns="2" :options="PLACE_TYPES" />

      <StopSelect v-if="state.placeType === 'stop'" />

      <template v-if="state.placeType">
        <FormGrid :classes="state.placeType === 'junction' ? 'grid grid-cols-2 gap-8 md:gap-12' : 'grid grid-cols-1'">
          <FormInput
            v-model="state.street1"
            name="street1"
            :label="streetLabel"
            :placeholder="state.placeType === 'car_park' ? 'z. B. Parkplatz Coop, Rikonerstrasse 5' : ''"
          />
          <FormInput v-if="state.placeType === 'junction'" v-model="state.street2" name="street2" label="Strasse 2" />
        </FormGrid>
        <FormInput v-model="state.town" name="town" label="Ortschaft" />
      </template>
    </ReportCard>

    <ReportCard title="Unser Fahrzeug">
      <VehiclePicker v-model="state.vehicleId" v-model:kind="state.vehicleKind" name="vehicle" />
      <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
        <FormInput v-model="state.driverLastName" name="driver_last_name" label="Familienname" />
        <FormInput v-model="state.driverFirstName" name="driver_first_name" label="Vorname" />
      </FormGrid>
      <p class="text-xs md:text-sm text-blue-gray">Wird auf diesem Handy für die nächste Meldung gespeichert.</p>
    </ReportCard>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import FormInput from '../../form/FormInput.vue';
import FormGrid from '../../form/FormGrid.vue';
import ReportCard from '../ui/ReportCard.vue';
import OptionButtons from '../ui/OptionButtons.vue';
import LinePicker from '../ui/LinePicker.vue';
import StopSelect from '../ui/StopSelect.vue';
import VehiclePicker from '../ui/VehiclePicker.vue';
import { useAccidentReport } from '../context.js';
import { PLACE_TYPES } from '../constants.js';
import { today } from '../state.js';

// Vehicle accident, step «Ort und Fahrzeug»
const { state } = useAccidentReport();

const streetLabel = computed(() => ({
  junction: 'Strasse 1',
  car_park: 'Parkplatz (Name oder Adresse)',
  stop: 'Strasse (optional)',
}[state.placeType] || 'Strasse'));
</script>

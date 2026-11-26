<template>
  <div class="space-y-32 md:space-y-40">
    <ReportCard :title="self ? 'Ihre Fahrt' : 'Wann und wo?'" :subtitle="self ? 'Danke, dass Sie uns den Unfall melden. Das Ausfüllen dauert etwa 5 Minuten.' : ''">
      <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
        <FormInput v-model="state.date" name="date" :label="self ? 'Datum des Unfalls' : 'Datum'" type="date" :max="today()" />
        <FormInput v-model="state.time" name="time" :label="self ? 'Zeit (ungefähr)' : 'Zeit'" type="time" />
      </FormGrid>

      <LinePicker :self-report="self" />
      <StopSelect :label="self ? 'Haltestelle (wo der Unfall passiert ist bzw. die nächste)' : 'Haltestelle (Unfallort bzw. nächste Haltestelle)'" />

      <div class="space-y-8">
        <ChipList v-model="state.where" :options="PASSENGER_WHERE" label="Wo genau?" />
        <FormInput v-model="state.whereNote" name="where_note" label="Ergänzung" :placeholder="self ? '' : 'z. B. hintere Tür, Treppenstufe'" />
      </div>

      <OptionButtons
        v-model="state.busState"
        name="bus_state"
        label="Der Bus …"
        :options="[{ value: 'stopped', label: 'stand still' }, { value: 'moving', label: 'fuhr' }]"
      />
      <ChipList v-if="state.busState === 'moving'" v-model="state.busMovements" :options="BUS_MOVEMENTS" label="Was machte der Bus?" optional="mehrere möglich" />

      <FormInput v-if="self" v-model="state.busNumber" name="bus_number" label="Busnummer (falls bekannt)" placeholder="steht im Bus vorne / an der Tür" />
    </ReportCard>

    <template v-if="!self">
      <ReportCard title="Bus und Mitarbeiter/in">
        <VehiclePicker v-model="state.vehicleId" kind="bus" :with-kind="false" name="vehicle" details="" />
        <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
          <FormInput v-model="state.driverLastName" name="driver_last_name" label="Familienname" />
          <FormInput v-model="state.driverFirstName" name="driver_first_name" label="Vorname" />
        </FormGrid>
        <p class="text-xs md:text-sm text-blue-gray">Wird auf diesem Handy für die nächste Meldung gespeichert.</p>
      </ReportCard>

      <ReportCard title="Wer füllt die Angaben zum Fahrgast aus?">
        <OptionButtons
          v-model="state.whoFills"
          name="who_fills"
          :columns="1"
          align="left"
          :options="[
            { value: 'now', label: 'Genügend Zeit: jetzt mit dem Fahrgast ausfüllen' },
            { value: 'card', label: 'Keine Zeit: Visitenkarte mit QR-Code abgeben' },
          ]"
        />
      </ReportCard>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import FormInput from '../../form/FormInput.vue';
import FormGrid from '../../form/FormGrid.vue';
import ReportCard from '../ui/ReportCard.vue';
import OptionButtons from '../ui/OptionButtons.vue';
import ChipList from '../ui/ChipList.vue';
import LinePicker from '../ui/LinePicker.vue';
import StopSelect from '../ui/StopSelect.vue';
import VehiclePicker from '../ui/VehiclePicker.vue';
import { useAccidentReport } from '../context.js';
import { BUS_MOVEMENTS, PASSENGER_WHERE } from '../constants.js';
import { today } from '../state.js';

// Passenger accident, first step: the trip (staff on site, or the passenger at home)
const { state } = useAccidentReport();

const self = computed(() => state.type === 'passenger_self');
</script>

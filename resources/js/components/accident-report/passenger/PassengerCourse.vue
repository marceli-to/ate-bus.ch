<template>
  <div class="space-y-32 md:space-y-40">
    <ReportCard title="Was ist passiert?">
      <ChipList v-model="state.accidentKinds" :options="PASSENGER_ACCIDENT_KINDS" />
      <FormTextarea
        v-model="state.passengerCourse"
        name="passenger_course"
        label="Hergang"
        placeholder="Kurz beschreiben. Tipp: Mikrofon der Tastatur zum Diktieren benützen."
        :rows="4"
        :maxlength="5000"
      />
      <OptionButtons
        v-model="state.noticed"
        name="noticed"
        :label="self ? 'Hat die Fahrerin / der Fahrer den Unfall bemerkt?' : 'Hat der/die Mitarbeiter/in den Unfall bemerkt?'"
        :options="YES_NO"
      />
      <OptionButtons
        v-model="state.reported"
        name="reported"
        :label="self ? 'Haben Sie den Unfall der Fahrerin / dem Fahrer gemeldet?' : 'Hat der Fahrgast den Unfall dem/der Mitarbeiter/in gemeldet?'"
        :options="YES_NO"
      />
    </ReportCard>

    <WitnessList />

    <ReportCard title="Folgen" hint="soweit bekannt">
      <div class="space-y-8">
        <FormLabel>Verletzte Körperteile <span class="font-normal text-blue-gray">antippen, mehrere möglich</span></FormLabel>
        <BodyPicker v-model="state.bodyParts" />
        <FormInput v-model="state.consequences" name="consequences" label="Art der Verletzung" placeholder="z. B. Prellung, Schürfung" />
      </div>
      <FormInput v-model="state.doctor" name="doctor" label="Arzt / Spital" />
      <OptionButtons
        v-model="state.unableToWork"
        name="unable_to_work"
        label="Arbeitsunfähig?"
        :columns="3"
        size="sm"
        :options="UNABLE_TO_WORK"
      />
      <FormInput v-if="state.unableToWork === 'yes'" v-model="state.unableToWorkDuration" name="unable_to_work_duration" label="Dauer" placeholder="z. B. 1 Woche" />
      <FormInput v-model="state.insurance" name="insurance" label="Eigene Versicherung (Krankenkasse / Unfallversicherung)" />
      <FormInput v-model="state.propertyDamage" name="property_damage" label="Sachschaden" placeholder="z. B. Brille, Handy, Kleider" />
      <PhotoList v-model="state.photos" label="Fotos" optional="optional" :max="config.maxFiles" />
      <FormInput v-model="state.remarks" name="remarks" label="Bemerkungen" />
    </ReportCard>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import FormInput from '../../form/FormInput.vue';
import FormTextarea from '../../form/FormTextarea.vue';
import FormLabel from '../../form/FormLabel.vue';
import ReportCard from '../ui/ReportCard.vue';
import OptionButtons from '../ui/OptionButtons.vue';
import ChipList from '../ui/ChipList.vue';
import BodyPicker from '../ui/BodyPicker.vue';
import PhotoList from '../ui/PhotoList.vue';
import WitnessList from '../ui/WitnessList.vue';
import { useAccidentReport } from '../context.js';
import { PASSENGER_ACCIDENT_KINDS, UNABLE_TO_WORK } from '../constants.js';

// Passenger accident, step «Hergang und Folgen»
const { config, state } = useAccidentReport();

const self = computed(() => state.type === 'passenger_self');
const YES_NO = [{ value: true, label: 'Ja' }, { value: false, label: 'Nein' }];
</script>

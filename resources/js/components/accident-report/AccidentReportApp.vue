<template>
  <div ref="top" class="scroll-mt-24">
    <h1 class="text-2xl md:text-3xl xl:text-4xl font-medium mb-16 md:mb-24">{{ heading }}</h1>

    <OtherPartyForm v-if="config.mode === 'other_party'" :config="config" />

    <template v-else>
      <!-- Progress -->
      <div v-if="state.type && !done" class="mb-24 md:mb-32">
        <div class="flex gap-4">
          <div v-for="(_, i) in steps" :key="i" class="flex-1 h-6" :class="i <= step ? 'bg-blue-primary' : 'bg-light-blue'"></div>
        </div>
        <p class="mt-8 text-xs md:text-sm text-blue-gray">{{ typeLabel }} · Schritt {{ step + 1 }} von {{ steps.length }}: {{ steps[step] }}</p>
      </div>

      <TypePicker v-if="!state.type" @pick="pickType" />
      <DoneStep v-else-if="done" :mail-sent="mailSent" />
      <component :is="stepComponent" v-else :key="`${state.type}-${step}`" />

      <div v-if="state.type" class="mt-24 space-y-16">
        <NoticeBox v-if="sending" tone="warning"><p>Die Meldung wird gesendet – bitte warten und die Seite nicht schliessen.</p></NoticeBox>
        <NoticeBox v-else-if="sendError" tone="danger" title="Nicht gesendet">
          <p>{{ sendError }}</p>
          <p>
            Die Angaben bleiben erhalten. Bitte nochmals auf «Senden» tippen.
            <template v-if="state.type !== 'passenger_self'">Notfalls das Pikett <PhoneLink :number="PHONE.standby" /> anrufen.</template>
          </p>
        </NoticeBox>
        <p v-else-if="message" class="text-error text-sm" role="alert">{{ message }}</p>

        <!-- Navigation -->
        <div class="sticky bottom-0 z-10 flex gap-8 py-12 bg-white border-t border-light-blue">
          <button v-if="done" type="button" :class="SECONDARY" @click="restart">Neue Meldung</button>
          <template v-else>
            <button v-if="!(state.type === 'passenger_self' && step === 0)" type="button" :class="SECONDARY" :disabled="sending" @click="back">Zurück</button>
            <FormButton v-if="showNext" type="button" :loading="sending" :disabled="sending" @click="next">{{ nextLabel }}</FormButton>
          </template>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import FormButton from '../form/FormButton.vue';
import NoticeBox from './ui/NoticeBox.vue';
import PhoneLink from './ui/PhoneLink.vue';
import TypePicker from './TypePicker.vue';
import ReviewStep from './ReviewStep.vue';
import DoneStep from './DoneStep.vue';
import OtherPartyForm from './OtherPartyForm.vue';
import VehicleTriage from './vehicle/VehicleTriage.vue';
import VehiclePlace from './vehicle/VehiclePlace.vue';
import VehicleParties from './vehicle/VehicleParties.vue';
import VehicleCourse from './vehicle/VehicleCourse.vue';
import PassengerTrip from './passenger/PassengerTrip.vue';
import PassengerPerson from './passenger/PassengerPerson.vue';
import PassengerCourse from './passenger/PassengerCourse.vue';
import BusinessCard from './passenger/BusinessCard.vue';
import StaffPerson from './staff/StaffPerson.vue';
import StaffEvent from './staff/StaffEvent.vue';
import StaffInjury from './staff/StaffInjury.vue';
import { provideAccidentReport } from './context.js';
import { createState, rememberDriver, rememberEmployee, stepsFor, triageComplete } from './state.js';
import { validateStep } from './validation.js';
import { sendReport } from './send.js';
import { useOtherPartySession } from './otherPartySession.js';
import { PHONE } from './constants.js';

// Accident report (Unfallmeldung). Rebuilt from the approved prototype of ATE Bus AG:
// same steps, questions and report; site design. Modes: staff, passenger (QR on the business card), other_party.
const props = defineProps({
  config: {
    type: Object,
    required: true,
  },
});

const config = props.config;
const state = createState();
provideAccidentReport(config, state);

if (config.mode === 'passenger') {
  state.type = 'passenger_self';
  state.time = '';
}

useOtherPartySession(state, config);

const SECONDARY = 'px-16 py-8 md:py-10 font-medium bg-light-blue text-blue-black hover:bg-blue-primary/20 cursor-pointer disabled:opacity-50';

const STEP_COMPONENTS = {
  vehicle: [VehicleTriage, VehiclePlace, VehicleParties, VehicleCourse, ReviewStep],
  staff: [StaffPerson, StaffEvent, StaffInjury, ReviewStep],
  passenger: [PassengerTrip, PassengerPerson, PassengerCourse, ReviewStep],
  passenger_card: [PassengerTrip, BusinessCard],
  passenger_self: [PassengerTrip, PassengerPerson, PassengerCourse, ReviewStep],
};

const TYPE_LABELS = {
  vehicle: 'Unfall Fahrzeug',
  staff: 'Unfall Mitarbeiter/in',
  passenger: 'Unfall Fahrgast',
  passenger_self: 'Unfallmeldung Fahrgast',
};

const top = ref(null);
const step = ref(0);
const done = ref(false);
const mailSent = ref(true);
const sending = ref(false);
const sendError = ref('');
const message = ref('');

const steps = computed(() => stepsFor(state));
const typeLabel = computed(() => TYPE_LABELS[state.type]);
const heading = computed(() => (config.mode === 'other_party' ? 'Unfallmeldung – Fahrzeug B' : state.type === 'passenger_self' ? 'Unfallmeldung Fahrgast' : 'Unfallmeldung'));

const stepComponent = computed(() => {
  const key = state.type === 'passenger' && state.whoFills === 'card' ? 'passenger_card' : state.type;
  return STEP_COMPONENTS[key][step.value];
});

const isLast = computed(() => step.value === steps.value.length - 1);

// Vehicle accident: no «Weiter» while the answers lead to «call the police» / «paper protocol»
const showNext = computed(() => !(state.type === 'vehicle' && step.value === 0 && !triageComplete(state)));

const nextLabel = computed(() => {
  if (isLast.value) return state.type === 'passenger_self' ? 'Meldung senden' : 'Meldung absenden';
  if (step.value === steps.value.length - 2 && steps.value.at(-1).startsWith('Prüfen')) return 'Prüfen';
  return 'Weiter';
});

const scrollTop = async () => {
  await nextTick();
  top.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

watch(step, () => {
  message.value = '';
  sendError.value = '';
  scrollTop();
});

const pickType = (type) => {
  state.type = type;
  if (type === 'passenger') state.vehicleKind = 'bus';
  step.value = 0;
  scrollTop();
};

const back = () => {
  if (step.value === 0) {
    state.type = '';
    message.value = '';
    scrollTop();
    return;
  }
  step.value -= 1;
};

const next = async () => {
  message.value = validateStep(state, config, step.value);
  if (message.value) return;

  // Names stay on this phone for the next report
  if ((state.type === 'vehicle' && step.value === 1) || (state.type === 'passenger' && step.value === 0)) rememberDriver(state);
  if (state.type === 'staff' && step.value === 0) rememberEmployee(state);

  if (!isLast.value) {
    step.value += 1;
    return;
  }

  sending.value = true;
  sendError.value = '';
  try {
    const result = await sendReport(state, config);
    mailSent.value = result.mail_sent !== false;
    done.value = true;
    scrollTop();
  } catch (error) {
    sendError.value = error.message;
  } finally {
    sending.value = false;
  }
};

const restart = () => window.location.reload();

// Do not lose a half-filled report by closing the tab
const warnBeforeLeaving = (event) => {
  const started = config.mode === 'staff' ? !!state.type : step.value > 0;
  if (started && !done.value) event.preventDefault();
};
onMounted(() => window.addEventListener('beforeunload', warnBeforeLeaving));
onBeforeUnmount(() => window.removeEventListener('beforeunload', warnBeforeLeaving));
</script>

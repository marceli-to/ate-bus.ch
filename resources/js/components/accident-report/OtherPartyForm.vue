<template>
  <div class="space-y-32 md:space-y-40">
    <NoticeBox v-if="status === 'invalid'" tone="danger" title="Code nicht gefunden">
      <p>Dieser Code ist ungültig oder abgelaufen. Bitte den QR-Code nochmals scannen oder den Code bei der Fahrerin / beim Fahrer erfragen.</p>
      <p><a href="/b" class="underline font-medium">Code von Hand eingeben</a></p>
    </NoticeBox>

    <NoticeBox v-else-if="status === 'done'" tone="success" title="✓ Vielen Dank">
      <p>Ihre Angaben sind bereits bei uns eingegangen. Vielen Dank!</p>
    </NoticeBox>

    <NoticeBox v-else-if="status === 'sent'" tone="success" title="✓ Vielen Dank">
      <p>Ihre Angaben wurden an die Fahrerin / den Fahrer der ATE Bus AG übermittelt. Sie erhalten die vollständige Unfallmeldung per E-Mail an <b>{{ form.email }}</b>.</p>
    </NoticeBox>

    <template v-else>
      <NoticeBox tone="success" title="Fahrzeug B – Ihre Angaben">
        <p>
          Unfall vom {{ formatDate(info.date) }}, {{ info.time }} Uhr mit {{ info.vehicle || 'einem Fahrzeug' }} der ATE Bus AG.
          Bitte füllen Sie Ihren Teil aus – das dauert etwa 3 Minuten.
        </p>
      </NoticeBox>

      <ReportCard title="Ihr Fahrzeug">
        <OptionButtons
          v-model="form.vehicleType"
          name="vehicle_type"
          label="Art des Fahrzeugs"
          :columns="3"
          size="sm"
          :options="VEHICLE_TYPES.map((value) => ({ value, label: VEHICLE_TYPE_NAMES[value] }))"
        />
        <div class="grid grid-cols-2 gap-8 md:gap-12">
          <PhotoField v-model="form.licenceFront" label="Führerausweis Vorderseite" />
          <PhotoField v-model="form.licenceBack" label="Führerausweis Rückseite" />
        </div>
        <PhotoField v-model="form.registration" label="Fahrzeugausweis" />
        <FormInput v-model="form.phone" name="phone" label="Ihre Handynummer" type="tel" placeholder="079 123 45 67" />
        <OptionButtons
          v-model="form.ownerIsDriver"
          name="owner_is_driver"
          label="Sind Sie auch Halter/in des Fahrzeugs?"
          :options="[{ value: true, label: 'Ja' }, { value: false, label: 'Nein' }]"
        />
        <FormInput v-if="!form.ownerIsDriver" v-model="form.ownerPhone" name="owner_phone" label="Telefon Halter/in" type="tel" />
        <div>
          <FormInput v-model="form.email" name="email" label="Ihre E-Mail" type="email" placeholder="name@beispiel.ch" />
          <p class="mt-4 text-xs md:text-sm text-blue-gray">Sie erhalten eine Kopie der Meldung.</p>
        </div>
      </ReportCard>

      <ReportCard title="Was hat Ihr Fahrzeug gemacht?">
        <ChipList v-model="form.maneuvers" :options="MANEUVERS" />
        <div class="space-y-8">
          <ChipList v-model="form.damages" :options="DAMAGES" label="Schaden an Ihrem Fahrzeug" />
          <FormInput v-model="form.damageNote" name="damage_note" label="Ergänzung" />
        </div>
      </ReportCard>

      <ReportCard title="Erster Aufprallpunkt" subtitle="Auf die Stelle tippen – ein rotes X wird angezeigt.">
        <ImpactPicker
          v-model="form.impact"
          party="B"
          title="B – Ihr Fahrzeug"
          :types="form.vehicleType ? [form.vehicleType] : VEHICLE_TYPES"
        />
      </ReportCard>

      <ReportCard title="Unterschrift">
        <template #subtitle>Sie bestätigen Ihre Angaben. Die Unterschrift ist <b>keine Schuldanerkennung</b>.</template>
        <SignaturePad v-model="form.signature" label="Unfallbeteiligter B" required />
      </ReportCard>

      <NoticeBox v-if="error" tone="danger"><p>{{ error }}</p></NoticeBox>

      <div class="sticky bottom-0 z-10 -mx-4 px-4 py-12 bg-white border-t border-light-blue">
        <FormButton type="button" :loading="sending" :disabled="sending" @click="send">
          {{ sending ? 'Wird gesendet …' : 'An Fahrzeug A senden' }}
        </FormButton>
      </div>
    </template>
  </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue';
import axios from 'axios';
import FormInput from '../form/FormInput.vue';
import FormButton from '../form/FormButton.vue';
import ReportCard from './ui/ReportCard.vue';
import OptionButtons from './ui/OptionButtons.vue';
import ChipList from './ui/ChipList.vue';
import NoticeBox from './ui/NoticeBox.vue';
import PhotoField from './ui/PhotoField.vue';
import ImpactPicker from './ui/ImpactPicker.vue';
import SignaturePad from './ui/SignaturePad.vue';
import { DAMAGES, MANEUVERS, VEHICLE_TYPES, VEHICLE_TYPE_NAMES } from './constants.js';
import { formatDate } from './report.js';
import { dataUrlToBlob } from './photos.js';
import { errorMessage } from './send.js';

// The other party of a vehicle accident fills in their part on their own phone (QR code / code from /b)
const props = defineProps({
  config: {
    type: Object,
    required: true,
  },
});

const info = props.config.otherParty; // { code, state, date, time, vehicle }
const status = ref(info.state); // open | done | invalid | sent
const sending = ref(false);
const error = ref('');

const form = reactive({
  vehicleType: '',
  licenceFront: null,
  licenceBack: null,
  registration: null,
  phone: '',
  ownerIsDriver: true,
  ownerPhone: '',
  email: '',
  maneuvers: [],
  damages: [],
  damageNote: '',
  impact: null,
  signature: null,
});

// The impact point belongs to the vehicle type
watch(() => form.vehicleType, () => { form.impact = null; });

const complete = () => form.vehicleType && form.licenceFront && form.licenceBack && form.registration
  && form.phone.trim() && form.email.trim() && (form.ownerIsDriver || form.ownerPhone.trim())
  && form.maneuvers.length && (form.damages.length || form.damageNote.trim()) && form.impact && form.signature;

const send = async () => {
  error.value = '';
  if (!complete()) {
    error.value = 'Bitte alle Pflichtangaben ausfüllen: Fahrzeugart, Fotos, Handy, E-Mail, Manöver, Schaden, Aufprallpunkt und Unterschrift.';
    return;
  }

  const data = new FormData();
  data.append('vehicle_type', form.vehicleType);
  data.append('phone', form.phone);
  data.append('owner_is_driver', form.ownerIsDriver ? '1' : '0');
  data.append('owner_phone', form.ownerPhone);
  data.append('email', form.email);
  form.maneuvers.forEach((value) => data.append('maneuvers[]', value));
  form.damages.forEach((value) => data.append('damages[]', value));
  data.append('damage_note', form.damageNote);
  data.append('impact[x]', form.impact.x);
  data.append('impact[y]', form.impact.y);
  data.append('signature', form.signature);
  data.append('licence_front', dataUrlToBlob(form.licenceFront), 'licence-front.jpg');
  data.append('licence_back', dataUrlToBlob(form.licenceBack), 'licence-back.jpg');
  data.append('registration', dataUrlToBlob(form.registration), 'registration.jpg');

  sending.value = true;
  try {
    await axios.post(props.config.submitUrl, data, { headers: { Accept: 'application/json' } });
    status.value = 'sent';
    window.scrollTo(0, 0);
  } catch (e) {
    error.value = `Nicht gesendet: ${errorMessage(e)} Bitte nochmals versuchen.`;
  } finally {
    sending.value = false;
  }
};
</script>

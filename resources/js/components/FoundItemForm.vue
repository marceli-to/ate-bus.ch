<template>
  <div ref="topRef" class="scroll-mt-24">
    <!-- Success State -->
    <div v-if="submitted" class="space-y-16 md:space-y-20">
      <div class="bg-success/10 border-l-4 border-success p-12 md:p-16 font-medium" role="status">
        Danke! Die Fundmeldung wurde gesendet.
      </div>
      <FormButton type="button" @click="reset">
        Weiteren Gegenstand melden
      </FormButton>
    </div>

    <!-- Form -->
    <form
      v-else
      class="space-y-16 md:space-y-20"
      novalidate
      @submit.prevent="submitForm"
    >
      <!-- Honeypot -->
      <input v-model="form.website" type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" class="absolute -left-[5000px]" />

      <div :ref="(el) => (fieldRefs.rolle = el)">
        <FormChoice
          v-model="form.rolle"
          label="Gefunden von"
          name="rolle"
          :options="toOptions(config.roles)"
          :required="true"
          :error="errors.rolle"
          @update:modelValue="clearError('rolle')"
        />
      </div>

      <!-- Fotos -->
      <section class="space-y-8 md:space-y-12">
        <h2 :class="headingClass">Fotos</h2>
        <div v-if="errors.fotos" class="text-error text-sm" role="alert">{{ errors.fotos }}</div>
        <div class="grid grid-cols-2 gap-8 md:gap-12">
          <FormPhoto v-model="form.foto1" label="Foto 1" :error="!!errors.fotos" @update:modelValue="clearError('fotos')" />
          <FormPhoto v-model="form.foto2" label="Foto 2" :error="!!errors.fotos" @update:modelValue="clearError('fotos')" />
        </div>
      </section>

      <!-- Gegenstand -->
      <section class="space-y-16 md:space-y-20">
        <h2 :class="headingClass">Gegenstand</h2>
        <div :ref="(el) => (fieldRefs.gegenstand = el)">
          <FormSelect
            v-model="form.gegenstand"
            label="Gegenstand"
            name="gegenstand"
            placeholder="Bitte wählen"
            :options="toOptions(config.items)"
            :required="true"
            :error="errors.gegenstand"
            @update:modelValue="clearError('gegenstand')"
          />
        </div>
        <FormTextarea
          v-model="form.beschreibung"
          label="Beschreibung (Farbe, Marke, Inhalt)"
          name="beschreibung"
          placeholder="z.B. schwarzer Rucksack, gelbe Reissverschlüsse"
          :rows="3"
          :maxlength="5000"
          :error="errors.beschreibung"
        />
      </section>

      <!-- Wo und wann -->
      <section class="space-y-16 md:space-y-20">
        <h2 :class="headingClass">Wo und wann</h2>
        <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
          <div :ref="(el) => (fieldRefs.datum = el)">
            <FormInput
              v-model="form.datum"
              label="Datum"
              name="datum"
              type="date"
              :max="today()"
              :required="true"
              :error="errors.datum"
              @update:modelValue="clearError('datum')"
            />
          </div>
          <FormInput
            v-model="form.zeit"
            label="Uhrzeit"
            name="zeit"
            type="time"
            :error="errors.zeit"
            @update:modelValue="clearError('zeit')"
          />
        </FormGrid>

        <div :ref="(el) => (fieldRefs.gruppe = el)">
          <FormChoice
            v-model="form.gruppe"
            label="Gruppe"
            name="gruppe"
            :options="toOptions(config.groups)"
            :required="true"
            :error="errors.gruppe"
            @update:modelValue="changeGroup"
          />
        </div>

        <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
          <div v-if="!isCleaning" :ref="(el) => (fieldRefs.linie = el)">
            <FormSelect
              v-model="form.linie"
              label="Linie"
              name="linie"
              :placeholder="form.gruppe ? 'Linie wählen' : 'Zuerst Gruppe'"
              :options="lineOptions"
              :disabled="!form.gruppe"
              :required="true"
              :error="errors.linie"
              @update:modelValue="clearError('linie')"
            />
          </div>
          <div :ref="(el) => (fieldRefs.bus = el)">
            <FormInput
              v-model="form.bus"
              label="Bus-Nr."
              name="bus"
              inputmode="numeric"
              placeholder="z.B. 61"
              :required="isCleaning"
              :error="errors.bus"
              @update:modelValue="changeBus"
            />
          </div>
        </FormGrid>

        <FormInput
          v-model="form.ort"
          label="Fundort im Bus"
          name="ort"
          placeholder="z.B. hinten links, Kinderwagenplatz"
          :error="errors.ort"
        />
      </section>

      <!-- Abgabe -->
      <section class="space-y-16 md:space-y-20">
        <h2 :class="headingClass">Abgabe</h2>
        <FormGrid classes="grid grid-cols-2 gap-8 md:gap-12">
          <div :ref="(el) => (fieldRefs.nachname = el)">
            <FormInput
              v-model="form.nachname"
              label="Familienname"
              name="nachname"
              placeholder="z.B. Muster"
              :required="true"
              :error="errors.nachname"
              @update:modelValue="clearError('nachname')"
            />
          </div>
          <div :ref="(el) => (fieldRefs.vorname = el)">
            <FormInput
              v-model="form.vorname"
              label="Vorname"
              name="vorname"
              placeholder="z.B. Max"
              :required="true"
              :error="errors.vorname"
              @update:modelValue="clearError('vorname')"
            />
          </div>
        </FormGrid>
        <div :ref="(el) => (fieldRefs.abgabe = el)">
          <FormChoice
            v-model="form.abgabe"
            label="Abgegeben bei"
            name="abgabe"
            :options="toOptions(config.dropOffs)"
            :columns="4"
            :required="true"
            :error="errors.abgabe"
            @update:modelValue="clearError('abgabe')"
          />
        </div>
      </section>

      <!-- Error Message -->
      <div v-if="submitError" class="bg-error/10 text-error p-12 text-sm" role="alert">
        {{ submitError }}
      </div>

      <div class="w-full">
        <FormButton type="submit" :loading="isSubmitting" :disabled="isSubmitting">
          {{ isSubmitting ? 'Wird gesendet …' : 'Fundmeldung senden' }}
        </FormButton>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, reactive, computed, provide, nextTick } from 'vue';
import axios from 'axios';
import FormInput from './form/FormInput.vue';
import FormSelect from './form/FormSelect.vue';
import FormTextarea from './form/FormTextarea.vue';
import FormGrid from './form/FormGrid.vue';
import FormButton from './form/FormButton.vue';
import FormChoice from './form/FormChoice.vue';
import FormPhoto from './form/FormPhoto.vue';

const props = defineProps({
  // { code, lines: {VBZ: [...], VBG: [...]}, items, roles, groups, dropOffs }
  config: {
    type: Object,
    required: true,
  },
  variant: {
    type: String,
    default: 'light-blue',
  },
});

provide('formVariant', props.variant);

const headingClass = 'text-lg md:text-xl xl:text-2xl font-medium';

const pad = (n) => String(n).padStart(2, '0');
const today = () => {
  const d = new Date();
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
};
const nowTime = () => {
  const d = new Date();
  return `${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

const toOptions = (list) => list.map((value) => ({ value, label: value }));

// Name und Rolle merken (wie in der ursprünglichen fundmeldung.php)
const remembered = (key) => {
  try {
    return localStorage.getItem(`ate_${key}`) || '';
  } catch {
    return '';
  }
};
const remember = () => {
  try {
    ['nachname', 'vorname', 'rolle'].forEach((key) => localStorage.setItem(`ate_${key}`, form[key]));
  } catch {
    // privater Modus o.ä.: ohne Speichern weiter
  }
};

const emptyForm = () => ({
  rolle: props.config.roles.includes(remembered('rolle')) ? remembered('rolle') : '',
  foto1: null,
  foto2: null,
  gegenstand: '',
  beschreibung: '',
  datum: today(),
  zeit: nowTime(),
  gruppe: '',
  linie: '',
  bus: '',
  ort: '',
  nachname: remembered('nachname'),
  vorname: remembered('vorname'),
  abgabe: '',
  website: '',
});

const form = reactive(emptyForm());
const errors = reactive({});
const isSubmitting = ref(false);
const submitted = ref(false);
const submitError = ref('');
const topRef = ref(null);

// Template-Refs pro Feld, für «zum ersten Fehler scrollen»
const fieldRefs = reactive({});

const isCleaning = computed(() => form.rolle === 'Reinigung');

const lineOptions = computed(() => toOptions(props.config.lines[form.gruppe] || []));

const clearError = (field) => {
  delete errors[field];
};

const changeGroup = () => {
  clearError('gruppe');
  if (!lineOptions.value.some((option) => option.value === form.linie)) {
    form.linie = '';
  }
};

// Gruppe aus der Bus-Nr. (10–40 VBZ, 54–78 VBG, 53 beide)
const changeBus = () => {
  clearError('bus');
  const n = parseInt(form.bus, 10);
  const group = n >= 10 && n <= 40 ? 'VBZ' : n >= 54 && n <= 78 ? 'VBG' : '';
  if (group && form.gruppe !== group) {
    form.gruppe = group;
    changeGroup();
  }
};

const scrollToFirstError = async () => {
  await nextTick();
  const first = Object.keys(fieldRefs).find((key) => errors[key]);
  const el = first ? fieldRefs[first] : null;
  (el || topRef.value)?.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const validate = () => {
  Object.keys(errors).forEach(clearError);

  if (!form.rolle) errors.rolle = '«Gefunden von» fehlt';
  if (!form.gegenstand) errors.gegenstand = 'Gegenstand fehlt';
  if (!form.datum) errors.datum = 'Datum fehlt';
  if (!form.gruppe) errors.gruppe = 'Gruppe fehlt';
  if (!isCleaning.value && !form.linie) errors.linie = 'Linie fehlt';
  if (isCleaning.value && !/^\d{1,3}$/.test(form.bus)) errors.bus = 'Bus-Nr. fehlt';
  if (!form.nachname.trim()) errors.nachname = 'Familienname fehlt';
  if (!form.vorname.trim()) errors.vorname = 'Vorname fehlt';
  if (!form.abgabe) errors.abgabe = 'Abgabeort fehlt';

  return Object.keys(errors).length === 0;
};

const submitForm = async () => {
  submitError.value = '';

  if (!validate()) {
    scrollToFirstError();
    return;
  }

  isSubmitting.value = true;
  remember();

  const data = new FormData();
  Object.entries(form).forEach(([key, value]) => {
    if (key === 'foto1' || key === 'foto2') {
      if (value) data.append(key, value, `${key}.jpg`);
    } else {
      data.append(key, value);
    }
  });
  data.append('k', props.config.code);

  try {
    await axios.post(`/fundmeldung?k=${encodeURIComponent(props.config.code)}`, data, {
      headers: { Accept: 'application/json' },
    });
    submitted.value = true;
    await nextTick();
    topRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  } catch (error) {
    const response = error.response;
    if (response?.status === 422 && response.data?.errors) {
      Object.assign(errors, response.data.errors);
      scrollToFirstError();
    } else if (response?.status === 429) {
      submitError.value = 'Zu viele Meldungen in kurzer Zeit. Bitte in ein paar Minuten nochmals versuchen.';
    } else if (response?.status === 404) {
      submitError.value = 'Der Link ist nicht mehr gültig. Bitte den Link aus der Infosoft-App neu öffnen.';
    } else if (!response) {
      submitError.value = 'Keine Verbindung – bitte nochmals versuchen.';
    } else {
      submitError.value = 'Senden fehlgeschlagen. Bitte Disposition informieren.';
    }
  } finally {
    isSubmitting.value = false;
  }
};

// Weitere Meldung: Name und Rolle bleiben, alles andere neu
const reset = async () => {
  Object.assign(form, emptyForm());
  Object.keys(errors).forEach(clearError);
  submitted.value = false;
  await nextTick();
  topRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};
</script>

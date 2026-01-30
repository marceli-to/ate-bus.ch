<template>
  <div>
    <!-- Success State -->
    <div v-if="submitted">
      <h3 class="text-lg md:text-xl xl:text-3xl font-bold mb-8 lg:mb-12">
        Vielen Dank für Ihre Meldung!
      </h3>
      <p>
        Wir haben Ihre Verlustmeldung erhalten und werden prüfen, ob der beschriebene Gegenstand bei uns abgegeben wurde.
      </p>
    </div>

    <!-- Form -->
    <div v-else>
      <h3 class="text-lg md:text-xl xl:text-3xl font-bold mb-8 lg:mb-12">
        Formular
      </h3>
      <form
        @submit.prevent="submitForm"
        class="space-y-16 md:space-y-20"
        ref="formRef"
        novalidate>
        <!-- Gender -->
        <div class="w-full" ref="genderRef">
          <div 
            v-if="errors.gender" 
            class="text-error text-sm mb-4"
            role="alert"
            :id="`error-gender`">
            {{ errors.gender }}
          </div>
          <div class="flex items-center gap-16 md:gap-24">
            <FormCheckbox
              v-model="form.gender"
              label="Frau"
              name="gender"
              value="frau"
              :aria-describedby="errors.gender ? 'error-gender' : undefined"
              @update:modelValue="clearError('gender')"
            />
            <FormCheckbox
              v-model="form.gender"
              label="Herr"
              name="gender"
              value="herr"
              :aria-describedby="errors.gender ? 'error-gender' : undefined"
              @update:modelValue="clearError('gender')"
            />
          </div>
        </div>

        <!-- Name -->
        <FormGrid>
          <div ref="firstnameRef">
            <FormInput
              v-model="form.firstname"
              label="Vorname"
              name="firstname"
              placeholder="Max"
              :required="true"
              :error="errors.firstname"
              :aria-describedby="errors.firstname ? 'error-firstname' : undefined"
              @update:modelValue="clearError('firstname')"
            />
          </div>
          <div ref="lastnameRef">
            <FormInput
              v-model="form.lastname"
              label="Nachname"
              name="lastname"
              placeholder="Muster"
              :required="true"
              :error="errors.lastname"
              :aria-describedby="errors.lastname ? 'error-lastname' : undefined"
              @update:modelValue="clearError('lastname')"
            />
          </div>
        </FormGrid>

        <!-- Contact -->
        <FormGrid>
          <div ref="emailRef">
            <FormInput
              v-model="form.email"
              label="Email"
              name="email"
              type="email"
              placeholder="Maxmuster@mail.ch"
              :required="true"
              :error="errors.email"
              :aria-describedby="errors.email ? 'error-email' : undefined"
              @update:modelValue="clearError('email')"
            />
          </div>
          <div ref="phoneRef">
            <FormInput
              v-model="form.phone"
              label="Telefon"
              name="phone"
              type="tel"
              placeholder="Nicht verlorene Mobilnummer angeben!"
              :required="true"
              :error="errors.phone"
              :aria-describedby="errors.phone ? 'error-phone' : undefined"
              @update:modelValue="clearError('phone')"
            />
          </div>
        </FormGrid>

        <!-- Date, Time, Bus Line -->
        <FormGrid classes="grid grid-cols-1 md:grid-cols-3 gap-16 xl:gap-20">
          <div ref="dateRef">
            <FormInput
              v-model="form.date"
              label="Datum"
              name="date"
              type="date"
              :max="today"
              :min="oneYearAgo"
              :required="true"
              :error="errors.date"
              :aria-describedby="errors.date ? 'error-date' : undefined"
              @update:modelValue="clearError('date')"
            />
          </div>
          <div ref="timeRef">
            <FormInput
              v-model="form.time"
              label="Uhrzeit"
              name="time"
              type="time"
              :required="true"
              :error="errors.time"
              :aria-describedby="errors.time ? 'error-time' : undefined"
              @update:modelValue="clearError('time')"
            />
          </div>
          <div ref="busLineRef">
            <FormSelect
              v-model="form.bus_line"
              label="Buslinie"
              name="bus_line"
              :placeholder="busLineLoadingState"
              :options="busLineOptions"
              :required="true"
              :disabled="isLoadingBusLines"
              :error="errors.bus_line"
              :aria-describedby="errors.bus_line ? 'error-bus_line' : undefined"
              @update:modelValue="clearError('bus_line')"
            />
            <p v-if="busLinesError" class="text-error text-sm mt-2" role="alert">
              {{ busLinesError }}
              <button 
                type="button" 
                @click="fetchBusLines" 
                class="underline ml-1">
                Erneut versuchen
              </button>
            </p>
          </div>
        </FormGrid>

        <!-- Description -->
        <div class="w-full" ref="descriptionRef">
          <FormTextarea
            v-model="form.description"
            label="Was wurde verloren? Beschreiben Sie den Gegenstand"
            name="description"
            placeholder="Marke, Modell, Grösse, Farbe, Beschreibung (Blauer Schirm mit abgerundetem Griff)"
            :required="true"
            :maxlength="2000"
            :error="errors.description"
            :aria-describedby="errors.description ? 'error-description' : undefined"
            @update:modelValue="clearError('description')"
          />
          <p class="text-sm text-gray-500 mt-1 tabular-nums text-right">
            {{ form.description.length }} / 2000 Zeichen
          </p>
        </div>

        <!-- Error Message -->
        <div 
          v-if="submitError" 
          class="bg-error/10 text-error p-4 text-sm"
          role="alert">
          {{ submitError }}
        </div>

        <!-- Submit Button -->
        <div class="w-full">
          <FormButton 
            type="submit" 
            :loading="isSubmitting"
            :disabled="isSubmitting || isLoadingBusLines">
            {{ isSubmitting ? 'Wird gesendet...' : 'Verlust melden' }}
          </FormButton>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, provide } from 'vue';
import axios from 'axios';
import FormInput from './form/FormInput.vue';
import FormSelect from './form/FormSelect.vue';
import FormCheckbox from './form/FormCheckbox.vue';
import FormTextarea from './form/FormTextarea.vue';
import FormGrid from './form/FormGrid.vue';
import FormButton from './form/FormButton.vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'white',
  },
});

provide('formVariant', props.variant);

// Form refs for scroll-to-error
const formRef = ref(null);
const genderRef = ref(null);
const firstnameRef = ref(null);
const lastnameRef = ref(null);
const emailRef = ref(null);
const phoneRef = ref(null);
const dateRef = ref(null);
const timeRef = ref(null);
const busLineRef = ref(null);
const descriptionRef = ref(null);

const fieldRefs = {
  gender: genderRef,
  firstname: firstnameRef,
  lastname: lastnameRef,
  email: emailRef,
  phone: phoneRef,
  date: dateRef,
  time: timeRef,
  bus_line: busLineRef,
  description: descriptionRef,
};

// Date constraints
const today = computed(() => {
  return new Date().toISOString().split('T')[0];
});

const oneYearAgo = computed(() => {
  const date = new Date();
  date.setFullYear(date.getFullYear() - 1);
  return date.toISOString().split('T')[0];
});

const form = reactive({
  gender: '',
  firstname: '',
  lastname: '',
  email: '',
  phone: '',
  date: '',
  time: '',
  bus_line: '',
  description: '',
});

const errors = reactive({
  gender: '',
  firstname: '',
  lastname: '',
  email: '',
  phone: '',
  date: '',
  time: '',
  bus_line: '',
  description: '',
});

const isSubmitting = ref(false);
const submitted = ref(false);
const submitError = ref('');

// Bus lines state
const busLineOptions = ref([]);
const isLoadingBusLines = ref(false);
const busLinesError = ref('');

const busLineLoadingState = computed(() => {
  if (isLoadingBusLines.value) return 'Wird geladen...';
  if (busLinesError.value) return 'Fehler beim Laden';
  return 'Linie wählen';
});

// Fetch bus lines from API
const fetchBusLines = async () => {
  isLoadingBusLines.value = true;
  busLinesError.value = '';
  
  try {
    const { data } = await axios.get('/api/bus-routes');
    busLineOptions.value = data.map(line => ({
      value: line.title,
      label: line.title,
    }));
  } catch (error) {
    busLinesError.value = 'Buslinien konnten nicht geladen werden.';
  } finally {
    isLoadingBusLines.value = false;
  }
};

onMounted(() => {
  fetchBusLines();
});

const clearError = (field) => {
  errors[field] = '';
};

// More robust email validation
const isValidEmail = (email) => {
  const pattern = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;
  return pattern.test(email);
};

const isValidDate = (dateString) => {
  if (!dateString) return false;
  const date = new Date(dateString);
  const now = new Date();
  const yearAgo = new Date();
  yearAgo.setFullYear(yearAgo.getFullYear() - 1);
  
  // Must be in the past and within 1 year
  return date <= now && date >= yearAgo;
};

const scrollToFirstError = (firstErrorField) => {
  const fieldRef = fieldRefs[firstErrorField];
  if (fieldRef?.value) {
    fieldRef.value.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
};

const validateForm = () => {
  Object.keys(errors).forEach((key) => errors[key] = '');

  let firstErrorField = null;

  const setError = (field, message) => {
    errors[field] = message;
    if (!firstErrorField) firstErrorField = field;
  };

  if (!form.gender) {
    setError('gender', 'Anrede fehlt');
  }

  if (!form.firstname.trim()) {
    setError('firstname', 'Vorname fehlt');
  }

  if (!form.lastname.trim()) {
    setError('lastname', 'Nachname fehlt');
  }

  if (!form.email.trim()) {
    setError('email', 'E-Mail fehlt');
  } else if (!isValidEmail(form.email)) {
    setError('email', 'E-Mail ungültig');
  }

  if (!form.phone.trim()) {
    setError('phone', 'Telefon fehlt');
  }

  if (!form.date) {
    setError('date', 'Datum fehlt');
  } else if (!isValidDate(form.date)) {
    setError('date', 'Datum muss innerhalb des letzten Jahres liegen');
  }

  if (!form.time) {
    setError('time', 'Uhrzeit fehlt');
  }

  if (!form.bus_line) {
    setError('bus_line', 'Buslinie fehlt');
  }

  if (!form.description.trim()) {
    setError('description', 'Beschreibung fehlt');
  } else if (form.description.length > 2000) {
    setError('description', 'Beschreibung ist zu lang (max. 2000 Zeichen)');
  }

  if (firstErrorField) {
    scrollToFirstError(firstErrorField);
    return false;
  }

  return true;
};

const getCSRFToken = () => {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  if (!token) {
    throw new Error('CSRF token not found');
  }
  return token;
};

const submitForm = async () => {
  submitError.value = '';

  if (!validateForm()) {
    return;
  }

  isSubmitting.value = true;

  try {
    const csrfToken = getCSRFToken();
    
    const { data } = await axios.post('/api/lost-and-found', {
      gender: form.gender,
      firstname: form.firstname,
      lastname: form.lastname,
      email: form.email,
      phone: form.phone,
      date: form.date,
      time: form.time,
      bus_line: form.bus_line,
      description: form.description,
    }, {
      headers: {
        'X-CSRF-TOKEN': csrfToken,
      },
    });

    if (data.success) {
      submitted.value = true;
    } else {
      if (data.errors) {
        let firstErrorField = null;
        Object.keys(data.errors).forEach((key) => {
          errors[key] = data.errors[key][0];
          if (!firstErrorField && fieldRefs[key]) firstErrorField = key;
        });
        if (firstErrorField) scrollToFirstError(firstErrorField);
      } else {
        submitError.value = data.message || 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.';
      }
    }
  } catch (error) {
    if (error.message === 'CSRF token not found') {
      submitError.value = 'Sicherheitsfehler. Bitte laden Sie die Seite neu.';
    } else if (error.response?.data?.errors) {
      let firstErrorField = null;
      Object.keys(error.response.data.errors).forEach((key) => {
        errors[key] = error.response.data.errors[key][0];
        if (!firstErrorField && fieldRefs[key]) firstErrorField = key;
      });
      if (firstErrorField) scrollToFirstError(firstErrorField);
    } else {
      submitError.value = error.response?.data?.message || 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.';
    }
  } finally {
    isSubmitting.value = false;
  }
};
</script>

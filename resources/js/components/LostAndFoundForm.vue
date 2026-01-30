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
        class="space-y-16 md:space-y-20">
        <!-- Gender -->
        <div class="w-full">
          <p v-if="errors.gender" class="text-error text-sm mb-4">{{ errors.gender }}</p>
          <div class="flex items-center gap-16 md:gap-24">
            <FormCheckbox
              v-model="form.gender"
              label="Frau"
              name="gender"
              value="frau"
              @update:modelValue="errors.gender = ''"
            />
            <FormCheckbox
              v-model="form.gender"
              label="Herr"
              name="gender"
              value="herr"
              @update:modelValue="errors.gender = ''"
            />
          </div>
        </div>

        <!-- Name -->
        <FormGrid>
          <FormInput
            v-model="form.firstname"
            label="Vorname"
            name="firstname"
            placeholder="Max"
            :required="true"
            :error="errors.firstname"
            @focus="errors.firstname = ''"
          />
          <FormInput
            v-model="form.lastname"
            label="Nachname"
            name="lastname"
            placeholder="Muster"
            :required="true"
            :error="errors.lastname"
            @focus="errors.lastname = ''"
          />
        </FormGrid>

        <!-- Contact -->
        <FormGrid>
          <FormInput
            v-model="form.email"
            label="Email"
            name="email"
            type="email"
            placeholder="Maxmuster@mail.ch"
            :required="true"
            :error="errors.email"
            @focus="errors.email = ''"
          />
          <FormInput
            v-model="form.phone"
            label="Telefon"
            name="phone"
            type="tel"
            placeholder="Nicht verlorene Mobilnummer angeben!"
            :required="true"
            :error="errors.phone"
            @focus="errors.phone = ''"
          />
        </FormGrid>

        <!-- Date, Time, Bus Line -->
        <FormGrid classes="grid grid-cols-1 md:grid-cols-3 gap-16 xl:gap-20">
          <FormInput
            v-model="form.date"
            label="Datum"
            name="date"
            type="date"
            :required="true"
            :error="errors.date"
            @focus="errors.date = ''"
          />
          <FormInput
            v-model="form.time"
            label="Uhrzeit"
            name="time"
            type="time"
            :required="true"
            :error="errors.time"
            @focus="errors.time = ''"
          />
          <FormSelect
            v-model="form.bus_line"
            label="Buslinie"
            name="bus_line"
            placeholder="Linie wählen"
            :options="busLineOptions"
            :required="true"
            :error="errors.bus_line"
            @focus="errors.bus_line = ''"
          />
        </FormGrid>

        <!-- Description -->
        <div class="w-full">
          <FormTextarea
            v-model="form.description"
            label="Was wurde verloren? Beschreiben Sie den Gegenstand"
            name="description"
            placeholder="Marke, Modell, Grösse, Farbe, Beschreibung (Blauer Schirm mit abgerundetem Griff)"
            :required="true"
            :error="errors.description"
            @focus="errors.description = ''"
          />
        </div>

        <!-- Error Message -->
        <div v-if="submitError" class="bg-error/10 text-error p-4 text-sm">
          {{ submitError }}
        </div>

        <!-- Submit Button -->
        <div class="w-full">
          <FormButton type="submit" :loading="isSubmitting">
            {{ isSubmitting ? 'Wird gesendet...' : 'Verlust melden' }}
          </FormButton>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, provide } from 'vue';
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
const busLineOptions = ref([]);

// Fetch bus lines from API
onMounted(async () => {
  try {
    const { data } = await axios.get('/api/bus-routes');
    busLineOptions.value = data.map(line => ({
      value: line.title,
      label: line.title,
    }));
  } catch (error) {
    console.error('Failed to fetch bus lines:', error);
  }
});

const validateForm = () => {
  Object.keys(errors).forEach((key) => errors[key] = '');

  let isValid = true;

  if (!form.gender) {
    errors.gender = 'Anrede fehlt';
    isValid = false;
  }

  if (!form.firstname.trim()) {
    errors.firstname = 'Vorname fehlt';
    isValid = false;
  }

  if (!form.lastname.trim()) {
    errors.lastname = 'Nachname fehlt';
    isValid = false;
  }

  if (!form.email.trim()) {
    errors.email = 'E-Mail fehlt';
    isValid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'E-Mail ungültig';
    isValid = false;
  }

  if (!form.phone.trim()) {
    errors.phone = 'Telefon fehlt';
    isValid = false;
  }

  if (!form.date) {
    errors.date = 'Datum fehlt';
    isValid = false;
  }

  if (!form.time) {
    errors.time = 'Uhrzeit fehlt';
    isValid = false;
  }

  if (!form.bus_line) {
    errors.bus_line = 'Buslinie fehlt';
    isValid = false;
  }

  if (!form.description.trim()) {
    errors.description = 'Beschreibung fehlt';
    isValid = false;
  }

  return isValid;
};

const submitForm = async () => {
  submitError.value = '';

  if (!validateForm()) {
    return;
  }

  isSubmitting.value = true;

  try {
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
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    });

    if (data.success) {
      submitted.value = true;
    } else {
      if (data.errors) {
        Object.keys(data.errors).forEach((key) => {
          errors[key] = data.errors[key][0];
        });
      } else {
        submitError.value = data.message || 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.';
      }
    }
  } catch (error) {
    console.error('Submit error:', error);
    if (error.response?.data?.errors) {
      Object.keys(error.response.data.errors).forEach((key) => {
        errors[key] = error.response.data.errors[key][0];
      });
    } else {
      submitError.value = error.response?.data?.message || 'Ein Fehler ist aufgetreten. Bitte versuchen Sie es erneut.';
    }
  } finally {
    isSubmitting.value = false;
  }
};
</script>

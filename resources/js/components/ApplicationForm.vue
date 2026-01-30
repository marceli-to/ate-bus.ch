<template>
  <div >
    <!-- Success State -->
    <div v-if="submitted">
      <h3 class="text-lg md:text-xl xl:text-3xl font-bold mb-8 lg:mb-12">
        Vielen Dank für Ihre Bewerbung!
      </h3>
      <p>
        Wir haben Ihre Unterlagen erhalten und werden uns so bald wie möglich bei Ihnen melden.
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

        <!-- Address -->
        <div class="w-full">
          <FormInput
            v-model="form.street"
            label="Strasse"
            name="street"
            placeholder="Musterstrasse"
            :required="true"
            :error="errors.street"
            @focus="errors.street = ''"
          />
        </div>

        <FormGrid>
          <FormInput
            v-model="form.zip"
            label="Postleitzahl"
            name="zip"
            placeholder="8307"
            :required="true"
            :error="errors.zip"
            @focus="errors.zip = ''"
          />
          <FormInput
            v-model="form.city"
            label="Ort"
            name="city"
            placeholder="Effretikon"
            :required="true"
            :error="errors.city"
            @focus="errors.city = ''"
          />
        </FormGrid>

        <!-- Contact -->
        <FormGrid>
          <FormInput
            v-model="form.phone"
            label="Telefonnummer"
            name="phone"
            type="tel"
            placeholder="076 123 45 67"
            :required="true"
            :error="errors.phone"
            @focus="errors.phone = ''"
          />
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
        </FormGrid>

        <!-- Skills & Permit -->
        <FormGrid>
          <FormSelect
            v-model="form.german_skills"
            label="Deutschkenntnisse"
            name="german_skills"
            placeholder="Muttersprache"
            :options="germanSkillsOptions"
            :required="true"
            :error="errors.german_skills"
            @focus="errors.german_skills = ''"
          />
          <FormSelect
            v-model="form.permit"
            label="Bewilligung"
            name="permit"
            placeholder="Schweizer:in"
            :options="permitOptions"
            :required="true"
            :error="errors.permit"
            @focus="errors.permit = ''"
          />
        </FormGrid>

        <!-- File Uploads -->
        <FormGrid _classes="grid grid-cols-1 md:grid-cols-3 gap-16 xl:gap-20">
          <div>
            <FormFilePond
              v-model="form.application_files"
              label="Bewerbung"
              name="application_files"
              hint="(max. 5 Dateien mit 10 MB)"
              :allow-multiple="true"
              :max-files="5"
              :required="true"
              :error="errors.application_files"
              @addfile="errors.application_files = ''"
            />
          </div>
          <div>
            <FormFilePond
              v-model="form.criminal_record"
              label="Strafregisterauszug"
              name="criminal_record"
              hint="(max. 1 Datei mit 10 MB)"
              :allow-multiple="false"
              :max-files="1"
              :required="true"
              :error="errors.criminal_record"
              @addfile="errors.criminal_record = ''"
            />
          </div>
          <div>
            <FormFilePond
              v-model="form.ivz_register"
              label="IVZ-Registerauszug"
              name="ivz_register"
              hint="(max. 1 Datei mit 10 MB)"
              :allow-multiple="false"
              :max-files="1"
              :required="true"
              :error="errors.ivz_register"
              @addfile="errors.ivz_register = ''"
            />
          </div>
        </FormGrid>

        <!-- Error Message -->
        <div v-if="submitError" class="bg-error/10 text-error p-4 text-sm">
          {{ submitError }}
        </div>

        <!-- Submit Button -->
        <div class="w-full">
          <FormButton type="submit" :loading="isSubmitting">
            {{ isSubmitting ? 'Wird gesendet...' : 'Bewerbung einreichen' }}
          </FormButton>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, provide } from 'vue';
import axios from 'axios';
import FormInput from './form/FormInput.vue';
import FormSelect from './form/FormSelect.vue';
import FormCheckbox from './form/FormCheckbox.vue';
import FormFilePond from './form/FormFilePond.vue';
import FormGrid from './form/FormGrid.vue';
import FormButton from './form/FormButton.vue';

const props = defineProps({
  jobId: {
    type: String,
    required: true,
  },
  variant: {
    type: String,
    default: 'light-blue',
  },
});

provide('formVariant', props.variant);

const form = reactive({
  gender: 'herr',
  firstname: 'Max',
  lastname: 'Muster',
  street: 'Musterstrasse 123',
  zip: '8000',
  city: 'Zürich',
  phone: '079 123 45 67',
  email: 'max.muster@example.com',
  german_skills: 'muttersprache',
  permit: 'schweizer',
  application_files: [],
  criminal_record: [],
  ivz_register: [],
});

const errors = reactive({
  gender: '',
  firstname: '',
  lastname: '',
  street: '',
  zip: '',
  city: '',
  phone: '',
  email: '',
  german_skills: '',
  permit: '',
  application_files: '',
  criminal_record: '',
  ivz_register: '',
});
const isSubmitting = ref(false);
const submitted = ref(false);
const submitError = ref('');

const germanSkillsOptions = [
  { value: 'muttersprache', label: 'Muttersprache' },
  { value: 'gut', label: 'Gut' },
  { value: 'grundkenntnisse', label: 'Grundkenntnisse' },
];

const permitOptions = [
  { value: 'schweizer', label: 'Schweizer:in' },
  { value: 'b', label: 'Ausweis B' },
  { value: 'c', label: 'Ausweis C' },
  { value: 'g', label: 'Ausweis G' },
  { value: 'eu_efta', label: 'EU/EFTA' },
];

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

  if (!form.street.trim()) {
    errors.street = 'Strasse fehlt';
    isValid = false;
  }

  if (!form.zip.trim()) {
    errors.zip = 'PLZ fehlt';
    isValid = false;
  }

  if (!form.city.trim()) {
    errors.city = 'Ort fehlt';
    isValid = false;
  }

  if (!form.phone.trim()) {
    errors.phone = 'Telefon fehlt';
    isValid = false;
  }

  if (!form.email.trim()) {
    errors.email = 'E-Mail fehlt';
    isValid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'E-Mail ungültig';
    isValid = false;
  }

  if (!form.german_skills) {
    errors.german_skills = 'Auswahl fehlt';
    isValid = false;
  }

  if (!form.permit) {
    errors.permit = 'Auswahl fehlt';
    isValid = false;
  }

  if (form.application_files.length === 0) {
    errors.application_files = 'Bewerbung fehlt';
    isValid = false;
  }

  if (form.criminal_record.length === 0) {
    errors.criminal_record = 'Strafregisterauszug fehlt';
    isValid = false;
  }

  if (form.ivz_register.length === 0) {
    errors.ivz_register = 'IVZ-Registerauszug fehlt';
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
    const { data } = await axios.post('/api/applications', {
      job_id: props.jobId,
      gender: form.gender,
      firstname: form.firstname,
      lastname: form.lastname,
      street: form.street,
      zip: form.zip,
      city: form.city,
      phone: form.phone,
      email: form.email,
      german_skills: form.german_skills,
      permit: form.permit,
      application_files: form.application_files,
      criminal_record: form.criminal_record[0],
      ivz_register: form.ivz_register[0],
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

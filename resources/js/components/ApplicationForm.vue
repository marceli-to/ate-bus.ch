<template>
  <div>
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

        <!-- Address -->
        <div class="w-full" ref="streetRef">
          <FormInput
            v-model="form.street"
            label="Strasse"
            name="street"
            placeholder="Musterstrasse"
            :required="true"
            :error="errors.street"
            :aria-describedby="errors.street ? 'error-street' : undefined"
            @update:modelValue="clearError('street')"
          />
        </div>

        <FormGrid>
          <div ref="zipRef">
            <FormInput
              v-model="form.zip"
              label="Postleitzahl"
              name="zip"
              placeholder="8307"
              :required="true"
              :error="errors.zip"
              :aria-describedby="errors.zip ? 'error-zip' : undefined"
              @update:modelValue="clearError('zip')"
            />
          </div>
          <div ref="cityRef">
            <FormInput
              v-model="form.city"
              label="Ort"
              name="city"
              placeholder="Effretikon"
              :required="true"
              :error="errors.city"
              :aria-describedby="errors.city ? 'error-city' : undefined"
              @update:modelValue="clearError('city')"
            />
          </div>
        </FormGrid>

        <!-- Contact -->
        <FormGrid>
          <div ref="phoneRef">
            <FormInput
              v-model="form.phone"
              label="Telefonnummer"
              name="phone"
              type="tel"
              placeholder="076 123 45 67"
              :required="true"
              :error="errors.phone"
              :aria-describedby="errors.phone ? 'error-phone' : undefined"
              @update:modelValue="clearError('phone')"
            />
          </div>
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
        </FormGrid>

        <!-- Skills & Permit -->
        <FormGrid>
          <div ref="germanSkillsRef">
            <FormSelect
              v-model="form.german_skills"
              label="Deutschkenntnisse"
              name="german_skills"
              placeholder="Muttersprache"
              :options="germanSkillsOptions"
              :required="true"
              :error="errors.german_skills"
              :aria-describedby="errors.german_skills ? 'error-german_skills' : undefined"
              @update:modelValue="clearError('german_skills')"
            />
          </div>
          <div ref="permitRef">
            <FormSelect
              v-model="form.permit"
              label="Bewilligung"
              name="permit"
              placeholder="Schweizer:in"
              :options="permitOptions"
              :required="true"
              :error="errors.permit"
              :aria-describedby="errors.permit ? 'error-permit' : undefined"
              @update:modelValue="clearError('permit')"
            />
          </div>
        </FormGrid>

        <!-- File Uploads -->
        <FormGrid _classes="grid grid-cols-1 md:grid-cols-3 gap-16 xl:gap-20">
          <div ref="applicationFilesRef">
            <FormFilePond
              v-model="form.application_files"
              label="Bewerbung"
              name="application_files"
              hint="(max. 5 Dateien mit 10 MB)"
              :allow-multiple="true"
              :max-files="5"
              :required="true"
              :error="errors.application_files"
              :aria-describedby="errors.application_files ? 'error-application_files' : undefined"
              @addfile="clearError('application_files')"
              @processfilestart="filesProcessing++"
              @processfileend="filesProcessing--"
              @processfileabort="filesProcessing--"
            />
          </div>
          <div ref="criminalRecordRef">
            <FormFilePond
              v-model="form.criminal_record"
              label="Strafregisterauszug"
              name="criminal_record"
              hint="(max. 1 Datei mit 10 MB)"
              :allow-multiple="false"
              :max-files="1"
              :required="true"
              :error="errors.criminal_record"
              :aria-describedby="errors.criminal_record ? 'error-criminal_record' : undefined"
              @addfile="clearError('criminal_record')"
              @processfilestart="filesProcessing++"
              @processfileend="filesProcessing--"
              @processfileabort="filesProcessing--"
            />
          </div>
          <div ref="ivzRegisterRef">
            <FormFilePond
              v-model="form.ivz_register"
              label="IVZ-Registerauszug"
              name="ivz_register"
              hint="(max. 1 Datei mit 10 MB)"
              :allow-multiple="false"
              :max-files="1"
              :required="true"
              :error="errors.ivz_register"
              :aria-describedby="errors.ivz_register ? 'error-ivz_register' : undefined"
              @addfile="clearError('ivz_register')"
              @processfilestart="filesProcessing++"
              @processfileend="filesProcessing--"
              @processfileabort="filesProcessing--"
            />
          </div>
        </FormGrid>

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
            :disabled="isSubmitting || filesProcessing > 0">
            <template v-if="filesProcessing > 0">
              Dateien werden verarbeitet...
            </template>
            <template v-else>
              {{ isSubmitting ? 'Wird gesendet...' : 'Bewerbung einreichen' }}
            </template>
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

// Form refs for scroll-to-error
const formRef = ref(null);
const genderRef = ref(null);
const firstnameRef = ref(null);
const lastnameRef = ref(null);
const streetRef = ref(null);
const zipRef = ref(null);
const cityRef = ref(null);
const phoneRef = ref(null);
const emailRef = ref(null);
const germanSkillsRef = ref(null);
const permitRef = ref(null);
const applicationFilesRef = ref(null);
const criminalRecordRef = ref(null);
const ivzRegisterRef = ref(null);

const fieldRefs = {
  gender: genderRef,
  firstname: firstnameRef,
  lastname: lastnameRef,
  street: streetRef,
  zip: zipRef,
  city: cityRef,
  phone: phoneRef,
  email: emailRef,
  german_skills: germanSkillsRef,
  permit: permitRef,
  application_files: applicationFilesRef,
  criminal_record: criminalRecordRef,
  ivz_register: ivzRegisterRef,
};

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
const filesProcessing = ref(0);

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

const clearError = (field) => {
  errors[field] = '';
};

// More robust email validation
const isValidEmail = (email) => {
  const pattern = /^[a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;
  return pattern.test(email);
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

  if (!form.street.trim()) {
    setError('street', 'Strasse fehlt');
  }

  if (!form.zip.trim()) {
    setError('zip', 'PLZ fehlt');
  }

  if (!form.city.trim()) {
    setError('city', 'Ort fehlt');
  }

  if (!form.phone.trim()) {
    setError('phone', 'Telefon fehlt');
  }

  if (!form.email.trim()) {
    setError('email', 'E-Mail fehlt');
  } else if (!isValidEmail(form.email)) {
    setError('email', 'E-Mail ungültig');
  }

  if (!form.german_skills) {
    setError('german_skills', 'Auswahl fehlt');
  }

  if (!form.permit) {
    setError('permit', 'Auswahl fehlt');
  }

  if (!form.application_files || form.application_files.length === 0) {
    setError('application_files', 'Bewerbung fehlt');
  }

  if (!form.criminal_record || form.criminal_record.length === 0) {
    setError('criminal_record', 'Strafregisterauszug fehlt');
  }

  if (!form.ivz_register || form.ivz_register.length === 0) {
    setError('ivz_register', 'IVZ-Registerauszug fehlt');
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

  // Prevent submission while files are still processing
  if (filesProcessing.value > 0) {
    submitError.value = 'Bitte warten Sie, bis alle Dateien verarbeitet wurden.';
    return;
  }

  if (!validateForm()) {
    return;
  }

  isSubmitting.value = true;

  try {
    const csrfToken = getCSRFToken();
    
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
      criminal_record: form.criminal_record?.[0] ?? null,
      ivz_register: form.ivz_register?.[0] ?? null,
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

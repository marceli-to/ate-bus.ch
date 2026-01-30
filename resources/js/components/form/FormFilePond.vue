<template>
  <div class="flex flex-col bg-white p-8 md:py-8 md:px-12">
    <FormLabel
      :required="required"
      :custom-class="error ? 'text-center text-error' : 'text-center'"
    >
      {{ error || label }}
    </FormLabel>
    <p v-if="hint && !error" class="text-blue-black text-xs italic text-center mb-4">{{ hint }}</p>
    <file-pond
      ref="pond"
      :name="name"
      :allow-multiple="allowMultiple"
      :max-files="maxFiles"
      :accepted-file-types="acceptedFileTypes"
      :max-file-size="maxFileSize"
      :server="serverConfig"
      :files="files"
      :label-idle="labelIdle"
      :label-file-type-not-allowed="'Dateityp nicht erlaubt'"
      :file-validate-type-label-expected-types="'Erlaubt: PDF, Word, JPG/PNG'"
      :label-max-file-size-exceeded="'Datei ist zu gross'"
      :label-max-file-size="'Max. {filesize}'"
      :label-max-total-file-size-exceeded="'Maximale Dateigrösse überschritten'"
      credits=""
      class="filepond-wrapper"
      :class="{ 'filepond-error': error }"
      @init="handleInit"
      @addfile="handleAddFile"
      @processfile="handleProcessFile"
      @removefile="handleRemoveFile"
      @error="handleError"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue';
import vueFilePond from 'vue-filepond';
import FilePondPluginFileValidateType from 'filepond-plugin-file-validate-type';
import FilePondPluginFileValidateSize from 'filepond-plugin-file-validate-size';
import FormLabel from './FormLabel.vue';

import 'filepond/dist/filepond.min.css';

const FilePond = vueFilePond(
  FilePondPluginFileValidateType,
  FilePondPluginFileValidateSize
);

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  label: {
    type: String,
    required: true,
  },
  name: {
    type: String,
    required: true,
  },
  hint: {
    type: String,
    default: '',
  },
  allowMultiple: {
    type: Boolean,
    default: false,
  },
  maxFiles: {
    type: Number,
    default: 1,
  },
  acceptedFileTypes: {
    type: Array,
    default: () => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png'],
  },
  maxFileSize: {
    type: String,
    default: '10MB',
  },
  required: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue', 'addfile']);

const pond = ref(null);
const files = ref([]);

const uploadIcon = `<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="filepond-upload-icon">
  <path d="M21.5 18.5V21.5H1.5V18.5" stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M11.5 2.5V17.5" stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M7.5 5.5L11.5 2.5L15.5 5.5" stroke="currentColor" stroke-width="2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
</svg>`;

const labelIdle = `<div class="filepond-custom-label">${uploadIcon}<span class="filepond--label-action">Datei hochladen</span></div>`;

const serverConfig = {
  process: {
    url: '/api/applications/upload',
    method: 'POST',
    headers: () => ({
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      'Accept': 'application/json',
    }),
    onload: (response) => {
      try {
        const data = JSON.parse(response);
        return data.id;
      } catch (e) {
        console.error('Failed to parse upload response:', response);
        return null;
      }
    },
    onerror: (response) => {
      console.error('Upload error:', response);
      return response;
    },
  },
  revert: (uniqueFileId, load, error) => {
    fetch('/api/applications/upload', {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        'Content-Type': 'text/plain',
        'Accept': 'application/json',
      },
      body: uniqueFileId,
    })
      .then((response) => {
        if (response.ok) {
          load();
        } else {
          error('Could not delete file');
        }
      })
      .catch(() => {
        error('Could not delete file');
      });
  },
};

const handleInit = () => {};

const handleAddFile = () => {
  emit('addfile');
};

const handleProcessFile = (error, file) => {
  if (!error) {
    const newValue = [...props.modelValue, file.serverId];
    emit('update:modelValue', newValue);
  }
};

const handleRemoveFile = (error, file) => {
  // Only remove from modelValue if there's no error and file has a serverId
  if (!error && file.serverId) {
    const newValue = props.modelValue.filter((id) => id !== file.serverId);
    emit('update:modelValue', newValue);
  }
};

const handleError = (error) => {
  console.error('FilePond error:', error);
};
</script>
<style>
/* Drop area */
.filepond-wrapper .filepond--panel-root {
  background-color: #f9f9f9 !important;
  border-radius: 0 !important;
}

.filepond-wrapper .filepond--drop-label {
  background-color: #f9f9f9 !important;
}

/* Custom label */
.filepond-custom-label {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.filepond-upload-icon {
  width: 20px;
  height: 20px;
  color: #02529F;
}

.filepond-wrapper .filepond--label-action {
  color: #02529F;
  text-decoration: underline;
  cursor: pointer;
  font-size: 0.875rem;
}

/* No rounded borders */
.filepond-wrapper .filepond--root,
.filepond-wrapper .filepond--panel,
.filepond-wrapper .filepond--item-panel,
.filepond-wrapper .filepond--file-wrapper,
.filepond-wrapper .filepond--file {
  border-radius: 0 !important;
}

/* Error state */
.filepond-error .filepond--panel-root {
  background-color: rgba(235, 0, 0, 0.1) !important;
}

/* Drag-over state */
.filepond-wrapper .filepond--root[data-hopper-state="drag-over"] .filepond--drop-label {
  background-color: rgba(2, 82, 159, 0.15) !important;
}

/* Error state styling */
.filepond-wrapper [data-filepond-item-state*="error"] .filepond--file-info {
  display: none !important;
}

.filepond-wrapper [data-filepond-item-state*="error"] .filepond--file-status {
  margin-left: 0 !important;
  margin-right: auto !important;
  max-width: none !important;
}

.filepond-wrapper [data-filepond-item-state*="error"] .filepond--action-remove-item {
  margin-left: auto !important;
}
</style>

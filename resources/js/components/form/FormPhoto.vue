<template>
  <div class="relative aspect-square">
    <label
      class="absolute inset-0 flex flex-col items-center justify-center gap-y-6 overflow-hidden cursor-pointer bg-light-blue border-2 border-dashed transition-colors"
      :class="error ? 'border-error' : 'border-blue-primary/40 hover:border-blue-primary'"
    >
      <template v-if="!preview">
        <svg class="w-32 h-32 text-blue-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
          <path d="M4 8h3l2-3h6l2 3h3v11H4z" />
          <circle cx="12" cy="13" r="3.5" />
        </svg>
        <span class="font-medium text-blue-black">{{ label }}</span>
      </template>
      <img v-else :src="preview" :alt="label" class="absolute inset-0 w-full h-full object-cover" />
      <input
        type="file"
        accept="image/*"
        capture="environment"
        class="sr-only"
        :aria-label="label"
        @change="handleChange"
      />
    </label>
    <button
      v-if="preview"
      type="button"
      class="absolute top-6 right-6 w-32 h-32 flex items-center justify-center bg-blue-black/80 text-white hover:bg-blue-black cursor-pointer"
      :aria-label="`${label} entfernen`"
      @click="remove"
    >
      <svg class="w-14 h-14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path d="M1 1l12 12M13 1L1 13" />
      </svg>
    </button>
  </div>
</template>

<script setup>
import { ref, watch, onBeforeUnmount } from 'vue';

const props = defineProps({
  // Blob (verkleinertes Foto) oder null
  modelValue: {
    default: null,
  },
  label: {
    type: String,
    required: true,
  },
  error: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue']);

const preview = ref('');

const setPreview = (blob) => {
  if (preview.value) URL.revokeObjectURL(preview.value);
  preview.value = blob ? URL.createObjectURL(blob) : '';
};

// Formular zurückgesetzt → Vorschau leeren
watch(() => props.modelValue, (value) => {
  if (!value) setPreview(null);
});

onBeforeUnmount(() => setPreview(null));

// Fotos im Browser verkleinern (max. 1600 px, JPEG): schneller Upload auch bei schwachem Netz
const shrink = (file) => new Promise((resolve) => {
  const url = URL.createObjectURL(file);
  const img = new Image();
  img.onload = () => {
    const scale = Math.min(1, 1600 / Math.max(img.width, img.height));
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(img.width * scale);
    canvas.height = Math.round(img.height * scale);
    canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
    URL.revokeObjectURL(url);
    canvas.toBlob((blob) => resolve(blob || file), 'image/jpeg', 0.8);
  };
  // Format, das der Browser nicht anzeigen kann (z.B. HEIC): Original senden
  img.onerror = () => {
    URL.revokeObjectURL(url);
    resolve(file);
  };
  img.src = url;
});

const handleChange = async (event) => {
  const file = event.target.files?.[0];
  event.target.value = '';
  if (!file) return;

  const blob = await shrink(file);
  setPreview(blob);
  emit('update:modelValue', blob);
};

const remove = () => {
  setPreview(null);
  emit('update:modelValue', null);
};
</script>

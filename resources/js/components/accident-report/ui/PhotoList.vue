<template>
  <div class="flex flex-col gap-y-8">
    <FormLabel v-if="label">
      {{ label }}<span v-if="optional" class="font-normal text-blue-gray"> {{ optional }}</span>
    </FormLabel>
    <slot />
    <div v-if="modelValue.length" class="grid grid-cols-3 sm:grid-cols-4 gap-6">
      <div v-for="(photo, i) in modelValue" :key="i" class="relative aspect-square bg-light-blue">
        <img :src="photo" :alt="`Foto ${i + 1}`" class="absolute inset-0 w-full h-full object-cover" />
        <button
          type="button"
          class="absolute top-4 right-4 w-28 h-28 flex items-center justify-center bg-blue-black/80 text-white hover:bg-blue-black cursor-pointer"
          :aria-label="`Foto ${i + 1} entfernen`"
          @click="remove(i)"
        >
          <svg class="w-12 h-12" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path d="M1 1l12 12M13 1L1 13" />
          </svg>
        </button>
      </div>
    </div>
    <PhotoButtons v-if="modelValue.length < max" multiple @photos="add" />
    <p v-else class="text-xs md:text-sm text-blue-gray">Maximal {{ max }} Fotos pro Meldung.</p>
  </div>
</template>

<script setup>
import FormLabel from '../../form/FormLabel.vue';
import PhotoButtons from './PhotoButtons.vue';

// Any number of photos (damage, scene), model = array of JPEG data URLs
const props = defineProps({
  modelValue: {
    type: Array,
    required: true,
  },
  label: {
    type: String,
    default: '',
  },
  optional: {
    type: String,
    default: '',
  },
  // The server accepts only so many files per report (PHP max_file_uploads)
  max: {
    type: Number,
    default: 20,
  },
});

const emit = defineEmits(['update:modelValue']);

const add = (photos) => emit('update:modelValue', [...props.modelValue, ...photos].slice(0, props.max));
const remove = (index) => emit('update:modelValue', props.modelValue.filter((_, i) => i !== index));
</script>

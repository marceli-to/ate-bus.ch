<template>
  <div class="grid grid-cols-2 gap-6">
    <label class="flex items-center justify-center gap-x-4 min-h-40 px-4 bg-white border border-blue-primary/40 hover:border-blue-primary text-blue-black text-xxs sm:text-xs md:text-sm font-medium cursor-pointer">
      <svg class="w-14 h-14 sm:w-18 sm:h-18 text-blue-primary shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <path d="M4 8h3l2-3h6l2 3h3v11H4z" />
        <circle cx="12" cy="13" r="3.5" />
      </svg>
      Kamera
      <input type="file" accept="image/*" capture="environment" class="sr-only" @change="pick" />
    </label>
    <label class="flex items-center justify-center gap-x-4 min-h-40 px-4 bg-white border border-blue-primary/40 hover:border-blue-primary text-blue-black text-xxs sm:text-xs md:text-sm font-medium cursor-pointer">
      <svg class="w-14 h-14 sm:w-18 sm:h-18 text-blue-primary shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
        <path d="M12 16V4M7 9l5-5 5 5M4 20h16" />
      </svg>
      Hochladen
      <input type="file" accept="image/*" :multiple="multiple" class="sr-only" @change="pick" />
    </label>
  </div>
</template>

<script setup>
import { readPhoto } from '../photos.js';

// «Kamera» (takes a photo) and «Hochladen» (from the gallery); emits shrunk JPEG data URLs
defineProps({
  multiple: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['photos']);

const pick = async (event) => {
  const files = [...(event.target.files || [])];
  event.target.value = '';
  if (files.length) {
    emit('photos', await Promise.all(files.map(readPhoto)));
  }
};
</script>

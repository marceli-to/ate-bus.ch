<template>
  <ReportCard :title="title" hint="falls vorhanden">
    <div v-for="(witness, i) in state.witnesses" :key="i" class="grid grid-cols-[1fr_1fr_auto] gap-8 items-end">
      <FormInput v-model="witness.name" :name="`witness_name_${i}`" label="Name" />
      <FormInput v-model="witness.phone" :name="`witness_phone_${i}`" label="Telefon" type="tel" />
      <button type="button" class="h-48 px-8 text-error font-bold cursor-pointer" :aria-label="`${title} ${i + 1} entfernen`" @click="state.witnesses.splice(i, 1)">✕</button>
    </div>
    <button type="button" class="text-blue-primary font-medium cursor-pointer" @click="state.witnesses.push({ name: '', phone: '' })">
      + Zeuge hinzufügen
    </button>
  </ReportCard>
</template>

<script setup>
import FormInput from '../../form/FormInput.vue';
import ReportCard from './ReportCard.vue';
import { useAccidentReport } from '../context.js';

defineProps({
  title: {
    type: String,
    default: 'Zeugen',
  },
});

const { state } = useAccidentReport();
</script>

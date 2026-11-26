<template>
  <div class="flex flex-col gap-y-16">
    <div class="flex flex-col">
      <FormLabel>Linie</FormLabel>
      <div class="grid grid-cols-[repeat(auto-fill,minmax(3.6rem,1fr))] gap-6">
        <button
          v-for="(info, number) in config.lines"
          :key="number"
          type="button"
          class="py-10 font-bold border-2 cursor-pointer transition-colors"
          :class="lineClass(number, info.network)"
          @click="state.line = number"
        >
          {{ number }}
        </button>
        <button
          type="button"
          class="col-span-2 py-10 font-medium border-2 cursor-pointer transition-colors"
          :class="state.line === 'none' ? 'bg-blue-primary border-blue-primary text-white' : 'bg-white border-blue-primary/30 text-blue-black'"
          @click="state.line = 'none'"
        >
          {{ selfReport ? 'Weiss nicht' : 'Keine Linie' }}
        </button>
      </div>
      <p class="mt-6 text-xs text-blue-gray">{{ selfReport ? 'Liniennummer steht vorne am Bus' : 'Blau = VBG · Grau = VBZ' }}</p>
    </div>

    <OptionButtons
      v-if="line"
      v-model="state.direction"
      name="direction"
      label="Fahrtrichtung"
      :columns="1"
      align="left"
      :options="Object.keys(line.directions).map((value) => ({ value, label: `→ ${value}` }))"
    />

    <NoticeBox v-if="needsControlCentre(state, config)" tone="warning" title="VBZ-Linie: Leitstelle informieren">
      <p class="text-xl font-bold">☎ <PhoneLink :number="PHONE.vbzControlCentre" /></p>
      <CheckField v-model="state.controlCentreInformed" label="VBZ-Leitstelle ist informiert" />
    </NoticeBox>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import FormLabel from '../../form/FormLabel.vue';
import OptionButtons from './OptionButtons.vue';
import NoticeBox from './NoticeBox.vue';
import PhoneLink from './PhoneLink.vue';
import CheckField from './CheckField.vue';
import { useAccidentReport } from '../context.js';
import { lineInfo, needsControlCentre } from '../report.js';
import { PHONE } from '../constants.js';

// Line, direction and (VBZ lines, staff only) the call to the VBZ control centre
defineProps({
  // Passenger fills in: «Weiss nicht» instead of «Keine Linie»
  selfReport: {
    type: Boolean,
    default: false,
  },
});

const { config, state } = useAccidentReport();

const line = computed(() => lineInfo(state, config));

const lineClass = (number, network) => {
  if (state.line === number) return 'bg-blue-primary border-blue-primary text-white';
  return network === 'VBZ' ? 'bg-light-grey border-dark-grey text-blue-gray' : 'bg-white border-blue-primary/30 text-blue-primary';
};
</script>

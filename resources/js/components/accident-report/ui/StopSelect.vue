<template>
  <div class="flex flex-col gap-y-8">
    <FormSelect
      :model-value="state.stop"
      name="stop"
      :label="label"
      placeholder="– Haltestelle wählen –"
      :options="options"
      @update:model-value="pick"
    />
    <FormInput v-if="state.stop === '_other'" v-model="state.stopOther" name="stop_other" label="Name der Haltestelle" />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import FormSelect from '../../form/FormSelect.vue';
import FormInput from '../../form/FormInput.vue';
import { useAccidentReport } from '../context.js';
import { lineInfo } from '../report.js';

// Stop: those of the chosen direction, otherwise all stops; «Andere Haltestelle …» for free text
defineProps({
  label: {
    type: String,
    default: 'Haltestelle',
  },
});

const { config, state } = useAccidentReport();

const options = computed(() => {
  const line = lineInfo(state, config);
  const stops = line && state.direction
    ? line.directions[state.direction]
    : [...new Set(Object.values(config.lines).flatMap((l) => Object.values(l.directions).flat()))].sort();
  return [...stops.map((stop) => ({ value: stop, label: stop })), { value: '_other', label: 'Andere Haltestelle …' }];
});

// Picking a stop fills in the town if it is still empty («Illnau, Bahnhof» → «Illnau»)
const pick = (stop) => {
  state.stop = stop;
  if (stop && stop !== '_other' && !state.town.trim()) {
    state.town = stop.split(',')[0].replace(/ ZH$/, '').trim();
  }
};
</script>

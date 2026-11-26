<template>
  <div class="flex flex-col gap-y-12">
    <OptionButtons
      v-if="withKind"
      :model-value="kind"
      :name="`${name}_kind`"
      :options="[{ value: 'bus', label: 'Bus' }, { value: 'car', label: 'PW' }]"
      @update:model-value="(value) => $emit('update:kind', value)"
    />
    <div v-if="kind" class="grid grid-cols-[repeat(auto-fill,minmax(3.6rem,1fr))] gap-6">
      <button
        v-for="vehicle in list"
        :key="vehicle.id"
        type="button"
        class="py-10 font-bold border-2 cursor-pointer transition-colors"
        :class="vehicle.id === modelValue ? 'bg-blue-primary border-blue-primary text-white' : 'bg-white border-blue-primary/30 text-blue-black'"
        @click="$emit('update:modelValue', vehicle.id)"
      >
        {{ vehicle.label.replace('Bus ', '') }}
      </button>
    </div>
    <div v-if="selected" class="bg-light-blue p-12 text-xs md:text-sm leading-relaxed">
      <b class="text-blue-primary">{{ selected.label }}</b> · {{ selected.plate }} · {{ selected.model }}
      <template v-if="details">
        <br>Stammnummer {{ selected.chassis_number }}
        <template v-if="details === 'full'">
          <br>Halter {{ selected.owner || 'ATE Bus AG' }} · Versicherung Zürich
        </template>
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import OptionButtons from './OptionButtons.vue';
import { useAccidentReport } from '../context.js';

// Vehicle of our fleet: Bus / PW, then the number
const props = defineProps({
  modelValue: {
    type: String,
    default: '',
  },
  kind: {
    type: String,
    default: '',
  },
  name: {
    type: String,
    required: true,
  },
  withKind: {
    type: Boolean,
    default: true,
  },
  exclude: {
    type: String,
    default: '',
  },
  // Summary below the grid: '' (plate only), 'number' (+ Stammnummer), 'full' (+ owner, insurance)
  details: {
    type: String,
    default: 'full',
  },
});

defineEmits(['update:modelValue', 'update:kind']);

const { config } = useAccidentReport();

const list = computed(() => config.vehicles.filter((vehicle) => vehicle.kind === props.kind && vehicle.id !== props.exclude));
const selected = computed(() => config.vehicles.find((vehicle) => vehicle.id === props.modelValue) || null);
</script>

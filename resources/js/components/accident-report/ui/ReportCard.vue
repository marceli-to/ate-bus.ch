<template>
  <section class="space-y-16 md:space-y-20">
    <div>
      <h2 class="text-lg md:text-xl xl:text-2xl font-medium">
        {{ title }}
        <span v-if="hint" class="text-xs md:text-sm font-normal text-blue-gray">{{ hint }}</span>
      </h2>
      <p v-if="subtitle || $slots.subtitle" class="mt-4 text-blue-gray">
        <slot name="subtitle">{{ subtitle }}</slot>
      </p>
    </div>
    <slot />
  </section>
</template>

<script setup>
// One block of the form: heading, optional hint and subtitle, then the fields
defineProps({
  title: {
    type: String,
    required: true,
  },
  hint: {
    type: String,
    default: '',
  },
  subtitle: {
    type: String,
    default: '',
  },
});
</script>

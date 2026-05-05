<template>
  <div
    class="relative sm:rounded-2xl mx-0 sm:mx-6 mt-0 sm:mt-6 bg-gradient-to-br from-[#3d27b4] via-[#6b3fd4] to-[#a855f7]"
    :class="allowOverflow ? 'overflow-visible' : 'overflow-hidden'"
  >
    <div class="absolute -top-20 -right-20 w-80 h-80 bg-white/5 rounded-full pointer-events-none"></div>
    <div class="absolute bottom-0 left-1/3 w-56 h-56 bg-white/5 rounded-full pointer-events-none"></div>

    <div class="relative z-10 px-6 sm:px-10 py-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
      <div class="min-w-0">
        <p v-if="kicker" class="text-xs font-bold text-white/50 uppercase tracking-widest mb-2">
          {{ kicker }}
        </p>
        <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight leading-tight">
          {{ title }}
        </h1>
        <p v-if="subtitle" class="mt-2 text-sm text-white/60 max-w-md leading-relaxed">
          {{ subtitle }}
        </p>
      </div>

      <div v-if="$slots.right" class="shrink-0">
        <slot name="right" />
      </div>
    </div>

    <div v-if="$slots.below" class="relative z-10 border-t border-white/10">
      <slot name="below" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  kicker: { type: String, default: '' },
  title: { type: String, required: true },
  subtitle: { type: String, default: '' },
  allowOverflow: { type: Boolean, default: false },
});
</script>

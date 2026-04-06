<template>
  <div 
    class="relative overflow-hidden transition-all duration-500 hover:scale-[1.01]"
    :class="[
      variant === 'dark' ? 'bg-slate-900/80' : 'bg-white/40',
      'backdrop-blur-3xl border border-white/40 rounded-[2.5rem] shadow-2xl shadow-black/10'
    ]"
  >
    <!-- Accent Gradient -->
    <div 
        v-if="accent" 
        class="absolute -top-24 -right-24 w-64 h-64 blur-3xl opacity-20 pointer-events-none"
        :class="accentColor"
    ></div>

    <div class="p-8 relative z-10 h-full flex flex-col">
        <slot />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
    variant: { type: String, default: 'light' },
    accent: { type: Boolean, default: false },
    accentColor: { type: String, default: 'bg-emerald-500' }
});
</script>

<template>
  <section class="relative min-h-[600px] flex items-center justify-center overflow-hidden bg-slate-900">
    <!-- Background -->
    <div class="absolute inset-0">
      <img v-if="d.bg_image" :src="d.bg_image" class="w-full h-full object-cover" alt="Hero" />
      <div v-else class="w-full h-full bg-gradient-to-br from-emerald-800 via-emerald-700 to-emerald-800"></div>
      <div v-if="d.overlay || true" class="absolute inset-0 bg-emerald-800/40 backdrop-brightness-50"></div>
    </div>
    
    <!-- Content -->
    <div class="relative z-10 text-center px-4 md:px-6 max-w-5xl mx-auto">
      <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 backdrop-blur-md rounded-full text-white/90 text-sm font-black uppercase tracking-[0.2em] mb-8 border border-white/20">
        <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse shadow-[0_0_10px_rgba(129,140,248,0.8)]"></span>
        {{ d.tagline || 'Premium Selection 2026' }}
      </div>
      
      <h1 class="text-6xl md:text-8xl font-black text-white leading-[0.85] tracking-tighter mb-8 uppercase italic drop-shadow-2xl">
        {{ d.headline || 'Elevate Your Reality' }}
      </h1>
      
      <p class="text-base md:text-lg md:text-xl text-emerald-100 font-medium mb-6 md:mb-12 max-w-2xl mx-auto leading-relaxed opacity-90">
        {{ d.subheadline || 'Experience the fusion of craftsmanship and modern design.' }}
      </p>
      
      <div class="flex gap-4 justify-center flex-wrap">
        <a :href="d.cta_url || '/catalog'" class="px-10 py-5 bg-emerald-500 text-white font-bold rounded-2xl text-xs uppercase tracking-widest hover:bg-emerald-500 transition-all hover:scale-[1.03] active:scale-95 shadow-2xl shadow-emerald-500/30">
          {{ d.cta_text || 'See Collection' }}
        </a>
        <a v-if="d.secondary_url" :href="d.secondary_url" class="px-10 py-5 bg-white/10 backdrop-blur-md text-white font-bold rounded-2xl text-xs uppercase tracking-widest border border-white/20 hover:bg-white/20 transition-all">
          Browse All
        </a>
      </div>
    </div>

    <!-- Decoration -->
    <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-black/20 to-transparent pointer-events-none"></div>
    <div class="absolute -top-32 -right-32 w-96 h-96 bg-emerald-500/20 rounded-full blur-[100px]"></div>
  </section>
</template>

<script setup>
const props = defineProps({ block: Object, dynamicData: Object, site: Object });
const d = props.block?.data || {};
</script>

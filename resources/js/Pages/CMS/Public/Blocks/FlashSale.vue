<template>
  <section class="py-6 md:py-12 bg-emerald-800 relative overflow-hidden">
    <!-- Abstract Background -->
    <div class="absolute inset-0 opacity-20">
        <div class="absolute -top-20 -left-20 w-80 h-80 bg-emerald-500 rounded-full blur-[100px]"></div>
        <div class="absolute -bottom-20 -right-20 w-80 h-80 bg-emerald-500 rounded-full blur-[100px]"></div>
    </div>

    <div class="max-w-[1400px] mx-auto px-4 md:px-6 relative z-10">
      <div class="flex flex-col lg:flex-row items-center justify-between gap-6 md:gap-12">
        <div class="text-center lg:text-left flex-1">
          <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/10 rounded-full text-sm font-black uppercase tracking-widest text-emerald-300 mb-6 border border-white/10">
            <i class="fas fa-bolt text-emerald-400 animate-pulse"></i> Priority Event
          </div>
          <h2 class="text-3xl md:text-4xl sm:text-4xl md:text-5xl font-black text-white leading-tight uppercase tracking-tighter italic">
            {{ d.title || 'Elite Midnight Sale' }}
          </h2>
          <p class="text-emerald-200 mt-4 font-bold text-sm tracking-widest opacity-70 uppercase">{{ d.subtitle || 'Exclusive reductions for a limited time window' }}</p>
          <div class="mt-8">
             <span class="text-sm font-black text-emerald-400 uppercase tracking-widest block mb-2">Apply Code at Checkout</span>
             <span class="inline-block font-black text-white bg-emerald-500 px-4 md:px-6 py-2 rounded-xl border border-emerald-500/50 shadow-xl shadow-emerald-500/20 tracking-widest">{{ d.coupon || 'STYLEHUB26' }}</span>
          </div>
        </div>

        <!-- Timer -->
        <div class="flex items-center gap-4 bg-white/5 backdrop-blur-md p-4 md:p-8 rounded-[2.5rem] border border-white/10 shadow-2xl">
           <div v-for="unit in countdown" :key="unit.label" class="flex flex-col items-center min-w-[80px]">
              <span class="text-3xl md:text-4xl font-black text-white tabular-nums tracking-tighter">{{ unit.value }}</span>
              <span class="text-sm text-emerald-400 font-black uppercase tracking-widest mt-2">{{ unit.label }}</span>
           </div>
        </div>

        <div class="lg:w-auto w-full">
           <a :href="d.cta_url || '/catalog'" class="block w-full text-center px-12 py-5 bg-white text-emerald-800 font-black rounded-2xl text-xs uppercase tracking-[0.2em] hover:bg-emerald-50 transition-all hover:scale-105 shadow-2xl active:scale-95">
             Access Sale 
           </a>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
const props = defineProps({ block: Object, dynamicData: Object, site: Object });
const d = props.block?.data || {};

const countdown = ref([
  { label: 'Hours', value: '00' },
  { label: 'Mins',  value: '00' },
  { label: 'Secs',  value: '00' },
]);

let timer;
const updateCountdown = () => {
  const end = d.ends_at ? new Date(d.ends_at) : new Date(Date.now() + 18 * 3600000); // 18h default
  const diff = Math.max(0, end - Date.now());
  const h = Math.floor(diff / 3600000);
  const m = Math.floor((diff % 3600000) / 60000);
  const s = Math.floor((diff % 60000) / 1000);
  countdown.value = [
    { label: 'Hours', value: String(h).padStart(2,'0') },
    { label: 'Minutes', value: String(m).padStart(2,'0') },
    { label: 'Seconds', value: String(s).padStart(2,'0') },
  ];
};
onMounted(() => { updateCountdown(); timer = setInterval(updateCountdown, 1000); });
onUnmounted(() => clearInterval(timer));
</script>

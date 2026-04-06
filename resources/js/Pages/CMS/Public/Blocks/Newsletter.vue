<template>
  <section class="py-24 bg-white">
    <div class="max-w-4xl mx-auto px-4 md:px-6">
      <div class="bg-emerald-800 rounded-[3rem] p-12 md:p-20 text-center relative overflow-hidden shadow-2xl">
        <!-- Decoration -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500 rounded-full blur-[100px] opacity-20 -mr-32 -mt-32"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-emerald-500 rounded-full blur-[100px] opacity-10 -ml-32 -mb-32"></div>
        
        <div class="relative z-10">
          <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/5 border border-white/10 rounded-full text-emerald-400 text-sm font-black uppercase tracking-[0.2em] mb-8">
            <i class="fas fa-gift"></i> Exclusive Privilege
          </div>
          
          <h2 class="text-3xl md:text-4xl md:text-4xl md:text-5xl font-black text-white tracking-tighter uppercase leading-none mb-6">
            {{ d.headline || 'Join the Inner Circle' }}
          </h2>
          
          <p class="text-emerald-200/60 font-medium text-base md:text-lg mb-6 md:mb-12 max-w-xl mx-auto leading-relaxed">
            {{ d.subtext || 'Unlock early access to drops and members-only reductions.' }}
          </p>
          
          <form @submit.prevent="subscribe" class="flex flex-col sm:flex-row gap-3 max-w-lg mx-auto">
            <input v-model="email" type="email" :placeholder="d.placeholder || 'Your professional email...'" required
              class="flex-1 px-4 md:px-6 py-5 rounded-2xl text-xs font-black uppercase tracking-widest outline-none bg-white/5 border border-white/10 text-white placeholder:text-emerald-300/30 focus:bg-white/10 transition-all focus:border-emerald-500" />
            <button type="submit" :disabled="loading"
              class="px-10 py-5 bg-white text-emerald-800 font-black rounded-2xl text-xs uppercase tracking-widest hover:bg-emerald-50 transition-all hover:scale-105 active:scale-95 disabled:opacity-70 whitespace-nowrap shadow-xl">
              <i v-if="loading" class="fas fa-spinner fa-spin mr-1"></i>
              {{ loading ? 'Securing...' : (d.cta || 'Gain Access') }}
            </button>
          </form>
          
          <transition enter-active-class="transition-all duration-500" enter-from-class="opacity-0 translate-y-4">
            <div v-if="success" class="mt-8 inline-flex items-center gap-3 px-4 md:px-6 py-3 bg-emerald-500/10 text-emerald-400 rounded-xl text-sm font-black uppercase tracking-[0.2em] border border-emerald-500/20">
              <i class="fas fa-check-circle"></i>
              Welcome. Check your inbox for code <strong>{{ d.coupon || 'STYLEHUB26' }}</strong>
            </div>
          </transition>
          
          <p class="text-emerald-200/30 text-sm font-black uppercase tracking-widest mt-8">Confidentiality guaranteed. Opt-out at any time.</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
const props   = defineProps({ block: Object, dynamicData: Object, site: Object });
const d       = props.block?.data || {};
const email   = ref('');
const loading = ref(false);
const success = ref(false);
const subscribe = async () => {
  loading.value = true;
  try {
    await axios.post('/cms/newsletter/subscribe', { email: email.value, site_id: props.site?.id });
    success.value = true;
    email.value = '';
  } catch { success.value = true; }
  loading.value = false;
};
</script>

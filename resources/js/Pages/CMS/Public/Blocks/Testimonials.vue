<template>
  <section class="py-24 bg-gray-50/30">
    <div class="max-w-[1400px] mx-auto px-4 md:px-6">
      <div class="text-center mb-16">
        <h2 class="text-sm font-black uppercase tracking-[0.3em] text-emerald-500 mb-4">Voice of Experience</h2>
        <h3 class="text-3xl md:text-4xl font-black text-emerald-800 uppercase tracking-tighter">Trusted by Thousands</h3>
        <div class="flex items-center justify-center gap-1.5 mt-4">
          <i v-for="n in 5" :key="n" class="fas fa-star text-amber-400 text-sm"></i>
          <span class="text-sm font-black text-slate-400 ml-2 uppercase tracking-widest">4.9 / 5.0 Precision Rating</span>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:p-8">
        <div v-for="(rev, i) in reviews.slice(0,3)" :key="i" 
            class="bg-white p-10 rounded-[2.5rem] border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-500 hover:-translate-y-2 relative group">
            
            <i class="fas fa-quote-right absolute top-4 md:p-8 right-8 text-emerald-50 text-3xl md:text-4xl group-hover:text-emerald-100 transition-colors"></i>

            <div class="relative z-10">
                <div class="flex gap-0.5 mb-6">
                    <i v-for="s in rev.rating" :key="s" class="fas fa-star text-amber-400 text-xs"></i>
                </div>
                
                <p class="text-slate-600 font-medium leading-relaxed mb-8 italic">"{{ rev.text }}"</p>
                
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 p-0.5 border border-emerald-100">
                        <img :src="rev.avatar" :alt="rev.name" class="w-full h-full rounded-[0.8rem] object-cover" />
                    </div>
                    <div>
                        <p class="text-sm font-black text-emerald-800 uppercase tracking-widest">{{ rev.name }}</p>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-[0.2em] mt-0.5">Verified Purchase</p>
                    </div>
                </div>
            </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
const props = defineProps({ block: Object, dynamicData: Object, site: Object });
const d = props.block?.data || {};

const reviews = d.testimonials || [
  { name:'Alexandra V.', rating:5, text:'The attention to detail in the packaging and the fabric quality is simply unmatched.', avatar:'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&q=80&w=100&h=100' },
  { name:'Marcus Chen', rating:5, text:'Finally a store that gets the urban minimalist aesthetic right. Shipping was incredibly efficient.', avatar:'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&q=80&w=100&h=100' },
  { name:'Elena Rodriguez',rating:5, text:'A seamless shopping experience from start to finish. The OPSCORE integration makes tracking so easy.', avatar:'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&q=80&w=100&h=100' },
];
</script>

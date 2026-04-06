<template>
  <section class="py-24 bg-white">
    <div class="max-w-[1400px] mx-auto px-4 md:px-6">
      <div class="flex items-end justify-between mb-6 md:mb-12">
        <div>
          <h2 class="text-2xl md:text-3xl font-black text-emerald-800 uppercase tracking-tighter">{{ d.title || 'Curated Departments' }}</h2>
          <p class="text-slate-400 text-sm font-bold uppercase tracking-widest mt-1">Discover what's trending</p>
        </div>
        <a href="#" class="text-xs font-black text-emerald-500 hover:underline uppercase tracking-widest">Browse All</a>
      </div>
      
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:p-6">
        <a v-for="cat in (dynamicCategories || defaultCats)" :key="cat.name" :href="cat.url"
          class="group relative overflow-hidden rounded-[2rem] aspect-[4/5] shadow-sm hover:shadow-2xl transition-all duration-700 hover:-translate-y-2">
          
          <img v-if="cat.image" :src="cat.image" :alt="cat.name" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-1000" />
          <div v-else class="absolute inset-0 bg-emerald-50 flex items-center justify-center text-emerald-200">
             <i class="fas fa-folder-open text-3xl md:text-4xl"></i>
          </div>
          
          <div class="absolute inset-0 bg-gradient-to-t from-emerald-800/90 via-emerald-800/20 to-transparent opacity-80 group-hover:opacity-60 transition-opacity"></div>
          
          <!-- Modern Accent -->
          <div class="absolute inset-4 border border-white/10 rounded-[1.5rem] opacity-0 group-hover:opacity-100 transition-opacity duration-500 scale-95 group-hover:scale-100"></div>

          <div class="absolute bottom-0 left-0 right-0 p-4 md:p-8 transform translate-y-2 group-hover:translate-y-0 transition-transform">
            <h4 class="text-xl font-black text-white uppercase tracking-tight">{{ cat.name }}</h4>
            <div class="h-0.5 w-0 group-hover:w-12 bg-emerald-500 transition-all duration-500 mt-2"></div>
            <p class="text-sm font-black text-emerald-300 mt-4 uppercase tracking-[0.2em] opacity-0 group-hover:opacity-100 transition-all delay-75">
              Shop Now <i class="fas fa-chevron-right ml-1"></i>
            </p>
          </div>
        </a>
      </div>
    </div>
  </section>
</template>

<script setup>
import { computed } from 'vue';
const props = defineProps({ block: Object, dynamicData: Object, site: Object });
const d = props.block?.data || {};

// If using dynamicData from Ecom
const dynamicCategories = computed(() => {
    if (!props.dynamicData?.categories) return null;
    return props.dynamicData.categories.slice(0, 4).map(c => ({
        name: c.name,
        url: '/' + c.slug,
        image: c.image || null
    }));
});

const defaultCats = [
  { name:'Men Outerwear',  url:'#', image:'https://images.unsplash.com/photo-1544022613-e87ef75a782a?auto=format&fit=crop&q=80&w=400' },
  { name:'Exquisite Formal', url:'#', image:'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&q=80&w=400' },
  { name:'Sport Active', url:'#', image:'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?auto=format&fit=crop&q=80&w=400' },
  { name:'Minimalist',      url:'#', image:'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?auto=format&fit=crop&q=80&w=400' },
];
</script>

<template>
  <section class="py-10 md:py-20 bg-white">
    <div class="max-w-[1400px] mx-auto px-4 md:px-6">
      <div class="flex items-end justify-between mb-6 md:mb-12">
        <div>
          <h2 class="text-2xl md:text-3xl font-black text-emerald-800 uppercase tracking-tighter">{{ d.title || 'Style Edit' }}</h2>
          <p class="text-slate-400 text-sm font-bold uppercase tracking-widest mt-1">Highly requested pieces</p>
        </div>
        <div class="flex gap-2">
            <button @click="scroll(-360)" class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-slate-400 hover:bg-emerald-500 hover:text-white transition-all shadow-sm">
              <i class="fas fa-arrow-left text-xs"></i>
            </button>
            <button @click="scroll(360)" class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-slate-400 hover:bg-emerald-500 hover:text-white transition-all shadow-sm">
              <i class="fas fa-arrow-right text-xs"></i>
            </button>
        </div>
      </div>

      <div class="relative">
        <div ref="scrollEl" class="flex gap-4 md:p-8 overflow-x-auto pb-10 snap-x snap-mandatory scroll-smooth no-scrollbar" style="scrollbar-width:none">
          <div v-for="product in displayProducts" :key="product.id"
            class="snap-start shrink-0 w-48 md:w-48 md:w-72 group cursor-pointer"
            @click="goToProduct(product)">
            
            <div class="relative aspect-[3/4] bg-gray-50 rounded-[2.5rem] overflow-hidden border border-gray-100 shadow-sm group-hover:shadow-2xl transition-all duration-700">
               <img v-if="primaryImage(product)" :src="primaryImage(product)" :alt="product.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-1000" />
               <div v-else class="w-full h-full bg-emerald-50 flex items-center justify-center text-emerald-200"><i class="fas fa-image text-3xl md:text-4xl"></i></div>

               <!-- Status Overlay -->
               <div v-if="product.discount_pct > 0" class="absolute top-5 left-5 px-3 py-1 bg-emerald-500 text-white text-sm font-black rounded-full shadow-lg">
                  SALE {{ Math.round(product.discount_pct) }}%
               </div>

               <!-- Interactive Primary Action -->
               <div class="absolute inset-0 bg-emerald-800/20 opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
               <button @click.stop="$emit('add-to-cart', product)"
                class="absolute bottom-6 left-6 right-6 py-4 bg-white/95 backdrop-blur-md text-emerald-800 text-sm font-black rounded-2xl opacity-0 group-hover:opacity-100 translate-y-4 group-hover:translate-y-0 transition-all duration-500 hover:bg-emerald-500 hover:text-white shadow-xl">
                 QUICK ADD TO BAG
               </button>
            </div>

            <div class="mt-6 px-2">
              <h4 class="text-sm font-bold text-slate-800 tracking-tight truncate leading-none mb-2">{{ product.name }}</h4>
              <div class="flex items-center justify-between">
                 <div class="flex items-center gap-2">
                    <span class="font-black text-emerald-500 text-base md:text-lg tracking-tighter">{{ currency }}{{ formatPrice(product.price) }}</span>
                    <span v-if="product.mrp > product.price" class="text-xs text-slate-300 line-through font-medium">{{ currency }}{{ formatPrice(product.mrp) }}</span>
                 </div>
                 <div class="flex items-center gap-1">
                    <i class="fas fa-star text-xs text-amber-400"></i>
                    <span class="text-sm font-black text-slate-400 uppercase">4.8</span>
                 </div>
              </div>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="loading" v-for="n in 5" :key="'sk'+n" class="snap-start shrink-0 w-48 md:w-72 rounded-[2.5rem] bg-gray-50/50 animate-pulse border border-gray-100">
            <div class="aspect-[3/4] bg-gray-100 rounded-[2.5rem]"></div>
            <div class="p-3 md:p-6 space-y-3">
              <div class="h-4 bg-gray-100 rounded-full w-2/3"></div>
              <div class="h-6 bg-gray-100 rounded-full w-full"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({ block: Object, dynamicData: Object, site: Object });
const emit  = defineEmits(['add-to-cart']);
const d     = props.block?.data || {};

const scrollEl = ref(null);
const loading  = ref(false);
const products = ref([]);

const currency = computed(() => props.dynamicData?.currency || '₹');

onMounted(async () => {
  // Priority: dynamicData.featured > dynamicData.products > fetch
  if (props.dynamicData?.featured?.length) {
    products.value = props.dynamicData.featured;
  } else if (props.dynamicData?.products?.length) {
    products.value = props.dynamicData.products.slice(0, d.limit || 12);
  } else {
    loading.value = true;
    try {
      const { data } = await axios.get('/cms/products', { params: { site_id: props.site?.id, limit: 12 } });
      products.value = data.data || [];
    } catch {} finally { loading.value = false; }
  }
});

const displayProducts = computed(() => products.value.slice(0, d.limit || 12));

const primaryImage = (p) => {
  try { 
    const imgs = typeof p.images === 'string' ? JSON.parse(p.images) : p.images; 
    return imgs?.[0]?.url || imgs?.[0] || null; 
  } catch { return null; }
};

const formatPrice = (n) => Number(n||0).toLocaleString('en-IN');
const goToProduct  = (p) => { window.location.href = `/${p.slug}`; };
const scroll = (px) => { scrollEl.value?.scrollBy({ left: px, behavior: 'smooth' }); };
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
</style>

<template>
  <section class="py-6 md:py-12 bg-gray-50 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4">
      <h2 class="text-xl md:text-2xl font-black text-gray-900 text-center mb-8">{{ d.title || 'Top Brands' }}</h2>
      <div class="relative">
        <div class="flex gap-4 md:p-8 overflow-hidden">
          <div class="flex gap-4 md:p-8 animate-marquee whitespace-nowrap">
            <div v-for="brand in brands.concat(brands)" :key="brand + Math.random()"
              class="flex items-center justify-center px-8 py-4 bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition-all hover:border-purple-200 cursor-default shrink-0">
              <span class="font-black text-gray-500 text-sm tracking-wider hover:text-purple-600 transition-colors">{{ brand }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
<script setup>
const props  = defineProps({ block: Object, dynamicData: Object, site: Object });
const d      = props.block?.data || {};
const brands = d.brands || ["Levi's","Van Heusen","Biba","Puma","Nike","Zara","Mango","H&M","Bata","Wrangler","Arrow","Peter England"];
</script>
<style scoped>
@keyframes marquee { 0%{transform:translateX(0)} 100%{transform:translateX(-50%)} }
.animate-marquee { animation: marquee 20s linear infinite; }
</style>

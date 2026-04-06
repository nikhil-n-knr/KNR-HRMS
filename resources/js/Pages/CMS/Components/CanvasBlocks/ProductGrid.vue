<template>
    <section class="w-full" :style="sectionStyle">
        <div class="max-w-7xl mx-auto px-6">
            <div v-if="block.content?.title" class="text-center mb-10">
                <h2 class="text-4xl font-black mb-3">{{ block.content.title }}</h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                <div v-for="(product, i) in products" :key="i"
                    class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all group cursor-pointer">
                    <div class="aspect-[4/3] bg-gray-50 flex items-center justify-center relative overflow-hidden">
                        <img v-if="product.image" :src="product.image" class="w-full h-full object-cover" />
                        <div v-else class="flex flex-col items-center justify-center text-gray-300 gap-2">
                            <i class="fas fa-image text-2xl"></i>
                            <span class="text-sm font-bold">Product {{ i+1 }}</span>
                        </div>
                        <div v-if="product.badge" class="absolute top-2 left-2 px-2 py-0.5 rounded-full text-sm font-black bg-red-500 text-white">
                            {{ product.badge }}
                        </div>
                    </div>
                    <div class="p-4">
                        <h3 class="font-black text-gray-900 text-sm mb-1 group-hover:text-indigo-600 transition-colors truncate">
                            {{ product.name || 'Product Name' }}
                        </h3>
                        <div class="flex items-center justify-between mt-3">
                            <div>
                                <span class="text-lg font-black text-gray-900">₹{{ product.price || '999' }}</span>
                                <span v-if="product.mrp" class="text-xs text-gray-400 line-through ml-1">₹{{ product.mrp }}</span>
                            </div>
                            <button class="w-8 h-8 rounded-full bg-indigo-600 text-white flex items-center justify-center hover:bg-indigo-700 transition-all shadow-sm">
                                <i class="fas fa-cart-plus text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script setup>
import { computed } from 'vue';
const props = defineProps({ block: Object, dynamicData: Object });
const products = computed(() => props.block?.content?.products?.length ? props.block.content.products : Array(6).fill({ name:'Sample Product', price:'1,299', mrp:'1,999', badge:'New', image:'' }));
const sectionStyle = computed(() => {
    const s = props.block?.styles || {};
    return { backgroundColor: s.bgColor||'#fff', paddingTop:(s.paddingY||80)+'px', paddingBottom:(s.paddingY||80)+'px' };
});
</script>

<template>
    <section class="w-full" :style="sectionStyle">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-14">
                <h2 class="text-4xl font-black mb-4">{{ block.content?.title || 'What Our Customers Say' }}</h2>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="(item, i) in items" :key="i"
                    class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-all">
                    <div class="flex text-amber-400 mb-4 text-sm gap-0.5">
                        <i v-for="n in 5" :key="n" class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-600 italic leading-relaxed mb-6">"{{ item.quote }}"</p>
                    <div class="flex items-center gap-3 border-t border-gray-100 pt-4">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-400 flex items-center justify-center text-white font-black text-sm">
                            {{ item.name?.charAt(0) || 'U' }}
                        </div>
                        <div>
                            <p class="font-black text-gray-900 text-sm">{{ item.name }}</p>
                            <p class="text-xs text-gray-400">{{ item.company }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script setup>
import { computed } from 'vue';
const props = defineProps({ block: Object });
const items = computed(() => props.block?.content?.items || [
    { name: 'Priya Sharma', company: 'TechStartup India', quote: 'This CMS transformed our entire web presence. Setup was done in a day!' },
    { name: 'Rahul Mehta', company: 'D2C Brand', quote: 'The ecommerce Razorpay integration was seamless. Our store went live in hours.' },
    { name: 'Ananya Patel', company: 'SaaS Co', quote: 'CRM → website data binding is a game changer. Our team loves it.' },
]);
const sectionStyle = computed(() => {
    const s = props.block?.styles || {};
    return { backgroundColor: s.bgColor||'#f8fafc', paddingTop:(s.paddingY||80)+'px', paddingBottom:(s.paddingY||80)+'px' };
});
</script>

<template>
    <section class="w-full" :style="sectionStyle">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-14">
                <h2 class="text-4xl font-black mb-4">{{ block.content?.title || 'Simple, Transparent Pricing' }}</h2>
                <p class="text-lg opacity-70">{{ block.content?.subtitle || 'No hidden fees. Cancel anytime.' }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="(plan, i) in plans" :key="i"
                    class="rounded-2xl border-2 p-8 relative"
                    :class="plan.popular ? 'border-indigo-500 bg-gradient-to-b from-indigo-50 to-white shadow-xl scale-105' : 'border-gray-200 bg-white'">
                    <div v-if="plan.popular" class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1 bg-indigo-500 text-white text-sm font-black uppercase tracking-wider rounded-full shadow">
                        Most Popular
                    </div>
                    <h3 class="text-lg font-black mb-2">{{ plan.name }}</h3>
                    <div class="flex items-end gap-1 mb-6">
                        <span class="text-4xl font-black">₹{{ plan.price }}</span>
                        <span class="text-gray-400 mb-1">/mo</span>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li v-for="(feature, fi) in plan.features" :key="fi" class="flex items-center gap-2 text-sm">
                            <i class="fas fa-check-circle text-emerald-500 text-xs shrink-0"></i>
                            {{ feature }}
                        </li>
                    </ul>
                    <a href="#" class="block w-full py-3 rounded-xl text-center font-bold text-sm transition-all"
                        :class="plan.popular ? 'bg-indigo-600 text-white hover:bg-indigo-700 shadow-lg shadow-indigo-500/30' : 'bg-gray-100 text-gray-800 hover:bg-gray-200'">
                        {{ plan.cta || 'Get Started' }}
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>
<script setup>
import { computed } from 'vue';
const props = defineProps({ block: Object });
const plans = computed(() => props.block?.content?.plans || [
    { name:'Starter', price:'999', popular:false, cta:'Get Started', features:['1 Site','10 Pages','50 Products','CRM Sync'] },
    { name:'Growth', price:'2,499', popular:true, cta:'Start Trial', features:['5 Sites','100 Pages','Unlimited Products','Razorpay','A/B Tests','Priority Support'] },
    { name:'Scale', price:'6,999', popular:false, cta:'Contact Sales', features:['Unlimited Sites','PWA Builder','Custom Domain','White Label','SLA'] },
]);
const sectionStyle = computed(() => {
    const s = props.block?.styles || {};
    return { backgroundColor: s.bgColor||'#f8fafc', paddingTop:(s.paddingY||80)+'px', paddingBottom:(s.paddingY||80)+'px' };
});
</script>

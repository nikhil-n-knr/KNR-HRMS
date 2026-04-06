<template>
    <section class="w-full" :style="sectionStyle">
        <div class="max-w-3xl mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-black mb-4">{{ block.content?.title || 'Frequently Asked Questions' }}</h2>
            </div>
            <div class="space-y-3">
                <div v-for="(item, i) in faqs" :key="i"
                    class="rounded-2xl border border-gray-200 overflow-hidden bg-white">
                    <button class="w-full px-6 py-4 flex items-center justify-between text-left font-bold text-gray-900 hover:bg-gray-50 transition-colors"
                        @click="open === i ? open = null : open = i">
                        <span>{{ item.q }}</span>
                        <i class="fas text-gray-400 text-xs ml-4 transition-transform"
                            :class="open === i ? 'fa-minus rotate-180' : 'fa-plus'"></i>
                    </button>
                    <div v-show="open === i" class="px-6 pb-5 text-sm text-gray-500 leading-relaxed border-t border-gray-100 pt-4">
                        {{ item.a }}
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script setup>
import { ref, computed } from 'vue';
const props = defineProps({ block: Object });
const open = ref(0);
const faqs = computed(() => props.block?.content?.faqs || [
    { q:'How does pricing work?', a:'We charge monthly per site. You can upgrade or downgrade anytime with no penalty.' },
    { q:'Can I import from WordPress?', a:'Yes — full migration support with our import tool. Contact us for assisted migrations.' },
    { q:'Is Razorpay integration included?', a:'Yes. Simply add your API keys in Settings → Payments and you are live.' },
    { q:'How many sites can I create?', a:'Depending on your plan: Starter (1), Growth (5), Scale (unlimited).' },
]);
const sectionStyle = computed(() => {
    const s = props.block?.styles || {};
    return { backgroundColor: s.bgColor||'#f8fafc', paddingTop:(s.paddingY||80)+'px', paddingBottom:(s.paddingY||80)+'px' };
});
</script>

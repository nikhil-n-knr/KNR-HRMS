<template>
    <section class="w-full text-center" :style="sectionStyle">
        <div class="max-w-3xl mx-auto px-6">
            <h2 class="text-4xl font-black mb-4">{{ block.content?.title || 'Ready to Get Started?' }}</h2>
            <p v-if="block.content?.subtitle" class="text-lg opacity-80 mb-8">{{ block.content.subtitle }}</p>
            <div class="flex flex-wrap items-center justify-center gap-4">
                <a :href="block.content?.btn_link || '#'" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl font-black text-sm bg-white text-indigo-700 shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all">
                    {{ block.content?.btn_text || 'Start Free Trial' }} <i class="fas fa-arrow-right text-xs"></i>
                </a>
                <a v-if="block.content?.btn2_text" :href="block.content?.btn2_link || '#'" class="px-8 py-4 rounded-xl font-bold text-sm border-2 border-white/40 hover:border-white transition-all">
                    {{ block.content.btn2_text }}
                </a>
            </div>
        </div>
    </section>
</template>
<script setup>
import { computed } from 'vue';
const props = defineProps({ block: Object });
const sectionStyle = computed(() => {
    const s = props.block?.styles || {};
    return { background: s.bgGradient || s.bgColor || 'linear-gradient(135deg,#6366f1,#8b5cf6)', paddingTop:(s.paddingY||80)+'px', paddingBottom:(s.paddingY||80)+'px', color: s.textColor||'#fff' };
});
</script>

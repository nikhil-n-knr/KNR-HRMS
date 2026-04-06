<template>
    <section class="relative w-full overflow-hidden" :style="sectionStyle">
        <!-- Background overlay for images -->
        <div v-if="block.styles?.bgImage" class="absolute inset-0 bg-black/40"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-6 flex items-center"
            :class="layout === 'centered' ? 'flex-col text-center min-h-[500px] justify-center' : 'gap-16 min-h-[480px]'">

            <!-- Text side -->
            <div :class="layout === 'split' ? 'w-1/2' : 'max-w-3xl'">
                <div v-if="block.content?.badge" class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-white/20 bg-white/10 text-base font-bold mb-6 backdrop-blur-sm">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    {{ block.content.badge }}
                </div>

                <h1 class="text-5xl font-black leading-tight tracking-tight mb-6" :style="headingStyle">
                    {{ block.content?.title || 'Your Amazing Headline' }}
                </h1>

                <p class="text-xl leading-relaxed mb-8 opacity-80">
                    {{ block.content?.subtitle || 'A compelling subtitle that drives conversions.' }}
                </p>

                <div v-if="block.content?.btn_text" class="flex flex-wrap gap-3" :class="layout === 'centered' ? 'justify-center' : ''">
                    <a :href="block.content?.btn_link || '#'"
                        class="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl font-black text-sm shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all"
                        :style="btnStyle">
                        {{ block.content.btn_text }}
                        <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                    <a v-if="block.content?.btn2_text" :href="block.content?.btn2_link || '#'"
                        class="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl font-bold text-sm border border-current opacity-80 hover:opacity-100 transition-all">
                        {{ block.content.btn2_text }}
                    </a>
                </div>

                <!-- Trust indicators -->
                <div v-if="block.content?.trust" class="flex items-center gap-4 mt-8 text-sm opacity-60">
                    <span v-for="t in block.content.trust" :key="t" class="flex items-center gap-1.5">
                        <i class="fas fa-check-circle text-emerald-400 text-xs"></i>{{ t }}
                    </span>
                </div>
            </div>

            <!-- Image / visual side for split layout -->
            <div v-if="layout === 'split'" class="w-1/2 flex items-center justify-center">
                <div v-if="block.content?.hero_image"
                    class="w-full h-80 rounded-2xl overflow-hidden shadow-2xl ring-1 ring-white/10">
                    <img :src="block.content.hero_image" class="w-full h-full object-cover" />
                </div>
                <div v-else class="w-full h-80 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center">
                    <div class="text-center opacity-50">
                        <i class="fas fa-image text-4xl mb-2"></i>
                        <p class="text-sm font-bold">Upload Hero Image</p>
                        <p class="text-xs opacity-70">Recommended: 800×600px</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue';
const props = defineProps({ block: Object, dynamicData: Object });

const layout = computed(() => props.block?.content?.layout || 'split');

const isDark = computed(() => {
    const bg = props.block?.styles?.bgColor || '';
    const textColor = props.block?.styles?.textColor || '';
    if (textColor === '#fff' || textColor === '#ffffff') return true;
    return bg.startsWith('#0') || bg.startsWith('#1') || bg.startsWith('#2') || bg.startsWith('#3');
});

const sectionStyle = computed(() => {
    const s = props.block?.styles || {};
    return {
        backgroundColor: s.bgColor || '#0f172a',
        background: s.bgGradient || undefined,
        backgroundImage: s.bgImage ? `url('${s.bgImage}')` : undefined,
        backgroundSize: s.bgImage ? 'cover' : undefined,
        backgroundPosition: s.bgImage ? 'center' : undefined,
        color: s.textColor || '#ffffff',
        paddingTop: (s.paddingY || 120) + 'px',
        paddingBottom: (s.paddingY || 120) + 'px',
    };
});

const headingStyle = computed(() => ({ color: props.block?.styles?.textColor || '#ffffff' }));
const btnStyle     = computed(() => ({ backgroundColor: '#10b981', color: '#fff' }));
</script>

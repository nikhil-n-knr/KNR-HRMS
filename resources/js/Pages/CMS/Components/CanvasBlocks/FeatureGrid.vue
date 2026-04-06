<template>
    <section class="w-full" :style="sectionStyle">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center mb-14" v-if="block.content?.title">
                <h2 class="text-4xl font-black tracking-tight mb-4">{{ block.content.title }}</h2>
                <p v-if="block.content?.subtitle" class="text-lg opacity-70">{{ block.content.subtitle }}</p>
            </div>

            <!-- 3-col icon grid -->
            <div v-if="!layout || layout === 'grid3'" class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div v-for="(item, i) in items" :key="i"
                    class="p-6 rounded-2xl bg-white shadow-sm border border-gray-100 hover:shadow-md hover:-translate-y-1 transition-all group">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4 text-xl"
                        :style="{ backgroundColor: iconBg(i), color: iconColor(i) }">
                        <i :class="item.icon || 'fas fa-star'"></i>
                    </div>
                    <h3 class="font-black text-gray-900 mb-2">{{ item.title }}</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">{{ item.body }}</p>
                </div>
            </div>

            <!-- Zig-zag layout -->
            <div v-else-if="layout === 'zigzag'" class="space-y-16">
                <div v-for="(item, i) in items" :key="i"
                    class="flex items-center gap-12"
                    :class="i % 2 === 1 ? 'flex-row-reverse' : ''">
                    <div class="flex-1">
                        <h3 class="text-3xl font-black mb-4">{{ item.title }}</h3>
                        <p class="text-gray-500 leading-relaxed">{{ item.body }}</p>
                    </div>
                    <div class="w-80 h-56 rounded-2xl bg-gray-100 flex items-center justify-center">
                        <img v-if="item.image" :src="item.image" class="w-full h-full object-cover rounded-2xl" />
                        <i v-else class="fas fa-image text-3xl text-gray-300"></i>
                    </div>
                </div>
            </div>

            <!-- Steps layout -->
            <div v-else-if="layout === 'steps'" class="flex items-start gap-0 justify-center">
                <div v-for="(item, i) in items" :key="i" class="flex-1 text-center relative">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-black text-lg mx-auto mb-4 shadow-lg"
                        style="background:linear-gradient(135deg,#6366f1,#8b5cf6)">
                        {{ item.step || i + 1 }}
                    </div>
                    <h3 class="font-black text-gray-900 mb-1">{{ item.title }}</h3>
                    <p class="text-sm text-gray-500">{{ item.body }}</p>
                    <!-- Connector -->
                    <div v-if="i < items.length - 1"
                        class="absolute top-6 left-1/2 w-full h-0.5 bg-gray-200 -z-10"></div>
                </div>
            </div>

            <!-- Logo cloud -->
            <div v-else-if="layout === 'logos'" class="text-center">
                <p class="text-xs font-bold uppercase tracking-widest text-gray-400 mb-8">{{ block.content?.title }}</p>
                <div class="flex flex-wrap items-center justify-center gap-8">
                    <div v-for="n in 6" :key="n" class="h-8 w-24 bg-gray-100 rounded-lg animate-pulse"></div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue';
const props = defineProps({ block: Object, dynamicData: Object });
const layout = computed(() => props.block?.content?.layout || 'grid3');
const items = computed(() => props.block?.content?.items || [
    { icon: 'fas fa-rocket', title: 'Lightning Fast', body: 'Optimized for speed and performance at scale.' },
    { icon: 'fas fa-brain', title: 'AI Powered', body: 'Built-in Gemini integration for smart automation.' },
    { icon: 'fas fa-shield-alt', title: 'Enterprise Secure', body: 'SOC2 compliant, GDPR ready, end-to-end encrypted.' },
]);
const colors = ['#6366f1','#10b981','#f59e0b','#ec4899','#06b6d4','#8b5cf6'];
const lightBgs = ['#eef2ff','#ecfdf5','#fffbeb','#fdf2f8','#ecfeff','#f5f3ff'];
const iconColor = (i) => colors[i % colors.length];
const iconBg    = (i) => lightBgs[i % lightBgs.length];
const sectionStyle = computed(() => {
    const s = props.block?.styles || {};
    return {
        backgroundColor: s.bgColor || '#ffffff',
        paddingTop: (s.paddingY || 80) + 'px',
        paddingBottom: (s.paddingY || 80) + 'px',
        color: s.textColor || '#111827',
    };
});
</script>

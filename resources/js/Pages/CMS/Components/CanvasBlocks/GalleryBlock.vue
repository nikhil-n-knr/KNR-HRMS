<template>
    <section class="w-full" :style="sectionStyle">
        <div class="max-w-7xl mx-auto px-6">
            <div v-if="block.content?.title" class="text-center mb-10">
                <h2 class="text-4xl font-black mb-4">{{ block.content.title }}</h2>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div v-for="(img, i) in images" :key="i"
                    class="rounded-2xl overflow-hidden bg-gray-100 aspect-square hover:scale-[1.02] transition-all group cursor-pointer">
                    <img v-if="img.url" :src="img.url" :alt="img.alt || ''" class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                        <i class="fas fa-image text-3xl text-gray-200 mb-2 group-hover:text-gray-300 transition-colors"></i>
                        <span class="text-xs text-gray-300">Image {{ i+1 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
<script setup>
import { computed } from 'vue';
const props = defineProps({ block: Object });
const images = computed(() => props.block?.content?.images?.length ? props.block.content.images : Array(6).fill({url:'',alt:''}) );
const sectionStyle = computed(() => {
    const s = props.block?.styles || {};
    return { backgroundColor: s.bgColor||'#fff', paddingTop:(s.paddingY||60)+'px', paddingBottom:(s.paddingY||60)+'px' };
});
</script>

<template>
    <section class="w-full relative overflow-hidden" :style="sectionStyle">
        <div class="max-w-4xl mx-auto px-6 text-center">
            <div v-if="block.content?.title" class="mb-8">
                <h2 class="text-4xl font-black mb-4">{{ block.content.title }}</h2>
            </div>
            <div class="relative rounded-2xl overflow-hidden bg-black aspect-video shadow-2xl">
                <iframe v-if="block.content?.video_url && block.content.video_url.includes('youtube')"
                    :src="block.content.video_url.replace('watch?v=','embed/')"
                    class="w-full h-full" frameborder="0" allow="autoplay; fullscreen" allowfullscreen></iframe>
                <video v-else-if="block.content?.video_url"
                    :src="block.content.video_url" :autoplay="block.content?.autoplay" controls
                    class="w-full h-full object-cover"></video>
                <div v-else class="w-full h-full flex flex-col items-center justify-center bg-gray-900 text-gray-400">
                    <div class="w-20 h-20 rounded-full bg-white/10 flex items-center justify-center mb-4">
                        <i class="fas fa-play text-2xl text-white/50"></i>
                    </div>
                    <p class="font-bold text-sm">Add Video URL in Style Inspector</p>
                    <p class="text-xs opacity-50 mt-1">YouTube or direct MP4 supported</p>
                </div>
            </div>
        </div>
    </section>
</template>
<script setup>
import { computed } from 'vue';
const props = defineProps({ block: Object });
const sectionStyle = computed(() => {
    const s = props.block?.styles || {};
    return { backgroundColor: s.bgColor||'#fff', paddingTop:(s.paddingY||80)+'px', paddingBottom:(s.paddingY||80)+'px' };
});
</script>

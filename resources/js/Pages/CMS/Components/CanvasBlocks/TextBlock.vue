<template>
    <section class="w-full" :style="sectionStyle">
        <div class="max-w-4xl mx-auto px-6">
            <div v-if="block.content?.title" class="text-center mb-10">
                <h2 class="text-4xl font-black mb-4">{{ block.content.title }}</h2>
                <p v-if="block.content?.subtitle" class="text-lg opacity-70">{{ block.content.subtitle }}</p>
            </div>
            <div class="prose prose-lg max-w-none" v-html="block.content?.body || '<p>Add your content here. Edit in the Style Inspector.</p>'"></div>
        </div>
    </section>
</template>
<script setup>
import { computed } from 'vue';
const props = defineProps({ block: Object });
const sectionStyle = computed(() => {
    const s = props.block?.styles || {};
    return { backgroundColor: s.bgColor||'#fff', paddingTop:(s.paddingY||80)+'px', paddingBottom:(s.paddingY||80)+'px', color: s.textColor||'#111827' };
});
</script>

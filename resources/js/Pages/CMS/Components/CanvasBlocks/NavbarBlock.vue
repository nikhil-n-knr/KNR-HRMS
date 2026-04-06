<template>
    <nav class="w-full sticky top-0 z-50" :style="navStyle">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
            <span class="font-black text-xl">{{ block.content?.logo_text || 'KNR Office' }}</span>
            <div class="flex items-center gap-6 text-sm font-bold">
                <a v-for="link in (block.content?.links || [{label:'Features',href:'#'},{label:'Pricing',href:'#'},{label:'Blog',href:'#'}])" :key="link.label" :href="link.href" class="opacity-70 hover:opacity-100 transition-opacity">
                    {{ link.label }}
                </a>
            </div>
            <a :href="block.content?.btn_link || '#'" class="px-4 py-2 rounded-xl text-xs font-black bg-indigo-600 text-white hover:bg-indigo-700 transition-all shadow-sm">
                {{ block.content?.btn_text || 'Get Started' }}
            </a>
        </div>
    </nav>
</template>
<script setup>
import { computed } from 'vue';
const props = defineProps({ block: Object });
const navStyle = computed(() => ({ backgroundColor: props.block?.styles?.bgColor||'#fff', borderBottom:'1px solid #e5e7eb', boxShadow:'0 1px 3px rgba(0,0,0,0.05)' }));
</script>

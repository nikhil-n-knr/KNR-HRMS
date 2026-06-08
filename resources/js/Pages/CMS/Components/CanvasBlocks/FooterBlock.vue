<template>
    <footer class="w-full" :style="sectionStyle">
        <div v-if="layout === 'mega'" class="max-w-7xl mx-auto px-6 pt-16 pb-8">
            <div class="grid grid-cols-4 gap-8 pb-12 border-b" :style="{ borderColor: 'rgba(255,255,255,0.1)' }">
                <div>
                    <div class="font-black text-xl mb-3">{{ block.content?.logo_text || 'OneHub Connect' }}</div>
                    <p class="text-sm opacity-60 leading-relaxed">{{ block.content?.tagline || 'Built for growing teams.' }}</p>
                    <div class="flex gap-3 mt-4">
                        <a v-for="s in (block.content?.socials || [])" :key="s.icon" :href="s.url" class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center hover:bg-white/20 transition-all text-sm">
                            <i :class="s.icon"></i>
                        </a>
                        <a v-for="n in ['fab fa-twitter','fab fa-linkedin','fab fa-instagram']" :key="n" href="#" class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center hover:bg-white/20 transition-all text-sm" v-if="!block.content?.socials?.length">
                            <i :class="n"></i>
                        </a>
                    </div>
                </div>
                <div v-for="(col, ci) in (block.content?.columns || [{ heading:'Product', links:['Features','Pricing','Changelog'] },{ heading:'Company', links:['About','Careers','Blog'] },{ heading:'Legal', links:['Privacy','Terms','Cookies'] }])" :key="ci">
                    <h4 class="font-black text-xs uppercase tracking-widest mb-4 opacity-50">{{ col.heading }}</h4>
                    <ul class="space-y-2">
                        <li v-for="link in col.links" :key="link">
                            <a href="#" class="text-sm opacity-60 hover:opacity-100 transition-opacity">{{ link.label || link }}</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="pt-6 text-xs opacity-40 flex items-center justify-between">
                <span>{{ block.content?.copyright || '© 2026 OneHub Connect. All rights reserved.' }}</span>
                <span>🇮🇳 Made in India</span>
            </div>
        </div>
        <div v-else class="max-w-7xl mx-auto px-6 py-6 flex items-center justify-between text-sm opacity-70">
            <span>{{ block.content?.copyright || '© 2026 OneHub Connect' }}</span>
            <div class="flex gap-4">
                <a href="#" class="hover:opacity-100 transition-opacity">Privacy</a>
                <a href="#" class="hover:opacity-100 transition-opacity">Terms</a>
            </div>
        </div>
    </footer>
</template>
<script setup>
import { computed } from 'vue';
const props = defineProps({ block: Object });
const layout = computed(() => props.block?.content?.layout || 'mega');
const sectionStyle = computed(() => {
    const s = props.block?.styles || {};
    return { backgroundColor: s.bgColor||'#0f172a', color: s.textColor||'#94a3b8' };
});
</script>

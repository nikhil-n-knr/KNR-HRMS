<template>
    <section class="w-full" :style="sectionStyle">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div v-for="(stat, i) in stats" :key="i">
                    <div class="text-5xl font-black mb-2" :style="{ color: colors[i % colors.length] }">
                        {{ stat.number }}
                    </div>
                    <div class="text-sm font-bold uppercase tracking-widest opacity-70">{{ stat.label }}</div>
                </div>
            </div>
        </div>
    </section>
</template>
<script setup>
import { computed } from 'vue';
const props = defineProps({ block: Object });
const stats = computed(() => props.block?.content?.stats || [
    { number:'100K+', label:'Customers' }, { number:'₹2Cr+', label:'GMV Processed' },
    { number:'99.9%', label:'Uptime' }, { number:'40+', label:'Integrations' },
]);
const colors = ['#6366f1','#10b981','#f59e0b','#ec4899'];
const sectionStyle = computed(() => {
    const s = props.block?.styles || {};
    return { backgroundColor: s.bgColor||'#0f172a', paddingTop:(s.paddingY||60)+'px', paddingBottom:(s.paddingY||60)+'px', color: s.textColor||'#fff' };
});
</script>

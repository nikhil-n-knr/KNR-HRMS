<template>
    <div class="w-full py-3 px-6 flex items-center justify-between text-sm font-bold" :style="barStyle">
        <span class="flex items-center gap-2">
            <i v-if="block.content?.icon" :class="block.content.icon"></i>
            {{ block.content?.text || '🚀 Special offer – Use code LAUNCH20 for 20% off!' }}
        </span>
        <a v-if="block.content?.link" :href="block.content.link" class="underline hover:no-underline text-xs opacity-80 hover:opacity-100">
            Learn more →
        </a>
        <div v-if="block.content?.countdown_to" class="flex items-center gap-1 font-mono text-xs">
            <span class="bg-black/20 rounded px-1.5 py-0.5">{{ countdown.h }}h</span>
            <span>:</span>
            <span class="bg-black/20 rounded px-1.5 py-0.5">{{ countdown.m }}m</span>
            <span>:</span>
            <span class="bg-black/20 rounded px-1.5 py-0.5">{{ countdown.s }}s</span>
        </div>
    </div>
</template>
<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
const props = defineProps({ block: Object });
const barStyle = computed(() => ({
    backgroundColor: props.block?.styles?.bgColor || '#fbbf24',
    color: props.block?.styles?.textColor || '#1c1917',
}));

const countdown = ref({ h: '00', m: '00', s: '00' });
let timer = null;
const pad = (n) => String(n).padStart(2, '0');
const updateCountdown = () => {
    const target = new Date(props.block?.content?.countdown_to || '');
    const diff = Math.max(0, Math.floor((target - Date.now()) / 1000));
    countdown.value = { h: pad(Math.floor(diff / 3600)), m: pad(Math.floor((diff % 3600) / 60)), s: pad(diff % 60) };
};
onMounted(() => { updateCountdown(); timer = setInterval(updateCountdown, 1000); });
onUnmounted(() => clearInterval(timer));
</script>

<template>
    <section class="w-full" :style="sectionStyle">
        <div class="max-w-2xl mx-auto px-6">
            <div class="text-center mb-10">
                <h2 class="text-4xl font-black mb-4">{{ block.content?.title || 'Get in Touch' }}</h2>
            </div>
            <form @submit.prevent class="bg-white rounded-2xl p-8 shadow-sm border border-gray-100 space-y-4">
                <div v-for="field in fields" :key="field" class="space-y-1">
                    <label class="text-xs font-black uppercase tracking-widest text-gray-500">{{ field }}</label>
                    <textarea v-if="field === 'message'" rows="4"
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all resize-none"
                        :placeholder="'Your ' + field"></textarea>
                    <input v-else :type="field === 'email' ? 'email' : 'text'"
                        class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                        :placeholder="'Your ' + field" />
                </div>
                <button type="submit" class="w-full py-3.5 rounded-xl font-black text-sm bg-indigo-600 text-white hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-500/20 flex items-center justify-center gap-2 mt-2">
                    {{ block.content?.btn_text || 'Send Message' }} <i class="fas fa-paper-plane text-xs"></i>
                </button>
                <p class="text-center text-xs text-gray-400">We reply within 24 hours.</p>
            </form>
        </div>
    </section>
</template>
<script setup>
import { computed } from 'vue';
const props = defineProps({ block: Object });
const fields = computed(() => props.block?.content?.fields || ['name','email','phone','message']);
const sectionStyle = computed(() => {
    const s = props.block?.styles || {};
    return { backgroundColor: s.bgColor||'#f8fafc', paddingTop:(s.paddingY||80)+'px', paddingBottom:(s.paddingY||80)+'px' };
});
</script>

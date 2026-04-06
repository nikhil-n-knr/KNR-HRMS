<template>
    <div class="space-y-6">
        <!-- Campaign Builder Timeline -->
        <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm relative overflow-hidden min-h-[500px]">
            <div class="flex justify-between items-start mb-12">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Campaign Builder</h2>
                    <p class="text-sm text-gray-500 font-medium tracking-tight">Advanced multi-channel orchestration timeline</p>
                </div>
                <div class="flex gap-2">
                    <button class="px-4 py-2 bg-emerald-50 rounded-xl text-xs font-black text-emerald-700 border border-emerald-100 uppercase tracking-widest"><i class="fas fa-dice"></i> A/B TEST</button>
                    <button class="px-4 py-2 bg-indigo-600 rounded-xl text-xs font-black text-white shadow-lg shadow-indigo-500/20 uppercase tracking-widest"><i class="fas fa-rocket"></i> LAUNCH</button>
                </div>
            </div>

            <!-- Draggable Timeline Mock -->
            <div class="flex items-center gap-6 overflow-x-auto py-10 px-4">
                <div v-for="(step, idx) in sequences[0].steps" :key="step" class="flex items-center gap-6 shrink-0">
                    <div class="w-48 p-6 bg-white border-2 border-dashed border-gray-200 rounded-3xl relative group hover:border-indigo-400 hover:scale-105 transition-all cursor-move shadow-sm">
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-gray-900 text-white rounded-full text-sm font-black uppercase tracking-widest leading-none">Day {{ (idx*3)+1 }}</div>
                        <div class="flex flex-col items-center text-center gap-3">
                            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-black shadow-inner border border-white">
                                <i :class="getIcon(step)"></i>
                            </div>
                            <span class="text-xs font-black text-gray-900 uppercase tracking-tighter">{{ step }}</span>
                            <div class="w-full flex justify-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-200"></span>
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-200"></span>
                            </div>
                        </div>
                    </div>
                    <div v-if="idx < sequences[0].steps.length - 1" class="w-12 flex justify-center text-indigo-300">
                        <i class="fas fa-long-arrow-alt-right text-3xl animate-pulse"></i>
                    </div>
                </div>

                <!-- Add Node Button -->
                <div class="w-20 h-20 rounded-full border-4 border-dashed border-gray-100 flex items-center justify-center text-gray-300 hover:border-indigo-300 hover:text-indigo-400 transition-all cursor-pointer group hover:bg-indigo-50">
                    <i class="fas fa-plus text-2xl group-hover:scale-125 transition-transform"></i>
                </div>
            </div>

            <!-- Variant Testing Results -->
            <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div v-for="variant in ab_tests[0].variants" :key="variant" 
                    class="p-6 rounded-2xl border-2 transition-all relative group overflow-hidden"
                    :class="variant.includes('Winner') ? 'bg-emerald-50 border-emerald-400/50 shadow-xl' : 'bg-gray-50 border-white shadow-sm'"
                >
                    <div v-if="variant.includes('Winner')" class="absolute -top-1 -right-1">
                         <div class="bg-emerald-600 text-white text-xs font-black py-1 px-4 rotate-45 translate-x-3 translate-y-1 shadow-md">WINNER</div>
                    </div>
                    <h4 class="text-sm font-black text-gray-500 uppercase tracking-widest mb-4">Variant Score</h4>
                    <p class="text-2xl font-black text-gray-900">{{ variant.split(': ')[1] }}</p>
                    <p class="text-xs font-bold text-gray-500 mt-1 uppercase">{{ variant.split(': ')[0] }}</p>
                    <div class="mt-4 flex gap-2">
                        <button class="px-3 py-1 rounded bg-white border border-gray-200 text-sm font-black text-gray-600 uppercase hover:bg-gray-50 transition-all">Preview</button>
                        <button v-if="variant.includes('Winner')" class="px-3 py-1 rounded bg-emerald-600 text-white text-sm font-black uppercase shadow-lg shadow-emerald-500/20">Apply</button>
                    </div>
                </div>
            </div>

            <div class="flex gap-4 mt-12 justify-center">
                <button class="px-8 py-3 bg-indigo-50 text-indigo-700 rounded-2xl text-sm font-black tracking-widest hover:bg-indigo-100 transition-all shadow-inner border border-indigo-100"><i class="fas fa-magic"></i> AI OPTIMIZE</button>
                <button class="px-8 py-3 bg-white text-gray-700 rounded-2xl text-sm font-black tracking-widest hover:bg-gray-50 transition-all shadow-sm border border-gray-200"><i class="fas fa-mobile-alt"></i> MOBILE PREVIEW</button>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    sequences: Array,
    ab_tests: Array
});

const getIcon = (step) => {
    if (step.includes('WhatsApp')) return 'fab fa-whatsapp';
    if (step.includes('Email')) return 'fas fa-envelope-open-text';
    if (step.includes('Voice')) return 'fas fa-phone-volume';
    if (step.includes('AR')) return 'fas fa-vr-cardboard';
    return 'fas fa-paper-plane';
}
</script>

<script setup>
import { ref } from 'vue';
import { useStudioStore } from '@/Stores/studioStore';

const props = defineProps({
    isOpen: Boolean,
    frontImage: String,
    backImage: String,
    orientation: {
        type: String,
        default: 'Landscape'
    }
});

const emit = defineEmits(['close']);
const isFlipped = ref(false);

const close = () => {
    emit('close');
};
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-8 bg-slate-950/80 backdrop-blur-md animate-fade-in font-outfit">
        <div class="relative w-full max-w-4xl bg-white rounded-[2.5rem] p-12 overflow-hidden flex flex-col items-center shadow-[0_50px_100px_-20px_rgba(0,0,0,0.5)] border border-white/20">
            <!-- Close Button -->
            <button @click="close" class="absolute top-8 right-8 w-12 h-12 flex items-center justify-center rounded-2xl bg-slate-50 text-slate-400 hover:text-slate-900 hover:bg-slate-100 transition-all border border-slate-100">
                <i class="fas fa-times text-lg"></i>
            </button>

            <!-- Metadata Header -->
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-black uppercase tracking-[0.2em] mb-4">
                    <div class="w-1 h-1 rounded-full bg-emerald-500 animate-pulse"></div>
                    Volumetric Render Node
                </div>
                <h2 class="text-3xl font-black text-slate-900 uppercase tracking-tight leading-none">Spatial Topology</h2>
                <p class="text-sm font-black text-slate-400 uppercase tracking-[0.3em] mt-3">Material & Structural Verification</p>
            </div>

            <!-- Assembly Stage -->
            <div class="relative w-full h-[450px] perspective-[1500px] flex items-center justify-center">
                <!-- Shadow beneath card -->
                <div class="absolute bottom-10 bg-slate-900/10 blur-2xl rounded-full transform scale-x-150 opacity-50 transition-all duration-700"
                     :class="orientation === 'Landscape' ? 'w-64 h-8' : 'w-48 h-6'"></div>
                
                <div 
                    class="relative transition-transform duration-[1200ms] cubic-bezier(0.4, 0, 0.2, 1) transform-style-3d cursor-pointer group"
                    :class="{ 
                        'rotate-y-180': isFlipped,
                        'w-[380px] h-[240px]': orientation === 'Landscape',
                        'w-[240px] h-[380px]': orientation === 'Portrait'
                    }"
                    @click="isFlipped = !isFlipped"
                >
                    <!-- Front Side (Primary Matrix) -->
                    <div class="absolute inset-0 backface-hidden rounded-[1.5rem] bg-white shadow-2xl overflow-hidden border border-slate-200">
                         <div class="w-full h-full relative">
                             <img v-if="frontImage" :src="frontImage" class="absolute inset-0 w-full h-full object-cover" />
                             <!-- Visual representation of front -->
                             <div v-else class="absolute inset-6 border-[1.5px] border-dashed border-slate-200 rounded-[1rem] flex flex-col items-center justify-center gap-3 bg-slate-50/50">
                                 <i class="fas fa-layer-group text-slate-200 text-4xl"></i>
                                 <p class="text-sm text-slate-300 font-black uppercase tracking-widest">Front Topology Output</p>
                             </div>
                             <!-- Gloss effect -->
                             <div class="absolute inset-0 opacity-20 bg-gradient-to-br from-white via-white/50 to-transparent pointer-events-none group-hover:rotate-12 group-hover:scale-150 transition-transform duration-1000 z-10"></div>
                         </div>
                    </div>

                    <!-- Back Side (Secondary Matrix) -->
                    <div class="absolute inset-0 backface-hidden rotate-y-180 rounded-[1.5rem] bg-slate-50 shadow-2xl overflow-hidden border border-slate-200">
                        <div class="w-full h-full relative">
                             <img v-if="backImage" :src="backImage" class="absolute inset-0 w-full h-full object-cover" />
                             <div v-else class="absolute inset-6 border-[1.5px] border-dashed border-slate-200 rounded-[1rem] flex flex-col items-center justify-center gap-3 bg-white/50">
                                 <i class="fas fa-qrcode text-slate-200 text-4xl opacity-50"></i>
                                 <p class="text-sm text-slate-300 font-black uppercase tracking-widest">Back Topology Output</p>
                             </div>
                             <!-- Gloss effect reverse -->
                             <div class="absolute inset-0 opacity-20 bg-gradient-to-bl from-white via-white/50 to-transparent pointer-events-none z-10"></div>
                         </div>
                    </div>
                    
                    <!-- Structural Punch Hole -->
                    <div class="absolute left-1/2 -translate-x-1/2 w-10 h-10 rounded-full bg-slate-100 border-4 border-white z-50 flex items-center justify-center translate-z-10 shadow-lg group-hover:scale-110 transition-all duration-700"
                         :class="orientation === 'Landscape' ? '-top-6' : '-top-5'">
                         <div class="w-4 h-4 rounded-full bg-slate-900 shadow-inner"></div>
                    </div>
                </div>
            </div>

            <!-- Calibration Controls -->
            <div class="absolute bottom-16 flex flex-col items-center gap-8">
                <button @click="isFlipped = !isFlipped" class="group flex items-center gap-3 px-10 py-4 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-emerald-600 active:scale-95 transition-all shadow-xl shadow-slate-200">
                    <i class="fas fa-sync-alt group-hover:rotate-180 transition-transform duration-700"></i>
                    {{ isFlipped ? 'Pivot to Primary Side' : 'Pivot to Secondary Side' }}
                </button>
                
                <div class="flex items-center gap-8 text-sm font-black uppercase tracking-[0.2em]">
                    <span class="flex items-center gap-2 text-emerald-500">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div> 
                        CMYK CALIBRATED
                    </span>
                    <span class="text-slate-200">|</span>
                    <span class="flex items-center gap-2 text-indigo-500">
                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-500 shadow-[0_0_8px_rgba(99,102,241,0.5)]"></div> 
                        PVC FINISH ENABLED
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.perspective-\[1500px\] {
    perspective: 1500px;
}
.transform-style-3d {
    transform-style: preserve-3d;
}
.backface-hidden {
    backface-visibility: hidden;
}
.rotate-y-180 {
    transform: rotateY(180deg);
}
.translate-z-10 {
    transform: translateZ(10px);
}
.cubic-bezier\(0\.4\,\ 0\,\ 0\.2\,\ 1\) {
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>

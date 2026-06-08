<template>
    <div class="space-y-6">
        <div v-if="forensics" class="space-y-8">
            <!-- Browser Metadata -->
            <div v-if="forensics.browser_metadata" class="bg-gray-50 rounded-xl p-6 border border-gray-100">
                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Runtime Environment</h3>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                    <div v-for="(val, key) in forensics.browser_metadata" :key="key">
                        <label class="block text-sm font-bold text-gray-400 uppercase tracking-tight">{{ key.replace('_', ' ') }}</label>
                        <p class="text-xs font-black text-gray-900 truncate">{{ val }}</p>
                    </div>
                </div>
            </div>

            <!-- Session Replay -->
            <div v-if="forensics.session_recording" class="bg-white rounded-xl p-8 border-2 border-dashed border-emerald-100 text-center">
                <div class="h-16 w-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <VideoCameraIcon class="w-8 h-8" />
                </div>
                <h3 class="text-lg font-black text-gray-900 tracking-tight">Visual Session Replay Available</h3>
                <p class="text-sm text-gray-500 mt-2 max-w-sm mx-auto">
                    A high-fidelity recording of the user's journey at the time of the bug is available for forensic analysis.
                </p>
                <button @click="playReplay" class="mt-6 bg-emerald-600 text-white px-8 py-3 rounded-xl font-bold shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all flex items-center gap-2 mx-auto">
                    <PlayIcon class="w-4 h-4" />
                    Launch Replay Room
                </button>
            </div>

            <!-- Console Logs -->
            <div v-if="forensics.console_logs && forensics.console_logs.length" class="space-y-4">
                 <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest border-b pb-2">Console Forensics</h3>
                 <div class="bg-gray-900 rounded-xl p-4 overflow-hidden font-mono text-sm space-y-1">
                    <div v-for="(log, idx) in forensics.console_logs" :key="idx" :class="[
                        'flex gap-3 px-2 py-1 rounded',
                        log.level === 'error' ? 'bg-red-900/40 text-red-300' : 'text-gray-400'
                    ]">
                        <span class="shrink-0 opacity-50">{{ log.time }}</span>
                        <span class="shrink-0 font-bold uppercase">{{ log.level }}</span>
                        <span class="truncate">{{ log.message }}</span>
                    </div>
                 </div>
            </div>
        </div>
        <div v-else class="py-12 text-center text-gray-400 italic text-sm">
            No diagnostic data captured for this ticket.
        </div>

        <!-- Mock Replay Room Modal -->
        <div v-if="showingReplay" class="fixed inset-0 z-[100] bg-slate-900 flex flex-col animate-in fade-in duration-300">
            <div class="h-16 px-6 border-b border-white/10 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="h-8 w-8 bg-emerald-500 rounded-lg flex items-center justify-center">
                        <VideoCameraIcon class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h3 class="text-white text-sm font-black uppercase tracking-widest">Replay Room: #{{ forensics?.bug_ticket_id }}</h3>
                        <p class="text-slate-400 text-sm font-bold">Forensic Session ID: {{ forensics?.id }}</p>
                    </div>
                </div>
                <button @click="showingReplay = false" class="text-slate-400 hover:text-white transition-colors">
                    <XMarkIcon class="w-6 h-6" />
                </button>
            </div>
            
            <div class="flex-1 bg-black flex items-center justify-center relative group">
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover:opacity-100 transition-opacity z-10 flex items-end p-8">
                    <div class="w-full flex items-center gap-6">
                        <button class="text-white hover:scale-110 transition-transform"><PlayIcon class="w-8 h-8" /></button>
                        <div class="flex-1 h-1 bg-white/20 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 w-1/3"></div>
                        </div>
                        <span class="text-white font-mono text-xs">01:42 / 05:00</span>
                    </div>
                </div>
                <div class="text-center space-y-4">
                    <div class="w-24 h-24 bg-white/5 rounded-full flex items-center justify-center mx-auto border border-white/10 animate-pulse">
                        <VideoCameraIcon class="w-10 h-10 text-emerald-500" />
                    </div>
                    <p class="text-slate-500 font-mono text-xs uppercase tracking-[0.3em]">Streaming Compressed Forensic Buffer...</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { VideoCameraIcon, PlayIcon, XMarkIcon } from '@heroicons/vue/24/solid';

const props = defineProps({
    forensics: Object
});

const showingReplay = ref(false);

const playReplay = () => {
    showingReplay.value = true;
};
</script>

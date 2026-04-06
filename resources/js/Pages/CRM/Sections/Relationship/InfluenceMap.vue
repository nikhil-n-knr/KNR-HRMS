<template>
    <div class="space-y-6 animate-in fade-in zoom-in-95 duration-700">
        <div class="bg-gray-900 rounded-[2.5rem] p-10 text-white relative min-h-[600px] overflow-hidden shadow-2xl border border-white/5">
            <!-- Grid Background Overlay -->
            <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808012_1px,transparent_1px),linear-gradient(to_bottom,#80808012_1px,transparent_1px)] bg-[size:40px_40px]"></div>

            <div class="relative z-10">
                <div class="flex items-center justify-between mb-12">
                    <div>
                        <h2 class="text-2xl font-black tracking-tight">B2B Influence Graph</h2>
                        <p class="text-emerald-500 text-sm font-black uppercase tracking-[0.2em] mt-1">Deep Relationship Mapping & Influence Scores</p>
                    </div>
                    <div class="flex gap-4">
                        <div class="flex items-center gap-2 bg-white/5 px-4 py-2 rounded-xl border border-white/10">
                             <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                             <span class="text-sm font-black uppercase tracking-widest text-gray-400">Account Nodes</span>
                        </div>
                        <div class="flex items-center gap-2 bg-white/5 px-4 py-2 rounded-xl border border-white/10">
                             <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                             <span class="text-sm font-black uppercase tracking-widest text-gray-400">Contact Nodes</span>
                        </div>
                    </div>
                </div>

                <!-- Abstract Visual Relationship Graph -->
                <div class="relative h-[400px] w-full flex items-center justify-center">
                    <!-- Central Node -->
                    <div class="w-32 h-32 rounded-full bg-gradient-to-br from-blue-600 to-blue-400 p-1 shadow-[0_0_50px_rgba(37,99,235,0.4)] relative group cursor-pointer">
                        <div class="w-full h-full rounded-full bg-gray-900 flex flex-col items-center justify-center border border-white/20">
                            <span class="text-sm font-black text-blue-400 uppercase tracking-widest">Account</span>
                            <span class="text-xs font-black text-white mt-1">EdTechX Hub</span>
                        </div>
                        <!-- Connecting Lines (SVG) -->
                        <svg class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[400px] pointer-events-none -z-10 overflow-visible">
                            <line x1="400" y1="200" x2="200" y2="100" stroke="rgba(255,255,255,0.1)" stroke-width="2" stroke-dasharray="4" />
                            <line x1="400" y1="200" x2="600" y2="100" stroke="rgba(255,255,255,0.1)" stroke-width="2" />
                            <line x1="400" y1="200" x2="300" y2="300" stroke="rgba(255,255,255,0.1)" stroke-width="2" stroke-dasharray="4" />
                            <line x1="400" y1="200" x2="500" y2="350" stroke="rgba(255,255,255,0.1)" stroke-width="2" />
                        </svg>
                    </div>

                    <!-- Orbiting Nodes -->
                    <div v-for="(node, i) in displayNodes" :key="i" 
                        class="absolute transition-all duration-700 hover:scale-110 cursor-pointer group"
                        :style="{ 
                            left: node.x + '%', 
                            top: node.y + '%',
                        }"
                    >
                         <div class="w-16 h-16 rounded-2xl bg-white/5 backdrop-blur-md border border-white/20 p-0.5 shadow-xl">
                            <div class="w-full h-full rounded-2xl bg-gray-900/50 flex flex-col items-center justify-center overflow-hidden">
                                <div class="w-full h-1 bg-gradient-to-r" :class="node.role === 'CEO' ? 'from-amber-400 to-orange-500' : 'from-emerald-400 to-teal-500'"></div>
                                <span class="text-xs font-black text-gray-500 uppercase mt-auto mb-1 tracking-tighter">{{ node.role }}</span>
                                <span class="text-sm font-black text-white mb-auto leading-none text-center px-1">{{ node.name }}</span>
                            </div>
                         </div>
                         <div class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center border-2 border-gray-900 shadow-lg text-xs font-black text-black">
                            {{ node.score }}
                         </div>
                    </div>
                </div>

                <!-- Influence Footer Metrics -->
                <div class="mt-20 grid grid-cols-1 md:grid-cols-3 gap-8">
                    <div class="p-6 rounded-3xl bg-white/5 border border-white/10 flex items-center gap-6">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center text-xl">
                            <i class="fas fa-project-diagram"></i>
                        </div>
                        <div>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Critical Path</p>
                            <h4 class="text-sm font-black text-white uppercase tracking-tight">CEO → VP ENG → Procurement</h4>
                        </div>
                    </div>
                    <div class="p-6 rounded-3xl bg-white/5 border border-white/10 flex items-center gap-6">
                        <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-500 flex items-center justify-center text-xl">
                            <i class="fas fa-network-wired"></i>
                        </div>
                        <div>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Clusters Detected</p>
                            <h4 class="text-sm font-black text-white uppercase tracking-tight">{{ clusters_detected }} Central Hubs Identified</h4>
                        </div>
                    </div>
                    <div class="p-6 rounded-3xl bg-white/5 border border-white/10 flex items-center gap-6">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-xl">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-widest leading-none mb-1">Influence Lift</p>
                            <h4 class="text-sm font-black text-white uppercase tracking-tight">+24.2% Prediction Boost</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    orbitNodes: { type: Array, default: () => [] },
    clusters_detected: { type: Number, default: 3 }
});

const displayNodes = computed(() => {
    if (props.orbitNodes.length > 0) return props.orbitNodes;
    // Fallback Mock
    return [
        { name: 'Sarah J.', role: 'CEO', score: 98, x: 25, y: 25 },
        { name: 'Mike Robinson', role: 'VP ENG', score: 84, x: 75, y: 25 },
        { name: 'David Lee', role: 'HEAD OF IT', score: 72, x: 35, y: 75 },
        { name: 'Elena Chen', role: 'COO', score: 91, x: 65, y: 80 },
    ];
});
</script>

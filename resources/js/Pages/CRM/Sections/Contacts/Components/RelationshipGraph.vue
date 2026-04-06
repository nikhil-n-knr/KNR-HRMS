<template>
    <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm overflow-hidden">
        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-6 flex items-center gap-2">
            <i class="fas fa-project-diagram text-blue-500"></i>
            Relationship Graph
        </h3>

        <div class="relative h-64 border border-dashed border-gray-100 rounded-2xl bg-gray-50/30 flex items-center justify-center overflow-hidden">
            <!-- Simple SVG Node Graph -->
            <svg width="100%" height="100%" viewBox="0 0 400 200" class="drop-shadow-sm">
                <!-- Links -->
                <line 
                    v-for="(link, index) in localGraphData.links" 
                    :key="'link-'+index"
                    :x1="getNodePos(link.source).x" :y1="getNodePos(link.source).y"
                    :x2="getNodePos(link.target).x" :y2="getNodePos(link.target).y"
                    stroke="#E5E7EB" 
                    stroke-width="2"
                    stroke-dasharray="4"
                />

                <!-- Nodes -->
                <g v-for="node in localGraphData.nodes" :key="node.id" class="group">
                    <circle 
                        :cx="getNodePos(node.id).x" :cy="getNodePos(node.id).y" 
                        :r="node.group === 'main' ? 20 : 15"
                        :fill="node.group === 'main' ? '#3B82F6' : '#FFFFFF'"
                        :stroke="node.group === 'main' ? '#BFDBFE' : '#E5E7EB'"
                        stroke-width="3"
                        class="transition-all duration-300 group-hover:r-22 cursor-pointer"
                    />
                    <text 
                        :x="getNodePos(node.id).x" :y="getNodePos(node.id).y + 35"
                        text-anchor="middle"
                        class="text-sm font-black fill-gray-900 pointer-events-none uppercase tracking-tighter"
                    >
                        {{ node.label }}
                    </text>
                    <text 
                        :x="getNodePos(node.id).x" :y="getNodePos(node.id).y + 45"
                        text-anchor="middle"
                        class="text-xs font-bold fill-gray-400 pointer-events-none uppercase tracking-widest"
                    >
                        {{ node.title }}
                    </text>
                </g>
            </svg>

            <!-- Gradient Overlays for depth -->
            <div class="absolute inset-0 pointer-events-none bg-radial-gradient from-transparent to-white/10"></div>
        </div>

        <div v-if="!graphData || graphData.nodes.length <= 1" class="mt-4 text-center">
            <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">No hidden connections discovered</p>
            <button class="mt-2 text-blue-600 text-sm font-black uppercase underline">Identify Relationships</button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    graphData: { type: Object, default: () => ({ nodes: [], links: [] }) }
});

const localGraphData = computed(() => {
    if (!props.graphData || !props.graphData.nodes || props.graphData.nodes.length === 0) {
        return {
            nodes: [{ id: 1, label: 'Contact', title: 'Director', group: 'main' }],
            links: []
        };
    }
    return props.graphData;
});

const getNodePos = (id) => {
    // Deterministic position layout for now
    const index = localGraphData.value.nodes.findIndex(n => n.id === id);
    if (index === 0) return { x: 200, y: 100 }; // Center for main node
    
    const angle = (index - 1) * (360 / (localGraphData.value.nodes.length - 1)) * (Math.PI / 180);
    const radius = 80;
    return {
        x: 200 + radius * Math.cos(angle),
        y: 100 + radius * Math.sin(angle)
    };
};
</script>

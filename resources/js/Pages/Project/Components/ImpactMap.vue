<script setup>
import { computed } from 'vue';

const props = defineProps({
    graph: {
        type: Object,
        default: () => ({ nodes: [], links: [] })
    }
});

// Simple layout logic for a predefined layered graph
// In a real app, use D3 to calculate positions. Here we map layers manually.
// Layer 0: Threat (Top)
// Layer 1: Asset (Middle)
// Layer 2: Service/Impact (Bottom)

const NODE_RADIUS = 30;
const LAYER_HEIGHT = 150;
const CANVAS_WIDTH = 800;
const CANVAS_HEIGHT = 400;

const processedNodes = computed(() => {
    // Distribute nodes by layer
    const layers = {};
    props.graph.nodes.forEach(n => {
        if (!layers[n.layer]) layers[n.layer] = [];
        layers[n.layer].push(n);
    });

    const calculated = [];
    Object.keys(layers).forEach(layerIndex => {
        const nodesInLayer = layers[layerIndex];
        const count = nodesInLayer.length;
        const spacing = CANVAS_WIDTH / (count + 1);
        
        nodesInLayer.forEach((node, idx) => {
            calculated.push({
                ...node,
                x: spacing * (idx + 1),
                y: 50 + (node.layer * LAYER_HEIGHT)
            });
        });
    });
    return calculated;
});

const processedLinks = computed(() => {
    return props.graph.links.map(link => {
        const source = processedNodes.value.find(n => n.id === link.source);
        const target = processedNodes.value.find(n => n.id === link.target);
        if (!source || !target) return null;
        return { ...link, x1: source.x, y1: source.y, x2: target.x, y2: target.y };
    }).filter(l => l);
});

const getNodeColor = (type) => {
    switch(type) {
        case 'threat': return '#EF4444'; // Red-500
        case 'asset': return '#3B82F6'; // Blue-500
        case 'service': return '#A855F7'; // Purple-500
        case 'data': return '#EAB308'; // Yellow-500
        default: return '#6B7280';
    }
};

const getNodeIcon = (type) => {
    switch(type) {
        case 'threat': return '💀';
        case 'asset': return '💾';
        case 'service': return '⚙️';
        case 'data': return '📄';
        default: return '📦';
    }
};
</script>

<template>
    <div class="w-full h-full flex items-center justify-center bg-gray-900 overflow-hidden relative rounded-xl border border-gray-800">
        <!-- Grid Background -->
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(#4b5563 1px, transparent 1px); background-size: 20px 20px;"></div>
        
        <svg :viewBox="`0 0 ${CANVAS_WIDTH} ${CANVAS_HEIGHT}`" class="w-full h-full max-w-4xl">
            <!-- Defs for arrowheads and glows -->
            <defs>
                <marker id="arrowhead" markerWidth="10" markerHeight="7" refX="28" refY="3.5" orient="auto">
                    <polygon points="0 0, 10 3.5, 0 7" fill="#4B5563" />
                </marker>
                <filter id="glow">
                    <feGaussianBlur stdDeviation="2.5" result="coloredBlur"/>
                    <feMerge>
                        <feMergeNode in="coloredBlur"/>
                        <feMergeNode in="SourceGraphic"/>
                    </feMerge>
                </filter>
            </defs>

            <!-- Links -->
            <g>
                <line v-for="(link, i) in processedLinks" :key="i"
                    :x1="link.x1" :y1="link.y1" :x2="link.x2" :y2="link.y2"
                    stroke="#4B5563" stroke-width="2" marker-end="url(#arrowhead)"
                    class="animate-pulse-slow"
                />
            </g>

            <!-- Nodes -->
            <g v-for="node in processedNodes" :key="node.id" class="cursor-pointer group">
                <!-- Outer Ring (Status) -->
                <circle :cx="node.x" :cy="node.y" :r="NODE_RADIUS + 4" 
                    fill="none" :stroke="getNodeColor(node.type)" stroke-width="2" 
                    class="opacity-50 group-hover:opacity-100 transition-all duration-300"
                    :filter="node.status === 'compromised' ? 'url(#glow)' : ''"
                />
                
                <!-- Inner Circle -->
                <circle :cx="node.x" :cy="node.y" :r="NODE_RADIUS" 
                    class="fill-gray-800 transition-all"
                />

                <!-- Icon -->
                <text :x="node.x" :y="node.y" dy=".35em" text-anchor="middle" class="text-xl pointer-events-none select-none">
                    {{ getNodeIcon(node.type) }}
                </text>

                <!-- Label -->
                <text :x="node.x" :y="node.y + NODE_RADIUS + 20" text-anchor="middle" fill="#9CA3AF" class="text-xs font-mono uppercase font-bold tracking-wider bg-gray-900">
                    {{ node.label }}
                </text>
                
                 <!-- Tag -->
                <text :x="node.x" :y="node.y + NODE_RADIUS + 35" text-anchor="middle" :fill="getNodeColor(node.type)" class="text-sm font-mono uppercase opacity-70">
                    {{ node.type }}
                </text>
            </g>
        </svg>

        <div class="absolute bottom-4 right-4 text-xs text-gray-600 font-mono">
            Auto-Generated Live Map • Refreshing...
        </div>
    </div>
</template>

<style scoped>
.animate-pulse-slow {
    animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 0.3; }
    50% { opacity: 0.8; }
}
</style>

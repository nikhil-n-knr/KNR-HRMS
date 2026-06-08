<script setup>
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, onMounted, watch, computed } from 'vue';
import { Chart } from 'chart.js/auto';
import { SankeyController, Flow } from 'chartjs-chart-sankey';
import { InformationCircleIcon } from '@heroicons/vue/24/outline';

Chart.register(SankeyController, Flow);

defineOptions({ layout: MainLayout });

const props = defineProps({
    sankey_data: Object,
    sunburst_data: Object
});

const activeTab = ref('sankey');
const chartCanvas = ref(null);
let chartInstance = null;

// --- FILTERS ---
const selectedCategories = ref([]);
const selectedLocations = ref([]);

// Extract unique node names from sankey for filter options
const sankeyNodes = computed(() => props.sankey_data?.nodes ?? []);
const categoryNodes = computed(() => sankeyNodes.value.filter((_, i) => {
    // Category nodes are those that have links FROM Capital Expenditure
    const capExIdx = props.sankey_data?.nodes?.findIndex(n => n.name === 'Capital Expenditure') ?? -1;
    return props.sankey_data?.links?.some(l => l.source === capExIdx && l.target === i);
}));
const locationNodes = computed(() => sankeyNodes.value.filter((_, i) => {
    // Location nodes are those that have links FROM a category node
    const catIdxs = categoryNodes.value.map(c => props.sankey_data?.nodes?.indexOf(c));
    return props.sankey_data?.links?.some(l => catIdxs.includes(l.source) && l.target === i);
}));

const filteredSankeyData = computed(() => {
    if (!props.sankey_data) return { nodes: [], links: [] };
    const rawNodes = props.sankey_data.nodes;
    const rawLinks = props.sankey_data.links;

    // If no filters, return all
    if (!selectedCategories.value.length && !selectedLocations.value.length) {
        return props.sankey_data;
    }

    // Filter links by selected categories/locations
    const filteredLinks = rawLinks.filter(l => {
        const fromName = rawNodes[l.source]?.name;
        const toName   = rawNodes[l.target]?.name;
        const isCatFilter = selectedCategories.value.length > 0;
        const isLocFilter = selectedLocations.value.length > 0;

        if (isCatFilter && fromName === 'Capital Expenditure') {
            return selectedCategories.value.includes(toName);
        }
        if (isCatFilter && isLocFilter) {
            return selectedCategories.value.includes(fromName) && selectedLocations.value.includes(toName);
        }
        if (isCatFilter) {
            return selectedCategories.value.includes(fromName) || selectedCategories.value.some(c => rawLinks.some(rl => rawNodes[rl.target]?.name === fromName && rawNodes[rl.source]?.name === 'Capital Expenditure' && rawNodes[rl.target]?.name === c));
        }
        if (isLocFilter) {
            return selectedLocations.value.includes(toName);
        }
        return true;
    });

    return { nodes: rawNodes, links: filteredLinks };
});

// --- SUNBURST LOGIC (Custom SVG) ---
const sunburstPaths = ref([]);

const calculateSunburst = (node, startAngle, endAngle, level) => {
    if (!node.children || node.children.length === 0) return;
    let currentAngle = startAngle;
    const totalValue = node.children.reduce((a, b) => a + (b.value || 0) + (b.children?.reduce((x,y)=> x + (y.value||0), 0) || 0), 0);
    if (totalValue === 0) return;

    const innerR = level * 50;
    const outerR = (level + 1) * 50;

    node.children.forEach((child, index) => {
        const childTotal = (child.value || 0) + (child.children?.reduce((x,y)=> x + (y.value||0), 0) || 0);
        const sliceAngle = (childTotal / totalValue) * (endAngle - startAngle);
        const nextAngle  = currentAngle + sliceAngle;

        if (sliceAngle > 0.01) { // Skip near-zero slices
            const path = describeArc(160, 160, innerR, outerR, currentAngle, nextAngle);
            sunburstPaths.value.push({
                d: path,
                fill: getColor(level, index),
                title: `${child.name}: $${(child.value || childTotal).toLocaleString()}`,
                value: child.value || childTotal
            });
        }

        calculateSunburst(child, currentAngle, nextAngle, level + 1);
        currentAngle = nextAngle;
    });
};

const polarToCartesian = (cx, cy, r, a) => ({ x: cx + r * Math.cos(a), y: cy + r * Math.sin(a) });

const describeArc = (x, y, innerRadius, outerRadius, startAngle, endAngle) => {
    if (endAngle - startAngle >= 2 * Math.PI) endAngle = startAngle + 2 * Math.PI - 0.001;
    const s  = polarToCartesian(x, y, outerRadius, endAngle);
    const e  = polarToCartesian(x, y, outerRadius, startAngle);
    const s2 = polarToCartesian(x, y, innerRadius, endAngle);
    const e2 = polarToCartesian(x, y, innerRadius, startAngle);
    const laf = endAngle - startAngle <= Math.PI ? '0' : '1';
    return `M ${s.x} ${s.y} A ${outerRadius} ${outerRadius} 0 ${laf} 0 ${e.x} ${e.y} L ${e2.x} ${e2.y} A ${innerRadius} ${innerRadius} 0 ${laf} 1 ${s2.x} ${s2.y} Z`;
};

const getColor = (level, index) => {
    const colors = [
        ['#34d399','#10b981','#059669','#047857'],
        ['#60a5fa','#3b82f6','#2563eb','#1d4ed8'],
        ['#fcd34d','#f59e0b','#d97706','#b45309'],
        ['#f87171','#ef4444','#dc2626','#b91c1c'],
    ];
    return colors[level % 4][index % 4];
};

const initSunburst = () => {
    sunburstPaths.value = [];
    if (props.sunburst_data && props.sunburst_data.children?.length) {
        calculateSunburst(props.sunburst_data, 0, 2 * Math.PI, 1);
    }
};

onMounted(() => {
    renderChart();
    initSunburst();
});

watch(activeTab, () => {
    if (activeTab.value === 'sankey') setTimeout(renderChart, 100);
});

watch(filteredSankeyData, () => {
    if (activeTab.value === 'sankey') setTimeout(renderChart, 100);
}, { deep: true });

const renderChart = () => {
    if (activeTab.value !== 'sankey' || !chartCanvas.value) return;
    if (chartInstance) { chartInstance.destroy(); chartInstance = null; }

    const data = filteredSankeyData.value;
    if (!data.nodes?.length || !data.links?.length) return;

    const rawNodes = data.nodes;
    const dataPoints = data.links
        .filter(l => l.value > 0)
        .map(l => ({
            from: rawNodes[l.source]?.name,
            to:   rawNodes[l.target]?.name,
            flow: l.value
        }))
        .filter(d => d.from && d.to);

    if (!dataPoints.length) return;

    const ctx = chartCanvas.value.getContext('2d');
    chartInstance = new Chart(ctx, {
        type: 'sankey',
        data: {
            datasets: [{
                label: 'Asset Capital Flow',
                data: dataPoints,
                colorFrom: () => 'rgba(16, 185, 129, 0.6)',
                colorTo:   () => 'rgba(59, 130, 246, 0.6)',
                colorMode: 'gradient',
                labels: Object.fromEntries(rawNodes.map(n => [n.name, n.name]))
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: (ctx) => `$${ctx.raw.flow?.toLocaleString() ?? 0}`
                    }
                }
            }
        }
    });
};
</script>

<template>
    <Head title="Charts & Analytics" />

    <div class="h-screen flex flex-col bg-gray-900 text-white overflow-hidden">

        <!-- Header -->
        <div class="p-6 border-b border-gray-800 flex flex-wrap justify-between items-center gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-400 to-blue-500">
                        Charts & Analytics
                    </h1>
                    <div class="group/tooltip relative flex items-center">
                        <InformationCircleIcon class="w-5 h-5 text-gray-400 cursor-help opacity-70 hover:opacity-100 transition-opacity" />
                        <div class="absolute left-full ml-3 top-1/2 -translate-y-1/2 w-72 bg-gray-800 text-white text-sm font-medium px-4 py-3 rounded-xl opacity-0 invisible group-hover/tooltip:opacity-100 group-hover/tooltip:visible transition-all shadow-2xl z-50 pointer-events-none border border-gray-700">
                            Colorful charts that help you see the bigger picture, like how your equipment loses value over time and where your money is going.
                        </div>
                    </div>
                </div>
                <p class="text-sm text-gray-500 mt-1">Deep dive into Asset Flow &amp; Cost Hierarchies</p>
            </div>

            <!-- Chart Toggle -->
            <div class="flex bg-gray-800 rounded-lg p-1">
                <button @click="activeTab = 'sankey'" class="px-4 py-2 rounded-md text-sm font-bold transition-all"
                    :class="activeTab === 'sankey' ? 'bg-gray-700 text-white shadow' : 'text-gray-400 hover:text-white'">
                    Flow (Sankey)
                </button>
                <button @click="activeTab = 'sunburst'" class="px-4 py-2 rounded-md text-sm font-bold transition-all"
                    :class="activeTab === 'sunburst' ? 'bg-gray-700 text-white shadow' : 'text-gray-400 hover:text-white'">
                    Cost Map (Sunburst)
                </button>
            </div>
        </div>

        <!-- Filters (Sankey only) -->
        <div v-if="activeTab === 'sankey'" class="px-6 py-3 border-b border-gray-800 flex flex-wrap gap-4 items-center bg-gray-900/50">
            <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Filters:</span>

            <!-- Category Filter -->
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs text-gray-400">Category:</span>
                <button v-for="node in categoryNodes" :key="node.name"
                    @click="selectedCategories.includes(node.name) ? selectedCategories.splice(selectedCategories.indexOf(node.name),1) : selectedCategories.push(node.name)"
                    class="px-2 py-1 rounded text-xs font-medium transition-all"
                    :class="selectedCategories.includes(node.name) ? 'bg-emerald-500 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'">
                    {{ node.name }}
                </button>
            </div>

            <!-- Location Filter -->
            <div class="flex items-center gap-2 flex-wrap">
                <span class="text-xs text-gray-400">Location:</span>
                <button v-for="node in locationNodes" :key="node.name"
                    @click="selectedLocations.includes(node.name) ? selectedLocations.splice(selectedLocations.indexOf(node.name),1) : selectedLocations.push(node.name)"
                    class="px-2 py-1 rounded text-xs font-medium transition-all"
                    :class="selectedLocations.includes(node.name) ? 'bg-blue-500 text-white' : 'bg-gray-700 text-gray-300 hover:bg-gray-600'">
                    {{ node.name }}
                </button>
            </div>

            <button v-if="selectedCategories.length || selectedLocations.length"
                @click="selectedCategories = []; selectedLocations = []"
                class="ml-auto px-3 py-1 rounded text-xs text-gray-400 hover:text-white border border-gray-700 hover:border-gray-500 transition-all">
                Clear Filters
            </button>
        </div>

        <!-- Content -->
        <div class="flex-1 p-6 overflow-hidden relative">

            <!-- SANKEY -->
            <div v-show="activeTab === 'sankey'" class="h-full w-full bg-gray-800 rounded-2xl p-4 shadow-2xl border border-gray-700 flex flex-col">
                <div v-if="!filteredSankeyData.links?.length" class="flex-1 flex items-center justify-center text-gray-500 text-sm">
                    <div class="text-center">
                        <div class="text-4xl mb-3">📊</div>
                        <p>No asset purchase data found. Add assets with purchase costs to see the flow.</p>
                    </div>
                </div>
                <canvas v-show="filteredSankeyData.links?.length" ref="chartCanvas" class="flex-1"></canvas>
            </div>

            <!-- SUNBURST (Custom SVG) -->
            <div v-show="activeTab === 'sunburst'" class="h-full w-full bg-gray-800 rounded-2xl p-4 shadow-2xl border border-gray-700 flex items-center justify-center relative">

                <div v-if="sunburstPaths.length === 0" class="text-center text-gray-500">
                    <div class="text-4xl mb-3">🌐</div>
                    <p class="text-sm">No asset cost data available. Add assets with purchase costs to see the cost map.</p>
                </div>

                <div v-else class="flex flex-col items-center gap-4">
                    <div class="absolute top-4 left-4 text-xs text-gray-500 leading-relaxed">
                        * Inner Ring: Locations<br>
                        * Outer Ring: Categories<br>
                        * Hover for cost breakdown
                    </div>

                    <svg width="600" height="600" viewBox="0 0 320 320" class="max-h-full max-w-full">
                        <g v-for="(path, i) in sunburstPaths" :key="i"
                           class="hover:opacity-70 transition-opacity cursor-pointer group">
                            <path :d="path.d" :fill="path.fill" stroke="#1f2937" stroke-width="1.5" />
                            <title>{{ path.title }}</title>
                        </g>
                        <!-- Center hole text -->
                        <text x="160" y="155" text-anchor="middle" dominant-baseline="middle" fill="#fff" font-size="11" font-weight="bold">TOTAL</text>
                        <text x="160" y="170" text-anchor="middle" dominant-baseline="middle" fill="#9ca3af" font-size="9">ASSETS</text>
                    </svg>
                </div>

                <div class="absolute bottom-4 text-center text-gray-600 w-full text-xs">
                    Hover over segments for cost details
                </div>
            </div>

        </div>
    </div>
</template>

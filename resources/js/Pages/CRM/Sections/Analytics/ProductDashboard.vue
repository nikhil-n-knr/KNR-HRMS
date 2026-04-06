<template>
    <div class="h-[calc(100vh-120px)] overflow-y-auto p-8 bg-gray-50/50">
        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Product Insights</h2>
                    <p class="text-sm text-gray-500 font-medium mt-1">Real-time performance metrics and profitability analysis.</p>
                </div>
                <div class="flex bg-white p-1 rounded-xl border border-gray-200 shadow-sm">
                    <button class="px-4 py-2 text-xs font-black uppercase rounded-lg bg-emerald-50 text-emerald-700 shadow-sm">This Month</button>
                    <button class="px-4 py-2 text-xs font-black uppercase rounded-lg text-gray-400 hover:text-gray-600">Last Quarter</button>
                </div>
            </div>

            <!-- Top Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Top Seller -->
                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-xl shadow-gray-100/50 relative overflow-hidden group">
                    <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fas fa-trophy text-6xl text-amber-400"></i>
                    </div>
                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Top Seller (Vol)</h4>
                    <div class="flex items-end gap-3" v-if="top_selling.length">
                        <div class="text-3xl font-black text-gray-900">{{ top_selling[0].product.name }}</div>
                    </div>
                    <div class="mt-2 text-sm font-bold text-emerald-600" v-if="top_selling.length">
                        {{ top_selling[0].units_sold }} units sold
                    </div>
                    <div class="mt-4 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-amber-400 h-full w-3/4 rounded-full"></div>
                    </div>
                </div>

                <!-- Recurring Revenue -->
                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-xl shadow-gray-100/50 relative overflow-hidden group">
                     <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fas fa-sync text-6xl text-blue-400"></i>
                    </div>
                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Recurring Ratio</h4>
                    <div class="flex items-end gap-3">
                        <div class="text-3xl font-black text-gray-900">{{ recurringRatio }}%</div>
                    </div>
                    <div class="mt-2 text-sm font-bold text-blue-600">of total revenue is subscription based</div>
                     <div class="mt-4 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-blue-400 h-full rounded-full" :style="`width: ${recurringRatio}%`"></div>
                    </div>
                </div>

                <!-- Avg Margin -->
                <div class="bg-white p-6 rounded-3xl border border-gray-100 shadow-xl shadow-gray-100/50 relative overflow-hidden group">
                     <div class="absolute top-0 right-0 p-6 opacity-10 group-hover:opacity-20 transition-opacity">
                        <i class="fas fa-chart-pie text-6xl text-emerald-400"></i>
                    </div>
                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Avg. Profit Margin</h4>
                    <div class="flex items-end gap-3">
                        <div class="text-3xl font-black text-gray-900">{{ avgMargin }}%</div>
                    </div>
                    <div class="mt-2 text-sm font-bold text-emerald-600">Across entire catalog</div>
                     <div class="mt-4 w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                        <div class="bg-emerald-400 h-full rounded-full" :style="`width: ${avgMargin}%`"></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Top Products Chart -->
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                    <h3 class="text-lg font-black text-gray-900 tracking-tight mb-6">Top Performing Products</h3>
                    <div class="h-64">
                         <Bar :data="barChartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- Profit Heatmap (Custom Grid) -->
                <div class="bg-white p-8 rounded-3xl border border-gray-100 shadow-sm">
                    <h3 class="text-lg font-black text-gray-900 tracking-tight mb-6">Profit Margin Heatmap</h3>
                    <p class="text-xs text-gray-400 font-bold mb-4">Color intensity indicates higher profit margin.</p>
                    
                    <div class="grid grid-cols-4 sm:grid-cols-5 gap-2 max-h-64 overflow-y-auto custom-scrollbar pr-2">
                        <div 
                            v-for="item in profit_heatmap" 
                            :key="item.id"
                            class="aspect-square rounded-xl flex flex-col items-center justify-center p-2 text-center transition-transform hover:scale-105 cursor-help relative group"
                            :class="getHeatmapColor(item.margin)"
                        >
                            <span class="text-sm font-black opacity-70 truncate w-full">{{ item.name }}</span>
                            <span class="text-xs font-bold">{{ item.margin }}%</span>
                            
                            <!-- Tooltip -->
                            <div class="absolute bottom-full mb-2 bg-gray-900 text-white text-sm py-1 px-2 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none whitespace-nowrap z-10">
                                {{ item.category }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js';
import { Bar } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

const props = defineProps({
    top_selling: { type: Array, default: () => [] },
    profit_heatmap: { type: Array, default: () => [] },
    recurring_trends: { type: Object, default: () => ({}) },
    products: { type: Array, default: () => [] }
});

// Computed properties for cards
const recurringRatio = computed(() => {
    // simplified calculation: recurring products / total products
    if (!props.products.length) return 0;
    const recurring = props.products.filter(p => p.type === 'recurring' || p.is_subscription).length;
    return Math.round((recurring / props.products.length) * 100);
});

const avgMargin = computed(() => {
    if (!props.profit_heatmap || !props.profit_heatmap.length) return 0;
    const sum = props.profit_heatmap.reduce((acc, curr) => acc + (parseFloat(curr.margin) || 0), 0);
    return Math.round(sum / props.profit_heatmap.length);
});

// Chart Data
const barChartData = computed(() => ({
    labels: props.top_selling.map(i => i.product.name),
    datasets: [{
        label: 'Units Sold',
        data: props.top_selling.map(i => i.units_sold),
        backgroundColor: '#10b981',
        borderRadius: 6,
    }]
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#1f2937',
            padding: 12,
            titleFont: { family: 'Inter', size: 13 },
            bodyFont: { family: 'Inter', size: 12 },
            cornerRadius: 8,
            displayColors: false
        }
    },
    scales: {
        y: { 
            grid: { color: '#f3f4f6' },
            ticks: { font: { family: 'Inter', size: 10 } },
            border: { display: false }
        },
        x: {
            grid: { display: false },
             ticks: { font: { family: 'Inter', size: 10 } },
             border: { display: false }
        }
    }
};

const getHeatmapColor = (margin) => {
    const m = parseFloat(margin);
    if (m < 10) return 'bg-rose-100 text-rose-700';
    if (m < 20) return 'bg-orange-100 text-orange-700';
    if (m < 40) return 'bg-emerald-100 text-emerald-700';
    if (m < 60) return 'bg-emerald-300 text-emerald-900';
    return 'bg-emerald-500 text-white';
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background-color: #e5e7eb;
    border-radius: 20px;
}
</style>

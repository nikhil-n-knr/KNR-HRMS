<template>
    <div class="space-y-8 text-left">
        <!-- Header -->
        <div class="flex justify-between items-end pb-6 border-b border-gray-100">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Sales Analytics</h2>
                <div class="flex items-center mt-2">
                    <span class="w-8 h-1 bg-blue-500 rounded-full mr-3"></span>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-widest">Pipeline Health & Performance</p>
                </div>
            </div>
        </div>

        <!-- Pipeline Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div v-for="item in pipeline" :key="item.stage" 
                 class="bg-white p-6 rounded-[30px] border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                <div class="text-sm font-black text-gray-400 uppercase tracking-widest mb-1">{{ item.stage }}</div>
                <div class="text-2xl font-black text-gray-900 tracking-tight">{{ item.count }} <span class="text-xs text-gray-400 font-bold ml-1">DEALS</span></div>
                <div class="mt-4 pt-4 border-t border-gray-50 flex items-center justify-between">
                    <span class="text-sm font-bold text-gray-500">VALUATION</span>
                    <span class="text-sm font-black text-blue-600">${{ formatCurrency(item.total_value) }}</span>
                </div>
                <div class="mt-2 flex items-center justify-between">
                    <span class="text-sm font-bold text-gray-400 uppercase">FORECASTED</span>
                    <span class="text-xs font-black text-emerald-500">${{ formatCurrency(item.weighted_value) }}</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Top Deals -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-gray-900 rounded-[40px] p-8 text-white relative overflow-hidden shadow-2xl">
                    <div class="relative z-10">
                        <h3 class="text-xl font-black mb-6 flex items-center gap-3">
                            <i class="fas fa-trophy text-yellow-400"></i>
                            Strategic Opportunities
                        </h3>
                        
                        <div class="space-y-4">
                            <div v-for="deal in top_deals" :key="deal.id" 
                                 class="bg-white/5 border border-white/10 p-4 rounded-2xl flex items-center justify-between group hover:bg-white/10 transition-colors">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center font-black text-xs text-blue-400">
                                        {{ deal.account?.name.substring(0,2).toUpperCase() }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-black tracking-tight">{{ deal.title }}</div>
                                        <div class="text-sm font-bold text-gray-400">{{ deal.account?.name }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-black text-blue-400">${{ formatCurrency(deal.value) }}</div>
                                    <div class="text-sm font-black uppercase tracking-widest text-gray-500">{{ deal.stage }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Representative Performance -->
            <div class="lg:col-span-1 space-y-6">
                <div class="border border-gray-100 bg-white rounded-[40px] p-8 shadow-sm">
                    <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-8">Top Performers</h3>
                    <div class="space-y-8">
                        <div v-for="rep in sales_rep_performance" :key="rep.id" class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-gray-50 rounded-2xl flex items-center justify-center ring-4 ring-white shadow-sm overflow-hidden">
                                <img v-if="rep.avatar" :src="rep.avatar" class="w-full h-full object-cover">
                                <span v-else class="text-lg font-black text-gray-300">{{ rep.name.charAt(0) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-black text-gray-900 truncate">{{ rep.name }}</div>
                                <div class="flex items-center gap-4 mt-1">
                                    <div class="text-sm font-bold text-gray-400 uppercase tracking-wider">
                                        <span class="text-blue-600">{{ rep.won_deals }}</span> Won
                                    </div>
                                    <div class="w-1 h-1 bg-gray-200 rounded-full"></div>
                                    <div class="text-sm font-bold text-gray-400 uppercase tracking-wider">
                                        <span class="text-gray-900">{{ rep.deals_count }}</span> Total
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Health Health Legend -->
                <div class="border border-gray-100 bg-white rounded-[40px] p-8 shadow-sm">
                    <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-6 px-1">Account Health</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-emerald-50">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
                                <span class="text-xs font-black text-emerald-900 uppercase">Healthy</span>
                            </div>
                            <span class="text-sm font-black text-emerald-600">{{ health_distribution?.good || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-amber-50">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.5)]"></div>
                                <span class="text-xs font-black text-amber-900 uppercase">Attention</span>
                            </div>
                            <span class="text-sm font-black text-amber-600">{{ health_distribution?.average || 0 }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-rose-50">
                            <div class="flex items-center gap-3">
                                <div class="w-2 h-2 rounded-full bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.5)]"></div>
                                <span class="text-xs font-black text-rose-900 uppercase">At Risk</span>
                            </div>
                            <span class="text-sm font-black text-rose-600">{{ health_distribution?.at_risk || 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    pipeline: Array,
    top_deals: Array,
    sales_rep_performance: Array,
    health_distribution: Object
});

const formatCurrency = (val) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(val);
};
</script>

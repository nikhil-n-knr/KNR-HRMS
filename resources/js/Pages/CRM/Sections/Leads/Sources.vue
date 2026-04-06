<template>
    <div class="space-y-12 text-left">
        <!-- Top Performance Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
            <div v-for="stat in topStats" :key="stat.label" 
                 class="bg-white p-8 rounded-[40px] shadow-sm border border-gray-100 group hover:shadow-2xl transition-all relative overflow-hidden text-left">
                <div :class="['absolute -right-2 -top-2 w-20 h-20 rounded-full opacity-10 group-hover:scale-150 transition-transform duration-700', stat.bg]"></div>
                <div class="relative">
                    <div class="text-sm font-black text-gray-400 uppercase tracking-widest mb-3">{{ stat.label }}</div>
                    <div class="text-3xl font-black text-gray-900 tracking-tighter">{{ stat.value }}</div>
                    <div :class="['mt-4 flex items-center text-sm font-black uppercase tracking-widest', stat.trendUp ? 'text-emerald-500' : 'text-rose-500']">
                        <i :class="['fas mr-2', stat.trendUp ? 'fa-arrow-up' : 'fa-arrow-down']"></i>
                        {{ stat.trend }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Distribution -->
        <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden text-left">
            <div class="px-10 py-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/30 text-left">
                <div>
                    <h3 class="text-xl font-black text-gray-800 tracking-tight">Channel Attribution</h3>
                    <p class="text-sm font-black text-gray-400 uppercase tracking-widest mt-1">ROI & Conversion by Source</p>
                </div>
                <button class="px-6 py-2 bg-white border border-gray-100 rounded-xl text-sm font-black uppercase tracking-widest text-gray-400 hover:text-indigo-600 transition-colors shadow-sm">
                    EXPORT PDF
                </button>
            </div>
            
            <table class="min-w-full divide-y divide-gray-50 text-left">
                <thead class="bg-white">
                    <tr>
                        <th class="px-10 py-6 text-left text-sm font-black text-gray-400 uppercase tracking-[0.2em]">Acquisition Source</th>
                        <th class="px-10 py-6 text-center text-sm font-black text-gray-400 uppercase tracking-[0.2em]">Volume</th>
                        <th class="px-10 py-6 text-center text-sm font-black text-gray-400 uppercase tracking-[0.2em]">Pipeline Impact</th>
                        <th class="px-10 py-6 text-center text-sm font-black text-gray-400 uppercase tracking-[0.2em]">Conversion Power</th>
                        <th class="px-10 py-6 text-right text-sm font-black text-gray-400 uppercase tracking-[0.2em]">Attributed Rev</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-50">
                    <tr v-for="source in sources" :key="source.name" class="hover:bg-indigo-50/20 transition-colors group">
                        <td class="px-10 py-6 whitespace-nowrap">
                            <div class="flex items-center">
                                <div :class="['w-12 h-12 rounded-[18px] flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform', source.bgClass]">
                                    <i :class="['fas text-lg shadow-sm', source.icon, source.textClass]"></i>
                                </div>
                                <div class="ml-5">
                                    <div class="text-sm font-black text-gray-800 tracking-tight">{{ source.name }}</div>
                                    <div class="text-sm font-bold text-gray-400 uppercase tracking-widest mt-1">Global Channel</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-10 py-6 whitespace-nowrap text-center">
                            <div class="text-sm font-black text-gray-700">{{ source.leads.toLocaleString() }}</div>
                            <div class="text-sm font-bold text-gray-300 uppercase mt-1">PROSPECTS</div>
                        </td>
                        <td class="px-10 py-6 whitespace-nowrap text-center">
                            <div class="text-xs font-black text-indigo-700 bg-indigo-50 px-3 py-1 rounded-lg inline-block">{{ source.deals }} Deals</div>
                        </td>
                        <td class="px-10 py-6 whitespace-nowrap">
                             <div class="flex flex-col items-center">
                                <div class="text-sm font-black text-gray-900 mb-2 uppercase">{{ source.conversion }}% Rate</div>
                                <div class="w-24 bg-gray-100 rounded-full h-1.5 overflow-hidden shadow-inner border border-gray-50">
                                    <div :class="['h-full rounded-full', source.progressClass]" :style="`width: ${source.conversion * 3}%`"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-10 py-6 whitespace-nowrap text-right">
                            <div class="text-sm font-black text-emerald-600">${{ source.revenue }}</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const topStats = [
    { label: 'Dominant Channel', value: 'Website', trend: '12% MoM', trendUp: true, bg: 'bg-blue-500' },
    { label: 'Conversion Star', value: 'Referral', trend: '24.5% Yield', trendUp: true, bg: 'bg-indigo-500' },
    { label: 'Capture Cost', value: '$45.20', trend: '-$3.10 Sav', trendUp: true, bg: 'bg-amber-500' },
    { label: 'MTD Volume', value: '1,248', trend: '8% Growth', trendUp: true, bg: 'bg-emerald-500' },
];

const sources = ref([
    { name: 'Website Portals', leads: 450, deals: 45, conversion: 10, revenue: '125,000', icon: 'fa-globe', bgClass: 'bg-blue-50', textClass: 'text-blue-500', progressClass: 'bg-blue-500' },
    { name: 'Referral Network', leads: 120, deals: 30, conversion: 25, revenue: '85,000', icon: 'fa-handshake', bgClass: 'bg-indigo-50', textClass: 'text-indigo-500', progressClass: 'bg-indigo-500' },
    { name: 'LinkedIn / Social', leads: 280, deals: 14, conversion: 5, revenue: '42,000', icon: 'fa-linkedin-in', bgClass: 'bg-sky-50', textClass: 'text-sky-500', progressClass: 'bg-sky-500' },
    { name: 'Direct Campaigns', leads: 310, deals: 22, conversion: 7, revenue: '38,500', icon: 'fa-paper-plane', bgClass: 'bg-amber-50', textClass: 'text-amber-500', progressClass: 'bg-amber-500' },
    { name: 'Offline Outbound', leads: 88, deals: 2, conversion: 2.2, revenue: '5,000', icon: 'fa-phone', bgClass: 'bg-rose-50', textClass: 'text-rose-500', progressClass: 'bg-rose-500' },
]);
</script>

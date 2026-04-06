<script setup>
import { 
    BanknotesIcon, 
    CheckBadgeIcon, 
    UserGroupIcon, 
    CalendarIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    stats: Object
});
</script>

<template>
    <div class="space-y-10 font-outfit">
        <!-- Strategic KPI Array -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
             <!-- Valuation -->
             <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/40 relative overflow-hidden group hover:scale-[1.02] transition-all duration-300">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-indigo-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="relative z-10">
                    <div class="w-12 h-12 bg-indigo-600 rounded-2xl flex items-center justify-center text-white mb-6 shadow-lg shadow-indigo-100">
                        <BanknotesIcon class="h-6 w-6" />
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Total valuation</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 tracking-tighter">₹{{ (stats?.total_valuation || 0).toLocaleString('en-IN') }}</span>
                    </div>
                    <div class="mt-4 flex items-center gap-2">
                        <div class="flex items-center text-sm font-black text-emerald-500 uppercase tracking-widest bg-emerald-50 px-2 py-0.5 rounded-full">
                            <ArrowTrendingUpIcon class="h-3 w-3 mr-1" />
                            +12% Asset Density
                        </div>
                    </div>
                </div>
            </div>

             <!-- In Stock -->
             <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/40 relative overflow-hidden group hover:scale-[1.02] transition-all duration-300">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="relative z-10">
                    <div class="w-12 h-12 bg-emerald-600 rounded-2xl flex items-center justify-center text-white mb-6 shadow-lg shadow-emerald-100">
                        <CheckBadgeIcon class="h-6 w-6" />
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Stock available</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 tracking-tighter">{{ stats?.in_stock }}</span>
                        <span class="text-sm font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-2 py-0.5 rounded-lg border border-emerald-100 shadow-sm">Protocol_Ready</span>
                    </div>
                    <p class="mt-4 text-sm font-black text-slate-400 uppercase tracking-widest opacity-60">Instant Allocation Buffer</p>
                </div>
            </div>

             <!-- Assigned -->
             <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/40 relative overflow-hidden group hover:scale-[1.02] transition-all duration-300">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-blue-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="relative z-10">
                    <div class="w-12 h-12 bg-blue-600 rounded-2xl flex items-center justify-center text-white mb-6 shadow-lg shadow-blue-100">
                        <UserGroupIcon class="h-6 w-6" />
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Active Deployment</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-slate-900 tracking-tighter">{{ stats?.assigned_assets }}</span>
                        <span class="text-sm font-black text-blue-600 uppercase tracking-widest bg-blue-50 px-2 py-0.5 rounded-lg border border-blue-100 shadow-sm">Utilized</span>
                    </div>
                    <p class="mt-4 text-sm font-black text-slate-400 uppercase tracking-widest opacity-60">Operative Engagement Nodes</p>
                </div>
            </div>

             <!-- Warranty -->
             <div class="bg-white p-6 rounded-3xl border border-slate-100 shadow-xl shadow-slate-200/40 relative overflow-hidden group hover:scale-[1.02] transition-all duration-300">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-rose-50 rounded-full opacity-50 group-hover:scale-150 transition-transform duration-700"></div>
                <div class="relative z-10">
                    <div class="w-12 h-12 bg-rose-500 rounded-2xl flex items-center justify-center text-white mb-6 shadow-lg shadow-rose-100">
                        <CalendarIcon class="h-6 w-6" />
                    </div>
                    <p class="text-sm font-black text-rose-400 uppercase tracking-[0.2em]">Risk: Warranty End</p>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-2xl font-black text-rose-600 tracking-tighter">{{ stats?.warranty_expiring_soon || 0 }}</span>
                        <span class="text-sm font-black text-rose-600 uppercase tracking-widest bg-rose-50 px-2 py-0.5 rounded-lg border border-rose-100 shadow-sm border-dashed">EXP_30D</span>
                    </div>
                    <div class="mt-4 flex items-center text-rose-500 gap-1.5">
                        <ArrowTrendingDownIcon class="h-3 w-3" />
                        <span class="text-sm font-black uppercase tracking-widest">Renewal Required</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- High Fidelity Intelligence Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
             <!-- Category Breakdown Card -->
             <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/50 relative overflow-hidden">
                <div class="flex justify-between items-center mb-10">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-[0.2em]">Category Distribution</h3>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1">Resource allocation by class</p>
                    </div>
                    <div class="w-10 h-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 transition-colors cursor-pointer border border-slate-100">
                        <ArrowTrendingUpIcon class="h-5 w-5" />
                    </div>
                </div>
                
                <div class="space-y-6">
                    <!-- Dynamic Bars based on sample stats -->
                    <div v-for="(count, cat) in {'Laptops': 65, 'Mobiles': 42, 'Furniture': 15, 'Servers': 8}" :key="cat" class="space-y-2">
                        <div class="flex justify-between items-center px-1">
                            <span class="text-sm font-black text-slate-600 uppercase tracking-widest">{{ cat }}</span>
                            <span class="text-base font-black text-slate-900 tabular-nums">{{ count }} Unit</span>
                        </div>
                        <div class="h-2 w-full bg-slate-100 rounded-full overflow-hidden shadow-inner">
                            <div 
                                class="h-full bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-full shadow-[0_0_10px_rgba(79,70,229,0.3)] transition-all duration-1000" 
                                :style="{ width: (count / 100 * 100) + '%' }"
                            ></div>
                        </div>
                    </div>
                </div>
             </div>

             <!-- Health Pulse Card -->
             <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/50 relative overflow-hidden group">
                <div class="flex justify-between items-center mb-10">
                    <div>
                        <h3 class="text-sm font-black text-slate-900 uppercase tracking-[0.2em]">Maintenance Pulse</h3>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-1">Asset physical status report</p>
                    </div>
                </div>
                
                <div class="flex items-center justify-center h-48 relative">
                    <!-- Complex Circle UI -->
                    <div class="w-40 h-40 rounded-full border-[10px] border-slate-50 flex items-center justify-center relative shadow-inner">
                        <div class="absolute inset-0 rounded-full border-[10px] border-emerald-500 border-t-transparent -rotate-45 shadow-[0_0_15px_rgba(16,185,129,0.2)]"></div>
                        <div class="text-center group-hover:scale-110 transition-transform">
                            <div class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-1 leading-none">Healthy</div>
                            <div class="text-3xl font-black text-slate-900 leading-none">94<span class="text-sm opacity-30">%</span></div>
                        </div>
                    </div>
                    
                    <div class="absolute top-0 right-0 p-4 space-y-3">
                         <div class="flex items-center gap-2">
                             <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                             <span class="text-sm font-black text-slate-500 uppercase tracking-widest">Normal Ops</span>
                         </div>
                         <div class="flex items-center gap-2">
                             <div class="w-2 h-2 rounded-full bg-amber-500"></div>
                             <span class="text-sm font-black text-slate-500 uppercase tracking-widest">Restricted</span>
                         </div>
                         <div class="flex items-center gap-2">
                             <div class="w-2 h-2 rounded-full bg-rose-500"></div>
                             <span class="text-sm font-black text-slate-500 uppercase tracking-widest">Critical</span>
                         </div>
                    </div>
                </div>
                
                <div class="mt-8 flex gap-4">
                     <button class="flex-1 h-11 bg-slate-50 border border-slate-100 rounded-2xl text-sm font-black text-indigo-600 uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all shadow-sm">View Reports</button>
                     <button class="flex-1 h-11 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-slate-800 transition-all shadow-xl shadow-slate-200">System Scan</button>
                </div>
             </div>
        </div>
    </div>
</template>

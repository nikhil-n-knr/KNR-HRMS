<script setup>
import { 
    BanknotesIcon, 
    ArrowDownTrayIcon, 
    DocumentTextIcon,
    ShieldCheckIcon,
    CurrencyRupeeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    employee: Object,
    payslips: Array
});

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { 
    style: 'currency', 
    currency: 'INR',
    maximumFractionDigits: 0 
}).format(val || 0);

const getStatusStyles = (status) => {
    switch (status?.toLowerCase()) {
        case 'paid':
        case 'published':
            return 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-sm';
        case 'pending':
            return 'bg-amber-50 text-amber-600 border-amber-100 shadow-sm';
        default:
            return 'bg-slate-50 text-slate-400 border-slate-100';
    }
};
</script>

<template>
    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-700">
        <!-- Strategic Header Matrix -->
        <div class="bg-white rounded-[2.5rem] border border-slate-100 p-8 md:p-10 shadow-2xl shadow-slate-200/40 relative overflow-hidden group">
            <div class="absolute -right-20 -top-20 w-80 h-80 bg-indigo-50 rounded-full opacity-30 group-hover:scale-110 transition-transform duration-1000"></div>
            
            <div class="flex flex-col md:flex-row justify-between items-center gap-8 relative z-10">
                <div class="flex items-center gap-6">
                    <div class="w-16 h-16 bg-slate-900 rounded-2xl flex items-center justify-center text-emerald-400 shadow-xl group-hover:rotate-12 transition-transform">
                        <BanknotesIcon class="w-8 h-8" />
                    </div>
                    <div>
                        <h2 class="text-2xl font-black text-slate-900 uppercase tracking-tight">Financial <span class="text-indigo-600">Artifacts</span></h2>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] mt-1.5 flex items-center gap-2">
                            <ShieldCheckIcon class="w-4 h-4 text-emerald-500" />
                            Secure Payroll Records & Net Compensation History
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1 leading-none">Last 12 Records</p>
                        <p class="text-sm font-black text-slate-900 uppercase tabular-nums leading-none mt-1">{{ payslips?.length || 0 }} Generated</p>
                    </div>
                    <div class="h-10 w-px bg-slate-100 mx-2 hidden sm:block"></div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-inner">
                         <CurrencyRupeeIcon class="w-6 h-6" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytical Grid of Slips -->
        <div v-if="payslips && payslips.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            <div v-for="slip in payslips" :key="slip.id" class="bg-white rounded-[2.5rem] border border-slate-100 p-6 shadow-sm hover:shadow-2xl hover:shadow-indigo-500/10 hover:border-indigo-200 transition-all group/card relative overflow-hidden">
                <!-- Data Visualization Strip -->
                <div class="absolute top-0 right-0 w-32 h-32 bg-slate-50 rounded-full -mr-16 -mt-16 opacity-50 group-hover/card:scale-110 group-hover/card:bg-indigo-50 transition-all duration-700"></div>
                
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-8">
                        <div>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] block mb-1 leading-none">Fiscal Record</span>
                            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight leading-none mt-1.5">{{ slip.month_label }}</h3>
                        </div>
                        <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest border" :class="getStatusStyles(slip.status)">
                            {{ slip.status }}
                        </span>
                    </div>

                    <div class="space-y-4 mb-8">
                        <div class="flex justify-between items-center p-4 bg-slate-50/50 rounded-2xl border border-slate-50 group-hover/card:bg-white group-hover/card:border-slate-100 transition-all">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Gross Earnings</span>
                            <span class="text-sm font-black text-slate-600 tabular-nums leading-none">{{ formatCurrency(slip.gross_pay) }}</span>
                        </div>
                        <div class="flex justify-between items-center p-4 bg-indigo-50/30 rounded-2xl border border-indigo-100/50 group-hover/card:bg-indigo-50 transition-all">
                            <span class="text-[10px] font-black text-indigo-600/60 uppercase tracking-widest leading-none">Net Compensation</span>
                            <span class="text-lg font-black text-indigo-600 tabular-nums leading-none">{{ formatCurrency(slip.net_pay) }}</span>
                        </div>
                    </div>

                    <a 
                        :href="slip.download_url"
                        class="w-full h-14 bg-slate-900 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] flex items-center justify-center gap-3 shadow-xl shadow-slate-200 hover:bg-emerald-600 hover:shadow-emerald-500/20 transition-all active:scale-[0.98] group/dl"
                    >
                        <ArrowDownTrayIcon class="w-4 h-4 text-emerald-400 transition-transform group-hover/dl:-translate-y-0.5" />
                        Download Artifact
                    </a>
                </div>
            </div>
        </div>

        <!-- Null State Discovery -->
        <div v-else class="bg-white rounded-[2.5rem] border border-slate-100 p-20 shadow-2xl shadow-slate-200/40 flex flex-col items-center justify-center text-center opacity-80 group">
             <div class="w-24 h-24 bg-slate-50 rounded-[2rem] flex items-center justify-center text-slate-200 mb-8 group-hover:rotate-12 transition-transform">
                 <DocumentTextIcon class="w-12 h-12" />
             </div>
             <h3 class="text-lg font-black text-slate-900 uppercase tracking-wider mb-2">No Compensation Records Found</h3>
             <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] max-w-xs leading-relaxed">System has not generated any published payslips for your account yet.</p>
        </div>
    </div>
</template>

<style scoped>
.tab-active {
    position: relative;
}
.tab-active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: #4f46e5;
}
</style>

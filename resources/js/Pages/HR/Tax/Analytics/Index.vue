<template>
    <Head title="Verification Analytics" />
    <MainLayout>
        <div class="py-8 px-4 sm:px-6 lg:px-8">
             <div class="flex justify-between items-center mb-8">
                <div>
                     <h1 class="text-2xl font-bold text-gray-900">Verification Health</h1>
                     <p class="text-gray-500">Real-time insights into the proof verification drive.</p>
                </div>
                <div class="flex gap-3">
                    <Link :href="route('hr.tax.reports.index')" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50">
                        Export Reports
                    </Link>
                    <Link :href="route('hr.tax.proofs.index')" class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm font-medium hover:bg-indigo-700">
                        Go to Queue
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Completion -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                         <span class="text-xs font-bold uppercase text-gray-400">Completion</span>
                    </div>
                    <div class="text-3xl font-black text-gray-900">{{ stats.completion_rate }}%</div>
                    <div class="w-full bg-gray-100 h-1.5 rounded-full mt-2 overflow-hidden">
                        <div class="bg-indigo-500 h-1.5 rounded-full transition-all duration-1000" :style="{ width: stats.completion_rate + '%' }"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">{{ stats.verified_count }} verified / {{ stats.verified_count + stats.rejected_count + stats.pending_count }} total</p>
                </div>

                <!-- Rejection Rate -->
                 <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 rounded-full bg-red-50 flex items-center justify-center text-red-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                         <span class="text-xs font-bold uppercase text-gray-400">Rejection Rate</span>
                    </div>
                    <div class="text-3xl font-black text-gray-900">{{ stats.rejection_rate }}%</div>
                    <p class="text-xs text-gray-500 mt-2">{{ stats.rejected_count }} rejected due to errors</p>
                </div>

                <!-- Tax Impact -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
                           <span class="font-bold text-lg">₹</span>
                        </div>
                         <span class="text-xs font-bold uppercase text-gray-400">Tax Saved</span>
                    </div>
                    <!-- Truncate logic for Millions -->
                    <div class="text-3xl font-black text-gray-900">₹ {{ formatCompact(stats.tax_saved_approx) }}</div>
                     <p class="text-xs text-gray-500 mt-2">Est. tax benefit passed to employees</p>
                </div>
                
                <!-- Pending High Value -->
                 <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-16 h-16 bg-orange-400 blur-2xl opacity-10 rounded-full"></div>
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-10 h-10 rounded-full bg-orange-50 flex items-center justify-center text-orange-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        </div>
                         <span class="text-xs font-bold uppercase text-orange-500">High Priority</span>
                    </div>
                    <div class="text-3xl font-black text-gray-900">{{ stats.high_value_pending }}</div>
                     <p class="text-xs text-gray-500 mt-2">Pending claims > ₹1 Lakh</p>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Velocity Chart -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-6">Verification Velocity (Last 7 Days)</h3>
                    <!-- Simple CSS Bar Chart -->
                    <div class="flex items-end justify-between h-40 space-x-2">
                         <div v-for="day in velocity" :key="day.date" class="flex flex-col items-center flex-1 group">
                                <div class="relative w-full flex justify-center">
                                    <span class="absolute -top-6 text-xs font-bold text-gray-600 opacity-0 group-hover:opacity-100 transition">{{ day.count }}</span>
                                    <div 
                                        class="w-full bg-indigo-100 hover:bg-indigo-500 transition-colors rounded-t-sm" 
                                        :style="{ height: Math.max(10, (day.count / maxVelocity) * 120 ) + 'px' }"
                                    ></div>
                                </div>
                                <span class="text-sm text-gray-400 mt-2">{{ formatDate(day.date) }}</span>
                         </div>
                    </div>
                </div>

                <!-- Help / Insight -->
                <div class="bg-indigo-900 rounded-xl shadow-lg p-6 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white/5 rounded-full blur-3xl -mr-16 -mt-16"></div>
                    <h3 class="font-bold text-lg mb-4 relative z-10">Payroll Deadline: 25th Jan</h3>
                    <p class="text-indigo-200 text-sm mb-6 relative z-10 max-w-sm">
                        Ensure all high-value tax proofs are verified before the deadline to reflect accuracy in this month's payroll. Unverified proofs will revert to taxable income.
                    </p>
                    <Link :href="route('hr.tax.proofs.index')" class="inline-block px-5 py-2.5 bg-white text-indigo-900 font-bold rounded-lg text-sm relative z-10 hover:bg-gray-100 transition">
                        Start Verifying
                    </Link>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { computed } from 'vue';

const props = defineProps({
    stats: Object,
    velocity: Array
});

const maxVelocity = computed(() => {
    return Math.max(...props.velocity.map(v => v.count), 1);
});

const formatCompact = (number) => new Intl.NumberFormat('en-IN', { notation: "compact", compactDisplay: "short" }).format(number);
const formatDate = (dateString) => {
    const d = new Date(dateString);
    return `${d.getDate()}/${d.getMonth()+1}`;
};
</script>

<template>
    <TalentLayout>
        <template #actions>
            <div class="flex items-center gap-3">
                 <a :target="'_blank'" :href="route('admin.document-templates.index')" class="p-2 bg-white border border-gray-200 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all shadow-sm" title="Template Builder">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                 </a>
                 <a :target="'_blank'" :href="route('admin.salary-structures.index')" class="p-2 bg-white border border-gray-200 rounded-xl text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all shadow-sm" title="Salary Config">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                 </a>
            </div>
        </template>
 
        <!-- Header Section -->
        <div class="mb-8 px-4 sm:px-0">
            <h1 class="text-2xl md:text-3xl font-black text-slate-900 tracking-tight">Offer Letters</h1>
            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Track and manage candidate agreements</p>
        </div>
 
        <!-- Mobile Card View -->
        <div class="block lg:hidden space-y-4 px-4 sm:px-0">
            <div v-for="offer in offers.data" :key="offer.id" class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 flex flex-col gap-4 relative overflow-hidden group">
                <div class="absolute left-0 top-0 h-full w-1 bg-gradient-to-b" :class="getStatusGradient(offer.status)"></div>
                
                <div class="flex justify-between items-start">
                    <div>
                        <h4 class="text-sm font-black text-slate-900 tracking-tight">{{ offer.job_application?.candidate?.first_name }} {{ offer.job_application?.candidate?.last_name }}</h4>
                        <p class="text-sm font-bold text-gray-400 uppercase tracking-widest">{{ offer.designation || offer.job_application?.job_posting?.title }}</p>
                    </div>
                    <span :class="getStatusClass(offer.status)" class="px-2.5 py-1 text-sm font-black uppercase tracking-widest rounded-full shadow-sm">
                        {{ offer.status }}
                    </span>
                </div>
 
                <div class="grid grid-cols-2 gap-4 bg-gray-50/50 p-3 rounded-xl border border-gray-100/50">
                    <div>
                        <p class="text-sm font-black text-gray-400 uppercase tracking-widest mb-1">Joining Date</p>
                        <p class="text-xs font-bold text-slate-600">{{ new Date(offer.joining_date).toLocaleDateString() }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-black text-gray-400 uppercase tracking-widest mb-1">Expiry</p>
                        <p class="text-xs font-bold text-slate-600">{{ offer.expiry_date ? new Date(offer.expiry_date).toLocaleDateString() : '-' }}</p>
                    </div>
                </div>
 
                <div class="flex gap-2">
                    <a :href="route('talent.offers.show', offer.id)" class="flex-1 bg-white border border-gray-200 text-slate-700 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest text-center shadow-sm hover:bg-gray-50">
                        Manage Offer
                    </a>
                    <a v-if="offer.token" :href="route('portal.offer.show', offer.token)" target="_blank" class="flex-1 bg-indigo-50 text-indigo-600 py-2.5 rounded-xl text-sm font-black uppercase tracking-widest text-center shadow-sm hover:bg-indigo-100 transition-all">
                        Preview Portal
                    </a>
                </div>
            </div>
            
            <div v-if="offers.data.length === 0" class="py-20 text-center bg-white rounded-3xl border-2 border-dashed border-gray-200">
                <div class="mx-auto h-12 w-12 text-gray-200 mb-2">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                </div>
                <p class="text-xs font-black text-gray-400 uppercase tracking-widest">No offer history found</p>
            </div>
        </div>
 
        <!-- Desktop Table View -->
        <div class="hidden lg:block bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50/50">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-black text-gray-400 uppercase tracking-[0.2em]">Candidate</th>
                        <th class="px-6 py-4 text-left text-sm font-black text-gray-400 uppercase tracking-[0.2em]">Role Details</th>
                        <th class="px-6 py-4 text-left text-sm font-black text-gray-400 uppercase tracking-[0.2em]">Joining Date</th>
                        <th class="px-6 py-4 text-left text-sm font-black text-gray-400 uppercase tracking-[0.2em]">Current Status</th>
                        <th class="px-6 py-4 text-left text-sm font-black text-gray-400 uppercase tracking-[0.2em]">Letter Expiry</th>
                        <th class="px-6 py-4 text-right text-sm font-black text-gray-400 uppercase tracking-[0.2em]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="offer in offers.data" :key="offer.id" class="hover:bg-gray-50/50 transition-colors group">
                        <td class="px-6 py-5 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-9 w-9 rounded-xl bg-indigo-50 flex items-center justify-center text-indigo-600 font-black text-sm border border-indigo-100 mr-3">
                                    {{ (offer.job_application?.candidate?.first_name?.[0] || '') + (offer.job_application?.candidate?.last_name?.[0] || '') }}
                                </div>
                                <div>
                                    <div class="text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors">
                                        {{ offer.job_application?.candidate?.first_name }} {{ offer.job_application?.candidate?.last_name }}
                                    </div>
                                    <div class="text-sm font-bold text-gray-400 lowercase tracking-tight">
                                        {{ offer.job_application?.candidate?.email }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap">
                            <div class="text-xs font-bold text-slate-600 tracking-tight">{{ offer.designation || offer.job_application?.job_posting?.title }}</div>
                        </td>
                         <td class="px-6 py-5 whitespace-nowrap">
                            <div class="text-xs font-bold text-slate-600 tracking-tight">{{ new Date(offer.joining_date).toLocaleDateString() }}</div>
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap">
                            <span :class="getStatusClass(offer.status)" class="px-3 py-1 text-sm font-black uppercase tracking-widest rounded-full shadow-sm">
                                {{ offer.status }}
                            </span>
                        </td>
                         <td class="px-6 py-5 whitespace-nowrap text-xs font-bold text-slate-400 tracking-tight">
                            {{ offer.expiry_date ? new Date(offer.expiry_date).toLocaleDateString() : '-' }}
                        </td>
                        <td class="px-6 py-5 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end space-x-2">
                                <a v-if="offer.token" :href="route('portal.offer.show', offer.token)" target="_blank" class="p-2 text-slate-400 hover:text-emerald-600 transition-colors" title="Candidate View">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                </a>
                                <a :href="route('talent.offers.show', offer.id)" class="p-2 text-slate-400 hover:text-indigo-600 transition-colors" title="Admin Settings">
                                     <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                     <tr v-if="offers.data.length === 0">
                        <td colspan="6" class="px-6 py-12 text-center text-gray-300">
                             <div class="mx-auto h-12 w-12 mb-2 opacity-50">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                             </div>
                             <p class="text-xs font-black uppercase tracking-widest">Found Zero Agreements</p>
                        </td>
                    </tr>
                </tbody>
            </table>
            
             <!-- Pagination -->
            <div v-if="offers.links && offers.data.length > 0" class="bg-gray-50/50 px-6 py-4 flex items-center justify-between border-t border-gray-100">
                 <div class="text-sm font-black text-gray-400 uppercase tracking-widest">
                     Showing {{ offers.from }} - {{ offers.to }} of {{ offers.total }} units
                 </div>
                 <div class="flex gap-2">
                     <Link v-if="offers.prev_page_url" :href="offers.prev_page_url" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-gray-50 shadow-sm">Prev</Link>
                     <Link v-if="offers.next_page_url" :href="offers.next_page_url" class="px-4 py-2 bg-white border border-gray-200 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-gray-50 shadow-sm">Next</Link>
                 </div>
            </div>
        </div>
    </TalentLayout>
</template>
 
<script setup>
import TalentLayout from '@/Layouts/TalentLayout.vue';
import { Link } from '@inertiajs/vue3';
 
const props = defineProps({
    offers: Object,
    filters: Object
});
 
const getStatusGradient = (status) => {
    switch (status) {
        case 'Draft': return 'from-slate-300 to-slate-500';
        case 'Sent': return 'from-indigo-400 to-indigo-600';
        case 'Viewed': return 'from-purple-400 to-purple-600';
        case 'Accepted': return 'from-emerald-400 to-emerald-600';
        case 'Rejected': return 'from-rose-400 to-rose-600';
        default: return 'from-gray-200 to-gray-400';
    }
};
 
const getStatusClass = (status) => {
    switch (status) {
        case 'Draft': return 'bg-slate-100 text-slate-800';
        case 'Sent': return 'bg-indigo-100 text-indigo-800 shadow-indigo-100';
        case 'Viewed': return 'bg-purple-100 text-purple-800 shadow-purple-100';
        case 'Accepted': return 'bg-emerald-100 text-emerald-800 shadow-emerald-100';
        case 'Rejected': return 'bg-rose-100 text-rose-800 shadow-rose-100';
        case 'Withdrawn': return 'bg-rose-50 text-rose-500 line-through';
        default: return 'bg-gray-100 text-gray-800';
    }
};
</script>


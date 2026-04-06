<template>
    <MainLayout>
        <Head title="Asset Procurement Queue" />
        
        <div class="space-y-10 pb-20 font-outfit animate-in fade-in slide-in-from-bottom-5 duration-700">
            <!-- Tactical Request Header -->
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8 bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-100 p-8 shadow-2xl shadow-slate-200/40 relative overflow-hidden group">
                <div class="absolute -right-8 -top-8 w-32 h-32 bg-indigo-50 rounded-full blur-2xl group-hover:bg-indigo-100 transition-colors duration-1000"></div>
                
                <div class="flex items-center gap-6 relative z-10">
                    <div class="w-14 h-14 bg-slate-900 rounded-2xl flex items-center justify-center text-indigo-400 shadow-xl group-hover:rotate-6 transition-transform">
                        <InboxArrowDownIcon class="w-8 h-8" />
                    </div>
                    <div>
                        <h1 class="text-2xl font-black text-slate-900 uppercase tracking-tight flex items-center gap-3">
                            Resource Request Hub
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-black bg-indigo-50 text-indigo-600 border border-indigo-100 uppercase tracking-widest shadow-sm">Audit Queue</span>
                        </h1>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mt-1.5 flex items-center gap-2">
                            <DocumentMagnifyingGlassIcon class="w-4 h-4 text-indigo-500" />
                            Authentication of internal resource allocation & hardware provisioning
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 relative z-10 shrink-0">
                    <div class="px-6 py-3 bg-slate-50 rounded-2xl border border-slate-100">
                        <span class="text-xs font-black text-slate-400 uppercase tracking-widest block mb-1">Queue Density</span>
                        <div class="text-lg font-black text-slate-900 tabular-nums">{{ requests.data.filter(r => r.status === 'Pending').length }} Pending Nodes</div>
                    </div>
                </div>
            </div>

            <!-- Registry Terminal -->
            <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/40 overflow-hidden relative group">
                <div class="overflow-x-auto relative z-10">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-slate-900 border-b border-slate-800">
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Operative Profile</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Requested Vector (Asset)</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Priority Matrix</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Contextual Reason</th>
                                <th class="px-8 py-6 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Status</th>
                                <th class="px-8 py-6 text-right text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Command Array</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="req in requests.data" :key="req.id" class="group/row hover:bg-slate-50/80 transition-all duration-300">
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-11 h-11 bg-slate-900 rounded-2xl flex items-center justify-center text-white font-black text-xs border-2 border-white shadow-lg group-hover/row:bg-indigo-600 transition-all">
                                            {{ req.user?.name[0] }}
                                        </div>
                                        <div>
                                            <div class="text-lg font-black text-slate-900 uppercase tracking-tight group-hover/row:text-indigo-700 transition-colors">{{ req.user?.name }}</div>
                                            <div class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mt-1 italic">ID: #OPR-{{ String(req.id).padStart(3, '0') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-3">
                                        <span class="text-lg">💻</span>
                                        <span class="text-base font-black text-slate-700 uppercase tracking-widest">{{ req.category?.name || 'GENERIC_RESOURCE' }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div v-if="req.priority === 'High'" class="px-3 py-1 bg-rose-50 text-rose-600 rounded-xl border border-rose-100 text-sm font-black uppercase tracking-widest shadow-sm flex items-center gap-2 w-fit">
                                        <div class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></div>
                                        Critical_Path
                                    </div>
                                    <div v-else-if="req.priority === 'Medium'" class="px-3 py-1 bg-amber-50 text-amber-600 rounded-xl border border-amber-100 text-sm font-black uppercase tracking-widest w-fit">
                                        Standard_Load
                                    </div>
                                    <div v-else class="px-3 py-1 bg-slate-50 text-slate-400 rounded-xl border border-slate-200 text-sm font-black uppercase tracking-widest w-fit">
                                        Low_Latency
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="max-w-[250px]">
                                        <p class="text-base font-black text-slate-500 uppercase tracking-tight leading-relaxed italic truncate" :title="req.reason">" {{ req.reason }} "</p>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div :class="getStatusStyles(req.status)" class="px-4 py-1.5 rounded-full border text-xs font-black uppercase tracking-[0.2em] shadow-sm w-fit">
                                        {{ req.status }}
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div v-if="req.status === 'Pending'" class="flex justify-end gap-3 opacity-0 group-hover/row:opacity-100 transition-all translate-x-4 group-hover/row:translate-x-0">
                                        <Link :href="route('admin.asset-requests.approve', req.id)" method="post" as="button" class="h-10 px-5 bg-slate-900 text-white rounded-xl text-sm font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-xl active:scale-95 flex items-center gap-2">
                                            <CheckIcon class="w-4 h-4 text-emerald-400" />
                                            Authorize
                                        </Link>
                                        <button @click="reject(req)" class="h-10 px-5 bg-white text-rose-600 border border-rose-100 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-rose-50 transition-all shadow-sm active:scale-95 flex items-center gap-2">
                                            <XMarkIcon class="w-4 h-4" />
                                            Deny
                                        </button>
                                    </div>
                                    <div v-else class="text-sm font-black text-slate-300 uppercase tracking-widest font-mono italic">
                                        VALIDATED: {{ req.approved_at ? new Date(req.approved_at).toLocaleDateString() : 'ARCHIVED' }}
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="requests.data.length === 0">
                                <td colspan="6" class="px-8 py-32 text-center grayscale opacity-30 italic">
                                    <InboxIcon class="w-20 h-20 mx-auto text-slate-300 mb-6 animate-pulse" />
                                    <p class="text-sm font-black uppercase tracking-[0.4em]">Zero incoming resource requests detected</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Denial Protocol Modal -->
            <PremiumModal 
                :show="showRejectModal" 
                @close="showRejectModal = false" 
                title="Denial Protocol" 
                subtitle="Specify Strategic Context for Rejection"
                icon="fa-circle-xmark"
                maxWidth="xl"
            >
                <div class="space-y-8 pt-6">
                    <div class="bg-rose-50 rounded-[2rem] border-2 border-rose-100 p-8 flex items-start gap-6 relative overflow-hidden group/alert">
                        <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-rose-500 shadow-xl group-hover/alert:scale-110 transition-transform">
                            <XMarkIcon class="w-8 h-8" />
                        </div>
                        <div>
                            <h4 class="text-lg font-black text-rose-700 uppercase tracking-tight">Access Denial</h4>
                            <p class="text-sm font-black text-rose-600/60 uppercase tracking-widest mt-1">Resource allocation termination is permanent</p>
                        </div>
                    </div>

                    <div class="space-y-3.5 px-2">
                        <label class="text-sm font-black text-slate-500 uppercase tracking-[0.2em] px-2 leading-none">Denial Context (Reason)</label>
                        <textarea v-model="rejectReason" rows="5" class="w-full bg-slate-50 border-2 border-slate-100 rounded-[1.5rem] p-6 text-lg font-black text-slate-900 focus:bg-white focus:ring-8 focus:ring-rose-500/5 focus:border-rose-500 transition-all placeholder:text-slate-300 uppercase tracking-widest leading-relaxed" placeholder="SPECIFY_REASON_FOR_DENIAL..."></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-10 border-t border-slate-50 px-2 mt-4">
                        <button type="button" @click="showRejectModal = false" class="text-sm font-black uppercase tracking-[0.34em] text-slate-400 hover:text-slate-600 transition-colors">Abort_Action</button>
                        <button @click="confirmReject" :disabled="!rejectReason" class="h-16 px-12 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] shadow-2xl shadow-rose-500/10 hover:bg-rose-600 transition-all flex items-center gap-4 active:scale-95 disabled:opacity-30 disabled:grayscale">
                            <CheckIcon class="w-5 h-5 text-emerald-400" />
                            Confirm Rejection
                        </button>
                    </div>
                </div>
            </PremiumModal>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import { 
    InboxArrowDownIcon, 
    DocumentMagnifyingGlassIcon, 
    CheckIcon, 
    XMarkIcon,
    InboxIcon,
    ExclamationTriangleIcon
} from '@heroicons/vue/24/outline';

defineProps({
    requests: Object
});

const showRejectModal = ref(false);
const selectedReq = ref(null);
const rejectReason = ref('');

const reject = (req) => {
    selectedReq.value = req;
    showRejectModal.value = true;
};

const confirmReject = () => {
    router.post(route('admin.asset-requests.reject', selectedReq.value.id), {
        rejection_reason: rejectReason.value
    }, {
        onSuccess: () => {
            showRejectModal.value = false;
            rejectReason.value = '';
            selectedReq.value = null;
        }
    });
};

const getStatusStyles = (status) => {
    switch(status) {
        case 'Pending': return 'bg-amber-50 text-amber-600 border-amber-100';
        case 'Approved': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'Rejected': return 'bg-rose-50 text-rose-600 border-rose-100';
        case 'Fulfilled': return 'bg-indigo-50 text-indigo-600 border-indigo-100';
        default: return 'bg-slate-50 text-slate-400 border-slate-200';
    }
};
</script>

<style scoped>
.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
</style>

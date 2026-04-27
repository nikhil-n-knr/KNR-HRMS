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
    ExclamationTriangleIcon,
    InformationCircleIcon,
    UserCircleIcon,
    CpuChipIcon,
    ClockIcon,
    BoltIcon,
    ArrowPathIcon,
    TrashIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
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
        case 'Pending': return 'bg-amber-50 text-amber-600 border-amber-100 shadow-amber-500/5';
        case 'Approved': return 'bg-emerald-50 text-emerald-600 border-emerald-100 shadow-emerald-500/5';
        case 'Rejected': return 'bg-rose-50 text-rose-600 border-rose-100 shadow-rose-500/5';
        case 'Fulfilled': return 'bg-indigo-50 text-indigo-600 border-indigo-100 shadow-indigo-500/5';
        default: return 'bg-slate-50 text-slate-400 border-slate-200';
    }
};

const getStatusLabel = (s) => {
    switch(s) {
        case 'Pending': return 'Waiting';
        case 'Approved': return 'Approved';
        case 'Rejected': return 'Rejected';
        case 'Fulfilled': return 'Given Out';
        default: return s;
    }
};
</script>

<template>
    <Head title="Equipment Requests" />
    
    <MainLayout>
        <div class="h-screen flex flex-col bg-slate-50 font-outfit overflow-hidden -m-8">
            <!-- Strategic Header Terminal -->
            <div class="bg-slate-900 border-b border-white/5 px-10 py-12 flex flex-shrink-0 justify-between items-center z-10 relative overflow-hidden">
                <div class="absolute -right-24 -top-24 w-96 h-96 bg-indigo-500/10 rounded-full blur-[100px]"></div>
                
                <div class="relative z-10 flex items-center gap-8">
                    <div class="w-16 h-16 bg-white/5 border border-white/10 rounded-2xl flex items-center justify-center text-indigo-400 shadow-xl backdrop-blur-xl group-hover:rotate-12 transition-transform">
                        <InboxArrowDownIcon class="h-8 w-8" />
                    </div>
                    <div>
                        <div class="flex items-center gap-4">
                            <h1 class="text-3xl font-black text-white uppercase tracking-tight">Equipment Requests</h1>
                            <div class="group/tooltip relative flex items-center">
                                <InformationCircleIcon class="w-5 h-5 text-indigo-400 cursor-help opacity-70 hover:opacity-100 transition-opacity" />
                                <div class="absolute left-full ml-4 top-1/2 -translate-y-1/2 w-80 bg-slate-900 text-white text-sm font-medium px-5 py-4 rounded-2xl opacity-0 invisible group-hover/tooltip:opacity-100 group-hover/tooltip:visible transition-all shadow-2xl z-50 pointer-events-none border border-slate-800 leading-relaxed shadow-indigo-500/20">
                                    This is the waiting list for equipment. When employees need a new laptop or chair, their request shows up here for you to "Approve" or "Reject".
                                </div>
                            </div>
                        </div>
                        <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] mt-2 italic shadow-sm">Manage hardware provisioning and allocation</p>
                    </div>
                </div>
                
                <div class="relative z-10 flex items-center gap-6">
                    <div class="h-16 px-8 bg-white/5 border border-white/10 rounded-2xl flex items-center gap-8 shadow-sm">
                        <div class="flex flex-col items-end">
                            <span class="text-[10px] font-black text-slate-500 uppercase tracking-widest italic">Waiting</span>
                            <span class="text-xl font-black text-white tabular-nums tracking-tighter">{{ requests.data.filter(r => r.status === 'Pending').length }} Pending</span>
                        </div>
                        <div class="w-1.5 h-10 bg-white/10 rounded-full"></div>
                        <div class="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center text-white shadow-lg animate-pulse">
                            <ClockIcon class="w-6 h-6" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-10 custom-scrollbar">
                <div class="max-w-[1600px] mx-auto">
                    <!-- Requests Terminal -->
                    <div class="bg-white rounded-[3rem] shadow-2xl shadow-slate-200/40 border border-slate-100 overflow-hidden relative group">
                        <div class="px-10 py-8 border-b border-slate-50 flex items-center justify-between bg-slate-50/50 backdrop-blur-sm">
                            <h2 class="text-lg font-black text-slate-900 uppercase tracking-tight italic">Request Queue</h2>
                            <span class="px-4 py-1.5 bg-slate-900 text-white rounded-full text-[10px] font-black uppercase tracking-widest">{{ requests.data.length }} total entries</span>
                        </div>

                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-900 border-b border-slate-800 italic">
                                    <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Who is asking?</th>
                                    <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">What do they need?</th>
                                    <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">How urgent?</th>
                                    <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Why?</th>
                                    <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest">Current State</th>
                                    <th class="px-10 py-6 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="req in requests.data" :key="req.id" class="group/row hover:bg-slate-50/50 transition-all duration-300">
                                    <td class="px-10 py-8">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-900 font-black text-sm border border-slate-200 shadow-sm group-hover/row:bg-slate-900 group-hover/row:text-emerald-400 group-hover/row:rotate-6 transition-all">
                                                {{ req.user?.name[0] }}
                                            </div>
                                            <div>
                                                <div class="text-base font-black text-slate-900 uppercase tracking-tight">{{ req.user?.name }}</div>
                                                <div class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1 italic">#ID: {{ String(req.id).padStart(3, '0') }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-10 py-8">
                                        <div class="flex items-center gap-3">
                                            <CpuChipIcon class="w-5 h-5 text-indigo-400" />
                                            <span class="text-sm font-black text-slate-700 uppercase tracking-widest italic">{{ req.category?.name || 'GENERIC' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-10 py-8">
                                        <div v-if="req.priority === 'High'" class="px-3 py-1 bg-rose-50 text-rose-600 rounded-xl border border-rose-100 text-[10px] font-black uppercase tracking-widest shadow-sm flex items-center gap-2 w-fit italic">
                                            <BoltIcon class="w-4 h-4 animate-bounce" />
                                            Urgent
                                        </div>
                                        <div v-else-if="req.priority === 'Medium'" class="px-3 py-1 bg-amber-50 text-amber-600 rounded-xl border border-amber-100 text-[10px] font-black uppercase tracking-widest w-fit italic">
                                            Regular
                                        </div>
                                        <div v-else class="px-3 py-1 bg-slate-50 text-slate-400 rounded-xl border border-slate-200 text-[10px] font-black uppercase tracking-widest w-fit italic">
                                            Low
                                        </div>
                                    </td>
                                    <td class="px-10 py-8">
                                        <div class="max-w-[200px]">
                                            <p class="text-xs font-black text-slate-400 uppercase tracking-tight leading-relaxed italic truncate" :title="req.reason">" {{ req.reason }} "</p>
                                        </div>
                                    </td>
                                    <td class="px-10 py-8">
                                        <div :class="getStatusStyles(req.status)" class="px-4 py-1.5 rounded-full border text-[10px] font-black uppercase tracking-[0.2em] shadow-sm w-fit">
                                            {{ getStatusLabel(req.status) }}
                                        </div>
                                    </td>
                                    <td class="px-10 py-8 text-right">
                                        <div v-if="req.status === 'Pending'" class="flex justify-end gap-3 opacity-0 group-hover/row:opacity-100 transition-all translate-x-4 group-hover/row:translate-x-0">
                                            <Link :href="route('admin.asset-requests.approve', req.id)" method="post" as="button" class="h-10 px-6 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 transition-all shadow-xl active:scale-95 flex items-center gap-2">
                                                <CheckIcon class="w-4 h-4 text-emerald-400" />
                                                Approve
                                            </Link>
                                            <button @click="reject(req)" class="h-10 px-6 bg-white text-rose-600 border border-rose-100 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-rose-600 hover:text-white transition-all shadow-sm active:scale-95 flex items-center gap-2">
                                                <XMarkIcon class="w-4 h-4" />
                                                Reject
                                            </button>
                                        </div>
                                        <div v-else class="text-[10px] font-black text-slate-300 uppercase tracking-[0.3em] font-mono italic">
                                            CLOSED: {{ req.approved_at ? new Date(req.approved_at).toLocaleDateString() : 'ARCHIVED' }}
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="requests.data.length === 0">
                                    <td colspan="6" class="px-10 py-32 text-center grayscale opacity-30 italic">
                                        <InboxIcon class="w-24 h-24 mx-auto text-slate-300 mb-6 animate-pulse" />
                                        <p class="text-xl font-black text-slate-900 uppercase tracking-tight">Box is empty</p>
                                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.4em] mt-2">No new requests detected right now</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Denial Modal Terminal -->
            <PremiumModal 
                :show="showRejectModal" 
                @close="showRejectModal = false" 
                title="Reject Request" 
                subtitle="Why are you saying no?"
                icon="XMarkIcon"
                maxWidth="xl"
            >
                <div class="space-y-10 pt-6">
                    <div class="bg-rose-50 rounded-[2.5rem] border-2 border-rose-100 p-8 flex items-start gap-8 relative overflow-hidden group/alert shadow-inner">
                        <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-rose-500 shadow-xl group-hover/alert:scale-110 transition-transform flex-shrink-0">
                            <XMarkIcon class="w-8 h-8" />
                        </div>
                        <div>
                            <h4 class="text-lg font-black text-rose-900 uppercase tracking-tight">Termination Protocol</h4>
                            <p class="text-xs font-black text-rose-600/60 uppercase tracking-widest mt-1 leading-relaxed">This person will not get this equipment. This action cannot be reversed easily.</p>
                        </div>
                    </div>

                    <div class="space-y-4 px-2">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.4em] px-4 leading-none italic">Reason (Required)</label>
                        <textarea v-model="rejectReason" rows="5" class="w-full bg-slate-50 border-2 border-slate-100 rounded-[2rem] p-8 text-base font-black text-slate-900 focus:bg-white focus:ring-12 focus:ring-rose-500/5 focus:border-rose-500 transition-all placeholder:text-slate-200 uppercase tracking-widest leading-relaxed font-mono" placeholder="TYPE_REASON_HERE..."></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-12 border-t border-slate-50 px-2 pb-6">
                        <button type="button" @click="showRejectModal = false" class="text-xs font-black uppercase tracking-[0.4em] text-slate-400 hover:text-slate-600 transition-colors">Cancel</button>
                        <button @click="confirmReject" :disabled="!rejectReason" class="h-20 px-12 bg-slate-900 text-white rounded-[1.5rem] text-sm font-black uppercase tracking-[0.2em] shadow-2xl shadow-rose-500/20 hover:bg-rose-600 transition-all flex items-center gap-6 active:scale-95 disabled:opacity-30 disabled:grayscale group/rejectbtn">
                            <TrashIcon class="w-7 h-7 text-rose-400 group-hover/rejectbtn:scale-125 transition-transform" />
                            Confirm Reject
                        </button>
                    </div>
                </div>
            </PremiumModal>
        </div>
    </MainLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}
</style>

<style scoped>
.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
</style>

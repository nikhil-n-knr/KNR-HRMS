<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import PremiumModal from '@/Components/PremiumModal.vue';
import { 
    MagnifyingGlassIcon, 
    FunnelIcon, 
    UserPlusIcon, 
    ArrowTopRightOnSquareIcon,
    CpuChipIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    assets: Object,
    categories: Array
});

// Assign Logic
const showAssignModal = ref(false);
const selectedAsset = ref(null);
const assignForm = useForm({
    user_id: ''
});

const openAssign = (asset) => {
    selectedAsset.value = asset;
    assignForm.reset();
    showAssignModal.value = true;
};

const submitAssign = () => {
    assignForm.post(route('admin.assets.assign', selectedAsset.value.id), {
        onSuccess: () => {
            showAssignModal.value = false;
            assignForm.reset();
        }
    });
};

const getStatusStyles = (status) => {
    switch (status) {
        case 'in_stock': return 'bg-emerald-50 text-emerald-600 border-emerald-100';
        case 'assigned': return 'bg-blue-50 text-blue-600 border-blue-100';
        case 'under_repair': return 'bg-amber-50 text-amber-600 border-amber-100';
        default: return 'bg-slate-50 text-slate-400 border-slate-100';
    }
};

const formatStatus = (status) => status?.replace(/_/g, ' ').toUpperCase();
</script>

<template>
    <div class="space-y-6 font-outfit">
        <!-- Intelligent Toolbar -->
        <div class="flex flex-col md:flex-row gap-4 mb-2">
            <div class="relative flex-1 group">
                <MagnifyingGlassIcon class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-500 transition-colors" />
                <input 
                    type="text" 
                    placeholder="SCAN_BY_TAG_NAME_OR_SERIAL..." 
                    class="w-full h-12 bg-white border-none rounded-2xl pl-11 pr-4 text-base font-black uppercase tracking-widest text-slate-700 focus:ring-4 focus:ring-indigo-500/10 shadow-sm transition-all"
                >
            </div>
            <div class="flex gap-3 overflow-x-auto hide-scrollbar pb-1 md:pb-0">
                <div class="relative min-w-[160px]">
                    <select class="w-full h-12 bg-white border-none rounded-2xl pl-4 pr-10 text-sm font-black uppercase tracking-widest text-slate-600 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer shadow-sm">
                        <option value="">ALL_CLASSES</option>
                        <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name.toUpperCase() }}</option>
                    </select>
                    <FunnelIcon class="w-3 h-3 absolute right-4 top-1/2 -translate-y-1/2 text-slate-400" />
                </div>
                <div class="relative min-w-[160px]">
                    <select class="w-full h-12 bg-white border-none rounded-2xl pl-4 pr-10 text-sm font-black uppercase tracking-widest text-slate-600 focus:ring-4 focus:ring-indigo-500/10 appearance-none cursor-pointer shadow-sm">
                        <option value="">ALL_STATUS</option>
                        <option value="in_stock">IN_STORAGE</option>
                        <option value="assigned">ACTIVE_SERVICE</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Registry Terminal -->
        <div class="bg-white rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/40 overflow-hidden relative min-h-[400px]">
            <!-- Desktop View -->
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-900 border-b border-slate-800">
                            <th class="px-6 py-5 text-left w-40">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Asset Index</span>
                            </th>
                            <th class="px-6 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Operative Hardware</span>
                            </th>
                            <th class="px-6 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Class</span>
                            </th>
                            <th class="px-6 py-5 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Deployment Node</span>
                            </th>
                            <th class="px-6 py-5 text-center w-32">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Pulse</span>
                            </th>
                            <th class="px-6 py-5 text-right w-40">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Control</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="asset in assets.data" :key="asset.id" class="group hover:bg-slate-50 transition-all duration-300">
                            <td class="px-6 py-6">
                                <span class="px-3 py-1.5 bg-slate-100 rounded-xl text-sm font-black text-indigo-600 font-mono tracking-tighter shadow-inner border border-slate-200 group-hover:bg-white transition-colors">
                                    {{ asset.asset_code }}
                                </span>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-2xl bg-slate-900 flex items-center justify-center text-white shadow-lg shadow-slate-200 group-hover:bg-indigo-600 transition-all border-2 border-white shrink-0 overflow-hidden">
                                        <CpuChipIcon class="h-6 w-6 opacity-30 group-hover:opacity-100 transition-opacity" />
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-base font-black text-slate-900 uppercase tracking-tight truncate leading-none group-hover:text-indigo-600 transition-colors">{{ asset.name }}</div>
                                        <div class="text-sm font-black text-slate-400 uppercase tracking-[0.2em] mt-2 leading-none">SN/ {{ asset.serial_number || 'UNKNOWN_NODE' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6 font-black text-sm text-slate-500 uppercase tracking-widest">
                                {{ asset.category?.name }}
                            </td>
                            <td class="px-6 py-6">
                                <div v-if="asset.assigned_to" class="flex items-center gap-3">
                                     <div class="h-8 w-8 rounded-lg bg-indigo-50 border border-indigo-100 flex items-center justify-center text-sm font-black text-indigo-600 uppercase shadow-sm">
                                        {{ asset.assigned_to.name.charAt(0) }}
                                     </div>
                                     <div class="min-w-0">
                                        <span class="block text-sm font-black text-slate-800 uppercase tracking-tight truncate">{{ asset.assigned_to.name }}</span>
                                        <span class="block text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Operative_ID: #{{ asset.assigned_to.id }}</span>
                                     </div>
                                </div>
                                <div v-else class="flex items-center gap-2 text-slate-300">
                                    <div class="w-2 h-2 rounded-full bg-slate-200 animate-pulse"></div>
                                    <span class="text-sm font-black uppercase tracking-widest italic opacity-60">Node_Isolated</span>
                                </div>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <span class="px-3 py-1 text-xs font-black rounded-full border uppercase tracking-widest transition-all shadow-sm"
                                      :class="getStatusStyles(asset.status)">
                                  {{ formatStatus(asset.status) }}
                                </span>
                            </td>
                            <td class="px-6 py-6 text-right">
                                <div class="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-all translate-x-2 group-hover:translate-x-0">
                                    <button v-if="asset.status === 'in_stock'" @click="openAssign(asset)" class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm active:scale-95" title="Authorize Deployment">
                                        <UserPlusIcon class="h-4 w-4" />
                                    </button>
                                    <Link :href="route('admin.assets.show', asset.id)" class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center hover:bg-indigo-600 transition-all shadow-md active:scale-95" title="Analyze Node">
                                        <ArrowTopRightOnSquareIcon class="h-4 w-4" />
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!assets.data || assets.data.length === 0">
                            <td colspan="6" class="px-6 py-32 text-center">
                                <div class="flex flex-col items-center gap-4 opacity-20 grayscale">
                                    <CpuChipIcon class="h-16 w-16 animate-pulse" />
                                    <span class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">No Hardware Records Detected</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card View -->
            <div class="lg:hidden divide-y divide-slate-50 bg-slate-50/50">
                <div v-if="!assets.data || assets.data.length === 0" class="p-20 text-center animate-in fade-in zoom-in duration-700">
                    <CpuChipIcon class="w-12 h-12 mx-auto text-slate-200 mb-4" />
                    <p class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">Hardware Cloud Depleted</p>
                </div>
                <div v-for="asset in assets.data" :key="'mb-'+asset.id" class="p-6 space-y-6 group relative overflow-hidden bg-white hover:bg-slate-50 transition-all active:scale-[0.98]">
                    <!-- MRT Header -->
                    <div class="flex items-start justify-between relative z-10">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="w-14 h-14 rounded-2xl bg-slate-900 border-2 border-white flex flex-col items-center justify-center text-white shadow-xl shrink-0 group-hover:bg-indigo-600 transition-all font-black">
                                <CpuChipIcon class="h-7 w-7 opacity-30" />
                            </div>
                            <div class="min-w-0">
                                <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight leading-none truncate mb-2">{{ asset.name }}</h4>
                                <div class="flex flex-wrap gap-2">
                                    <span class="text-xs font-black text-indigo-600 uppercase tracking-widest bg-indigo-50 px-2 py-0.5 rounded border border-indigo-100 shadow-sm">{{ asset.asset_code }}</span>
                                    <span class="text-xs font-black text-slate-400 uppercase tracking-widest opacity-60">SN: {{ asset.serial_number || 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <span class="px-3 py-1 text-xs font-black rounded-full border uppercase tracking-widest shadow-sm shrink-0" :class="getStatusStyles(asset.status)">
                            {{ formatStatus(asset.status) }}
                        </span>
                    </div>

                    <!-- Deployment Node -->
                    <div class="bg-slate-50/80 p-5 rounded-2xl border border-slate-100 group-hover:bg-white transition-all relative z-10 shadow-inner">
                        <div class="flex justify-between items-center">
                            <div class="space-y-3">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] block">Assigned Operative</span>
                                <div v-if="asset.assigned_to" class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center text-sm font-black text-white shadow-md border border-white uppercase">{{ asset.assigned_to.name.charAt(0) }}</div>
                                    <span class="text-sm font-black text-slate-700 uppercase tracking-tight">{{ asset.assigned_to.name }}</span>
                                </div>
                                <span v-else class="text-sm font-black text-slate-300 uppercase tracking-widest italic opacity-60">Node_Isolated</span>
                            </div>
                            <div class="text-right space-y-3 pl-4 border-l border-slate-200">
                                <span class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] block text-right">Node_Class</span>
                                <span class="block text-sm font-black text-slate-700 uppercase tracking-widest">{{ asset.category?.name }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Terminal -->
                    <div class="flex gap-3 relative z-10 pt-2">
                        <button v-if="asset.status === 'in_stock'" @click="openAssign(asset)" class="flex-1 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center gap-3 text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-emerald-200 active:scale-95 transition-all">
                            <UserPlusIcon class="w-4 h-4" />
                            DEPLOY_NODE
                        </button>
                        <Link :href="route('admin.assets.show', asset.id)" class="bg-slate-900 text-white rounded-2xl h-12 flex items-center justify-center gap-3 px-6 text-sm font-black uppercase tracking-[0.2em] shadow-xl shadow-slate-200 active:scale-95 flex-1 transition-all">
                            <ArrowTopRightOnSquareIcon class="w-4 h-4" />
                            NODE_INTEL
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Generic Pagination Placeholder (matching Attendance pattern) -->
            <div class="px-8 py-6 bg-slate-50/80 flex flex-col md:flex-row justify-between items-center gap-6 border-t border-slate-100" v-if="assets.data && assets.data.length > 0">
                 <div class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">
                    SYNC_PULSE Registry {{ assets.current_page }} / {{ assets.last_page }}
                 </div>
                 <div class="flex gap-2">
                       <template v-for="(link, k) in assets.links" :key="k">
                            <Link 
                                v-if="link.url"
                                :href="link.url" 
                                class="h-10 px-4 text-sm font-black uppercase tracking-widest rounded-xl transition-all shadow-sm flex items-center justify-center active:scale-95" 
                                :class="link.active ? 'bg-slate-900 text-white' : 'bg-white text-slate-400 border border-slate-200 hover:text-indigo-600 shadow-sm'"
                                v-html="link.label"
                            />
                       </template>
                 </div>
            </div>
        </div>

        <!-- Deploy Node Modal -->
        <PremiumModal 
            :show="showAssignModal" 
            @close="showAssignModal = false" 
            title="Deploy Hardware Node" 
            :subtitle="'Assigning: ' + selectedAsset?.name"
            icon="fa-user-astronaut"
            maxWidth="xl"
        >
            <form @submit.prevent="submitAssign" class="space-y-8 pt-4">
                <div class="p-6 bg-indigo-50/50 rounded-3xl border-2 border-indigo-100/50 flex items-center gap-6 group transition-all">
                    <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center text-indigo-600 shadow-xl border border-indigo-100 group-hover:rotate-12 transition-transform">
                        <CpuChipIcon class="h-7 w-7" />
                    </div>
                    <div>
                        <div class="text-base font-black text-slate-900 uppercase tracking-tight leading-none">{{ selectedAsset?.name }}</div>
                        <div class="text-sm font-black text-indigo-600 uppercase tracking-[0.2em] mt-2.5 leading-none">TAG: {{ selectedAsset?.asset_code }}</div>
                    </div>
                </div>

                <div class="space-y-3">
                    <label class="text-base font-black text-slate-500 uppercase tracking-[0.2em] px-1">Target Operative Identifier</label>
                    <div class="relative group">
                        <MagnifyingGlassIcon class="w-4 h-4 absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors pointer-events-none" />
                        <input 
                            v-model="assignForm.user_id" 
                            type="text" 
                            placeholder="SCAN_OPERATIVE_ID_OR_NAME..." 
                            class="w-full h-14 bg-slate-50 border-2 border-slate-100 rounded-2xl pl-11 pr-4 text-base font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all uppercase tracking-widest shadow-sm"
                            required
                        >
                    </div>
                    <p class="text-sm font-black text-slate-400 uppercase tracking-widest px-1 ml-0.5 opacity-60">Authorize deployment to specific operation node</p>
                </div>

                <div class="flex items-center justify-between pt-6 border-t border-slate-100">
                    <button @click="showAssignModal = false" type="button" class="text-sm font-black uppercase tracking-[0.2em] text-slate-400 hover:text-slate-600 transition-colors">Abort Deployment</button>
                    <button type="submit" :disabled="assignForm.processing" class="h-14 px-12 bg-slate-900 text-white rounded-2xl text-sm font-black uppercase tracking-[0.2em] hover:bg-indigo-600 transition-all flex items-center gap-3 shadow-xl shadow-slate-200 active:scale-95 group">
                         <div v-if="assignForm.processing" class="w-4 h-4 border-2 border-indigo-400 border-t-transparent rounded-full animate-spin"></div>
                         <UserPlusIcon v-else class="h-4 w-4" />
                         <span>{{ assignForm.processing ? 'Syncing...' : 'Confirm Authorization' }}</span>
                    </button>
                </div>
            </form>
        </PremiumModal>
    </div>
</template>

<style scoped>
.hide-scrollbar::-webkit-scrollbar {
    display: none;
}
.hide-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
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

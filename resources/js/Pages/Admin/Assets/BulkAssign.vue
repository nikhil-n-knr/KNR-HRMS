<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { ref, computed } from 'vue';
import { 
    UsersIcon, 
    ServerStackIcon, 
    MagnifyingGlassIcon,
    ArrowRightIcon,
    CheckCircleIcon,
    ArrowLeftIcon,
    InformationCircleIcon,
    TagIcon,
    UserCircleIcon,
    ArrowPathIcon,
    CursorArrowRaysIcon,
    InboxStackIcon,
    ExclamationTriangleIcon,
    SparklesIcon,
    BoltIcon,
    ArchiveBoxIcon,
    CubeIcon,
    CheckBadgeIcon,
    UserPlusIcon,
    UserIcon,
    CpuChipIcon,
    ShieldCheckIcon,
    FingerPrintIcon
} from '@heroicons/vue/24/solid';

defineOptions({ layout: MainLayout });

const props = defineProps({
    assets: Array,
    users: Array
});

const form = useForm({
    asset_ids: [],
    user_id: ''
});

const search = ref('');

const filteredAssets = computed(() => {
    if (!search.value) return props.assets;
    const q = search.value.toLowerCase();
    return props.assets.filter(a => 
        a.name.toLowerCase().includes(q) || 
        (a.serial_number && a.serial_number.toLowerCase().includes(q))
    );
});

const toggleSelectAll = () => {
    if (form.asset_ids.length === filteredAssets.value.length && filteredAssets.value.length > 0) {
        form.asset_ids = [];
    } else {
        form.asset_ids = filteredAssets.value.map(a => a.id);
    }
};

const submit = () => {
    if (!form.user_id || form.asset_ids.length === 0) return;
    form.post(route('admin.assets.bulk-assign.process'));
};

const selectedUser = computed(() => {
    return props.users.find(u => u.id == form.user_id);
});
</script>

<template>
    <Head title="Box Handover Terminal" />
    
    <div class="h-screen flex flex-col bg-slate-50 font-outfit overflow-hidden -m-8 p-12 relative animate-in fade-in duration-1000 text-left">
        <!-- AI Grid Background -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,#80808008_1px,transparent_1px),linear-gradient(to_bottom,#80808008_1px,transparent_1px)] bg-[size:40px_40px] pointer-events-none"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-500/5 via-transparent to-transparent pointer-events-none"></div>

        <!-- Tactical Command Header -->
        <header class="flex items-center justify-between mb-10 bg-white rounded-3xl p-8 shadow-sm relative z-20 group border border-slate-200">
            <div class="flex items-center gap-8">
                <Link :href="route('admin.assets.dashboard', { view: 'list' })" 
                    class="w-12 h-12 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 hover:border-indigo-100 transition-all active:scale-90 shadow-sm shrink-0">
                    <ArrowLeftIcon class="w-6 h-6" />
                </Link>
                <div class="text-left">
                    <div class="flex items-center gap-4">
                        <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tight leading-none">Handover Terminal</h1>
                        <div class="px-4 py-1.5 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center gap-2.5">
                            <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></div>
                            <span class="text-[8px] font-bold text-emerald-600 uppercase tracking-widest">CHANNEL_SECURE</span>
                        </div>
                    </div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2.5 leading-none">MASS_PROVISIONING_SYSTEM_v4.0</p>
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <div class="group relative">
                    <div class="px-8 py-4 bg-slate-50 border border-slate-200 rounded-2xl text-center relative z-10 transition-all group-hover:border-indigo-200">
                        <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest mb-1">UNITS_STAGED</p>
                        <p class="text-2xl font-black text-slate-900 leading-none tabular-nums">{{ form.asset_ids.length }}</p>
                    </div>
                </div>
            </div>
        </header>

        <div class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-8 min-h-0 relative z-10 overflow-hidden">
            
            <!-- Resource Matrix (Selection) -->
            <div class="lg:col-span-8 bg-white rounded-3xl border border-slate-200 p-8 flex flex-col min-h-0 overflow-hidden shadow-sm relative group">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,_var(--tw-gradient-stops))] from-indigo-50/10 via-transparent to-transparent pointer-events-none"></div>
                
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-6 relative z-10 text-left">
                    <div class="relative flex-1 w-full group/search">
                        <MagnifyingGlassIcon class="absolute left-6 top-1/2 -translate-y-1/2 w-6 h-6 text-slate-400 group-focus-within/search:text-indigo-600 transition-all duration-500" />
                        <input 
                            v-model="search" 
                            type="text" 
                            placeholder="SEARCH_MATRIX_RESOURCES..." 
                            class="w-full h-16 bg-slate-50 border border-slate-100 rounded-2xl pl-16 pr-8 text-lg font-black text-slate-900 uppercase tracking-tight focus:bg-white focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-400 shadow-sm transition-all placeholder:text-slate-200"
                        >
                    </div>
                    <button @click="toggleSelectAll" 
                        class="h-16 px-8 bg-slate-900 text-white rounded-2xl text-[9px] font-bold uppercase tracking-widest hover:bg-emerald-600 transition-all active:scale-95 shadow-lg shrink-0 group/all border border-slate-800">
                        <div class="flex items-center gap-3">
                            <SparklesIcon class="w-4 h-4 text-indigo-400 group-hover/all:rotate-90 transition-transform duration-700" />
                            {{ form.asset_ids.length === filteredAssets.length ? 'PURGE_STAGE' : 'STAGE_ALL_NODES' }}
                        </div>
                    </button>
                </div>

                <!-- Matrix Grid -->
                <div class="flex-1 overflow-y-auto pr-2 no-scrollbar relative z-10 text-left">
                    <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-5 pb-8">
                        <div 
                            v-for="asset in filteredAssets" 
                            :key="asset.id" 
                            @click="form.asset_ids.includes(asset.id) ? form.asset_ids.splice(form.asset_ids.indexOf(asset.id), 1) : form.asset_ids.push(asset.id)"
                            class="group relative bg-white border border-slate-100 rounded-3xl p-6 cursor-pointer transition-all duration-500 shadow-sm hover:shadow-xl hover:-translate-y-1 overflow-hidden text-left"
                            :class="form.asset_ids.includes(asset.id) ? 'border-indigo-600 ring-4 ring-indigo-500/5 bg-indigo-50/10' : 'hover:border-indigo-200'"
                        >
                            <div v-if="form.asset_ids.includes(asset.id)" class="absolute top-0 right-0 w-16 h-16 bg-indigo-600 rounded-bl-3xl flex items-start justify-end p-3 text-white animate-in slide-in-from-top-right-full duration-500 shadow-lg">
                                <CheckBadgeIcon class="w-6 h-6" />
                            </div>

                            <div class="flex flex-col gap-6 relative z-10">
                                <div class="w-12 h-12 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center transition-all duration-700 shadow-sm group-hover:bg-slate-900 group-hover:text-indigo-400 shrink-0"
                                    :class="form.asset_ids.includes(asset.id) ? 'bg-slate-900 text-indigo-400' : 'text-slate-300'">
                                    <CpuChipIcon class="w-6 h-6" />
                                </div>
                                <div class="text-left">
                                    <h5 class="text-lg font-black text-slate-900 uppercase tracking-tight leading-tight group-hover:text-indigo-600 transition-colors">{{ asset.name }}</h5>
                                    <div class="flex items-center gap-2 mt-2">
                                         <FingerPrintIcon class="w-3 h-3 text-slate-400" />
                                         <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest font-mono">{{ asset.serial_number || 'BULK_STOCK' }}</p>
                                    </div>
                                    <div class="mt-4 flex items-center gap-2">
                                        <div class="px-3 py-1 bg-slate-100 text-[8px] font-bold text-slate-500 uppercase tracking-widest rounded-lg transition-all group-hover:bg-indigo-600 group-hover:text-white">
                                            {{ asset.category?.name || 'NODE' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recipient Command Panel -->
            <div class="lg:col-span-4 bg-white rounded-3xl p-10 shadow-sm flex flex-col relative overflow-hidden group/panel border border-slate-200">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-50/30 via-transparent to-transparent pointer-events-none"></div>
                
                <h3 class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-10 flex items-center gap-4 border-b border-slate-100 pb-6">
                    <UserPlusIcon class="w-6 h-6 text-indigo-500" />
                    TARGET_RECIPIENT_NODE
                </h3>

                <div class="space-y-10 flex-1 relative z-10 text-left">
                    <div class="space-y-4 group/select">
                        <label class="px-6 text-[9px] font-bold text-slate-400 uppercase tracking-widest leading-none group-focus-within/select:text-indigo-500 transition-colors text-left block">Select Operational Lead</label>
                        <div class="relative">
                            <UserIcon class="absolute left-6 top-1/2 -translate-y-1/2 w-6 h-6 text-slate-300 group-focus-within/select:text-indigo-500 transition-colors" />
                            <select v-model="form.user_id" class="w-full h-16 bg-slate-50 border border-slate-200 rounded-2xl pl-16 pr-8 text-lg font-black text-slate-900 uppercase tracking-tight focus:bg-white focus:ring-8 focus:ring-indigo-500/5 focus:border-indigo-400 transition-all shadow-sm appearance-none cursor-pointer">
                                <option value="">SELECT_IDENTITY...</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name.toUpperCase() }}</option>
                            </select>
                        </div>
                    </div>

                    <Transition name="fade-up-complex">
                        <div v-if="selectedUser" class="p-8 bg-slate-50 border border-slate-200 rounded-3xl flex flex-col items-center text-center animate-in zoom-in-95 duration-700 group/user shadow-sm scale-[1.01] hover:bg-white transition-all">
                             <div class="w-24 h-24 bg-white border border-slate-100 rounded-2xl flex items-center justify-center text-3xl font-black text-indigo-600 shadow-md mb-8 group-hover/user:scale-110 transition-transform duration-700 overflow-hidden">
                                 <img v-if="selectedUser.profile_photo_url" :src="selectedUser.profile_photo_url" class="w-full h-full object-cover">
                                 <span v-else>{{ selectedUser.name.charAt(0) }}</span>
                             </div>
                             <h4 class="text-2xl font-black text-slate-900 uppercase tracking-tight leading-none group-hover/user:text-indigo-600 transition-colors">{{ selectedUser.name }}</h4>
                             <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-4 bg-white px-4 py-1.5 rounded-full border border-slate-100 shadow-sm">VERIFIED_RECIPIENT_LIAISON</p>
                        </div>
                        <div v-else class="h-64 flex flex-col items-center justify-center text-center opacity-30">
                             <div class="w-20 h-20 border-2 border-slate-200 border-dashed rounded-2xl flex items-center justify-center mb-6">
                                <UserCircleIcon class="w-10 h-10 text-slate-300 animate-pulse" />
                             </div>
                             <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest px-8 leading-loose">Establish a recipient node to initialize the handover protocol.</p>
                        </div>
                    </Transition>
                </div>

                <div class="pt-10 relative z-10">
                    <button 
                        @click="submit" 
                        :disabled="!form.user_id || form.asset_ids.length === 0 || form.processing"
                        class="w-full h-20 bg-slate-900 text-white rounded-2xl text-[10px] font-bold uppercase tracking-widest shadow-lg hover:bg-indigo-600 transition-all flex items-center justify-center gap-6 active:scale-95 disabled:opacity-20 disabled:grayscale group/submit border border-slate-800"
                    >
                        <ArrowPathIcon v-if="form.processing" class="w-6 h-6 animate-spin" />
                        <ShieldCheckIcon v-else class="w-6 h-6 text-indigo-400 group-hover/submit:scale-125 transition-all" />
                        <span>{{ form.processing ? 'SYNCING_PROTOCOL...' : 'COMMIT_HANDOVER' }}</span>
                    </button>
                    <p v-if="form.asset_ids.length > 0" class="text-center text-[8px] font-bold text-slate-400 uppercase tracking-widest mt-4">PROVISIONING {{ form.asset_ids.length }} STAGED NODES</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 0px;
}
.no-scrollbar::-webkit-scrollbar { display: none; }

.fade-up-complex-enter-active {
    transition: all 0.7s cubic-bezier(0.16, 1, 0.3, 1);
}
.fade-up-complex-enter-from {
    opacity: 0;
    transform: translateY(30px) scale(0.9);
}

.shadow-3xl {
    box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.2);
}
</style>

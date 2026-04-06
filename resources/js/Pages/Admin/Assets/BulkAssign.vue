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
    ArrowLeftIcon
} from '@heroicons/vue/24/outline';

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
    if (!form.user_id) return;
    form.post(route('admin.assets.bulk-assign.process'));
};
</script>

<template>
    <Head title="Bulk Fleet Assignment" />
    <MainLayout>
        <div class="max-w-[1600px] mx-auto space-y-10 pb-20 font-outfit animate-in fade-in slide-in-from-bottom-5 duration-700">
            <!-- Strategic Header Terminal -->
            <div class="bg-slate-900 rounded-[3rem] p-10 md:p-14 border border-slate-800 shadow-2xl shadow-indigo-500/20 relative overflow-hidden group">
                <div class="absolute -right-32 -top-32 w-96 h-96 bg-indigo-500/10 rounded-full blur-[100px] group-hover:bg-indigo-500/20 transition-all duration-1000"></div>
                
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
                    <div class="flex items-center gap-8">
                        <Link :href="route('admin.assets.dashboard', { view: 'list' })" class="w-14 h-14 bg-white/5 border border-white/10 rounded-2xl flex items-center justify-center text-slate-400 hover:text-white hover:bg-white/10 transition-all shadow-sm active:scale-95 shrink-0">
                            <ArrowLeftIcon class="w-6 h-6" />
                        </Link>
                        <div>
                            <div class="flex items-center gap-4 mb-3">
                                <div class="w-10 h-10 bg-indigo-500/20 rounded-xl flex items-center justify-center text-indigo-400 shadow-inner">
                                    <UsersIcon class="w-5 h-5" />
                                </div>
                                <h1 class="text-3xl font-black text-white uppercase tracking-tight">Bulk Fleet Assignment</h1>
                            </div>
                            <p class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] ml-14">Mass deployment protocol to operative accounts</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Operating Matrix -->
            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-12 gap-8 h-[750px] relative">
                
                <!-- Fleet Selection Panel -->
                <div class="lg:col-span-8 bg-white rounded-[3rem] border border-slate-100 shadow-2xl shadow-slate-200/40 flex flex-col overflow-hidden group">
                    <div class="p-8 border-b border-slate-100 bg-slate-50 flex flex-col sm:flex-row justify-between items-center gap-6">
                        <h3 class="text-base font-black text-slate-900 uppercase tracking-[0.3em] flex items-center gap-3">
                            <ServerStackIcon class="w-5 h-5 text-indigo-500" /> Waitlist Fleet
                            <span class="px-2 py-1 bg-white border border-slate-200 rounded-lg text-slate-500 shadow-sm ml-2">{{ form.asset_ids.length }} Selected</span>
                        </h3>
                        <div class="relative w-full sm:w-72 group/search">
                            <MagnifyingGlassIcon class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 group-focus-within/search:text-indigo-500 transition-colors" />
                            <input v-model="search" type="text" placeholder="Scan nodes..." class="w-full h-12 bg-white border-2 border-slate-200 rounded-2xl pl-4 pr-10 text-sm font-black text-slate-900 uppercase tracking-widest focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all shadow-sm placeholder:text-slate-400">
                        </div>
                    </div>

                    <div class="p-4 border-b border-slate-100 bg-slate-100/50 flex items-center gap-4 px-8 cursor-pointer hover:bg-slate-100 transition-colors group/chk" @click="toggleSelectAll">
                        <div class="relative flex items-center justify-center">
                            <input type="checkbox" :checked="form.asset_ids.length > 0 && form.asset_ids.length === filteredAssets.length" class="w-5 h-5 rounded border-2 border-slate-300 text-indigo-600 focus:ring-indigo-500/30 transition-all cursor-pointer">
                        </div>
                        <span class="text-sm font-black text-slate-500 uppercase tracking-[0.3em] group-hover/chk:text-indigo-600 transition-colors">Select Array Scope ({{ filteredAssets.length }})</span>
                    </div>

                    <div class="flex-1 overflow-y-auto custom-scrollbar p-6 space-y-3 bg-slate-50/30">
                        <label v-for="asset in filteredAssets" :key="asset.id" 
                            class="flex items-center gap-6 p-5 rounded-2xl border-2 transition-all cursor-pointer shadow-sm hover:shadow-md group/item"
                            :class="form.asset_ids.includes(asset.id) ? 'bg-indigo-50/50 border-indigo-200' : 'bg-white border-slate-100 hover:border-slate-200'"
                        >
                            <input type="checkbox" v-model="form.asset_ids" :value="asset.id" class="w-5 h-5 rounded border-2 border-slate-300 text-indigo-600 focus:ring-indigo-500/30 transition-all shrink-0 cursor-pointer">
                            <div class="flex-1 min-w-0">
                                <p class="text-base font-black uppercase tracking-tight truncate group-hover/item:text-indigo-700 transition-colors" :class="form.asset_ids.includes(asset.id) ? 'text-indigo-900' : 'text-slate-900'">{{ asset.name }}</p>
                                <p class="text-sm font-black font-mono tracking-widest mt-1 opacity-70" :class="form.asset_ids.includes(asset.id) ? 'text-indigo-600' : 'text-slate-400'">S/N: {{ asset.serial_number || 'UNKNOWN_LOG' }}</p>
                            </div>
                            <div class="shrink-0 text-right">
                                <span class="px-3 py-1.5 bg-emerald-50 text-emerald-600 text-xs font-black uppercase tracking-[0.2em] rounded border border-emerald-100 shadow-sm inline-block">Available</span>
                                <p class="text-sm font-black text-slate-400 uppercase tracking-widest mt-2">{{ asset.category?.name || 'GENERIC' }}</p>
                            </div>
                        </label>

                        <div v-if="filteredAssets.length === 0" class="h-full flex flex-col items-center justify-center text-center opacity-40 grayscale space-y-4">
                            <ServerStackIcon class="w-16 h-16 text-slate-400 animate-pulse" />
                            <span class="text-sm font-black uppercase tracking-[0.4em]">Zero tracking entities match scope</span>
                        </div>
                    </div>
                </div>

                <!-- Assignment Console -->
                <div class="lg:col-span-4 bg-slate-900 rounded-[3rem] border border-slate-800 shadow-2xl p-10 flex flex-col relative overflow-hidden h-fit sticky top-10">
                    <div class="absolute -right-20 -top-20 w-80 h-80 bg-indigo-500/10 rounded-full blur-[80px]"></div>
                    
                    <div class="relative z-10">
                        <h3 class="text-base font-black text-white uppercase tracking-[0.3em] mb-8 flex items-center gap-3 border-b border-indigo-500/20 pb-4">
                            <ArrowRightIcon class="w-5 h-5 text-indigo-400" />
                            Target Operative
                        </h3>
                        
                        <div class="space-y-4 mb-10">
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest px-2">Select Account ID</label>
                            <select v-model="form.user_id" class="w-full h-80 bg-white/5 border border-white/10 rounded-[2rem] p-4 text-base font-black text-white uppercase tracking-widest focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all shadow-inner custom-scrollbar-dark" size="10">
                                <option v-for="user in users" :key="user.id" :value="user.id" class="bg-slate-900 border-b border-slate-800 py-3">{{ user.name }}</option>
                            </select>
                            <div v-if="form.user_id" class="px-5 py-3 bg-white/10 rounded-xl border border-white/10 flex items-center gap-3 backdrop-blur-sm">
                                <CheckCircleIcon class="w-5 h-5 text-emerald-400" />
                                <span class="text-sm font-black uppercase tracking-[0.2em] text-emerald-100">Target Validated</span>
                            </div>
                        </div>

                        <button type="submit" 
                            :disabled="form.processing || form.asset_ids.length === 0 || !form.user_id"
                            class="w-full h-16 bg-indigo-600 text-white rounded-2xl text-sm font-black uppercase tracking-[0.3em] hover:bg-emerald-500 transition-all flex items-center justify-center gap-4 shadow-[0_0_30px_rgba(79,70,229,0.3)] active:scale-95 group/submit disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-indigo-600 disabled:shadow-none"
                        >
                            <div v-if="form.processing" class="w-5 h-5 border-2 border-indigo-200 border-t-transparent rounded-full animate-spin"></div>
                            <span v-else>Assign Matrix ({{ form.asset_ids.length }})</span>
                        </button>
                        
                        <p class="mt-8 text-sm font-black uppercase tracking-[0.2em] text-slate-500 text-center leading-relaxed italic">
                            Authorization will bind {{ form.asset_ids.length }} nodes to operative ID: {{ form.user_id || 'PENDING' }}.
                        </p>
                    </div>
                </div>
            </form>
        </div>
    </MainLayout>
</template>

<style scoped>
.font-mono {
    font-family: 'JetBrains Mono', monospace;
}
.custom-scrollbar::-webkit-scrollbar {
    width: 8px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 20px;
    border: 2px solid #f8fafc;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}

.custom-scrollbar-dark::-webkit-scrollbar {
    width: 8px;
}
.custom-scrollbar-dark::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar-dark::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.1);
    border-radius: 20px;
    border: 2px solid transparent;
    background-clip: padding-box;
}
.custom-scrollbar-dark::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.2);
}
</style>

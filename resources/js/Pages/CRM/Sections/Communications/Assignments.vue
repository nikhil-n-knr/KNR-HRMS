<template>
    <div class="h-[calc(100vh-160px)] flex flex-col space-y-8 animate-in fade-in duration-700">
        <!-- Stats Dashboard -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div v-for="stat in statsCards" :key="stat.label" class="bg-white p-8 rounded-[40px] shadow-sm border border-gray-100 relative overflow-hidden group hover:shadow-xl transition-all">
                <div class="relative z-10 text-left">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-2">{{ stat.label }}</p>
                    <h3 class="text-3xl font-black text-gray-900 tracking-tight">{{ stat.value }}</h3>
                    <div class="flex items-center gap-2 mt-4">
                        <span :class="['text-[10px] font-black px-2 py-1 rounded-lg border', stat.trendClass]">
                            <i :class="stat.trendIcon"></i> {{ stat.trend }}
                        </span>
                        <span class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">vs Last Week</span>
                    </div>
                </div>
                <i :class="[stat.icon, 'absolute -right-4 -bottom-4 text-8xl text-gray-50 opacity-0 group-hover:opacity-100 group-hover:scale-110 transition-all duration-700']"></i>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-8 flex-1 overflow-hidden">
            <!-- Assignment Workshop -->
            <div class="col-span-12 lg:col-span-8 flex flex-col bg-white rounded-[50px] shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-10 py-8 border-b border-gray-50 bg-gray-50/20 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="text-left">
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">Handshake Protocols</h3>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mt-1">Direct Mapping: Neural Channels to Personnel</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="relative w-64">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                            <input v-model="accountSearch" type="text" placeholder="FILTER CHANNELS..." class="w-full bg-white border-none rounded-2xl pl-12 pr-6 py-3.5 text-xs font-black uppercase tracking-widest shadow-inner focus:ring-4 focus:ring-indigo-500/10 transition-all">
                        </div>
                        <button @click="syncAll" class="w-12 h-12 bg-indigo-600 text-white rounded-2xl flex items-center justify-center hover:bg-gray-900 transition-all shadow-xl shadow-indigo-100 active:scale-95">
                            <i class="fas fa-rotate text-sm"></i>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-hidden flex">
                    <!-- Source: Email Accounts -->
                    <div class="w-full overflow-y-auto p-10 space-y-4">
                        <div v-for="acc in filteredAccounts" :key="acc.id" 
                             class="p-6 rounded-[32px] border-2 transition-all group flex flex-col md:flex-row md:items-center justify-between gap-6"
                             :class="acc.user_id ? 'bg-white border-gray-50' : 'bg-amber-50/30 border-dashed border-amber-200 shadow-sm'">
                            
                            <div class="flex items-center gap-6">
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg transition-all group-hover:rotate-6"
                                     :class="getProviderColor(acc.provider)">
                                    <i :class="[getProviderIcon(acc.provider), 'text-xl']"></i>
                                </div>
                                <div class="text-left">
                                    <div class="flex items-center gap-3">
                                        <h4 class="text-base font-black text-gray-900">{{ acc.email }}</h4>
                                        <span v-if="!acc.user_id" class="px-2 py-0.5 bg-amber-500 text-white text-[8px] font-black uppercase tracking-[0.2em] rounded-md animate-pulse">Unassigned</span>
                                    </div>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ acc.provider }} ENGINE</span>
                                        <span class="w-1 h-1 rounded-full bg-gray-200"></span>
                                        <span class="text-[10px] font-bold text-gray-300">Last Pulse: {{ acc.last_synced_at || 'Never' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="flex flex-col items-end">
                                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-3">Protocol Handlers (Shared Access)</p>
                                    <div class="flex flex-wrap justify-end gap-2 max-w-[400px]">
                                        <button v-for="u in users" :key="u.id" 
                                                @click="toggleUserAssignment(acc, u.id)"
                                                class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest border-2 transition-all flex items-center gap-2"
                                                :class="acc.user_ids.includes(u.id) 
                                                    ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-100' 
                                                    : 'bg-white border-gray-100 text-gray-400 hover:border-indigo-200 hover:text-indigo-600'">
                                            <i class="fas" :class="acc.user_ids.includes(u.id) ? 'fa-check' : 'fa-plus'"></i>
                                            {{ u.name.split(' ')[0] }}
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col gap-2 mt-auto pb-1">
                                     <button @click="unlinkAccount(acc.id)" class="w-11 h-11 bg-rose-50 text-rose-500 rounded-xl flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all shadow-sm active:scale-95">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="filteredAccounts.length === 0" class="flex flex-col items-center justify-center py-20 text-center opacity-40">
                             <i class="fas fa-satellite-dish text-6xl mb-6 animate-pulse"></i>
                             <p class="text-sm font-black uppercase tracking-[0.3em] text-gray-400">Scanning for active neural frequencies...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Identity Watchlist -->
            <div class="col-span-12 lg:col-span-4 flex flex-col space-y-8">
                <div class="bg-gray-900 rounded-[50px] p-10 text-white shadow-2xl relative overflow-hidden flex-1 flex flex-col">
                    <i class="fas fa-shield-halved absolute -right-10 -top-10 text-[200px] text-white/5 opacity-20 rotate-12"></i>
                    
                    <div class="relative z-10 mb-10">
                        <h3 class="text-xl font-black tracking-tight">Surveillance Hub</h3>
                        <p class="text-[10px] font-black text-indigo-400 uppercase tracking-widest mt-1 opacity-80">Personnel Mapping & Integrity Reports</p>
                    </div>

                    <div class="flex-1 overflow-y-auto space-y-4 relative z-10 custom-scrollbar pr-2">
                        <div v-for="user in usersWithAccounts" :key="user.id" class="p-6 rounded-[32px] bg-white/5 border border-white/5 hover:bg-white/10 transition-all">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-[20px] bg-indigo-600 flex items-center justify-center font-black text-lg shadow-lg">
                                        {{ user.name[0] }}
                                    </div>
                                    <div class="text-left">
                                        <h4 class="text-sm font-black leading-tight">{{ user.name }}</h4>
                                        <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mt-0.5">Assigned: {{ user.account_count }} Channels</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-if="user.linked_emails.length" class="flex flex-col gap-2 bg-black/40 p-4 rounded-2xl border border-white/5">
                                <div v-for="email in user.linked_emails" :key="email" class="flex items-center gap-2 text-[10px] font-bold text-gray-400">
                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-sm animate-pulse"></div>
                                    <span class="truncate">{{ email }}</span>
                                </div>
                            </div>
                            <p v-else class="text-[9px] font-bold text-gray-500 uppercase tracking-widest italic opacity-60">Identity has no active mappings.</p>
                        </div>
                    </div>

                    <div class="relative z-10 mt-10 pt-10 border-t border-white/5">
                        <div class="bg-indigo-500/10 p-6 rounded-[30px] border border-indigo-500/20">
                            <h5 class="text-[10px] font-black uppercase tracking-widest text-indigo-400 mb-3">System Integrity</h5>
                            <div class="flex items-center justify-between text-xs font-black uppercase tracking-widest">
                                <span>Pulse Score</span>
                                <span class="text-emerald-500">98.4%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    accounts: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    stats: { type: Object, default: () => ({}) }
});

const accountSearch = ref('');

const statsCards = computed(() => [
    { label: 'Synced Channels', value: props.accounts.length, icon: 'fas fa-link', trend: '+12%', trendClass: 'bg-emerald-50 text-emerald-600 border-emerald-100', trendIcon: 'fas fa-arrow-up' },
    { label: 'Unassigned', value: props.accounts.filter(a => a.user_ids.length === 0).length, icon: 'fas fa-user-slash', trend: '-5%', trendClass: 'bg-indigo-50 text-indigo-600 border-indigo-100', trendIcon: 'fas fa-arrow-down' },
    { label: 'Neural Throughput', value: '4.2k', icon: 'fas fa-brain', trend: '+24%', trendClass: 'bg-emerald-50 text-emerald-600 border-emerald-100', trendIcon: 'fas fa-arrow-up' },
    { label: 'Active Personnel', value: props.users.length, icon: 'fas fa-users-viewfinder', trend: 'Stable', trendClass: 'bg-gray-50 text-gray-400 border-gray-100', trendIcon: 'fas fa-minus' },
]);

const filteredAccounts = computed(() => {
    if (!accountSearch.value) return props.accounts;
    const term = accountSearch.value.toLowerCase();
    return props.accounts.filter(a => a.email.toLowerCase().includes(term) || a.provider.toLowerCase().includes(term));
});

const usersWithAccounts = computed(() => {
    return props.users.map(u => {
        const userAccounts = props.accounts.filter(a => a.user_ids.includes(u.id));
        return {
            ...u,
            account_count: userAccounts.length,
            linked_emails: userAccounts.map(a => a.email)
        };
    }).sort((a, b) => b.account_count - a.account_count);
});

const getProviderIcon = (provider) => {
    const icons = {
        google: 'fab fa-google',
        outlook: 'fab fa-windows',
        imap: 'fas fa-server'
    };
    return icons[provider.toLowerCase()] || 'fas fa-envelope';
};

const getProviderColor = (provider) => {
    const colors = {
        google: 'bg-[#DB4437] text-white',
        outlook: 'bg-[#0078D4] text-white',
        imap: 'bg-indigo-600 text-white'
    };
    return colors[provider.toLowerCase()] || 'bg-gray-900 text-white';
};

const toggleUserAssignment = (acc, userId) => {
    let newUserIds = [...acc.user_ids];
    if (newUserIds.includes(userId)) {
        newUserIds = newUserIds.filter(id => id !== userId);
    } else {
        newUserIds.push(userId);
    }

    router.post(route('crm.comms.settings.accounts.assign', acc.id), { 
        user_ids: newUserIds 
    }, { preserveScroll: true });
};

const syncAll = () => {
    router.post(route('crm.comms.hub.sync'), {}, { preserveScroll: true });
};

const unlinkAccount = (id) => {
    if (confirm('Critical Protocol Violation: This will purge all neural connection data. Proceed?')) {
        router.delete(route('crm.comms.settings.accounts.unlink', id), { preserveScroll: true });
    }
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.05); border-radius: 10px; }
</style>

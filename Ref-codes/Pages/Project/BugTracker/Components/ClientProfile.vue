<template>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Identity Card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white/80 backdrop-blur-xl p-8 rounded-[40px] border border-white/50 shadow-sm text-center relative overflow-hidden group">
                <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-br from-emerald-500 to-teal-400 opacity-10 group-hover:opacity-20 transition-all duration-500"></div>
                
                <!-- Logo / Avatar -->
                <div class="h-24 w-24 rounded-3xl bg-gradient-to-br from-emerald-500 to-teal-400 p-1 mx-auto mb-6 shadow-xl relative z-10 transition-transform duration-500 group-hover:scale-110">
                    <div class="h-full w-full rounded-[20px] bg-white flex items-center justify-center text-teal-700 font-black text-3xl">
                        {{ client.name?.[0] }}
                    </div>
                </div>

                <div class="space-y-1 relative z-10">
                    <h3 class="text-2xl font-black text-slate-800 tracking-tight">{{ client.name }}</h3>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">{{ client.code }}</p>
                </div>

                <div class="mt-8 pt-8 border-t border-slate-100 flex justify-center gap-4 relative z-10">
                    <div class="text-center">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Projects</p>
                        <h4 class="text-lg font-black text-slate-800">{{ projects?.length || 0 }}</h4>
                    </div>
                    <div class="h-8 w-px bg-slate-100"></div>
                    <div class="text-center">
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Account Age</p>
                        <h4 class="text-lg font-black text-slate-800">{{ formatAge(client.created_at) }}</h4>
                    </div>
                </div>
            </div>

            <!-- Contract Snapshot -->
            <div class="bg-white/80 backdrop-blur-xl p-6 rounded-3xl border border-white/50 shadow-sm space-y-4">
                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Active Engagement</h4>
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <i class="far fa-calendar-alt text-lg"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest font-black leading-none mb-1">Contract Started</p>
                            <p class="text-xs font-black text-slate-800">{{ formatDate(client.contract_start) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="h-10 w-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center">
                            <i class="far fa-calendar-times text-lg"></i>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest font-black leading-none mb-1">Contract Renew Date</p>
                            <p class="text-xs font-black text-slate-800">{{ formatDate(client.contract_end) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Details & Directory -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white/80 backdrop-blur-xl p-8 rounded-[40px] border border-white/50 shadow-sm space-y-8">
                 <div class="flex justify-between items-center">
                    <h3 class="text-sm font-black text-slate-800 uppercase tracking-tighter">Organisation Profile</h3>
                    <button class="px-5 py-2 bg-slate-50 text-slate-500 text-[10px] font-black uppercase tracking-widest rounded-xl hover:bg-emerald-50 hover:text-emerald-600 transition-all border border-slate-100 active:scale-95">
                        Update Request
                    </button>
                 </div>

                 <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div v-for="(val, label) in profileItems" :key="label" class="space-y-2 group">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest group-hover:text-emerald-500 transition-colors">{{ label }}</p>
                        <p class="text-xs font-black text-slate-600 border-b border-dashed border-slate-200 pb-2 group-hover:border-emerald-200 transition-colors">
                            {{ val || 'Not Configured' }}
                        </p>
                    </div>
                 </div>

                 <div class="pt-8 border-t border-slate-100">
                    <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest mb-6">Contact Directory</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div v-for="user in client.users" :key="user.id" 
                            class="p-4 rounded-3xl bg-slate-50/50 border border-slate-100 flex items-center gap-4 hover:border-emerald-200 transition-all">
                            <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center text-[10px] font-black">
                                {{ user.name?.[0] }}
                            </div>
                            <div class="flex flex-col">
                                <span class="text-xs font-black text-slate-800">{{ user.name }}</span>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter">{{ user.email }}</span>
                            </div>
                        </div>
                    </div>
                 </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps(['client', 'projects']);

const profileItems = computed(() => ({
    'Official Identity': props.client.name,
    'Authorised Contact': props.client.contact_person,
    'Registered Email': props.client.email,
    'Primary Workspace': 'Main Office - Bangalore',
    'Fiscal Representative': 'System Generated',
    'Global Security ID': props.client.code
}));

const formatDate = (date) => {
    if (!date) return 'TBD';
    return new Date(date).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
};

const formatAge = (date) => {
    if (!date) return 'New Account';
    const diff = new Date() - new Date(date);
    const months = Math.floor(diff / (1000 * 60 * 60 * 24 * 30));
    if (months < 12) return months + ' Months';
    return (months / 12).toFixed(1) + ' Years';
};
</script>

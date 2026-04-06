<script setup>
const providers = [
    { id: 'zoom', name: 'Zoom Video', icon: 'fas fa-video', status: 'connected', color: 'bg-blue-600' },
    { id: 'google', name: 'Google Meet', icon: 'fab fa-google', status: 'connected', color: 'bg-emerald-500' },
    { id: 'teams', name: 'MS Teams', icon: 'fab fa-microsoft', status: 'pending', color: 'bg-indigo-600' }
];

const syncAccounts = [
    { email: 'admin@company.com', type: 'Primary Sender', status: 'Active' },
    { email: 'notifications@crm.system', type: 'System Notification', status: 'Internal' }
];
</script>

<template>
    <div class="space-y-10 animate-in fade-in duration-500">
        <header>
            <h2 class="text-3xl font-black text-gray-900 tracking-tight">Infrastructure Hub</h2>
            <p class="text-gray-400 font-bold uppercase tracking-widest text-[9px] mt-1">Provider Synchronicity & Credentials</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div v-for="p in providers" :key="p.id" class="p-10 bg-white rounded-[40px] border border-gray-100 shadow-sm relative overflow-hidden group hover:shadow-2xl transition-all">
                <i :class="p.icon" class="absolute -bottom-6 -right-6 text-9xl text-gray-50/50 rotate-12 transition-transform group-hover:scale-110"></i>
                <div :class="p.color" class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-2xl mb-8 shadow-xl shadow-gray-100"><i :class="p.icon"></i></div>
                <h3 class="text-xl font-black text-gray-900 leading-tight mb-2">{{ p.name }}</h3>
                <div class="flex items-center gap-2">
                    <span :class="p.status === 'connected' ? 'bg-emerald-500' : 'bg-amber-500'" class="w-2 h-2 rounded-full"></span>
                    <p class="text-[9px] font-black uppercase tracking-widest text-gray-400">{{ p.status }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-50 rounded-[40px] p-10 border border-gray-100 shadow-inner">
            <h4 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-8">Authoritative Email Profiles</h4>
            <div class="space-y-4">
                <div v-for="a in syncAccounts" :key="a.email" class="bg-white p-6 rounded-3xl flex items-center justify-between border border-white shadow-sm hover:border-indigo-100 transition-all">
                    <div class="flex items-center gap-5">
                        <div class="w-12 h-12 rounded-2xl bg-gray-900 text-white flex items-center justify-center text-xl shadow-lg"><i class="fas fa-envelope"></i></div>
                        <div>
                            <p class="text-sm font-black text-gray-900 tracking-tight leading-none mb-1">{{ a.email }}</p>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest">{{ a.type }}</p>
                        </div>
                    </div>
                    <span class="px-5 py-2 bg-gray-50 rounded-xl text-[9px] font-black uppercase tracking-widest text-gray-600 border border-gray-100">{{ a.status }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

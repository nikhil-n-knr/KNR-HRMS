<template>
    <div class="flex flex-col h-full bg-white">
        <!-- Client Header (Only if client selected) -->
        <div v-if="client" class="p-6 border-b border-slate-100 bg-slate-50/30 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-white font-black text-xl shadow-lg shadow-indigo-200">
                    {{ clientInitials }}
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">{{ client.first_name }} {{ client.last_name }}</h2>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-xs font-bold text-slate-500">{{ client.email }}</span>
                        <span class="w-1 h-1 rounded-full bg-slate-300"></span>
                        <span class="px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-widest">High Value</span>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <button @click="router.get(route('crm.comms.hub'), { section: 'communications', tab: 'client360' })" class="bg-white border border-slate-200 px-4 py-2 rounded-xl hover:bg-slate-50 transition-all text-slate-600 text-xs font-bold shadow-sm">
                    <i class="fas fa-search mr-2"></i> New Search
                </button>
                <div class="w-px h-6 bg-slate-200 mx-2"></div>
                <button class="flex items-center gap-2 px-4 py-2.5 bg-slate-900 text-white rounded-xl font-bold hover:bg-black transition-all shadow-lg shadow-slate-200">
                    <i class="fas fa-exchange-alt text-indigo-400"></i>
                    <span>Transfer Owner</span>
                </button>
            </div>
        </div>

        <!-- Search Landing State (If no client selected) -->
        <div v-else class="flex-1 flex flex-col items-center justify-center p-8 bg-slate-50/20">
            <div class="max-w-md w-full space-y-8 text-center">
                <div class="w-20 h-20 bg-indigo-50 rounded-3xl flex items-center justify-center mx-auto shadow-xl shadow-indigo-100/50">
                    <i class="fas fa-user-astronaut text-indigo-600 text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-black text-slate-900 tracking-tight">Client <span class="text-indigo-600">360°</span></h2>
                    <p class="text-slate-500 font-medium mt-2">Search for a contact or lead to view their full interaction timeline, health score, and history.</p>
                </div>
                
                <div class="relative group">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-600 transition-colors"></i>
                    <input 
                        v-model="searchQuery" 
                        @keyup.enter="onSearch"
                        type="text" 
                        placeholder="Search name, email, or company..." 
                        class="w-full pl-14 pr-6 py-5 bg-white border border-slate-200 rounded-3xl shadow-xl shadow-slate-200/50 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all font-bold placeholder:text-slate-300"
                    />
                </div>

                <!-- Search Results -->
                <div v-if="searchResults && searchResults.length > 0" class="space-y-3 pt-4">
                    <div v-for="res in searchResults" :key="res.id" 
                        @click="selectContact(res.id)"
                        class="flex items-center justify-between p-4 bg-white border border-slate-100 rounded-2xl hover:border-indigo-200 hover:shadow-lg transition-all cursor-pointer text-left"
                    >
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 font-bold">
                                {{ (res.first_name[0] + res.last_name[0]).toUpperCase() }}
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-900">{{ res.first_name }} {{ res.last_name }}</h4>
                                <p class="text-xs text-slate-500 font-medium">{{ res.email }}</p>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-slate-300"></i>
                    </div>
                </div>
                <div v-else-if="searchQuery && !searchResults" class="p-8 text-slate-400 font-medium italic">
                    Press Enter to search...
                </div>
            </div>
        </div>

        <div v-if="client" class="flex-1 flex overflow-hidden">
            <!-- Timeline (Left) -->
            <div class="flex-1 overflow-y-auto p-8 bg-slate-50/30 scroll-smooth custom-scrollbar">
                <div class="max-w-3xl mx-auto space-y-10 py-10">
                    <!-- Interaction Filters -->
                    <div class="flex items-center justify-center gap-2 mb-12">
                        <button v-for="tag in filterTags" :key="tag.id" 
                            class="px-4 py-2 rounded-full text-xs font-black uppercase tracking-widest transition-all"
                            :class="activeFilter === tag.id ? 'bg-indigo-600 text-white shadow-lg' : 'bg-white text-slate-500 border border-slate-200 hover:border-indigo-200'"
                            @click="activeFilter = tag.id"
                        >
                            {{ tag.label }}
                        </button>
                    </div>

                    <!-- Timeline Item -->
                    <div v-for="(item, index) in filteredTimeline" :key="item.id" class="relative pl-12">
                        <!-- Connecting Line -->
                        <div v-if="index < filteredTimeline.length - 1" 
                            class="absolute left-5 top-10 bottom-0 w-0.5 bg-slate-200 rounded-full"
                        ></div>
                        
                        <!-- Icon Circle -->
                        <div class="absolute left-0 top-0 w-10 h-10 rounded-2xl bg-white border-2 flex items-center justify-center z-10 shadow-sm"
                            :class="item.typeColor"
                        >
                            <i :class="['fas', item.icon, 'text-sm']"></i>
                        </div>

                        <!-- Card -->
                        <div class="bg-white rounded-2xl border border-slate-100 shadow-xl shadow-slate-200/40 p-6 group hover:border-indigo-100 transition-all">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-black uppercase tracking-widest text-slate-400">{{ item.date }}</span>
                                    <span class="w-1 h-1 rounded-full bg-slate-200"></span>
                                    <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600">{{ item.type }}</span>
                                </div>
                                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:text-indigo-600 transition-colors">
                                        <i class="fas fa-reply text-[10px]"></i>
                                    </button>
                                    <button class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-50 text-slate-400 hover:text-rose-600 transition-colors">
                                        <i class="fas fa-trash text-[10px]"></i>
                                    </button>
                                </div>
                            </div>

                            <h4 class="font-bold text-slate-900 text-lg leading-snug">{{ item.title }}</h4>
                            <p class="text-slate-500 text-sm mt-2 leading-relaxed line-clamp-2" v-if="item.content">
                                {{ item.content }}
                            </p>
                            
                            <!-- Attachments / Metadata -->
                            <div v-if="item.meta" class="mt-4 pt-4 border-t border-slate-50 flex items-center gap-4">
                                <div v-for="meta in item.meta" :key="meta.id" class="flex items-center gap-1.5">
                                    <i :class="['fas', meta.icon, 'text-slate-300 text-[10px]']"></i>
                                    <span class="text-[10px] font-bold text-slate-500">{{ meta.text }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="filteredTimeline.length === 0" class="py-20 text-center opacity-50">
                        <i class="fas fa-ghost text-4xl mb-4 text-slate-200"></i>
                        <p class="text-sm font-bold uppercase tracking-widest text-slate-400">No activity found for this filter</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions (Right) -->
            <div class="w-80 border-l border-slate-100 p-8 space-y-8 bg-white z-20">
                <div class="space-y-4">
                    <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">Quick Actions</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <button @click="$emit('action', 'new-email')" class="col-span-2 flex items-center justify-center gap-3 p-4 bg-indigo-50 text-indigo-600 rounded-2xl font-bold hover:bg-indigo-600 hover:text-white transition-all">
                            <i class="fas fa-paper-plane"></i>
                            <span>Email</span>
                        </button>
                        <button @click="$emit('action', 'schedule-meeting')" class="flex flex-col items-center gap-2 p-4 bg-slate-50 text-slate-600 rounded-2xl font-bold hover:bg-slate-100 transition-all text-xs">
                            <i class="fas fa-calendar-alt text-lg"></i>
                            <span>Meeting</span>
                        </button>
                        <button class="flex flex-col items-center gap-2 p-4 bg-slate-50 text-slate-600 rounded-2xl font-bold hover:bg-slate-100 transition-all text-xs opacity-50 cursor-not-allowed cursor-pointer" title="Coming Soon">
                            <i class="fas fa-sticky-note text-lg"></i>
                            <span>Note</span>
                        </button>
                    </div>
                </div>

                <div class="pt-8 border-t border-slate-50 space-y-4">
                    <h3 class="text-sm font-black text-slate-400 uppercase tracking-widest">Account Health</h3>
                    <div class="bg-emerald-50 p-5 rounded-3xl border border-emerald-100/50">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-emerald-800">Engagement Score</span>
                            <span class="text-lg font-black text-emerald-600">92/100</span>
                        </div>
                        <div class="w-full h-2 bg-emerald-200 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 w-[92%]"></div>
                        </div>
                        <p class="text-[10px] font-bold text-emerald-700 mt-3 leading-tight uppercase tracking-tight italic">
                            Highly active: Last touched recently.
                        </p>
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
    client: Object,
    timeline: Array,
    searchResults: Array
});

const searchQuery = ref('');
const activeFilter = ref('all');

const clientInitials = computed(() => {
    if (!props.client) return '??';
    const name = props.client.name || (props.client.first_name + ' ' + props.client.last_name);
    return name.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
});

const onSearch = () => {
    router.get(route('crm.comms.hub'), { 
        section: 'communications', 
        tab: 'client360', 
        search: searchQuery.value 
    }, { preserveState: true, preserveScroll: true });
};

const selectContact = (id) => {
    router.get(route('crm.comms.hub'), { 
        section: 'communications', 
        tab: 'client360', 
        contact_id: id 
    }, { preserveState: true, preserveScroll: true });
};

const filterTags = [
    { id: 'all', label: 'All' },
    { id: 'email', label: 'Emails' },
    { id: 'meeting', label: 'Meetings' },
];

const filteredTimeline = computed(() => {
    if (!props.timeline) return [];
    if (activeFilter.value === 'all') return props.timeline;
    return props.timeline.filter(item => item.type.toLowerCase() === activeFilter.value);
});
</script>

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
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}
</style>

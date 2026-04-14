<template>
    <AppLayout v-bind="$props">
        <div class="px-6 space-y-7 pb-32 pt-4">
            <header class="flex justify-between items-end px-1">
                <div>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">Signal Matrix</h2>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.3em] mt-1 italic">Real-time Alert Streams</p>
                </div>
                <button 
                  v-if="items.length > 0"
                  @click="markAllAsRead" 
                  class="text-[9px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-3 py-1.5 rounded-lg border border-white active:scale-90 transition-transform"
                >
                  Clear Matrix
                </button>
            </header>

            <div class="space-y-4">
                <div v-if="loading" class="text-center py-10 opacity-50"><i class="fas fa-satellite-dish animate-bounce text-emerald-500"></i></div>
                
                <div v-else-if="items.length === 0" class="text-center py-24 opacity-30">
                    <i class="fas fa-bell-slash text-4xl mb-4 block"></i>
                    <p class="text-[10px] font-black uppercase tracking-widest">Signal Matrix Clear</p>
                </div>

                <div 
                  v-for="item in items" 
                  :key="item.id" 
                  class="nature-card p-5 flex gap-4 transition-all relative overflow-hidden bg-white"
                  :class="item.read_at ? 'opacity-60 grayscale-[0.5]' : 'border-emerald-50 shadow-xl shadow-emerald-900/5'"
                  @click="markAsRead(item)"
                >
                    <div v-if="!item.read_at" class="absolute left-0 top-0 w-1 h-full bg-emerald-500"></div>
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-xs shadow-sm border border-white shrink-0" :class="getIconBg(item.type)">
                        <i :class="getIcon(item.type)"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start gap-2 mb-1">
                            <h4 class="text-xs font-black text-slate-900 uppercase tracking-tight leading-tight truncate">{{ item.title || item.data?.subject || 'System Alert' }}</h4>
                            <span class="text-[8px] font-black text-slate-300 uppercase italic whitespace-nowrap">{{ item.created_at_human }}</span>
                        </div>
                        <p class="text-[10px] text-slate-500 font-bold leading-relaxed line-clamp-2 italic">{{ item.data?.message || item.content }}</p>
                    </div>
                </div>
            </div>

            <div v-if="nextPageUrl" class="text-center pb-8">
                <button @click="loadMore" class="text-[9px] font-black text-emerald-600 uppercase tracking-widest italic">Decrypt Older Data</button>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import AppLayout from '../App.vue';
import axios from 'axios';

const items = ref([]);
const loading = ref(true);
const nextPageUrl = ref(null);

const fetchNotifications = async () => {
    try {
        const response = await axios.get('/api/mobile/v1/notifications');
        items.value = response.data.data;
        nextPageUrl.value = response.data.next_page_url;
    } catch (err) {
        console.error("Failed to fetch notifications:", err);
    } finally {
        loading.value = false;
    }
};

const markAsRead = async (item) => {
    if (item.read_at) return;
    try {
        await axios.post(`/api/mobile/v1/notifications/${item.id}/read`);
        item.read_at = new Date().toISOString();
    } catch (err) {
        console.error("Error marking read:", err);
    }
};

const markAllAsRead = async () => {
    try {
        await axios.post('/api/mobile/v1/notifications/all/read');
        items.value.forEach(i => i.read_at = new Date().toISOString());
    } catch (err) {
        console.error("Error marking all read:", err);
    }
};

const getIcon = (type) => {
    const icons = {
        'task': 'fas fa-tasks',
        'leave': 'fas fa-calendar-day',
        'attendance': 'fas fa-fingerprint',
        'system': 'fas fa-cog',
        'bug': 'fas fa-bug'
    };
    return icons[type] || 'fas fa-bell';
};

const getIconBg = (type) => {
    const bgs = {
        'task': 'bg-blue-100 text-blue-600',
        'leave': 'bg-emerald-100 text-emerald-600',
        'attendance': 'bg-amber-100 text-amber-600',
        'bug': 'bg-rose-100 text-rose-600'
    };
    return bgs[type] || 'bg-slate-100 text-slate-500';
};

onMounted(fetchNotifications);
</script>

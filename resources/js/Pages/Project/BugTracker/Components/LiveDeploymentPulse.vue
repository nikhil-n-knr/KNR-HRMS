<template>
    <div class="bg-white rounded-[3rem] p-10 border border-slate-100 shadow-xl shadow-slate-200/20">
        <div class="flex justify-between items-center mb-10">
            <div>
                <h3 class="text-2xl font-black tracking-tight text-slate-900">Deployment Pulse</h3>
                <p class="text-sm font-black uppercase tracking-widest text-slate-400 mt-1">Live Release Orchestration</p>
            </div>
            <button @click="showNewRoundModal = true" class="h-10 w-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-indigo-100 hover:scale-110 transition-transform">
                <PlusIcon class="w-5 h-5" />
            </button>
        </div>

        <div v-if="loading" class="flex justify-center py-10">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-slate-900"></div>
        </div>

        <div v-else-if="!rounds.length" class="text-center py-20 opacity-30 italic text-sm">
            No active deployment rounds.
        </div>

        <div v-else class="space-y-6">
            <div v-for="round in rounds" :key="round.id" class="relative pl-12 pb-8 last:pb-0 group">
                <!-- Connector Line -->
                <div class="absolute left-5 top-10 bottom-0 w-px bg-slate-100 group-last:hidden"></div>
                
                <!-- Status Icon -->
                <div :class="[getStatusColor(round.status), 'absolute left-0 top-0 h-10 w-10 rounded-2xl flex items-center justify-center shadow-lg transition-transform group-hover:scale-110']">
                    <RocketLaunchIcon v-if="round.status === 'production'" class="w-5 h-5 text-white" />
                    <BeakerIcon v-else-if="round.status === 'staging'" class="w-5 h-5 text-white" />
                    <ClipboardIcon v-else class="w-5 h-5 text-white" />
                </div>

                <div class="bg-slate-50/50 border border-slate-100 p-6 rounded-[2rem] hover:bg-white hover:shadow-xl hover:shadow-slate-200/50 transition-all">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h4 class="text-lg font-black text-slate-900">v{{ round.version }}</h4>
                            <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">{{ formatDate(round.created_at) }}</p>
                        </div>
                        <div class="flex gap-2">
                            <button v-if="round.status === 'planning'" @click="updateStatus(round, 'staging')" class="px-4 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-black uppercase tracking-widest hover:bg-indigo-100 transition-colors">Ship to Staging</button>
                            <button v-if="round.status === 'staging'" @click="updateStatus(round, 'production')" class="px-4 py-1.5 bg-emerald-50 text-emerald-600 rounded-lg text-sm font-black uppercase tracking-widest hover:bg-emerald-100 transition-colors">Go Live</button>
                            <span :class="[getStatusBadge(round.status), 'px-3 py-1.5 rounded-lg text-sm font-black uppercase tracking-widest shadow-sm']">
                                {{ round.status }}
                            </span>
                        </div>
                    </div>
                    
                    <p class="text-sm text-slate-500 mb-4 line-clamp-2">{{ round.notes || 'No release notes provided.' }}</p>
                    
                    <div class="flex items-center gap-4">
                        <div class="flex -space-x-2">
                            <div class="h-6 w-6 rounded-full bg-slate-900 flex items-center justify-center text-xs font-black text-white ring-2 ring-white ring-inset shadow-sm">{{ round.tickets_count }}</div>
                            <span class="text-sm font-black uppercase tracking-widest text-slate-400 pl-4 self-center">Included Tickets</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { PlusIcon, RocketLaunchIcon, BeakerIcon, ClipboardIcon } from '@heroicons/vue/24/solid';
import axios from 'axios';
import dayjs from 'dayjs';

const props = defineProps({
    projectId: [Number, String]
});

const rounds = ref([]);
const loading = ref(true);
const showNewRoundModal = ref(false);

const fetchRounds = async () => {
    loading.value = true;
    try {
        const response = await axios.get(route('deployments.index'), {
            params: { project_id: props.projectId }
        });
        rounds.value = response.data;
    } catch (e) {
        console.error("Pulse signal lost", e);
    } finally {
        loading.value = false;
    }
};

const updateStatus = async (round, status) => {
    try {
        await axios.put(route('deployments.update-status', round.id), { status });
        fetchRounds();
    } catch (e) {
        console.error("Status shift failed", e);
    }
};

const getStatusColor = (status) => {
    if (status === 'production') return 'bg-emerald-500 shadow-emerald-200';
    if (status === 'staging') return 'bg-indigo-500 shadow-indigo-200';
    return 'bg-slate-400 shadow-slate-200';
};

const getStatusBadge = (status) => {
    if (status === 'production') return 'bg-emerald-50 text-emerald-600';
    if (status === 'staging') return 'bg-indigo-50 text-indigo-600';
    return 'bg-slate-100 text-slate-600';
};

const formatDate = (date) => dayjs(date).format('MMM DD, YYYY');

onMounted(fetchRounds);
</script>

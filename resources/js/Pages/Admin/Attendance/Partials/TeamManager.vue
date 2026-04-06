<template>
    <div class="space-y-6">
        <!-- Command Bar -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white/90 backdrop-blur-md p-4 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg">
                    <i class="fas fa-users-gear text-[14px]"></i>
                </div>
                <div>
                    <h2 class="text-xs font-black text-slate-800 uppercase tracking-tight">Squad Control</h2>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mt-1">Operational Team Matrix</p>
                </div>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <div class="relative flex-1 md:w-64">
                    <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                    <input v-model="searchQuery" type="text" placeholder="Filter Squads..." 
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl h-10 pl-10 pr-4 text-sm font-black text-slate-600 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest">
                </div>
                <button @click="openCreateModal" class="h-10 px-4 bg-slate-900 text-white rounded-xl text-sm font-black uppercase tracking-widest hover:bg-emerald-600 transition-all flex items-center gap-2 shadow-lg active:scale-95">
                    <i class="fas fa-plus"></i>
                    New Squad
                </button>
            </div>
        </div>

        <!-- Squad Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div v-for="team in filteredTeams" :key="team.id" 
                class="bg-white rounded-3xl border border-slate-200 p-6 hover:shadow-xl hover:shadow-slate-200/50 hover:border-emerald-500/30 transition-all group relative overflow-hidden"
            >
                <div class="absolute -right-12 -bottom-12 w-32 h-32 bg-slate-50 rounded-full group-hover:bg-emerald-50 transition-colors -z-0"></div>

                <div class="relative z-10 flex flex-col h-full">
                    <div class="flex justify-between items-start mb-6">
                        <div class="w-12 h-12 rounded-2xl bg-slate-900 flex items-center justify-center text-white text-lg font-black shadow-lg group-hover:rotate-6 transition-transform">
                            {{ team.name.charAt(0).toUpperCase() }}
                        </div>
                        <div class="flex gap-1.5 opacity-0 group-hover:opacity-100 transition-all">
                            <button @click="openEditModal(team)" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 flex items-center justify-center hover:text-emerald-600 hover:bg-emerald-50 transition-all">
                                <i class="fas fa-edit text-sm"></i>
                            </button>
                            <button @click="confirmDelete(team.id)" class="w-8 h-8 rounded-lg bg-white border border-slate-200 text-slate-400 flex items-center justify-center hover:text-rose-600 hover:bg-rose-50 transition-all">
                                <i class="fas fa-trash text-sm"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex-1">
                        <h3 class="text-sm font-black text-slate-800 uppercase tracking-tight group-hover:text-emerald-700 transition-colors">{{ team.name }}</h3>
                        <div class="mt-4 flex items-center gap-2">
                            <div class="w-1 h-1 rounded-full bg-emerald-500"></div>
                            <span class="text-sm font-black text-slate-400 uppercase tracking-widest">Lead: {{ team.manager?.name || 'Unassigned' }}</span>
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-50 flex items-center justify-between">
                        <div class="flex -space-x-2">
                            <div v-for="member in team.members?.slice(0, 4)" :key="member.id" class="w-7 h-7 rounded-full bg-slate-100 border-2 border-white flex items-center justify-center text-xs font-black text-slate-500 shadow-sm">
                                {{ member.name.charAt(0) }}
                            </div>
                            <div v-if="team.members_count > 4" class="w-7 h-7 rounded-full bg-emerald-600 border-2 border-white flex items-center justify-center text-xs font-black text-white shadow-sm">
                                +{{ team.members_count - 4 }}
                            </div>
                        </div>
                        <span class="text-sm font-black text-slate-900 bg-slate-100 px-2 py-1 rounded-lg border border-slate-200">{{ team.members_count }} Units</span>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredTeams.length === 0" class="col-span-full py-20 bg-white rounded-3xl border-2 border-dashed border-slate-200 flex flex-col items-center justify-center gap-4">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center text-slate-300">
                    <i class="fas fa-users-slash text-2xl"></i>
                </div>
                <div class="text-center">
                    <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest">No Squads Synchronized</h4>
                    <p class="text-sm text-slate-400 font-bold uppercase tracking-[0.2em] mt-1">Initialize organizational units to continue</p>
                </div>
            </div>
        </div>

        <PremiumModal 
            :show="showModal" 
            @close="showModal = false" 
            :title="isEditing ? 'Modify Squad' : 'Deploy New Squad'" 
            subtitle="Configure organizational node"
            icon="fa-users-gear"
            @confirm="submit"
        >
            <div class="space-y-6 p-1">
                <div class="space-y-2">
                    <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Squad Name</label>
                    <input v-model="form.name" type="text" class="w-full bg-slate-50 border border-slate-200 rounded-xl h-12 px-4 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest">
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Lead Matrix (Manager)</label>
                    <select v-model="form.manager_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl h-12 px-4 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest">
                        <option :value="null">Unassigned</option>
                        <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                    </select>
                </div>

                <div class="space-y-2">
                    <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Parent Command</label>
                    <select v-model="form.parent_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl h-12 px-4 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest">
                        <option :value="null">Global Root</option>
                        <option v-for="t in teams" :key="t.id" :value="t.id">{{ t.name }}</option>
                    </select>
                </div>
            </div>
        </PremiumModal>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import PremiumModal from '@/Components/PremiumModal.vue';
import { useToastStore } from '@/stores/toast';

const props = defineProps({
    teams: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    roles: Array,
});

const toast = useToastStore();
const searchQuery = ref('');
const showModal = ref(false);
const isEditing = ref(false);

const form = ref({
    id: null,
    name: '',
    manager_id: null,
    parent_id: null,
});

const filteredTeams = computed(() => {
    if (!searchQuery.value) return props.teams;
    const q = searchQuery.value.toLowerCase();
    return props.teams.filter(t => t.name.toLowerCase().includes(q));
});

const openCreateModal = () => {
    isEditing.value = false;
    form.value = { id: null, name: '', manager_id: null, parent_id: null };
    showModal.value = true;
};

const openEditModal = (team) => {
    isEditing.value = true;
    form.value = { ...team };
    showModal.value = true;
};

const submit = () => {
    if (isEditing.value) {
        router.put(route('admin.attendance.teams.update', form.value.id), form.value, {
            onSuccess: () => { showModal.value = false; toast.success("Squad updated"); },
        });
    } else {
        router.post(route('admin.attendance.teams.store'), form.value, {
            onSuccess: () => { showModal.value = false; toast.success("Squad deployed"); },
        });
    }
};

const confirmDelete = (id) => {
    if (confirm("Decommission this squad?")) {
        router.delete(route('admin.attendance.teams.destroy', id), {
            onSuccess: () => toast.success("Squad purged"),
        });
    }
};
</script>

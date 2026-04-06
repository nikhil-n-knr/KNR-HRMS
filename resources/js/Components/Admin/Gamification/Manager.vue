<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();

const props = defineProps({
    rules: Array,
    badges: Array
});

const activeTab = ref('rules');
const showBadgeModal = ref(false);
const confirmState = ref({ show: false, title: '', message: '', onConfirm: null });

// --- Rules Logic ---
const updateRule = (rule, field, value) => {
    if (!rule.id) {
        console.error("Rule ID missing", rule);
        return;
    }
    router.put(route('admin.attendance.gamification.rules.update', { pointRule: rule.id }), {
        id: rule.id,
        [field]: value
    }, {
        preserveScroll: true,
        onSuccess: () => toast.success("Economic parameter adjusted")
    });
};

// --- Badge Logic ---
const badgeForm = useForm({
    name: '',
    slug: '',
    description: '',
    icon: '🏆',
    points_bonus: 0
});

const createBadge = () => {
    badgeForm.slug = badgeForm.name.toLowerCase().replace(/ /g, '-').replace(/[^\w-]+/g, '');
    badgeForm.post(route('admin.attendance.gamification.badges.store'), {
        onSuccess: () => {
             showBadgeModal.value = false;
             badgeForm.reset();
             toast.success("New achievement node initialized");
        }
    });
};

const deleteBadge = (id) => {
    confirmState.value = {
        show: true,
        title: 'Terminate Achievement Node?',
        message: 'This will permanently remove the badge and all associated player records from the economy.',
        onConfirm: () => {
             router.delete(route('admin.attendance.gamification.badges.destroy', id), {
                 onSuccess: () => {
                     confirmState.value.show = false;
                     toast.success("Node terminated");
                 }
             });
        }
    };
};
</script>

<template>
    <div class="animate-fade-in pb-12 font-outfit">
        <!-- Studio Header -->
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 bg-white/90 backdrop-blur-md p-4 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg shadow-slate-200 transition-transform hover:scale-105">
                    <i class="fas fa-trophy text-lg text-emerald-400"></i>
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-800 tracking-tight leading-none uppercase">Gamification Studio</h2>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1.5 leading-none">Reward Calibration & Points Economy</p>
                </div>
            </div>
            
            <div class="flex items-center gap-2 bg-slate-50 p-1 rounded-xl border border-slate-200 shadow-inner">
                <button 
                    @click="activeTab = 'rules'" 
                    :class="activeTab === 'rules' ? 'bg-white text-emerald-600 shadow-sm scale-100' : 'text-slate-500 hover:text-slate-700'"
                    class="px-5 py-2 text-[9px] font-black uppercase tracking-widest rounded-lg transition-all"
                >
                    Economy Config
                </button>
                <button 
                    @click="activeTab = 'badges'" 
                    :class="activeTab === 'badges' ? 'bg-white text-emerald-600 shadow-sm scale-100' : 'text-slate-500 hover:text-slate-700'"
                    class="px-5 py-2 text-[9px] font-black uppercase tracking-widest rounded-lg transition-all"
                >
                    Badges
                </button>
            </div>
        </div>

        <!-- ECONOMY RULES TAB -->
        <div v-if="activeTab === 'rules'" class="animate-content-fade">
            <div class="grid grid-cols-1 gap-4 max-w-6xl mx-auto">
                <div v-for="(rule, index) in rules" :key="rule.id" 
                    class="group bg-white p-5 rounded-2xl border border-slate-200 shadow-sm transition-all hover:border-emerald-500/30 flex flex-col lg:flex-row items-center gap-6"
                >
                    <!-- Visual Rank Icon -->
                    <div class="flex items-center gap-6 flex-1 w-full">
                        <div class="w-12 h-12 rounded-xl bg-slate-50 flex items-center justify-center text-lg font-black text-slate-300 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-all shadow-inner border border-slate-100">
                            {{ String(index + 1).padStart(2, '0') }}
                        </div>
                        <div class="space-y-1.5 flex-1">
                            <h3 class="text-xs font-black text-slate-800 uppercase tracking-tight group-hover:text-emerald-700 transition-colors">{{ rule.name }}</h3>
                            <div class="flex items-center gap-2">
                                <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest bg-slate-100/50 px-2 py-0.5 rounded border border-slate-100">
                                    TRIGGER: {{ rule.event_key }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Rule Parameters -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full lg:w-auto">
                        <!-- Active Status -->
                        <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100 flex flex-col items-center justify-center min-w-[120px]">
                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-2">Service Status</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    :checked="rule.is_active" 
                                    @change="updateRule(rule, 'is_active', $event.target.checked)"
                                    class="sr-only peer"
                                >
                                <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-5 peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-500 shadow-inner"></div>
                            </label>
                        </div>

                        <!-- Points Input -->
                        <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100 flex flex-col items-center gap-1.5">
                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest">Multiplier</label>
                            <div class="relative">
                                <input 
                                    type="number" 
                                    :value="rule.points" 
                                    @change="updateRule(rule, 'points', $event.target.value)"
                                    class="w-20 h-10 rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 font-black text-base text-center bg-white shadow-sm transition-all"
                                >
                                <span class="absolute -right-5 top-1/2 -translate-y-1/2 text-[10px] font-black text-slate-300 uppercase rotate-90">PTS</span>
                            </div>
                        </div>

                        <!-- Logic Condition -->
                        <div class="bg-slate-50/50 p-4 rounded-xl border border-slate-100 flex flex-col gap-1.5 min-w-[200px]">
                            <label class="text-[8px] font-black text-slate-400 uppercase tracking-widest px-1">Logic Pattern</label>
                            <div class="relative">
                                <i class="fas fa-terminal absolute left-3 top-1/2 -translate-y-1/2 text-emerald-500 text-[8px]"></i>
                                <input 
                                    type="text" 
                                    :value="rule.condition_logic"
                                    @change="updateRule(rule, 'condition_logic', $event.target.value)"
                                    placeholder="e.g. time < 09:00"
                                    class="w-full h-10 pl-8 pr-3 rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 font-mono text-[10px] font-bold text-slate-600 bg-white shadow-sm transition-all"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BADGES TAB -->
        <div v-if="activeTab === 'badges'" class="animate-content-fade">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 max-w-6xl mx-auto">
                <!-- Create Hero Card -->
                <button @click="showBadgeModal = true" class="h-full min-h-[220px] border-2 border-dashed border-slate-200 rounded-3xl flex flex-col items-center justify-center gap-4 text-slate-400 hover:border-emerald-500 hover:text-emerald-600 hover:bg-emerald-50/30 transition-all group relative overflow-hidden">
                    <div class="w-14 h-14 bg-white rounded-2xl shadow-sm border border-slate-100 flex items-center justify-center text-xl group-hover:scale-110 group-hover:rotate-12 transition-transform">
                        <i class="fas fa-puzzle-piece text-emerald-500"></i>
                    </div>
                    <div class="text-center">
                        <span class="block font-black uppercase tracking-[0.2em] text-[8px] mb-1 text-slate-500">Node Fabrication</span>
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600/70">Initialize Reward</span>
                    </div>
                </button>

                <!-- Achievement Cards -->
                <div v-for="badge in badges" :key="badge.id" 
                    class="group bg-white rounded-3xl border border-slate-200 shadow-sm transition-all hover:border-emerald-500/30 hover:-translate-y-1 relative overflow-hidden p-6 flex flex-col items-center text-center"
                >
                    <div class="w-16 h-16 bg-slate-50 text-3xl flex items-center justify-center rounded-2xl border border-slate-100 shadow-inner group-hover:scale-105 transition-transform">
                        {{ badge.icon || '🏆' }}
                    </div>

                    <div class="mt-4 space-y-2 flex-1">
                        <h3 class="font-black text-slate-800 text-xs tracking-tight uppercase group-hover:text-emerald-700 transition-colors">
                            {{ badge.name }}
                        </h3>
                        <div class="flex justify-center">
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest bg-slate-50 px-2 py-0.5 rounded border border-slate-100">
                                {{ badge.slug }}
                            </span>
                        </div>
                        <p class="text-[10px] text-slate-500 font-medium leading-relaxed line-clamp-2 px-2">
                            {{ badge.description }}
                        </p>
                    </div>
                    
                    <div class="mt-6 w-full py-2 bg-slate-900 text-emerald-400 rounded-xl text-[9px] font-black uppercase tracking-widest shadow-lg shadow-slate-200 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        +{{ badge.points_bonus }} ENERGY UNITS
                    </div>

                    <button @click="deleteBadge(badge.id)" class="absolute top-4 right-4 w-8 h-8 bg-slate-50 text-slate-300 hover:bg-rose-50 hover:text-rose-500 rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all translate-x-2 group-hover:translate-x-0">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Achievement Designer Modal -->
        <Modal :show="showBadgeModal" @close="showBadgeModal = false" maxWidth="xl">
            <div class="bg-white p-8 font-outfit">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 bg-slate-900 text-emerald-400 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-palette text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-black text-slate-800 tracking-tight leading-none uppercase">Initialize Badge</h2>
                        <p class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] mt-1.5">Achievement Logic Calibration</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Badge Designation</label>
                        <input v-model="badgeForm.name" type="text" placeholder="e.g. TEMPORAL VANGUARD" class="w-full h-12 rounded-xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 font-black text-sm bg-slate-50 shadow-inner px-5" />
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 flex flex-col items-center gap-3">
                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Icon Symbol</label>
                            <input v-model="badgeForm.icon" type="text" class="w-full h-16 rounded-xl border-white focus:border-emerald-500 focus:ring-emerald-500 font-black text-3xl text-center bg-white shadow-md transition-all" />
                        </div>
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100 flex flex-col items-center gap-3">
                            <label class="block text-[8px] font-black text-slate-400 uppercase tracking-widest">Energy Multiplier</label>
                            <input v-model="badgeForm.points_bonus" type="number" class="w-full h-16 rounded-xl border-white focus:border-emerald-500 focus:ring-emerald-500 font-black text-2xl text-center bg-white shadow-md transition-all" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-[9px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Deployment Criteria</label>
                        <textarea v-model="badgeForm.description" rows="3" placeholder="Define the unlocking protocol for this achievement..." class="w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 font-bold text-xs bg-slate-50 shadow-inner px-5 py-4"></textarea>
                    </div>

                    <div class="flex justify-end gap-4 pt-6 mt-4 border-t border-slate-100">
                        <button @click="showBadgeModal = false" class="px-6 py-3 text-slate-400 font-black text-[10px] uppercase tracking-widest hover:text-slate-600 transition-colors">Abort</button>
                        <button @click="createBadge" :disabled="badgeForm.processing" class="px-10 py-4 bg-slate-900 text-white font-black text-[10px] uppercase tracking-[0.2em] rounded-xl hover:bg-emerald-600 shadow-xl shadow-slate-200 transition-all transform active:scale-95 disabled:opacity-50">
                            Deploy Badge
                        </button>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Reuse PolicyBuilder's Modal style for confirmation -->
        <Modal :show="confirmState.show" @close="confirmState.show = false" maxWidth="md">
            <div class="p-8 text-center">
                <div class="w-16 h-16 rounded-xl bg-slate-900 text-rose-500 flex items-center justify-center mx-auto mb-6 shadow-xl">
                    <i class="fas fa-radiation text-xl animate-pulse"></i>
                </div>
                <h3 class="text-lg font-black text-slate-800 mb-2 uppercase tracking-tight">{{ confirmState.title }}</h3>
                <p class="text-[10px] text-slate-400 mb-8 font-bold uppercase tracking-widest max-w-xs mx-auto leading-relaxed">{{ confirmState.message }}</p>
                <div class="flex justify-center gap-4">
                    <button @click="confirmState.show = false" class="px-6 py-2 text-slate-400 font-black text-[9px] uppercase tracking-widest hover:text-slate-600">Cancel</button>
                    <button @click="confirmState.onConfirm" class="px-8 py-3 bg-rose-600 text-white font-black text-[9px] uppercase tracking-[0.15em] rounded-xl hover:bg-rose-700 shadow-lg shadow-rose-200 transition-all active:scale-95">
                        Confirm Data Purge
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<style scoped>
.animate-content-fade {
    animation: content-in 0.4s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
}

@keyframes content-in {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.shadow-inner {
    box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);
}

input[type="number"] {
    -moz-appearance: textfield;
}
input[type="number"]::-webkit-outer-spin-button,
input[type="number"]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
</style>

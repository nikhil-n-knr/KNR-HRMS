<script setup>
import { ref } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import PremiumModal from '@/Components/PremiumModal.vue';
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();
const props = defineProps({
    rotations: Array
});

const showModal = ref(false);
const editingRotation = ref(null);

const form = useForm({
    name: '',
    cycle_days: 7,
});

const openCreate = () => {
    editingRotation.value = null;
    form.reset();
    showModal.value = true;
};

const openEdit = (rotation) => {
    editingRotation.value = rotation;
    form.name = rotation.name;
    form.cycle_days = rotation.cycle_days;
    showModal.value = true;
};

const submit = () => {
    if (editingRotation.value) {
        form.put(route('rotations.update', editingRotation.value.id), {
            onSuccess: () => {
                showModal.value = false;
                toast.success("Rotation protocol updated");
            }
        });
    } else {
        form.post(route('rotations.store'), {
            onSuccess: () => {
                showModal.value = false;
                toast.success("Rotation protocol initialized");
            }
        });
    }
};

const deleteRotation = (id) => {
    if (confirm('Execute protocol deletion sequence?')) {
        router.delete(route('rotations.destroy', id), {
            onSuccess: () => toast.success("Rotation protocol purged")
        });
    }
};
</script>

<template>
    <component :is="AttendanceLayout" title="Cycle Intelligence" activeTab="rotations" v-bind="$props">
        <Head title="Shift Rotations" />
        
        <div class="max-w-[1200px] mx-auto space-y-6 pb-12">
            <!-- Compact Command Bar -->
            <div class="h-14 bg-white/80 backdrop-blur-md p-2 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div class="flex items-center gap-3 px-2">
                    <div class="w-9 h-9 bg-emerald-600 rounded-lg flex items-center justify-center text-white shadow-sm">
                        <i class="fas fa-rotate text-base"></i>
                    </div>
                    <div>
                        <h2 class="text-xs font-black text-slate-800 uppercase tracking-tight leading-none">Shift Rotations</h2>
                        <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-1.5">Cycle Management Protocol</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 pr-1">
                    <button @click="openCreate" class="h-9 flex items-center gap-2 bg-slate-900 text-white px-4 rounded-lg hover:bg-slate-800 transition-all text-sm font-bold uppercase tracking-widest active:scale-95 shadow-sm">
                        <i class="fas fa-plus text-sm"></i>
                        <span>New Rotation</span>
                    </button>
                </div>
            </div>

            <!-- Registry Terminal -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden min-h-[400px]">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-slate-900 border-b border-slate-800">
                            <th class="px-5 py-3 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Protocol Name</span>
                            </th>
                            <th class="px-5 py-3 text-left">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Temporal Cycle</span>
                            </th>
                            <th class="px-5 py-3 text-right">
                                <span class="text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="rotation in rotations" :key="rotation.id" class="group hover:bg-emerald-50/30 transition-all duration-150 border-b border-slate-50 last:border-0">
                            <td class="px-5 py-3">
                                <span class="text-sm font-black text-slate-800 uppercase tracking-tighter leading-none">{{ rotation.name }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-2 py-0.5 rounded border border-emerald-100 shadow-sm">{{ rotation.cycle_days }} DAYS</span>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-right">
                                <div class="flex justify-end gap-1.5 opacity-0 group-hover:opacity-100 transition-all">
                                    <button @click="openEdit(rotation)" class="h-8 w-8 rounded-lg bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center hover:bg-emerald-600 hover:text-white transition-all shadow-sm active:scale-95" title="Modify Protocol">
                                        <i class="fas fa-sliders text-sm"></i>
                                    </button>
                                    <button @click="deleteRotation(rotation.id)" class="h-8 w-8 rounded-lg bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all shadow-sm active:scale-95" title="Purge Protocol">
                                        <i class="fas fa-trash-can text-sm"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="rotations.length === 0">
                            <td colspan="3" class="p-20 text-center">
                                <div class="flex flex-col items-center gap-3 opacity-30 grayscale shrink-0">
                                    <i class="fas fa-spinner-third animate-spin text-2xl"></i>
                                    <span class="text-sm font-bold text-slate-400 uppercase tracking-widest">No Rotation Records Found</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Configuration Modal -->
            <PremiumModal 
                :show="showModal" 
                @close="showModal = false" 
                :title="editingRotation ? 'Modify Rotation' : 'Initialize Rotation'" 
                subtitle="Configure Temporal Cycle Parameters"
                icon="fa-rotate"
                maxWidth="lg"
            >
                <form @submit.prevent="submit" class="space-y-4 pt-2">
                    <div class="space-y-1.5">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Protocol Identification</label>
                        <div class="relative group">
                            <i class="fas fa-signature absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-sm"></i>
                            <input v-model="form.name" type="text" required class="w-full h-10 bg-slate-50 border border-slate-200 rounded-lg pl-10 pr-4 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all placeholder:text-slate-300 uppercase tracking-widest" placeholder="e.g. STANDARD WEEKLY CORE">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-sm font-black text-slate-400 uppercase tracking-widest px-1">Temporal Span (Days)</label>
                        <div class="relative group">
                            <i class="fas fa-calendar-day absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-sm"></i>
                            <input v-model="form.cycle_days" type="number" required min="1" class="w-full h-10 bg-slate-50 border border-slate-200 rounded-lg pl-10 pr-4 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase tracking-widest">
                        </div>
                    </div>

                    <div class="pt-6 border-t border-slate-100 flex justify-end gap-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 rounded-lg text-sm font-black text-slate-400 uppercase tracking-widest hover:bg-slate-50 transition-all">Abort</button>
                        <button type="submit" :disabled="form.processing" class="h-10 px-6 bg-slate-900 text-white rounded-lg text-sm font-black uppercase tracking-widest hover:bg-slate-800 transition-all flex items-center gap-2 group shadow-sm disabled:opacity-50 active:scale-95">
                            <i class="fas fa-shield-check text-sm text-emerald-400"></i>
                            Commit Configuration
                        </button>
                    </div>
                </form>
            </PremiumModal>
        </div>
    </component>
</template>


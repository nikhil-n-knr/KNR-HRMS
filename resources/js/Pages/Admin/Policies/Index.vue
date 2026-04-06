<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import PolicyBuilder from './Builder.vue';
import Modal from '@/Components/Modal.vue';
import { useToastStore } from '@/stores/toast';

const toast = useToastStore();

const props = defineProps({
    policies: { type: Array, default: () => [] }
});

const isModalOpen = ref(false);
const editingPolicy = ref(null);

// Policy Management
const openCreate = () => {
    editingPolicy.value = null;
    isModalOpen.value = true;
};

const openEdit = (policy) => {
    editingPolicy.value = policy;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    editingPolicy.value = null;
};

const showDeleteConfirm = ref(false);
const policyToDelete = ref(null);

const initiateDelete = (policy) => {
    policyToDelete.value = policy;
    showDeleteConfirm.value = true;
};

const executeDelete = () => {
    router.delete(route('admin.attendance.policies.destroy', policyToDelete.value.id), {
        onSuccess: () => {
            showDeleteConfirm.value = false;
            toast.success("Policy architecture deconstructed");
        }
    });
};
</script>

<template>
    <div class="animate-fade-in pb-24 font-outfit">
        <Head title="Unified Policy Hub" />
        
        <!-- Hub Header -->
        <div class="h-14 bg-white/80 backdrop-blur-md p-2 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div class="flex items-center gap-3 px-2">
                <div class="w-9 h-9 bg-emerald-600 rounded-lg flex items-center justify-center text-white shadow-sm">
                    <i class="fas fa-shield-halved text-base"></i>
                </div>
                <div>
                    <h2 class="text-xs font-black text-slate-800 uppercase tracking-tight leading-none">Policy Hub</h2>
                    <p class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-1.5 block">Governance & Compliance</p>
                </div>
            </div>
            
            <button @click="openCreate" class="h-10 px-6 bg-slate-900 text-white rounded-lg text-sm font-black uppercase tracking-widest hover:bg-slate-800 transition-all flex items-center gap-2 shadow-md active:scale-95 group">
                <i class="fas fa-plus-circle text-emerald-400 group-hover:rotate-90 transition-transform"></i>
                New Protocol
            </button>
        </div>

        <!-- Policy Intelligence Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <!-- Add New Node -->
            <button @click="openCreate" class="group border-2 border-dashed border-slate-200 rounded-2xl flex flex-col items-center justify-center p-8 text-slate-300 hover:border-emerald-400 hover:text-emerald-600 hover:bg-emerald-50/30 transition-all duration-300 min-h-[180px] bg-white shadow-sm relative overflow-hidden">
                <div class="w-12 h-12 bg-slate-50 rounded-xl flex items-center justify-center mb-4 text-slate-200 group-hover:scale-110 group-hover:bg-white transition-all duration-300 shadow-inner">
                    <i class="fas fa-plus text-xl"></i>
                </div>
                <span class="text-sm font-black uppercase tracking-widest">Initialize Policy</span>
            </button>

            <!-- Policy Cards -->
            <div v-for="policy in policies" :key="policy.id" 
                class="group bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:border-emerald-200 hover:shadow-lg hover:shadow-emerald-500/5 transition-all duration-300 relative flex flex-col justify-between overflow-hidden cursor-pointer"
                @click="openEdit(policy)"
            >
                <div>
                    <div class="flex justify-between items-start mb-6">
                        <div class="px-3 py-1 bg-emerald-50 text-emerald-600 text-xs font-black uppercase tracking-widest rounded-lg shadow-sm border border-emerald-100">
                            Priority {{ policy.priority }}
                        </div>
                        <button @click.stop="initiateDelete(policy)" class="w-8 h-8 rounded-lg bg-white border border-slate-100 flex items-center justify-center text-slate-300 hover:text-rose-500 hover:bg-rose-50 hover:border-rose-100 transition-all opacity-0 group-hover:opacity-100 shadow-sm">
                            <i class="fas fa-trash-alt text-sm"></i>
                        </button>
                    </div>

                    <h3 class="text-sm font-black text-slate-800 mb-1.5 truncate group-hover:text-emerald-600 transition-colors uppercase tracking-tight">{{ policy.name }}</h3>
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                        <span class="text-sm text-slate-400 font-bold uppercase tracking-widest leading-none">Active Governance</span>
                    </div>

                    <div class="space-y-3 mb-6">
                        <div class="flex items-center gap-3 text-sm font-black text-slate-500 bg-slate-50 p-3 rounded-xl border border-slate-100 transition-all uppercase tracking-tight leading-none">
                            <i class="fas fa-stopwatch text-emerald-500"></i>
                            <span>{{ policy.rules?.grace_late_entry || 0 }}m Grace / {{ policy.rules?.half_day_hours || 4 }}h Half-Day</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 border-t border-slate-100 pt-5 mt-auto">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-1.5">Nodes</span>
                        <span class="text-lg font-black text-slate-800 leading-none">{{ policy.departments_count || 0 }}</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-right">
                        <span class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-1.5">Operatives</span>
                        <span class="text-lg font-black text-slate-800 leading-none">{{ policy.employees_count || 0 }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Policy Architect Modal -->
        <Modal :show="isModalOpen" @close="closeModal" maxWidth="6xl">
            <div class="bg-white/95 backdrop-blur-3xl min-h-[80vh] font-outfit relative">
                <button @click="closeModal" class="absolute top-10 right-10 w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 hover:text-rose-500 flex items-center justify-center transition-all z-50">
                    <i class="fas fa-times text-xl"></i>
                </button>
                <div class="p-2">
                    <PolicyBuilder :policy="editingPolicy" @close="closeModal" :embedded="true" />
                </div>
            </div>
        </Modal>

        <!-- Terminate Policy Confirmation -->
        <Modal :show="showDeleteConfirm" @close="showDeleteConfirm = false" maxWidth="md">
            <div class="bg-white p-12 font-outfit text-center">
                <div class="w-24 h-24 bg-rose-50 rounded-[40px] flex items-center justify-center mx-auto mb-8 text-rose-500 shadow-2xl shadow-rose-500/10 rotate-12">
                    <i class="fas fa-radiation text-4xl"></i>
                </div>
                <h3 class="text-2xl font-black text-slate-800 mb-4 whitespace-nowrap uppercase tracking-tight">Deconstruction Protocol</h3>
                <p class="text-sm text-slate-400 font-bold uppercase tracking-widest mb-12 leading-relaxed max-w-xs mx-auto">
                    Terminating policy architecture will leave assigned employees in a governance vacuum.
                </p>
                <div class="flex gap-4">
                    <button @click="showDeleteConfirm = false" class="flex-1 px-8 py-5 bg-slate-50 text-slate-400 font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-slate-100 transition-all">Abort</button>
                    <button @click="executeDelete" class="flex-1 px-8 py-5 bg-rose-600 text-white font-black text-xs uppercase tracking-widest rounded-2xl hover:bg-rose-700 shadow-2xl shadow-rose-600/20 transition-all">Confirm Termination</button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({ 
    exits: Array,
    employees: Array // For picker
});

const stages = ['Resigned', 'No Due Pending', 'FnF Processing', 'Settled'];

const groupedExits = computed(() => {
    const groups = {
        'Resigned': [],
        'No Due Pending': [],
        'FnF Processing': [],
        'Settled': []
    };
    
    props.exits.forEach(exit => {
        // Map status to column if needed, or assume exact match
        let stage = exit.status;
        if (!groups[stage]) stage = 'Resigned'; // Default or fallback
        groups[stage].push(exit);
    });
    return groups;
});

const showResignModal = ref(false);
const resignForm = useForm({
    employee_id: '',
    resignation_date: new Date().toISOString().substr(0, 10),
    last_working_day_proposed: '',
    reason_type: 'Better Opportunity',
    reason_details: ''
});

const submitResignation = () => {
    resignForm.post(route('finance.exit.store'), {
        onSuccess: () => {
             showResignModal.value = false;
             resignForm.reset();
        }
    });
};

const updateStage = (exit, newStage) => {
    if (confirm(`Move ${exit.employee?.user?.name} to ${newStage}?`)) {
        router.post(route('finance.exit.update-stage', exit.id), { status: newStage });
    }
};

// Helper for days remaining
const daysToLwd = (lwd) => {
    if(!lwd) return '-';
    const diff = new Date(lwd) - new Date();
    const days = Math.ceil(diff / (1000 * 60 * 60 * 24));
    return days > 0 ? `${days} Days Left` : 'Overdue';
};
</script>
<template>
    <div class="h-full flex flex-col space-y-4">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800">Exit & FNF Settlement Hub</h2>
             <button @click="showResignModal = true" class="px-4 py-2 bg-red-600 text-white rounded text-sm font-medium hover:bg-red-700 shadow-sm">
                + Initiate Resignation
            </button>
        </div>

        <div class="flex-1 overflow-x-auto overflow-y-hidden">
            <div class="flex h-full space-x-4 pb-2">
                <!-- Kanban Columns -->
                <div v-for="stage in stages" :key="stage" class="w-80 flex-shrink-0 flex flex-col bg-gray-100 rounded-lg p-3">
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3 flex justify-between">
                        {{ stage }}
                        <span class="bg-gray-200 text-gray-600 px-2 rounded-full">{{ groupedExits[stage]?.length || 0 }}</span>
                    </h3>
                    
                    <div class="flex-1 overflow-y-auto space-y-3">
                         <div v-for="card in groupedExits[stage]" :key="card.id" class="bg-white p-4 rounded shadow-sm border border-gray-200 cursor-pointer hover:shadow-md transition">
                             <div class="flex justify-start items-center mb-2">
                                <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-700 mr-2">
                                    {{ card.employee?.user?.name.charAt(0) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 leading-tight">{{ card.employee?.user?.name }}</h4>
                                    <span class="text-xs text-gray-500">{{ card.employee?.employee_code }}</span>
                                </div>
                             </div>
                             
                             <div class="text-xs text-gray-600 space-y-1 mb-3">
                                <p><span class="font-semibold">LWD:</span> {{ card.last_working_day_approved || 'TBD' }}</p>
                                <p v-if="stage === 'Resigned'" :class="daysToLwd(card.last_working_day_approved).includes('Overdue') ? 'text-red-600 font-bold' : 'text-green-600'">
                                    {{ daysToLwd(card.last_working_day_approved) }}
                                </p>
                             </div>

                             <!-- Actions based on stage -->
                             <div class="pt-2 border-t border-gray-100 flex justify-end space-x-2">
                                 <button v-if="stage === 'Resigned'" @click="updateStage(card, 'No Due Pending')" class="text-xs text-indigo-600 hover:underline">Start No-Due &rarr;</button>
                                 <button v-if="stage === 'No Due Pending'" @click="updateStage(card, 'FnF Processing')" class="text-xs text-indigo-600 hover:underline">Process FnF &rarr;</button>
                                 <button v-if="stage === 'FnF Processing'" @click="updateStage(card, 'Settled')" class="text-xs text-emerald-600 font-bold hover:underline">Settle & Close &rarr;</button>
                             </div>
                         </div>
                         
                         <div v-if="!groupedExits[stage]?.length" class="text-center py-10 opacity-40">
                             <p class="text-sm text-gray-500 italic">No items</p>
                         </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Initiate Resignation Modal -->
        <Modal :show="showResignModal" @close="showResignModal = false">
             <!-- To be implemented if Modal component available, otherwise simplistic overlay -->
             <div v-if="showResignModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
                    <h3 class="text-lg font-bold mb-4">Initiate Resignation</h3>
                    <form @submit.prevent="submitResignation" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Employee</label>
                            <select v-model="resignForm.employee_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm">
                                <option value="">Select Employee</option>
                                <option v-for="emp in employees" :key="emp.id" :value="emp.id">{{ emp.name }}</option>
                            </select>
                        </div>
                        <div>
                             <label class="block text-sm font-medium text-gray-700">Resignation Date</label>
                             <input type="date" v-model="resignForm.resignation_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                        </div>
                        <div>
                             <label class="block text-sm font-medium text-gray-700">Proposed LWD</label>
                             <input type="date" v-model="resignForm.last_working_day_proposed" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                        </div>
                         <div>
                             <label class="block text-sm font-medium text-gray-700">Reason</label>
                             <textarea v-model="resignForm.reason_details" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm" placeholder="Reason for leaving..."></textarea>
                        </div>
                        <div class="flex justify-end gap-2 mt-6">
                            <button type="button" @click="showResignModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm font-medium">Cancel</button>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium" :disabled="resignForm.processing">Initiate</button>
                        </div>
                    </form>
                </div>
             </div>
        </Modal>
    </div>
</template>

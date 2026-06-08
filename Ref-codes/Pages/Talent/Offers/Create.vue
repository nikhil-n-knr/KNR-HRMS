<template>
    <TalentLayout>
        <div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold mb-6">Generate Offer Letter</h2>
            
            <div class="bg-white/80 backdrop-blur-xl shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        For: {{ application.candidate.first_name }} {{ application.candidate.last_name }}
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">
                        Position: {{ application.job_posting.title }} ({{ application.job_posting.job_code }})
                    </p>
                </div>
                
                <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                        
                        <!-- Template Selection (Multi) -->
                        <div>
                            <InputLabel value="Select Documents to Generate" class="mb-2" />
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-60 overflow-y-auto border p-4 rounded bg-gray-50">
                                <div v-for="(group, type) in groupedTemplates" :key="type">
                                    <h4 class="font-bold text-xs uppercase text-gray-500 mb-2">{{ type }}</h4>
                                    <div v-for="t in group" :key="t.id" class="flex items-center mb-2">
                                        <input 
                                            type="checkbox" 
                                            :id="'t-'+t.id" 
                                            :value="t.id" 
                                            v-model="form.template_ids"
                                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                        >
                                        <label :for="'t-'+t.id" class="ml-2 text-sm text-gray-700">{{ t.name }}</label>
                                    </div>
                                </div>
                                <div v-if="props.templates.length === 0" class="text-sm text-gray-500 italic">No templates found.</div>
                            </div>
                            <p v-if="form.errors.template_ids" class="text-xs text-red-500 mt-1">{{ form.errors.template_ids }}</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <!-- Helper: Designation -->
                             <div>
                                <InputLabel for="designation" value="Designation" />
                                <TextInput id="designation" v-model="form.designation" class="mt-1 block w-full" required />
                            </div>

                            <!-- Amount -->
                            <div>
                                <InputLabel for="amount" value="Annual CTC" />
                                <div class="relative mt-1 rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                      <span class="text-gray-500 sm:text-sm">{{ form.salary_currency === 'INR' ? '₹' : '$' }}</span>
                                    </div>
                                    <input 
                                        type="number" 
                                        name="amount" 
                                        id="amount" 
                                        v-model="form.salary_amount" 
                                        class="block w-full rounded-md border-0 py-1.5 pl-7 pr-20 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" 
                                        placeholder="0.00" 
                                        required
                                    >
                                    <div class="absolute inset-y-0 right-0 flex items-center">
                                      <select v-model="form.salary_currency" class="h-full rounded-md border-0 bg-transparent py-0 pl-2 pr-7 text-gray-500 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                                        <option>INR</option>
                                        <option>USD</option>
                                        <option>EUR</option>
                                      </select>
                                    </div>
                                </div>
                            </div>

                             <!-- Salary Structure -->
                            <div>
                                <BaseSelect
                                    id="structure"
                                    v-model="form.salary_structure_id"
                                    label="Salary Structure"
                                    :error="form.errors.salary_structure_id"
                                >
                                    <option value="">Manual Breakdown</option>
                                    <option v-for="s in salaryStructures" :key="s.id" :value="s.id">{{ s.name }}</option>
                                </BaseSelect>
                            </div>
                        </div>

                        <!-- Salary Breakdown Configuration -->
                        <div class="border border-gray-200 rounded-md bg-white overflow-hidden">
                            <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                                <h4 class="text-sm font-semibold text-gray-900">Salary Breakdown ({{ form.salary_structure_id ? 'Auto-Calculated' : 'Manual Entry' }})</h4>
                                <button v-if="!form.salary_structure_id" type="button" @click="addComponent" class="text-xs text-indigo-600 hover:text-indigo-900 font-medium flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                    Add Component
                                </button>
                            </div>
                            
                            <div class="p-4">
                                <div v-if="form.salary_breakdown.components.length === 0" class="text-center py-4 text-sm text-gray-500 italic">
                                    No components defined. Select a structure or add manually.
                                </div>
                                <table v-else class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Component</th>
                                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Monthly</th>
                                            <th scope="col" class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Annual</th>
                                            <th v-if="!form.salary_structure_id" scope="col" class="relative px-3 py-2"><span class="sr-only">Actions</span></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(comp, index) in form.salary_breakdown.components" :key="index">
                                            <td class="px-3 py-2 whitespace-nowrap">
                                                <TextInput 
                                                    v-if="!form.salary_structure_id" 
                                                    v-model="comp.name" 
                                                    placeholder="Name" 
                                                    class="block w-full text-sm border-0 border-b border-transparent focus:border-indigo-600 focus:ring-0 px-0 h-8" 
                                                    required 
                                                />
                                                <span v-else class="text-sm text-gray-900 font-medium">{{ comp.name }}</span>
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-right text-sm text-gray-500">
                                                <!-- Visual Placeholder for Monthly -->
                                                <span v-if="comp.is_auto" class="text-xs text-gray-400 italic">Auto</span>
                                                <span v-else>{{ (comp.value / 12).toFixed(2) }}</span>
                                            </td>
                                            <td class="px-3 py-2 whitespace-nowrap text-right">
                                                <TextInput 
                                                     v-if="!form.salary_structure_id" 
                                                    v-model="comp.value" 
                                                    type="number" 
                                                    placeholder="0.00" 
                                                    class="block w-full text-right text-sm border-0 border-b border-transparent focus:border-indigo-600 focus:ring-0 px-0 h-8" 
                                                    @input="updateTotal" 
                                                    required 
                                                />
                                                 <span v-else class="text-sm text-gray-900">{{ Number(comp.value).toFixed(2) }}</span>
                                            </td>
                                            <td v-if="!form.salary_structure_id" class="px-3 py-2 whitespace-nowrap text-right text-sm font-medium">
                                                <button type="button" @click="removeComponent(index)" class="text-red-400 hover:text-red-600">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td class="px-3 py-2 text-sm font-bold text-gray-900">Total Validated</td>
                                            <td class="px-3 py-2 text-right text-sm text-gray-500">{{ (form.salary_amount / 12).toFixed(2) }}</td>
                                            <td class="px-3 py-2 text-right text-sm font-bold text-indigo-600">
                                                {{ form.salary_currency }} {{ form.salary_amount }}
                                            </td>
                                            <td v-if="!form.salary_structure_id"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                             <!-- Offer Date -->
                            <div>
                                <InputLabel for="offer_date" value="Offer Date" />
                                <TextInput id="offer_date" v-model="form.offer_date" type="date" class="mt-1 block w-full" required />
                            </div>
                             <!-- Joining Date -->
                            <div>
                                <InputLabel for="joining" value="Joining Date" />
                                <TextInput id="joining" v-model="form.joining_date" type="date" class="mt-1 block w-full" required />
                            </div>
                        </div>

                        <div class="flex justify-end">
                            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                Generate Draft
                            </PrimaryButton>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </TalentLayout>
</template>

<script setup>
import TalentLayout from '@/Layouts/TalentLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';

const props = defineProps({
    application: Object,
    templates: Array,
    salaryStructures: Array
});

const form = useForm({
    job_application_id: props.application.id,
    template_ids: [],
    salary_structure_id: '',
    offers_mode: 'template', // Defaulting for simple flow
    salary_currency: 'INR',
    salary_amount: '',
    designation: props.application.job_posting.title,
    offer_date: new Date().toISOString().substr(0, 10),
    joining_date: '',
    expiry_date: '',
    salary_breakdown: { components: [] }
});

const groupedTemplates = computed(() => {
    return props.templates.reduce((groups, t) => {
        const type = t.type.charAt(0).toUpperCase() + t.type.slice(1);
        if (!groups[type]) groups[type] = [];
        groups[type].push(t);
        return groups;
    }, {});
});

// Watch for Structure Change or Amount Change to Recalculate Mock Breakdown
watch([() => form.salary_structure_id, () => form.salary_amount], ([newStructId, newAmount]) => {
    if (!newStructId || !newAmount) return;
    
    const structure = props.salaryStructures.find(s => s.id === newStructId);
    if (structure && structure.components) {
        // Simple Client-Side Estimation for Visuals (Real calc happens on backend)
        const amount = parseFloat(newAmount);
        
        form.salary_breakdown.components = structure.components.map(comp => {
            let val = 0;
            if (comp.type === 'Fixed' || comp.calculation_type === 'flat') {
                val = comp.value; // Assuming value is stored
            } else {
                 // Percentage of Basic? Or CTC?
                 // This is complex to replicate 100% on frontend without the service logic.
                 // We will just show the Component Name and a Placeholder or 0 for now
                 // UNLESS we replicate the formula.
                 // Let's just list the components so the user knows what will be generated.
                 val = 0; 
            }
            return { name: comp.name, value: val, is_auto: true };
        });
    }
});

const submit = () => {
    form.post(route('talent.offers.store'));
};

const addComponent = () => {
    form.salary_breakdown.components.push({ name: '', value: 0 });
};
const removeComponent = (index) => {
    form.salary_breakdown.components.splice(index, 1);
};
</script>

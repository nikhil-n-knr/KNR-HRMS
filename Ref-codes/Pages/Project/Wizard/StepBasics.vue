<template>
    <div class="space-y-6 animate-fade-in-up">
        <div class="text-center mb-8">
            <h2 class="text-xl font-bold text-gray-800">Project Essentials</h2>
            <p class="text-gray-500">Define the core identity and visibility rules.</p>
        </div>

        <!-- Clone Source (Optional) -->
        <div class="bg-indigo-50/50 p-6 rounded-[2rem] border border-indigo-100 mb-4">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-6 w-1 bg-indigo-500 rounded-full"></div>
                <h3 class="text-xs font-black text-slate-400 uppercase tracking-[0.2em]">Templates & Blueprinting</h3>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
                <BaseSelect
                    v-model="form.clone_from_id"
                    label="Clone from Existing Project"
                    color="indigo"
                >
                    <option :value="null">Fresh Start (No Blueprint)</option>
                    <option v-for="proj in existingProjects" :key="proj.id" :value="proj.id">
                        {{ proj.name }} ({{ proj.code }})
                    </option>
                </BaseSelect>
                <p class="text-xs text-slate-500 italic mb-3">If selected, modules and configurations will be duplicated from the source project.</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Client Selection -->
            <div>
                <BaseSelect
                    v-model="form.client_id"
                    label="Client *"
                    color="indigo"
                    :error="form.errors.client_id"
                >
                    <option value="" disabled>Select a Client</option>
                    <option v-for="client in clients" :key="client.id" :value="client.id">{{ client.name }}</option>
                </BaseSelect>
            </div>

            <!-- Project Name -->
            <div>
                <BaseInput
                    v-model="form.name"
                    label="Project Name *"
                    placeholder="e.g. Apollo Redesign"
                    color="indigo"
                    :error="form.errors.name"
                />
            </div>

            <!-- Project Code -->
            <div>
                <BaseInput
                    v-model="form.code"
                    label="Project Code *"
                    placeholder="e.g. APL-01"
                    color="indigo"
                    class="uppercase"
                    :error="form.errors.code"
                />
            </div>

            <!-- Visibility -->
            <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Visibility Mode *</label>
                <div class="grid grid-cols-2 gap-3">
                    <button 
                        v-for="mode in visibilityOptions" 
                        :key="mode.value"
                        @click.prevent="form.visibility = mode.value"
                        class="px-4 py-3 text-sm text-left border rounded-2xl transition-all flex flex-col justify-center"
                        :class="form.visibility === mode.value ? 'bg-indigo-600 border-indigo-600 text-white shadow-lg shadow-indigo-200' : 'bg-white border-slate-200 hover:border-indigo-300 text-slate-600'"
                    >
                        <div class="font-black uppercase tracking-tight text-xs">{{ mode.label }}</div>
                        <div class="text-[10px] opacity-70 font-bold mt-0.5">{{ mode.desc }}</div>
                    </button>
                </div>
            </div>

            <!-- Conditional: Team Selection -->
            <div v-if="form.visibility === 'team_locked'" class="md:col-span-2 bg-slate-50 p-6 rounded-[2rem] border border-slate-100">
                <label class="block text-sm font-black text-slate-700 uppercase tracking-tight mb-3">Project Team Allocation</label>
                <MultiUserSelect 
                    v-model="form.selected_teams" 
                    :items="teams" 
                    placeholder="Select teams that will have access to this project..." 
                />
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-3">All members of selected teams will be automatically granted access.</p>
            </div>

            <!-- Conditional: Freelancer Selection -->
            <div v-if="form.visibility === 'freelancer_mode'" class="md:col-span-2 bg-slate-50 p-6 rounded-[2rem] border border-slate-100">
                <label class="block text-sm font-black text-slate-700 uppercase tracking-tight mb-3">Freelancer / External Node Access</label>
                <MultiUserSelect 
                    v-model="form.selected_freelancers" 
                    :items="allEmployees" 
                    placeholder="Select external contributors or freelancers..." 
                />
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-3 text-amber-600">Freelancer mode restricts visibility to assigned tasks only.</p>
            </div>

             <!-- Dates -->
             <div class="space-y-2">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Timeline *</label>
                <div class="grid grid-cols-2 gap-4">
                     <BaseInput
                        v-model="form.dates.start"
                        type="date"
                        label="Launch Date"
                        color="indigo"
                        :error="form.errors['dates.start']"
                    />
                     <BaseInput
                        v-model="form.dates.end"
                        type="date"
                        label="Target Deadline"
                        color="indigo"
                        :error="form.errors['dates.end']"
                    />
                </div>
            </div>

            <!-- Project Owners -->
            <div class="space-y-4 col-span-1 md:col-span-2">
                <div class="flex items-center justify-between">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">Project Authority (Owners)</label>
                    <button 
                        @click.prevent="showAllOwners = !showAllOwners"
                        class="text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-800 transition-colors"
                    >
                        {{ showAllOwners ? 'Show Managers Only' : 'Show All Employees' }}
                    </button>
                </div>
                <MultiUserSelect 
                    v-model="form.owners" 
                    :items="showAllOwners ? allEmployees : managers" 
                    placeholder="Search and select project stakeholders..." 
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import MultiUserSelect from '@/Components/MultiUserSelect.vue';

const props = defineProps({
    form: Object,
    clients: Array,
    managers: Array,
    existingProjects: Array,
    teams: Array,
    allEmployees: Array
});

const showAllOwners = ref(false);

const visibilityOptions = [
    { value: 'public', label: 'Public', desc: 'Global Visibility' },
    { value: 'team_locked', label: 'Team Locked', desc: 'Secure Protocol' },
    { value: 'stealth', label: 'Stealth', desc: 'Hidden Registry' },
    { value: 'freelancer_mode', label: 'Freelancer', desc: 'Task Isolator' },
];
</script>

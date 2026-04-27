<template>
    <div class="space-y-6 animate-fade-in-up">
        <div class="text-center mb-8">
            <h2 class="text-xl font-bold text-gray-800">Project Essentials</h2>
            <p class="text-gray-500">Define the core identity and visibility rules.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Client Selection -->
            <div>
                <BaseSelect
                    v-model="form.client_id"
                    label="Client"
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
                    label="Project Name"
                    placeholder="e.g. Apollo Redesign"
                    color="indigo"
                    :error="form.errors.name"
                />
            </div>

            <!-- Project Code -->
            <div>
                <BaseInput
                    v-model="form.code"
                    label="Project Code"
                    placeholder="e.g. APL-01"
                    color="indigo"
                    class="uppercase"
                    :error="form.errors.code"
                />
            </div>

            <!-- Visibility -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Visibility Mode</label>
                <div class="grid grid-cols-2 gap-3">
                    <button 
                        v-for="mode in visibilityOptions" 
                        :key="mode.value"
                        @click.prevent="form.visibility = mode.value"
                        class="px-3 py-2 text-sm text-left border rounded-xl transition-all"
                        :class="form.visibility === mode.value ? 'bg-indigo-50 border-indigo-500 text-indigo-700 ring-1 ring-indigo-500 shadow-sm' : 'bg-white border-gray-200 hover:bg-gray-50 text-gray-600'"
                    >
                        <div class="font-bold">{{ mode.label }}</div>
                        <div class="text-xs opacity-75">{{ mode.desc }}</div>
                    </button>
                </div>
            </div>

             <!-- Dates -->
             <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Timeline</label>
                <div class="grid grid-cols-2 gap-4">
                     <BaseInput
                        v-model="form.dates.start"
                        type="date"
                        color="indigo"
                        :error="form.errors['dates.start']"
                    />
                     <BaseInput
                        v-model="form.dates.end"
                        type="date"
                        color="indigo"
                    />
                </div>
            </div>
            <!-- Project Owners -->
            <div class="space-y-2 col-span-1 md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Project Owners / Managers <span class="text-gray-400 text-xs font-normal ml-1">(Optional, can select multiple)</span></label>
                <MultiUserSelect 
                    v-model="form.owners" 
                    :items="managers" 
                    placeholder="Search and select project stakeholders..." 
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import BaseInput from '@/Components/BaseInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import MultiUserSelect from '@/Components/MultiUserSelect.vue';
const props = defineProps({
    form: Object,
    clients: Array,
    managers: Array
});

const visibilityOptions = [
    { value: 'public', label: 'Public', desc: 'Visible to everyone' },
    { value: 'team_locked', label: 'Team Locked', desc: 'Members only' },
    { value: 'stealth', label: 'Stealth', desc: 'Hidden from lists' },
    { value: 'freelancer_mode', label: 'Freelancer', desc: 'Task-only view' },
];
</script>

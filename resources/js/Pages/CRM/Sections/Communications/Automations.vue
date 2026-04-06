<template>
    <div class="h-[calc(100vh-160px)] flex flex-col space-y-8">
        <!-- Automation Header -->
        <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-8 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <div class="w-14 h-14 bg-indigo-50 rounded-2xl flex items-center justify-center text-indigo-600 shadow-sm border border-indigo-100">
                    <i class="fas fa-robot text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Intelligence Engine</h2>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Rule-Based Engagement Protocols</p>
                </div>
            </div>
            <button @click="showCreateRule = true" class="px-8 py-3.5 bg-gray-900 text-white rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-black transition-all shadow-xl flex items-center gap-2">
                <i class="fas fa-plus"></i> Deploy New Rule
            </button>
        </div>

        <!-- Deploy Rule Modal -->
        <div v-if="showCreateRule" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-[40px] shadow-2xl max-w-lg w-full overflow-hidden border border-white">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight">Deploy Intelligence Rule</h3>
                    <button @click="showCreateRule = false" class="text-gray-400 hover:text-gray-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-8 space-y-6 text-left">
                    <div class="space-y-4">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block ml-1">Rule Name</label>
                        <input v-model="ruleForm.name" type="text" placeholder="e.g. Lead Welcome Automation" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
                    </div>
                    <div class="space-y-4">
                        <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest block ml-1">Trigger Event</label>
                        <select v-model="ruleForm.trigger_event" class="w-full bg-gray-50 border-none rounded-2xl p-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/10">
                            <option v-for="trigger in triggers" :key="trigger" :value="trigger">{{ trigger.replace('_', ' ').toUpperCase() }}</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-3 pt-6">
                        <button @click="showCreateRule = false" class="px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest text-gray-400 hover:bg-gray-50">Cancel</button>
                        <button @click="submitRule" class="px-8 py-3 bg-gray-900 text-white rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-black shadow-xl">
                            Deploy Rule
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rules Grid -->
        <div class="flex-1 overflow-y-auto">
            <div v-if="rules.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="rule in rules" :key="rule.id" class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-8 hover:shadow-xl transition-all group relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-8 opacity-5 text-indigo-600">
                        <i class="fas fa-bolt text-8xl"></i>
                    </div>
                    
                    <div class="flex items-center justify-between mb-6">
                        <span :class="[rule.is_active ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-gray-50 text-gray-400 border-gray-100', 'px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-widest border']">
                            {{ rule.is_active ? 'Operational' : 'Paused' }}
                        </span>
                        <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <button class="w-8 h-8 rounded-lg bg-gray-50 text-gray-400 hover:text-indigo-600"><i class="fas fa-edit text-xs"></i></button>
                            <button class="w-8 h-8 rounded-lg bg-gray-50 text-gray-400 hover:text-rose-600"><i class="fas fa-trash-alt text-xs"></i></button>
                        </div>
                    </div>

                    <h4 class="text-lg font-black text-gray-900 mb-2 leading-tight">{{ rule.name }}</h4>
                    <p class="text-xs text-gray-500 font-medium leading-relaxed mb-6">{{ rule.description || 'No description provided.' }}</p>

                    <div class="space-y-3 pt-6 border-t border-gray-50">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-play text-gray-300 text-[10px]"></i>
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Trigger: {{ rule.trigger_event }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <i class="fas fa-cog text-gray-300 text-[10px]"></i>
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Action: {{ JSON.parse(rule.actions || '[]').length }} steps</span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="h-full bg-white rounded-[40px] border border-gray-100 border-dashed flex flex-col items-center justify-center p-12 text-center">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-8 border border-gray-100">
                    <i class="fas fa-brain text-4xl text-gray-100"></i>
                </div>
                <h3 class="text-xl font-black text-gray-900 tracking-tight mb-2">Neural Static</h3>
                <p class="text-sm text-gray-400 font-bold uppercase tracking-widest max-w-xs">No automation rules deployed. Your team is currently handling all touchpoints manually.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    rules: { type: Array, default: () => [] },
    triggers: { type: Array, default: () => [] }
});

const showCreateRule = ref(false);
const ruleForm = ref({
    name: '',
    trigger_event: 'lead_created',
    actions: []
});

const submitRule = () => {
    router.post(route('crm.automation-rules.store'), ruleForm.value, {
        onSuccess: () => {
            showCreateRule.value = false;
            ruleForm.value = { name: '', trigger_event: 'lead_created', actions: [] };
        }
    });
};
</script>

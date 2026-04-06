<template>
    <div class="space-y-8 text-left">
        <!-- Header -->
        <div class="flex justify-between items-end pb-4 border-b border-gray-100 text-left">
            <div class="text-left">
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Lead Scoring Rules</h2>
                <div class="flex items-center mt-2">
                    <span class="w-8 h-1 bg-amber-500 rounded-full mr-3"></span>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-widest">Autonomous Qualification Engine</p>
                </div>
            </div>
            <button @click="showModal = true" class="px-8 py-3 bg-amber-600 text-white rounded-2xl hover:bg-amber-700 transition-all font-black text-sm shadow-xl shadow-amber-100 flex items-center group">
                <i class="fas fa-plus mr-2 text-xs group-hover:rotate-90 transition-transform"></i>
                ADD RULE
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 text-left">
            <!-- Rules List -->
            <div class="lg:col-span-2 space-y-6 text-left">
                <div v-for="rule in rules" :key="rule.id" 
                     class="bg-white rounded-[32px] border border-gray-100 p-8 shadow-sm hover:shadow-xl hover:shadow-gray-50 transition-all group relative overflow-hidden">
                    
                    <div class="absolute top-0 right-0 p-4 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2">
                         <button class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all">
                            <i class="fas fa-edit text-xs"></i>
                         </button>
                         <button class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-all">
                            <i class="fas fa-trash text-xs"></i>
                         </button>
                    </div>

                    <div class="flex items-start gap-6">
                        <div :class="[
                            'w-16 h-16 rounded-[24px] flex flex-col items-center justify-center font-black text-xl shadow-lg',
                            rule.points > 0 ? 'bg-emerald-500 text-white shadow-emerald-100' : 'bg-rose-500 text-white shadow-rose-100'
                        ]">
                            <span class="text-sm leading-none opacity-60 uppercase mb-1">{{ rule.points > 0 ? 'Adds' : 'Subs' }}</span>
                            {{ Math.abs(rule.points) }}
                        </div>
                        
                        <div class="flex-1">
                            <h3 class="text-lg font-black text-gray-900 mb-2">{{ rule.name }}</h3>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="px-3 py-1 bg-gray-50 text-gray-400 rounded-lg text-sm font-black uppercase tracking-widest border border-gray-100">IF FIELD</span>
                                <span class="px-3 py-1 bg-indigo-50 text-indigo-600 rounded-lg text-sm font-black uppercase tracking-widest border border-indigo-100">{{ rule.criteria_field }}</span>
                                <span class="text-sm font-bold text-gray-300 italic">{{ rule.operator }}</span>
                                <span class="px-3 py-1 bg-amber-50 text-amber-600 rounded-lg text-sm font-black uppercase tracking-widest border border-amber-100">{{ rule.value }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="rules.length === 0" class="bg-gray-50/50 rounded-[40px] border-2 border-dashed border-gray-200 p-20 text-center">
                    <div class="w-20 h-20 bg-white rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-sm border border-gray-100">
                        <i class="fas fa-calculator text-3xl text-gray-200"></i>
                    </div>
                    <p class="text-gray-400 font-black uppercase tracking-widest text-xs">No Rules Configured</p>
                </div>
            </div>

            <!-- Insights / How It Works -->
            <div class="space-y-6 text-left">
                <div class="bg-indigo-900 rounded-[40px] p-10 text-white shadow-2xl shadow-indigo-100 relative overflow-hidden text-left">
                    <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
                    
                    <h3 class="text-xl font-black mb-6 flex items-center">
                        <i class="fas fa-bolt text-amber-400 mr-3"></i> Scoring Engine
                    </h3>
                    <p class="text-indigo-100 text-sm font-medium leading-relaxed mb-8">
                        Leads are automatically evaluated against these rules in real-time. High scores (60+) trigger automatic "Hot Lead" notifications.
                    </p>

                    <div class="space-y-6">
                        <div class="p-5 bg-white/10 rounded-2xl border border-white/10">
                            <div class="flex justify-between text-sm font-black uppercase tracking-widest mb-3 opacity-60">
                                <span>Cold Range</span>
                                <span>0 - 20 pts</span>
                            </div>
                            <div class="h-1.5 bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-rose-400 w-1/3 shadow-[0_0_10px_rgba(244,63,94,0.5)]"></div>
                            </div>
                        </div>
                        <div class="p-5 bg-white/10 rounded-2xl border border-white/10">
                            <div class="flex justify-between text-sm font-black uppercase tracking-widest mb-3 opacity-60">
                                <span>Warm Range</span>
                                <span>21 - 60 pts</span>
                            </div>
                            <div class="h-1.5 bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-amber-400 w-2/3 shadow-[0_0_10px_rgba(251,191,36,0.5)]"></div>
                            </div>
                        </div>
                        <div class="p-5 bg-white/10 rounded-2xl border border-white/10">
                            <div class="flex justify-between text-sm font-black uppercase tracking-widest mb-3 opacity-60">
                                <span>HOT Range</span>
                                <span>61+ pts</span>
                            </div>
                            <div class="h-1.5 bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-400 w-full shadow-[0_0_10px_rgba(16,185,129,0.5)]"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- MOCK CREATE MODAL -->
        <div v-if="showModal" @click.self="showModal = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4 text-left">
            <div class="relative bg-white rounded-[40px] shadow-2xl max-w-lg w-full overflow-hidden border border-white text-left">
                <div class="bg-gray-50/50 px-8 py-6 border-b border-gray-100 flex justify-between items-center text-left">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white mr-4 shadow-lg shadow-indigo-100">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">New Scoring Rule</h3>
                    </div>
                    <button @click="showModal = false" class="w-10 h-10 rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-gray-900 flex items-center justify-center shadow-sm transition-all hover:rotate-90">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-8 space-y-6 text-left">
                    <div class="space-y-4 text-left">
                        <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1">Rule Name</label>
                        <input type="text" class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 px-5 text-sm font-bold shadow-inner" placeholder="e.g. LinkedIn Source Multiplier">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 mb-2">Field</label>
                                <select class="w-full bg-gray-50 border-gray-100 rounded-2xl py-3 px-4 text-xs font-bold shadow-inner">
                                    <option>Source</option>
                                    <option>Email</option>
                                    <option>Company</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 mb-2">Points</label>
                                <input type="number" class="w-full bg-gray-50 border-gray-100 rounded-2xl py-3 px-4 text-xs font-bold shadow-inner" placeholder="10">
                            </div>
                        </div>
                    </div>
                    <button @click="showModal = false" class="w-full bg-indigo-600 text-white py-4 rounded-2xl font-black text-sm hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 mt-4 flex items-center justify-center">
                        <i class="fas fa-check-circle mr-2"></i> CREATE RULE
                    </button>
                    <button @click="showModal = false" class="w-full text-gray-400 font-bold text-sm uppercase tracking-widest py-2 hover:text-gray-600 transition-colors">Discard Changes</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    rules: {
        type: Array,
        default: () => [
            { id: 1, name: 'Premium Domain Match', criteria_field: 'email', operator: 'contains', value: '.gov', points: 35 },
            { id: 2, name: 'Marketing Source Multiplier', criteria_field: 'source', operator: '=', value: 'Website', points: 15 },
            { id: 3, name: 'Incomplete Phone Penalty', criteria_field: 'phone', operator: 'is_empty', value: 'null', points: -20 },
        ]
    }
});

const showModal = ref(false);
</script>

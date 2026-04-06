<template>
    <div class="space-y-8 text-left">
        <!-- Header -->
        <div class="flex justify-between items-end pb-4 border-b border-gray-100 text-left">
            <div class="text-left">
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Assignment Engine</h2>
                <div class="flex items-center mt-2">
                    <span class="w-8 h-1 bg-purple-500 rounded-full mr-3"></span>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-widest">Automatic Rep Distribution</p>
                </div>
            </div>
            <div class="flex gap-4">
                <button @click="reRunRules" class="px-6 py-3 bg-white border border-gray-100 text-gray-400 rounded-2xl hover:text-gray-900 hover:bg-gray-50 transition-all font-black text-xs shadow-sm">
                    RE-RUN ALL RULES
                </button>
                <button @click="showModal = true" class="px-8 py-3 bg-purple-600 text-white rounded-2xl hover:bg-purple-700 transition-all font-black text-sm shadow-xl shadow-purple-100 flex items-center group">
                    <i class="fas fa-plus mr-2 text-xs group-hover:rotate-180 transition-transform"></i>
                    NEW RULE
                </button>
            </div>
        </div>

        <!-- Rules List -->
        <div class="space-y-6 text-left">
            <div v-for="(rule, index) in rules" :key="rule.id" 
                 class="bg-white rounded-[40px] border border-gray-100 p-8 shadow-sm hover:shadow-2xl hover:shadow-purple-50/50 transition-all relative group overflow-hidden text-left">
                
                <!-- Rank Badge -->
                <div class="absolute left-0 top-0 w-16 h-16 bg-purple-50 text-purple-600 flex items-center justify-center font-black text-xs rounded-br-[24px]">
                    #{{ index + 1 }}
                </div>

                <div class="pl-12 flex flex-col md:flex-row justify-between items-center gap-8 text-left">
                    <div class="flex-1 text-left">
                        <div class="flex items-center gap-3 mb-3">
                            <h3 class="text-xl font-black text-gray-900 leading-tight">{{ rule.name }}</h3>
                            <span v-if="rule.is_active" class="px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-sm font-black uppercase tracking-widest border border-emerald-100">Live</span>
                            <span v-else class="px-3 py-1 bg-gray-50 text-gray-300 rounded-full text-sm font-black uppercase tracking-widest border border-gray-100">Paused</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-sm font-black text-gray-400 uppercase tracking-widest">Logic:</span>
                            <span class="px-4 py-2 bg-gray-50 text-gray-600 rounded-2xl text-base font-bold border border-gray-100 italic shadow-inner">{{ rule.condition }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-8 text-left">
                        <div class="flex flex-col items-center">
                            <div class="flex -space-x-2 mb-2">
                                <div class="w-8 h-8 rounded-full bg-purple-100 border-2 border-white flex items-center justify-center text-sm text-purple-600 font-black"><i class="fas fa-user text-xs"></i></div>
                                <div class="w-8 h-8 rounded-full bg-indigo-100 border-2 border-white flex items-center justify-center text-sm text-indigo-600 font-black"><i class="fas fa-user text-xs"></i></div>
                            </div>
                            <span class="text-sm font-black text-gray-400 uppercase tracking-widest mb-1">Assigned To</span>
                            <span class="text-xs font-black text-gray-800">{{ rule.assignee }}</span>
                        </div>
                        
                        <div class="flex gap-2">
                            <button class="w-12 h-12 rounded-2xl bg-gray-50 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-all border border-gray-100">
                                <i class="fas fa-pen text-xs"></i>
                            </button>
                            <button class="w-12 h-12 rounded-2xl bg-gray-50 text-gray-400 hover:text-rose-600 hover:bg-rose-50 transition-all border border-gray-100">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Default Route -->
        <div class="bg-gray-50/50 rounded-[40px] border-2 border-dashed border-gray-200 p-8 flex items-center justify-between text-left">
            <div class="flex items-center gap-6">
                <div class="w-16 h-16 bg-white rounded-3xl shadow-sm flex items-center justify-center border border-gray-100">
                    <i class="fas fa-inbox text-2xl text-gray-200"></i>
                </div>
                <div>
                    <h4 class="text-sm font-black text-gray-800 uppercase tracking-tight">Fallback Destination</h4>
                    <p class="text-xs text-gray-400 font-bold mt-1 leading-relaxed">Incoming leads that match no rules will be sent to the <span class="text-indigo-600">Unassigned Sales Queue</span>.</p>
                </div>
            </div>
            <button class="text-sm font-black text-indigo-600 hover:text-indigo-800 transition-colors uppercase tracking-widest">Update Fallback</button>
        </div>

        <!-- MOCK MODAL -->
        <div v-if="showModal" @click.self="showModal = false" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4 text-left">
            <div class="relative bg-white rounded-[40px] shadow-2xl max-w-lg w-full overflow-hidden border border-white text-left">
                <div class="bg-purple-50/50 px-8 py-6 border-b border-purple-100 flex justify-between items-center text-left">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-purple-600 rounded-xl flex items-center justify-center text-white mr-4 shadow-lg shadow-purple-100">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">Capture Engine Rule</h3>
                    </div>
                    <button @click="showModal = false" class="w-10 h-10 rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-gray-900 flex items-center justify-center shadow-sm transition-all hover:rotate-90">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-8 space-y-6 text-left">
                    <div class="space-y-4">
                        <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1">Rule Identity</label>
                        <input type="text" class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 px-5 text-sm font-bold shadow-inner" placeholder="e.g. Enterprise EMEA Pipeline">
                        
                        <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1">Assign To User / Team</label>
                        <select class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4 px-5 text-sm font-bold shadow-inner">
                            <option>Enterprise Sales Team</option>
                            <option>Round Robin (SDR Team)</option>
                            <option>Direct: Sarah Jenkins</option>
                        </select>
                    </div>
                    <button @click="showModal = false" class="w-full bg-purple-600 text-white py-4 rounded-2xl font-black text-sm hover:bg-purple-700 transition-all shadow-xl shadow-purple-100 mt-4 flex items-center justify-center">
                        <i class="fas fa-power-off mr-2"></i> ACTIVATE RULE
                    </button>
                    <button @click="showModal = false" class="w-full text-gray-400 font-bold text-sm uppercase tracking-widest py-2 hover:text-gray-600 transition-colors">Abort Mission</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const showModal = ref(false);

const rules = ref([
    { 
        id: 1, 
        name: 'Enterprise Tech Pipeline', 
        is_active: true, 
        condition: 'Industry = Technology AND Employees > 1000', 
        assignee: 'Enterprise Sales Team' 
    },
    { 
        id: 2, 
        name: 'EMEA Regional Shield', 
        is_active: true, 
        condition: 'Country IN [UK, Germany, France]', 
        assignee: 'Sarah Jenkins' 
    },
    { 
        id: 3, 
        name: 'Marketing Web Capture', 
        is_active: false, 
        condition: 'Source = Website', 
        assignee: 'Round Robin (SDR Team)' 
    },
]);

const reRunRules = () => {
    alert('Scanning entire pool for redistribution...');
};
</script>

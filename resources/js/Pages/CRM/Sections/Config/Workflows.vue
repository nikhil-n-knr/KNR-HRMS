<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative font-sans text-left">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group no-print">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <i class="fas fa-info text-xs"></i>
            </button>
            <div class="absolute right-0 top-10 w-80 bg-gray-900 text-white text-sm p-6 rounded-[32px] shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium font-sans">
                <div class="font-black tracking-[0.2em] uppercase mb-3 text-indigo-400 border-b border-indigo-500/20 pb-3 text-left">Orchestration & Logic Flow</div>
                <p class="text-gray-300 leading-relaxed mb-4 text-left font-sans">Workflow Automation defines the event-driven logic of your global sales engine. Configure triggers and sequential actions to maintain operational velocity across the entire ecosystem.</p>
                <div class="space-y-2 text-left">
                    <div class="flex items-center gap-3 text-left font-sans"><div class="w-2 h-2 rounded-full bg-indigo-500 shadow-lg shadow-indigo-500/50"></div> <span>Event-Driven Rule Propagation</span></div>
                    <div class="flex items-center gap-3 text-left font-sans"><div class="w-2 h-2 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/50"></div> <span>Sequential Velocity Orchestration</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40 no-print text-left font-sans">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 text-left font-sans text-left font-sans">
                    <div class="text-left font-sans text-left font-sans text-left font-sans">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight text-left font-sans text-left font-sans">Logic Orchestration</h2>
                        <div class="flex items-center mt-3 text-left font-sans text-left font-sans text-left font-sans">
                            <i class="fas fa-bolt text-indigo-500 mr-3 text-left font-sans text-left font-sans text-left font-sans"></i>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans text-left font-sans text-left font-sans">Automation Engine: Synchronized</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-left font-sans text-left font-sans">
                         <button @click="createNewWorkflow" class="px-8 py-3.5 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all font-black text-sm tracking-widest uppercase shadow-xl shadow-indigo-100 flex items-center group active:scale-95 text-left font-sans text-left font-sans text-left font-sans">
                            <i class="fas fa-plus mr-2 text-xs group-hover:rotate-12 transition-transform text-left font-sans text-left font-sans text-left font-sans"></i>
                            Initialize New Workflow
                        </button>
                        <button @click="printView" class="w-12 h-12 bg-white border border-gray-200 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm group relative text-left font-sans text-left font-sans text-left font-sans">
                            <i class="fas fa-print text-sm text-left font-sans text-left font-sans text-left font-sans"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="px-8 py-10 grid grid-cols-1 md:grid-cols-3 gap-10 text-left font-sans text-left font-sans no-print">
                <div class="bg-indigo-900 p-10 rounded-[50px] shadow-2xl relative overflow-hidden group text-left font-sans text-left font-sans">
                    <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/10 rounded-full blur-3xl group-hover:bg-white/20 transition-all duration-700 text-left font-sans"></div>
                    <div class="relative z-10 text-left font-sans">
                        <div class="text-sm font-black text-indigo-400 uppercase tracking-[0.2em] mb-3 text-left font-sans">Active Logic Nodes</div>
                        <div class="text-5xl font-black text-white text-left font-sans">{{ (workflows || []).length }}</div>
                        <div class="mt-6 flex items-center gap-3 text-left font-sans text-left font-sans">
                             <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse text-left font-sans"></div>
                             <span class="text-sm font-black text-indigo-300 uppercase tracking-widest text-left font-sans">Global Grid Synchronized</span>
                        </div>
                    </div>
                </div>
                <div class="bg-white p-10 rounded-[50px] shadow-sm border border-gray-100 relative overflow-hidden group text-left font-sans text-left font-sans border-b-8 border-b-emerald-400">
                    <div class="relative z-10 text-left font-sans">
                        <div class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mb-3 text-left font-sans">Execution Velocity</div>
                        <div class="text-5xl font-black text-gray-900 text-left font-sans">Instant</div>
                        <p class="mt-6 text-sm font-black text-emerald-600 uppercase tracking-widest text-left font-sans text-left font-sans flex items-center items-center"><i class="fas fa-check-circle mr-2 text-left font-sans"></i> Optimal Throughput</p>
                    </div>
                </div>
                <div class="bg-white p-10 rounded-[50px] shadow-sm border border-gray-100 relative overflow-hidden group text-left font-sans text-left font-sans border-b-8 border-b-indigo-400">
                    <div class="relative z-10 text-left font-sans">
                        <div class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] mb-3 text-left font-sans">Engine Integrity</div>
                        <div class="text-5xl font-black text-gray-900 text-left font-sans">99.9<span class="text-indigo-500">%</span></div>
                        <p class="mt-6 text-sm font-black text-indigo-400 uppercase tracking-widest text-left font-sans text-left font-sans flex items-center items-center"><i class="fas fa-shield-alt mr-2 text-left font-sans"></i> Protocol Secured</p>
                    </div>
                </div>
            </div>

            <!-- Matrix Surface -->
            <div class="flex-1 overflow-auto px-8 pb-12 text-left font-sans text-left font-sans" id="print-area">
                <div class="bg-white rounded-[50px] shadow-sm border border-gray-100 overflow-hidden text-left font-sans text-left font-sans">
                     <table class="min-w-full divide-y divide-gray-100 text-left font-sans text-left font-sans">
                        <thead class="bg-gray-50/20 text-left font-sans text-left font-sans">
                            <tr>
                                <th class="px-12 py-8 text-left text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">Automation Schema</th>
                                <th class="px-12 py-8 text-center text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">Entity Target</th>
                                <th class="px-12 py-8 text-center text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">Operational Nodes</th>
                                <th class="px-12 py-8 text-right text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans">Control</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-left font-sans text-left font-sans">
                            <tr v-for="w in workflows" :key="w.id" class="hover:bg-indigo-50/10 transition-all group border-l-4 border-l-transparent hover:border-l-indigo-600 text-left font-sans">
                                <td class="px-12 py-10 text-left font-sans">
                                    <div class="flex items-center text-left font-sans text-left font-sans">
                                        <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-[24px] flex items-center justify-center shadow-inner group-hover:bg-indigo-600 group-hover:text-white transition-all mr-6 text-left font-sans text-left font-sans">
                                            <i class="fas fa-robot text-lg text-left font-sans text-left font-sans"></i>
                                        </div>
                                        <div class="text-left font-sans text-left font-sans">
                                            <div class="text-base font-black text-gray-900 text-left font-sans text-left font-sans">{{ w.name }}</div>
                                            <div class="text-sm font-bold text-gray-400 uppercase tracking-widest mt-1 text-left font-sans text-left font-sans">{{ w.description || 'System automation trigger' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-12 py-10 text-center text-left font-sans text-left font-sans">
                                    <span class="px-5 py-2.5 bg-indigo-900 text-white rounded-2xl text-sm font-black uppercase tracking-widest shadow-xl shadow-indigo-900/10 text-left font-sans text-left font-sans">
                                        {{ w.entity_type }} PROTOCOL
                                    </span>
                                </td>
                                <td class="px-12 py-10 text-center text-left font-sans text-left font-sans">
                                    <div class="flex items-center justify-center -space-x-4 text-left font-sans text-left font-sans">
                                        <div v-for="s in (w.stages || []).slice(0, 3)" :key="s.id" 
                                             class="w-9 h-9 rounded-xl border-2 border-white bg-indigo-50 flex items-center justify-center text-sm font-black text-indigo-600 shadow-sm"
                                             :title="s.name">
                                             {{ s.name.charAt(0) }}
                                        </div>
                                        <div v-if="(w.stages || []).length > 3" class="w-9 h-9 rounded-xl border-2 border-white bg-gray-900 flex items-center justify-center text-sm font-black text-white shadow-xl">
                                            +{{ w.stages.length - 3 }}
                                        </div>
                                        <div v-if="!(w.stages || []).length" class="text-sm font-black text-gray-300 uppercase tracking-widest opacity-40 text-left font-sans">Linear Flow</div>
                                    </div>
                                </td>
                                <td class="px-12 py-10 text-right text-left font-sans text-left font-sans">
                                     <button @click="manageWorkflow(w.id)" class="px-8 py-4 bg-white border border-gray-100 text-gray-900 rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-gray-900 hover:text-white transition-all shadow-sm active:scale-95 text-left font-sans text-left font-sans text-left font-sans">
                                        RECONFIGURE ENGINE
                                     </button>
                                </td>
                            </tr>
                            <tr v-if="!workflows || workflows.length === 0">
                                <td colspan="4" class="px-12 py-32 text-center text-left font-sans text-left font-sans">
                                    <div class="w-24 h-24 bg-white rounded-[40px] shadow-sm flex items-center justify-center mb-8 border border-gray-100 mx-auto text-left font-sans text-left font-sans text-left font-sans">
                                         <i class="fas fa-microchip text-3xl opacity-20 text-indigo-500 text-left font-sans text-left font-sans"></i>
                                    </div>
                                    <h3 class="text-xl font-black text-gray-900 tracking-tight text-left font-sans">Automation Void</h3>
                                    <p class="text-sm text-gray-400 font-bold uppercase tracking-widest mt-2 text-left font-sans">Initialize workflows to define your global event-driven logic.</p>
                                </td>
                            </tr>
                        </tbody>
                     </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

const props = defineProps({
    workflows: { type: Array, default: () => [] }
});

const createNewWorkflow = () => {
    router.visit(route('crm.workflows.index'));
};

const manageWorkflow = (id) => {
    router.visit(route('crm.workflows.index', { workflow: id }));
};

const printView = () => window.print();
</script>

<style scoped>
/* Custom Hide Scrollbar */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.05); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.1); }

@media print {
    #print-area { padding: 0 !important; }
    .no-print { display: none !important; }
}
</style>

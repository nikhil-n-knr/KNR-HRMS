<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import ProjectLayout from '@/Layouts/ProjectLayout.vue';
import ImpactMap from './Components/ImpactMap.vue';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const props = defineProps({
    project: Object,
    blockers: Array,
    pulse: Array,
    team: Array,
    incidents: Array, // Active Wars
    activeSprint: Object
});

const activeIncidentId = ref(null);
const activeTab = ref('overview'); // overview, impact, timeline, containment
const localGraphData = ref(null); // For Impact Map

const activeIncident = computed(() => {
    return activeIncidentId.value ? props.incidents.find(i => i.id === activeIncidentId.value) : null;
});

// Watch to reset graph when incident changes

watch(activeIncidentId, (newId) => { 
    localGraphData.value = null;
    if (newId) {
        const incident = props.incidents.find(i => i.id === newId);
        // In real app, we would fetch graph here.
        // For now, if it has 'data_impact_map' in DB, we could parse it, but we are mocking.
    }
});

const generateMockGraph = () => {
    // Simulate Intelligence Engine Analysis
    localGraphData.value = {
        nodes: [
            { id: 'threat', label: 'SQL Injection', type: 'threat', layer: 0, status: 'active' },
            { id: 'db_users', label: 'Users DB', type: 'asset', layer: 1, status: 'compromised' },
            { id: 'svc_auth', label: 'Auth Service', type: 'service', layer: 2, status: 'warning' },
            { id: 'svc_api', label: 'API Gateway', type: 'service', layer: 2, status: 'ok' },
            { id: 'data_pii', label: 'Customer PII', type: 'data', layer: 1, status: 'leaking' }
        ],
        links: [
            { source: 'threat', target: 'db_users' },
            { source: 'db_users', target: 'data_pii' },
            { source: 'db_users', target: 'svc_auth' },
            { source: 'svc_auth', target: 'svc_api' }
        ]
    };
};

const formatTime = (date) => dayjs(date).fromNow();

// Incident Form
const showDeclareModal = ref(false);
const incidentForm = useForm({
    title: '',
    type: 'Critical Issue',
    severity: 'High',
    description: ''
});

const submitIncident = () => {
    incidentForm.post(route('projects.incidents.store', props.project.id), {
        onSuccess: () => {
            showDeclareModal.value = false;
            incidentForm.reset();
        }
    });
};

const resolveIncident = () => {
    if(!activeIncident.value) return;
    if(confirm('Mark this incident as Resolved?')) {
        router.put(route('projects.incidents.update', { project: props.project.id, incident: activeIncident.value.id }), {
            status: 'Resolved',
            lifecycle_stage: 'Recovery'
        });
    }
};

const executeAction = (actionId) => {
    if(!activeIncident.value) return;
    if(confirm(`Execute protocol: ${actionId}? This will trigger backend systems.`)) {
        router.post(route('projects.incidents.execute', { 
            project: props.project.id, 
            incident: activeIncident.value.id 
        }), {
            action: actionId
        }, {
            onSuccess: () => {
                // Flash message handles feedback
            }
        });
    }
};

// Speedometer Logic
const sprintProgress = computed(() => {
    if (!props.activeSprint) return 0;
    const start = dayjs(props.activeSprint.start_date);
    const end = dayjs(props.activeSprint.end_date);
    const now = dayjs();
    const total = end.diff(start, 'day');
    if (total === 0) return 0;
    const passed = now.diff(start, 'day');
    return Math.min(Math.max((passed / total) * 100, 0), 100);
});
</script>

<template>
    <ProjectLayout :project="project">
        <div class="min-h-screen md:h-full flex flex-col md:flex-row bg-gray-900 text-white overflow-x-hidden">
            <!-- Sidebar: War Selection -->
            <div class="w-full md:w-64 bg-gray-800 border-b md:border-b-0 md:border-r border-gray-700 flex flex-col flex-shrink-0">
                <div class="p-4 border-b border-gray-700">
                    <h2 class="text-sm font-black uppercase tracking-widest text-gray-500 mb-2">Operations</h2>
                    <button @click="activeIncidentId = null" :class="{'bg-gray-700 text-white shadow-lg shadow-green-500/10': !activeIncidentId, 'text-gray-400 hover:text-white': activeIncidentId}" class="w-full text-left px-3 py-2.5 rounded-lg text-xs font-bold transition-all flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                        General Readiness
                    </button>
                    
                     <div class="mt-4 flex justify-between items-center mb-2 px-1">
                        <h2 class="text-sm font-black uppercase tracking-widest text-gray-500">Active Wars</h2>
                        <button @click="showDeclareModal = true" class="text-sm text-red-400 hover:text-red-300 font-black border border-red-500/30 px-2 py-0.5 rounded uppercase hover:bg-red-500/10 transition">+ Declare</button>
                     </div>
                     
                     <div class="space-y-1 overflow-y-auto max-h-48 md:max-h-none md:flex-1 custom-scrollbar pr-1">
                         <button v-for="war in incidents" :key="war.id" 
                            @click="activeIncidentId = war.id"
                            :class="{'bg-red-900/30 border border-red-500/40 text-white shadow-lg shadow-red-500/10': activeIncidentId === war.id, 'text-gray-400 hover:text-white hover:bg-gray-700/50 border border-transparent': activeIncidentId !== war.id}" 
                            class="w-full text-left px-3 py-2.5 rounded-lg text-xs font-medium transition-all group relative"
                        >
                            <div class="flex justify-between items-start mb-1">
                                <span class="text-sm font-black uppercase tracking-tighter" :class="war.severity === 'Severe' ? 'text-red-500' : 'text-orange-400'">{{ war.type }}</span>
                                <span class="text-sm bg-gray-900/50 px-1.5 rounded text-gray-500 font-mono">{{ war.lifecycle_stage }}</span>
                            </div>
                            <div class="truncate font-bold leading-tight uppercase tracking-tight">{{ war.title }}</div>
                         </button>
                         
                         <div v-if="incidents.length === 0" class="text-sm text-gray-600 px-2 py-4 text-center italic">
                             No active incidents detected.
                         </div>
                     </div>
                </div>
            </div>
            
            <!-- Main Content Area -->
            <div class="flex-1 flex flex-col overflow-hidden relative">
                
                <!-- General Readiness Dashboard (Original View) -->
                <div v-if="!activeIncidentId" class="flex-1 p-4 flex flex-col gap-4 overflow-hidden">
                    <!-- Standard Header -->
                     <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 bg-gray-800 p-4 rounded-xl border border-gray-700 shadow-lg">
                        <div class="flex items-center gap-4">
                             <div class="h-10 w-10 flex items-center justify-center bg-green-600 rounded-lg shadow-[0_0_15px_rgba(22,163,74,0.5)] flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                             </div>
                             <div class="min-w-0">
                                 <h2 class="text-lg md:text-xl font-black uppercase tracking-widest text-white truncate">System Secure</h2>
                                 <p class="text-sm text-gray-400 font-mono">Monitoring {{ project.code }} • Defcon 5</p>
                             </div>
                        </div>
                        
                        <!-- Speedometer -->
                        <div class="w-full md:flex-1 md:max-w-md md:mx-10">
                            <div class="flex justify-between text-sm text-gray-400 mb-1.5 font-mono uppercase tracking-widest">
                                <span>Sprint Time</span>
                                <span class="text-white font-bold">{{ Math.round(sprintProgress) }}%</span>
                            </div>
                            <div class="w-full bg-gray-700/50 h-1.5 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-blue-500 to-indigo-500 shadow-[0_0_10px_rgba(59,130,246,0.5)]" :style="{ width: `${sprintProgress}%` }"></div>
                            </div>
                        </div>

                        <!-- Actions -->
                         <div class="w-full md:w-auto">
                            <button class="w-full md:w-auto px-6 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-black uppercase tracking-widest rounded shadow-lg transition-all active:transform active:scale-95">Deploy</button>
                        </div>
                     </div>
                     
                     <!-- Original Grid -->
                     <div class="flex-1 grid grid-cols-1 lg:grid-cols-3 gap-4 min-h-0">
                         <div class="lg:col-span-2 flex flex-col gap-4 min-h-0">
                            <!-- Blockers -->
                            <div class="flex-1 bg-gray-800 rounded-xl border border-gray-700 p-4 flex flex-col min-h-0">
                                 <h3 class="text-red-500 font-black uppercase tracking-widest text-sm mb-4 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                                    Potential Threats ({{ blockers.length }})
                                 </h3>
                                 <div v-if="blockers.length === 0" class="flex-1 flex items-center justify-center flex-col text-gray-500 opacity-50">
                                     <span class="text-6xl mb-2">🛡️</span>
                                     <span class="text-xl font-bold">No Active Threats</span>
                                 </div>
                                 <div v-else class="flex-1 overflow-y-auto space-y-2 pr-2 custom-scrollbar">
                                     <div v-for="task in blockers" :key="task.id" class="bg-gray-900 border-l-4 border-red-500 p-3 rounded shadow hover:bg-gray-700 transition flex justify-between items-start group cursor-pointer">
                                         <div>
                                             <div class="text-xs text-red-400 font-bold uppercase mb-0.5">{{ task.priority }} • {{ task.stage?.name }}</div>
                                             <h4 class="text-white font-bold group-hover:text-red-300 transition-colors">{{ task.title }}</h4>
                                         </div>
                                         <button class="text-xs bg-red-500/10 text-red-500 px-2 py-1 rounded border border-red-500/20 hover:bg-red-500 hover:text-white transition">Investigate</button>
                                     </div>
                                 </div>
                            </div>
                            
                            <!-- Team -->
                            <div class="bg-gray-800 rounded-xl border border-gray-700 p-4">
                                <h3 class="text-blue-400 font-black uppercase tracking-widest text-sm mb-4">Active Personnel</h3>
                                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                                    <div v-for="member in team" :key="member.id" class="bg-gray-900 p-2 rounded-lg border border-gray-700 flex items-center gap-3 relative overflow-hidden group">
                                        <span v-if="member.is_online" class="absolute top-0 right-0 w-2.5 h-2.5 bg-green-500 rounded-bl-md shadow-[0_0_8px_rgba(34,197,94,0.6)]"></span>
                                        <img :src="member.profile_photo_url" class="h-8 w-8 rounded-full bg-gray-700 object-cover ring-1 ring-gray-800">
                                        <div class="min-w-0">
                                            <p class="text-white text-sm font-black truncate uppercase tracking-tighter">{{ member.name }}</p>
                                            <p class="text-sm text-gray-500 truncate mt-0.5">
                                                <span v-if="member.current_task" class="text-indigo-400 font-bold">{{ member.current_task.code }}</span>
                                                <span v-else class="text-gray-600">Standby</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                         </div>
                         
                         <!-- Pulse -->
                         <div class="bg-gray-800 rounded-xl border border-gray-700 p-0 flex flex-col overflow-hidden min-h-0">
                             <div class="p-4 border-b border-gray-700 bg-gray-800 z-10">
                                <h3 class="text-purple-400 font-black uppercase tracking-widest text-sm">System Pulse</h3>
                             </div>
                             <div class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar bg-gray-900/50">
                                 <div v-for="act in pulse" :key="act.id" class="flex gap-3 text-sm animate-fade-in">
                                     <div class="flex flex-col items-center">
                                         <div class="w-2 h-2 rounded-full bg-purple-500 mt-1.5 shadow-[0_0_8px_rgba(168,85,247,0.8)]"></div>
                                         <div class="flex-1 w-px bg-gray-700 my-1"></div>
                                     </div>
                                     <div class="pb-4 border-b border-gray-800 w-full">
                                         <div class="flex justify-between items-baseline mb-1">
                                             <span class="font-bold text-gray-300">{{ act.user?.name || 'System' }}</span>
                                             <span class="text-xs text-gray-600 font-mono">{{ formatTime(act.created_at) }}</span>
                                         </div>
                                         <p class="text-gray-400 leading-snug">
                                             <span class="text-purple-300 uppercase text-sm font-bold tracking-wider mr-1">{{ act.type }}</span>
                                             <span class="text-gray-500" v-if="act.task">on <span class="text-gray-300 hover:text-white cursor-pointer hover:underline">{{ act.task.title }}</span></span>
                                         </p>
                                     </div>
                                 </div>
                             </div>
                        </div>
                     </div>
                </div>
                
                <!-- Active Incident View (Crisis Mode) -->
                <div v-else class="flex-1 flex flex-col overflow-hidden bg-red-950/20">
                     <!-- Crisis Header -->
                     <div class="p-6 border-b border-red-900/30 bg-red-900/10 flex justify-between items-start">
                         <div>
                             <div class="flex items-center gap-3 mb-2">
                                 <span class="px-2 py-1 bg-red-600 text-white text-xs font-black uppercase tracking-wider rounded animate-pulse">
                                     {{ activeIncident.severity }} Severity
                                 </span>
                                 <span class="text-red-400 font-mono text-xs uppercase tracking-widest">
                                     STAGE: {{ activeIncident.lifecycle_stage }}
                                 </span>
                             </div>
                             <h1 class="text-3xl font-black text-white leading-tight uppercase">{{ activeIncident.title }}</h1>
                             <p class="text-red-300/70 mt-1 max-w-2xl">{{ activeIncident.description }}</p>
                         </div>
                         <div class="flex gap-4">
                             <div v-if="activeIncident.resolved_at" class="text-right">
                                 <div class="text-green-500 font-bold uppercase tracking-widest">Resolved</div>
                                 <div class="text-gray-500 text-xs font-mono">{{ formatTime(activeIncident.resolved_at) }}</div>
                             </div>
                             <button v-else @click="resolveIncident" class="px-6 py-3 bg-green-600 hover:bg-green-500 text-white font-bold uppercase rounded shadow-lg shadow-green-900/20 transition-all active:transform active:scale-95">
                                 Mark Resolved
                             </button>
                         </div>
                     </div>
                     
                     <!-- Smart Tabs Header -->
                     <div class="px-6 border-b border-red-900/30 flex gap-6 text-sm font-bold uppercase tracking-wider text-gray-500 bg-black/20">
                         <button @click="activeTab = 'overview'" :class="{'text-white border-b-2 border-red-500': activeTab === 'overview', 'hover:text-gray-300': activeTab !== 'overview'}" class="py-3 px-1 transition-all">Situation Report</button>
                         <button @click="activeTab = 'impact'" :class="{'text-white border-b-2 border-red-500': activeTab === 'impact', 'hover:text-gray-300': activeTab !== 'impact'}" class="py-3 px-1 transition-all">Impact Map</button>
                         <button @click="activeTab = 'containment'" :class="{'text-white border-b-2 border-red-500': activeTab === 'containment', 'hover:text-gray-300': activeTab !== 'containment'}" class="py-3 px-1 transition-all">Containment Plan</button>
                         <button @click="activeTab = 'timeline'" :class="{'text-white border-b-2 border-red-500': activeTab === 'timeline', 'hover:text-gray-300': activeTab !== 'timeline'}" class="py-3 px-1 transition-all">Timeline</button>
                     </div>
                     
                     <!-- Smart Tabs Content -->
                     <div class="flex-1 p-6 overflow-y-auto custom-scrollbar">
                         
                         <!-- 1. Situation Report -->
                         <div v-if="activeTab === 'overview'" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                             <div class="bg-black/30 p-6 rounded-xl border border-red-900/30">
                                 <h3 class="text-red-400 font-bold uppercase tracking-widest mb-4">Incident Details</h3>
                                 <div class="space-y-4 text-sm">
                                     <div class="flex justify-between border-b border-white/5 pb-2">
                                         <span class="text-gray-500">Declared By</span>
                                         <span class="text-white">{{ activeIncident.declared_by }}</span> 
                                         <!-- ideally resolved via relation/prop -->
                                     </div>
                                     <div class="flex justify-between border-b border-white/5 pb-2">
                                         <span class="text-gray-500">Incident Type</span>
                                         <span class="text-white">{{ activeIncident.type }}</span>
                                     </div>
                                      <div class="flex justify-between border-b border-white/5 pb-2">
                                         <span class="text-gray-500">Start Time</span>
                                         <span class="text-white">{{ formatTime(activeIncident.created_at) }}</span>
                                     </div>
                                 </div>
                             </div>
                             
                             <div class="bg-black/30 p-6 rounded-xl border border-red-900/30">
                                  <h3 class="text-blue-400 font-bold uppercase tracking-widest mb-4">Response Team</h3>
                                  <div class="flex -space-x-3">
                                      <!-- Mock Team -->
                                      <div class="h-12 w-12 rounded-full bg-gray-800 border-2 border-gray-900 flex items-center justify-center text-xs font-bold text-gray-500">?</div>
                                  </div>
                                  <button class="mt-6 w-full py-2 bg-indigo-600/20 text-indigo-400 border border-indigo-500/30 rounded uppercase text-xs font-bold hover:bg-indigo-600/30 transition">Assign Specialists</button>
                             </div>
                         </div>
                         
                         <!-- 2. Impact Map -->
                         <div v-else-if="activeTab === 'impact'" class="h-full flex flex-col">
                             <div class="flex justify-between items-center mb-4 px-2">
                                 <div>
                                     <h3 class="text-white font-bold text-lg">Cross-System Impact Analysis</h3>
                                     <p class="text-xs text-gray-400">Visualizing blast radius of the current incident.</p>
                                 </div>
                                 <div class="flex gap-2 text-xs">
                                     <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span> Threat</span>
                                     <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-500"></span> Asset</span>
                                     <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-purple-500"></span> Service</span>
                                 </div>
                             </div>
                             
                             <div class="flex-1 min-h-0 bg-black/40 rounded-xl border border-gray-800 overflow-hidden relative">
                                 <ImpactMap v-if="localGraphData" :graph="localGraphData" />
                                 <div v-else class="h-full flex items-center justify-center flex-col text-gray-500">
                                     <div class="text-4xl mb-2 opacity-50">📡</div>
                                     <p>No impact graph data available.</p>
                                     <button class="mt-2 text-xs text-indigo-400 hover:text-white underline" @click="generateMockGraph">Run Simulation</button>
                                 </div>
                             </div>
                         </div>
                         
                         <!-- 3. Containment -->
                         <div v-else-if="activeTab === 'containment'" class="space-y-4">
                             <div class="flex items-center gap-4 p-4 bg-indigo-900/20 border-l-4 border-indigo-500 rounded-r-lg">
                                 <div class="h-6 w-6 rounded-full border-2 border-indigo-500 flex items-center justify-center text-indigo-500 font-bold text-xs">1</div>
                                 <div class="flex-1">
                                     <h4 class="text-white font-bold">Isolate Affected Systems</h4>
                                     <p class="text-sm text-indigo-300">Disable network traffic to DB-04 and Web-02.</p>
                                 </div>
                                 <button @click="executeAction('isolate_system')" class="px-3 py-1 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded transition active:scale-95">Execute</button>
                             </div>
                              <div class="flex items-center gap-4 p-4 bg-gray-800/50 border-l-4 border-gray-600 rounded-r-lg opacity-75 hover:opacity-100 transition">
                                 <div class="h-6 w-6 rounded-full border-2 border-gray-600 flex items-center justify-center text-gray-500 font-bold text-xs">2</div>
                                 <div class="flex-1">
                                     <h4 class="text-gray-300 font-bold">Rotate Credentials</h4>
                                     <p class="text-sm text-gray-500">Invalidate current AWS keys and DB passwords.</p>
                                 </div>
                                 <button @click="executeAction('rotate_credentials')" class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-white text-xs font-bold rounded transition active:scale-95">Execute</button>
                             </div>
                         </div>
                         
                         <!-- 4. Timeline -->
                         <div v-else class="relative space-y-8 pl-8 before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-gray-700">
                             <div class="relative">
                                 <div class="absolute -left-10 mt-1 h-4 w-4 rounded-full bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.5)]"></div>
                                 <div class="text-sm text-gray-500 font-mono mb-1">{{ formatTime(activeIncident.created_at) }}</div>
                                 <div class="bg-gray-800 p-4 rounded-lg border border-gray-700">
                                     <span class="text-red-400 font-bold uppercase text-xs">Detection</span>
                                     <p class="text-white mt-1">Incident Declared by Command.</p>
                                 </div>
                             </div>
                         </div>
                         
                     </div>
                </div>
            </div>
            
            <!-- Declare Modal -->
            <Modal :show="showDeclareModal" @close="showDeclareModal = false">
                <div class="p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">Declare New War</h2>
                    <div class="space-y-4">
                        <BaseInput v-model="incidentForm.title" label="Incident Title" placeholder="e.g. Production DB Latency" />
                        <div class="grid grid-cols-2 gap-4">
                            <BaseSelect v-model="incidentForm.type" label="Type">
                                <option>Critical Issue</option>
                                <option>Security Breach</option>
                                <option>Data Loss</option>
                                <option>Performance Degradation</option>
                            </BaseSelect>
                            <BaseSelect v-model="incidentForm.severity" label="Severity">
                                <option>High</option>
                                <option>Critical</option>
                                <option>Severe</option>
                            </BaseSelect>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea v-model="incidentForm.description" rows="3" class="w-full border-gray-300 rounded shadow-sm focus:ring-indigo-500 focus:border-indigo-500"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-3">
                        <button @click="showDeclareModal = false" class="text-gray-500 px-4">Cancel</button>
                        <PrimaryButton @click="submitIncident" :disabled="incidentForm.processing" class="bg-red-600 hover:bg-red-700 border-transparent">DECLARE WAR</PrimaryButton>
                    </div>
                </div>
            </Modal>
        </div>
    </ProjectLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #1f2937; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 10px; }
.animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
</style>

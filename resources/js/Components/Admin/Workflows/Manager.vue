<template>
    <div class="animate-fade-in pb-24 font-outfit">
        <!-- Studio Header -->
        <div class="h-14 bg-white/80 backdrop-blur-md rounded-xl border border-slate-200 p-2 shadow-sm flex flex-col md:flex-row justify-between items-center mb-6 gap-4 text-[10px]">
            <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-lg">
                <button 
                    @click="currentView = 'studio'"
                    :class="currentView === 'studio' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400 hover:text-slate-600'"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-md transition-all duration-300"
                >
                    <i class="fas fa-project-diagram text-[11px]"></i>
                    <span class="font-bold uppercase tracking-tight">Workflow Studio</span>
                </button>
                <button 
                    @click="currentView = 'architect'"
                    :class="currentView === 'architect' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400 hover:text-slate-600'"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-md transition-all duration-300"
                >
                    <i class="fas fa-pencil-ruler text-[11px]"></i>
                    <span class="font-bold uppercase tracking-tight">Pipeline Architect</span>
                </button>
                <button 
                    @click="currentView = 'pulse'"
                    :class="currentView === 'pulse' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-400 hover:text-slate-600'"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-md transition-all duration-300"
                >
                    <i class="fas fa-chart-line text-[11px]"></i>
                    <span class="font-bold uppercase tracking-tight">Intelligence Pulse</span>
                </button>
            </div>
             
            <div class="flex items-center gap-2">
                <button @click="openCreateWorkflow()" class="flex items-center gap-2 px-3 py-1.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-all shadow-sm shadow-emerald-200 group">
                    <i class="fas fa-plus text-[10px] group-hover:rotate-90 transition-transform"></i>
                    <span class="font-bold uppercase tracking-wider">New Pipeline</span>
                </button>
                <div class="h-6 w-px bg-slate-200"></div>
                <button @click="showInitModal = true" class="flex items-center gap-2 px-3 py-1.5 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition-all border border-slate-200">
                    <i class="fas fa-bolt text-[10px] text-amber-500"></i>
                    <span class="font-bold uppercase tracking-wider">Initialize</span>
                </button>
            </div>
        </div>

        <!-- Main Viewport -->
        <div v-if="currentView === 'studio'">
            <!-- Empty State -->
            <div v-if="!loading && (!localWorkflows || localWorkflows.length === 0)" class="p-12 text-center bg-white rounded-xl border-2 border-dashed border-slate-200 max-w-2xl mx-auto">
            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-200">
                <i class="fas fa-wind text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2 uppercase tracking-tight">No Procedures Active</h3>
            <p class="text-[11px] text-slate-400 font-bold uppercase tracking-widest mb-8 max-w-xs mx-auto">Architect your first organizational procedure to begin synchronization.</p>
            <button @click="showInitModal = true" class="px-8 py-3 bg-emerald-600 text-white font-bold text-[10px] uppercase tracking-widest rounded-lg hover:bg-emerald-700 shadow-lg shadow-emerald-500/10 transition-all">
                Initialize Systems
            </button>
        </div>

        <!-- Workflow List -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div v-for="flow in localWorkflows" :key="flow.id" class="bg-white rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-all overflow-hidden flex flex-col group">
                <!-- Card Header -->
                <div class="px-4 py-3 border-b border-slate-50 flex justify-between items-center bg-slate-50/30 shrink-0">
                    <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-white flex items-center justify-center text-emerald-500 shadow-sm border border-slate-100 group-hover:rotate-6 transition-transform">
                            <i class="fas fa-project-diagram text-[11px]"></i>
                        </div>
                        <div>
                            <h3 class="text-[11px] font-bold text-slate-800 uppercase tracking-tight leading-none mb-1 line-clamp-1">{{ flow.name }}</h3>
                            <div class="flex items-center gap-2">
                                <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">{{ flow.entity_type.replace('_', ' ') }}</span>
                                <span v-if="!flow.is_active" class="px-1.5 py-0.5 bg-slate-100 text-slate-400 text-[7px] font-bold uppercase tracking-widest rounded">Draft</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-1">
                        <button @click="openEditWorkflow(flow)" class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-emerald-600 hover:border-emerald-100 shadow-sm transition-all hover:scale-105 active:scale-95" title="Calibrate Logic">
                            <i class="fas fa-sliders-h text-[9px]"></i>
                        </button>
                        <button @click="initiateDelete(flow, 'workflow')" class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-400 hover:text-rose-500 hover:border-rose-100 shadow-sm transition-all hover:scale-105 active:scale-95" title="Terminate Node">
                            <i class="fas fa-trash-alt text-[9px]"></i>
                        </button>
                    </div>
                </div>

                <!-- Stages Visual Chain -->
                <div class="p-4 flex-1 space-y-3 relative overflow-hidden">
                    <div v-if="flow.stages && flow.stages.length > 0" class="flex flex-col gap-2 relative">
                        <!-- Connector Trace -->
                        <div class="absolute left-[13px] top-4 bottom-4 w-px bg-slate-100"></div>

                        <div v-for="(stage, idx) in flow.stages" :key="stage.id" class="relative z-10 flex items-center gap-3 group/stage">
                            <!-- Node -->
                            <div class="w-7 h-7 shrink-0 rounded-lg bg-white border border-slate-200 text-slate-600 font-bold text-[9px] flex items-center justify-center shadow-sm group-hover/stage:border-emerald-300 group-hover/stage:text-emerald-600 transition-all duration-300">
                                {{ idx + 1 }}
                            </div>
                            
                            <!-- Node Info -->
                            <div class="flex-1 bg-slate-50/50 border border-slate-100 rounded-lg px-2.5 py-1.5 transition-all hover:bg-white hover:border-emerald-100 hover:shadow-sm cursor-pointer flex justify-between items-center" @click="openEditStage(flow, stage)">
                                <div>
                                    <h4 class="text-[10px] font-bold text-slate-700 uppercase tracking-tight leading-none mb-0.5">{{ stage.name }}</h4>
                                    <span class="text-[7px] font-bold text-slate-400 uppercase tracking-widest block">{{ stage.approver_type }}</span>
                                </div>
                                <div class="flex gap-1 opacity-0 group-hover/stage:opacity-100 transition-opacity">
                                    <button @click.stop="moveStage(flow, stage, 'up')" v-if="idx > 0" class="w-5 h-5 rounded-md bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 flex items-center justify-center"><i class="fas fa-chevron-up text-[7px]"></i></button>
                                    <button @click.stop="moveStage(flow, stage, 'down')" v-if="idx < flow.stages.length - 1" class="w-5 h-5 rounded-md bg-white border border-slate-200 text-slate-400 hover:text-emerald-600 flex items-center justify-center"><i class="fas fa-chevron-down text-[7px]"></i></button>
                                    <button @click.stop="initiateDelete(stage, 'stage')" class="w-5 h-5 rounded-md bg-white border border-slate-200 text-rose-300 hover:text-rose-600 flex items-center justify-center"><i class="fas fa-times text-[7px]"></i></button>
                                </div>
                            </div>
                        </div>

                        <!-- Add Stage Button -->
                        <div class="relative z-10 flex items-center gap-3 pl-[12px]">
                            <button @click="openAddStage(flow)" class="w-2.5 h-2.5 rounded-full bg-emerald-500 border border-white shadow-sm ring-2 ring-emerald-50 flex items-center justify-center text-white hover:scale-110 active:scale-95 transition-all group/add">
                                <i class="fas fa-plus text-[4px]"></i>
                                <span class="absolute left-5 whitespace-nowrap text-[8px] font-bold text-emerald-500 uppercase tracking-widest opacity-0 group-hover/add:opacity-100 transition-opacity">Add Objective</span>
                            </button>
                        </div>
                    </div>
                    <div v-else class="h-24 flex flex-col items-center justify-center text-center">
                        <i class="fas fa-code-branch text-slate-100 text-xl mb-2"></i>
                        <span class="text-[9px] font-bold text-slate-300 uppercase tracking-widest">Awaiting Nodes</span>
                        <button @click="openAddStage(flow)" class="mt-2 text-[8px] font-bold text-emerald-500 uppercase tracking-widest hover:underline">Deploy First Node</button>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-4 py-2.5 bg-slate-50/50 border-t border-slate-50 flex justify-between items-center group-hover:bg-emerald-50/30 transition-colors shrink-0">
                    <div class="flex gap-4">
                        <div class="flex items-center gap-1.5 text-[8px] font-bold text-slate-400 uppercase tracking-widest">
                            <i class="fas fa-check-circle text-emerald-400"></i>
                            {{ flow.approved_status || 'Approved' }}
                        </div>
                        <div class="flex items-center gap-1.5 text-[8px] font-bold text-slate-400 uppercase tracking-widest">
                            <i class="fas fa-times-circle text-rose-400"></i>
                            {{ flow.rejected_status || 'Rejected' }}
                        </div>
                    </div>
                    <i class="fas fa-shield-alt text-slate-200 text-[10px]"></i>
                </div>
            </div>
            </div>
        </div>

        <!-- Intelligence Pulse View -->
        <div v-else-if="currentView === 'pulse'" class="animate-content-fade">
             <PulseView :workflows="localWorkflows" />
        </div>

        <!-- Pipeline Architect (Integrated View) -->
        <div v-else-if="currentView === 'architect'" class="animate-content-fade p-12 text-center bg-white rounded-xl border border-slate-200">
             <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6 text-emerald-500 shadow-inner">
                <i class="fas fa-pencil-ruler text-2xl"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2 uppercase tracking-tight">Pipeline Architect</h3>
            <p class="text-[11px] text-slate-400 font-bold uppercase tracking-widest mb-4">Select a procedure from the studio to begin deep-mapping its logical nodes.</p>
            <button @click="currentView = 'studio'" class="text-[10px] font-black text-emerald-600 uppercase tracking-widest hover:underline">Return to Studio</button>
        </div>

        <!-- Pipeline Architect Modal -->
        <PremiumModal 
            :show="showWorkflowForm" 
            @close="showWorkflowForm = false" 
            :title="workflowForm.id ? 'Modify Procedure' : 'Architect Procedure'" 
            subtitle="Global Logic Deployment"
            icon="fa-route"
            maxWidth="xl"
        >
            <div class="space-y-6">
                <div>
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Pipeline Designation</label>
                    <input v-model="workflowForm.name" type="text" placeholder="e.g. ALPHA TIMESHEET APPROVAL" class="w-full h-10 rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-0 font-bold bg-slate-50 text-[11px] uppercase tracking-tight px-4 shadow-sm" />
                </div>

                <div>
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Procedural Description</label>
                    <input v-model="workflowForm.description" type="text" placeholder="Execution logic details..." class="w-full h-10 rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-0 font-bold bg-slate-50 text-[11px] uppercase tracking-tight px-4 shadow-sm" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Entity Type</label>
                        <select v-model="workflowForm.entity_type" class="w-full h-10 rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-0 font-bold bg-slate-50 text-[11px] uppercase tracking-tight px-4 shadow-sm">
                            <option value="timesheet">TIMESHEET</option>
                            <option value="leave_request">LEAVE REQUEST</option>
                            <option value="attendance_regularization">ATTENDANCE REG.</option>
                            <option value="expense">EXPENSE</option>
                            <option value="payroll">PAYROLL</option>
                            <option value="asset_request">ASSET REQUEST</option>
                            <option value="shift_swap">SHIFT SWAP</option>
                            <option value="generic">GENERIC/OTHER</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Trigger Event</label>
                        <select v-model="workflowForm.trigger_event" class="w-full h-10 rounded-lg border-slate-200 focus:border-emerald-500 focus:ring-0 font-bold bg-slate-50 text-[11px] uppercase tracking-tight px-4 shadow-sm">
                            <option value="on_submit">ON_SUBMIT</option>
                            <option value="manual">MANUAL</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Operational State</label>
                        <div class="flex items-center gap-3 h-10 px-4 bg-slate-50 rounded-lg border border-slate-200">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="workflowForm.is_active" class="sr-only peer">
                                <div class="w-8 h-4 bg-slate-200 rounded-full peer peer-checked:bg-emerald-500 transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:after:translate-x-4"></div>
                            </label>
                            <span class="text-[9px] font-bold text-slate-600 uppercase tracking-widest">{{ workflowForm.is_active ? 'Online' : 'Draft Mode' }}</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Commit Status</label>
                        <input v-model="workflowForm.approved_status" type="text" class="w-full h-9 rounded-lg border-slate-200 font-bold text-[11px] bg-slate-50 px-4 text-emerald-600 uppercase tracking-widest" />
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Terminated Status</label>
                        <input v-model="workflowForm.rejected_status" type="text" class="w-full h-9 rounded-lg border-slate-200 font-bold text-[11px] bg-slate-50 px-4 text-rose-600 uppercase tracking-widest" />
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100 font-bold text-[9px] uppercase tracking-widest">
                    <button @click="showWorkflowForm = false" class="px-6 py-2.5 text-slate-400 hover:text-slate-600 transition-colors">Abort</button>
                    <button @click="submitWorkflow" :disabled="workflowForm.processing" class="px-8 py-2.5 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-all disabled:opacity-50 shadow-md">
                        {{ workflowForm.id ? 'Commit Procedure' : 'Deploy Pipeline' }}
                    </button>
                </div>
            </div>
        </PremiumModal>

        <!-- Stage Architect Modal -->
        <PremiumModal 
            :show="showStageModal" 
            @close="showStageModal = false" 
            :title="stageForm.id ? 'Refine Objective' : 'Deploy Node'" 
            subtitle="Stage Parametric Configuration"
            icon="fa-layer-group"
            maxWidth="xl"
        >
            <div class="space-y-6">
                <div>
                    <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Objective Title</label>
                    <input v-model="stageForm.name" type="text" placeholder="e.g. FINANCIAL CLEARANCE" class="w-full h-10 rounded-lg border-slate-200 focus:border-indigo-500 focus:ring-0 font-bold bg-slate-50 text-[11px] uppercase tracking-tight px-4 shadow-sm" />
                </div>

                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 shadow-inner">
                    <div class="flex items-center gap-2 mb-4">
                        <i class="fas fa-user-circle text-indigo-400 text-xs"></i>
                        <h4 class="text-[9px] font-bold text-slate-600 uppercase tracking-widest leading-none">Primary Approver Protocol</h4>
                    </div>
                    <div class="space-y-4">
                        <select v-model="stageForm.approver_type" class="w-full h-9 rounded-lg border-slate-200 focus:ring-0 font-bold bg-white shadow-sm px-4 text-[11px] uppercase tracking-tight">
                            <option value="manager">EMPLOYEE'S MANAGER</option>
                            <option value="team_lead">TEAM LEAD</option>
                            <option value="role">SPECIFIC ROLE (HR/EXECUTIVE)</option>
                            <option value="specific_user">SPECIFIC AGENT</option>
                            <option value="team">SPECIFIC NODE (TEAM)</option>
                            <option value="department">SPECIFIC SECTOR (DEPT)</option>
                        </select>
                        
                        <!-- Contextual Inputs -->
                        <div v-if="stageForm.approver_type === 'role'" class="animate-content-fade">
                            <select v-model="stageForm.role_id" class="w-full h-9 rounded-lg border-slate-200 font-bold bg-white shadow-sm px-4 text-[11px] uppercase tracking-tight">
                                <option :value="null">-- SELECT ROLE --</option>
                                <option v-for="role in localRoles" :key="role.id" :value="role.id">{{ role.name }}</option>
                            </select>
                        </div>
                        <div v-if="stageForm.approver_type === 'specific_user'" class="animate-content-fade">
                            <UserSearchInput v-model="stageForm.user_id" :initial-label="stageForm.user_label" placeholder="Scan identification tag..." class="!h-9 !rounded-lg" />
                        </div>
                        <div v-if="stageForm.approver_type === 'team'" class="animate-content-fade">
                            <select v-model="stageForm.team_id" class="w-full h-9 rounded-lg border-slate-200 font-bold bg-white shadow-sm px-4 text-[11px] uppercase tracking-tight">
                                <option :value="null">-- SELECT TEAM --</option>
                                <option v-for="team in localTeams" :key="team.id" :value="team.id">{{ team.name }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Multi-Approver Matrix -->
                <div class="bg-indigo-50/20 p-4 rounded-xl border border-indigo-100 shadow-inner">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-users text-indigo-400 text-xs"></i>
                            <h4 class="text-[9px] font-bold text-indigo-600 uppercase tracking-widest leading-none">Co-Approver Matrix</h4>
                        </div>
                        <span class="text-[7px] font-bold text-indigo-300 uppercase tracking-widest bg-white px-2 py-0.5 rounded border border-indigo-100">Optional</span>
                    </div>

                    <!-- Existing Matrix -->
                    <div v-if="stageForm.additional_approvers.length > 0" class="flex flex-wrap gap-2 mb-4">
                        <div v-for="(approver, idx) in stageForm.additional_approvers" :key="idx" class="bg-white border border-indigo-100 text-indigo-700 px-3 py-1.5 rounded-lg text-[9px] font-bold uppercase tracking-widest flex items-center gap-2 shadow-sm group">
                            <span class="text-[7px] opacity-40">{{ approver.type }}</span>
                            {{ approver.label }}
                            <button @click="removeAdditionalApprover(idx)" class="text-indigo-200 hover:text-rose-500"><i class="fas fa-times-circle"></i></button>
                        </div>
                    </div>

                    <div class="flex items-end gap-2">
                        <div class="flex-1">
                            <div v-if="tempApprover.type === 'specific_user'">
                                <UserSearchInput @selected="addAdditionalUser" placeholder="Select Unit..." class="!h-9 !rounded-lg" />
                            </div>
                            <select v-else @change="addAdditionalTeam($event.target.value)" class="w-full h-9 rounded-lg border-slate-200 font-bold bg-white shadow-sm px-4 text-[11px] uppercase tracking-tight">
                                <option value="">SCAN NODE (TEAM)...</option>
                                <option v-for="t in localTeams" :key="t.id" :value="t.id">{{ t.name }}</option>
                            </select>
                        </div>
                        <div class="flex gap-1 bg-white p-1 rounded-lg shadow-sm border border-slate-100">
                            <button @click="tempApprover.type = 'specific_user'" :class="tempApprover.type === 'specific_user' ? 'bg-indigo-500 text-white' : 'text-slate-400 hover:text-slate-600'" class="w-8 h-8 rounded-md flex items-center justify-center transition-all">
                                <i class="fas fa-user text-[10px]"></i>
                            </button>
                            <button @click="tempApprover.type = 'team'" :class="tempApprover.type === 'team' ? 'bg-indigo-500 text-white' : 'text-slate-400 hover:text-slate-600'" class="w-8 h-8 rounded-md flex items-center justify-center transition-all">
                                <i class="fas fa-users text-[10px]"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-2 px-1">Decisional Logic</label>
                            <select v-model="stageForm.approval_strategy" class="w-full h-9 rounded-lg border-slate-200 font-bold bg-slate-50 px-4 text-[10px] uppercase tracking-widest">
                                <option value="all_must_approve">CONSENSUS (ALL)</option>
                                <option value="any_can_approve">FIRST RESPONDER (ANY)</option>
                            </select>
                        </div>
                        <div class="flex items-center gap-3 pt-6 px-1">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" v-model="stageForm.allow_self_approval" class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 rounded-full peer peer-checked:bg-indigo-500 transition-colors after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4"></div>
                            </label>
                            <span class="text-[9px] font-bold text-slate-600 uppercase tracking-widest">Allow Self-Authorization</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100 font-bold text-[9px] uppercase tracking-widest">
                    <button @click="showStageModal = false" class="px-6 py-2.5 text-slate-400 hover:text-slate-600 transition-colors">Abort</button>
                    <button @click="submitStage" :disabled="stageForm.processing" class="px-8 py-2.5 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition-all disabled:opacity-50 shadow-md">
                        {{ stageForm.id ? 'Commit Protocol' : 'Deploy Node' }}
                    </button>
                </div>
            </div>
        </PremiumModal>

        <!-- Deletion Probe Modal -->
        <PremiumModal 
            :show="showDeleteConfirm" 
            @close="showDeleteConfirm = false" 
            title="Termination Sequence" 
            subtitle="Logical Node Deletion"
            icon="fa-exclamation-triangle"
            maxWidth="md"
        >
            <div class="text-center">
                <p class="text-[11px] text-slate-500 mb-8 font-bold uppercase leading-relaxed max-w-xs mx-auto">
                    Initiating termination of <span class="text-rose-600 underline">{{ deleteType }}</span> node.<br/>Procedural data will be unrecoverable.
                </p>
                <div class="flex gap-3 font-bold text-[9px] uppercase tracking-widest">
                    <button @click="showDeleteConfirm = false" class="flex-1 px-6 py-2.5 text-slate-400 hover:text-slate-600">Abort</button>
                    <button @click="executeDelete" class="flex-1 px-8 py-2.5 bg-rose-600 text-white rounded-lg hover:bg-rose-700 shadow-md transition-all active:scale-95">Terminate</button>
                </div>
            </div>
        </PremiumModal>

        <!-- Core System Initialization Modal -->
        <PremiumModal 
            :show="showInitModal" 
            @close="showInitModal = false" 
            title="Core System Sync" 
            subtitle="Standard Architecture Deployment"
            icon="fa-bolt"
            maxWidth="md"
        >
            <div class="text-center">
                <p class="text-[11px] text-slate-500 mb-8 font-bold uppercase leading-relaxed max-w-xs mx-auto">
                    Synchronize standard approval architectures for Timesheets and Attendance operations?
                </p>
                <div class="flex gap-3 font-bold text-[9px] uppercase tracking-widest">
                    <button @click="showInitModal = false" class="flex-1 px-6 py-2.5 text-slate-400 hover:text-slate-600">Cancel</button>
                    <button @click="confirmInit" class="flex-1 px-8 py-2.5 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 shadow-md transition-all">Sync Now</button>
                </div>
            </div>
        </PremiumModal>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import PremiumModal from '@/Components/PremiumModal.vue';
import { useToastStore } from '@/stores/toast';
import UserSearchInput from '@/Components/Common/UserSearchInput.vue';
import PulseView from './PulseView.vue';

const toast = useToastStore();

const props = defineProps({
    workflows: { type: Array, default: () => [] },
    needsInit: { type: Boolean, default: true },
    roles: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    teams: { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] }
});

// State
const localWorkflows = ref(props.workflows || []);
const needsInit = ref((props.workflows || []).length === 0);
const localRoles = ref(props.roles || []);
const localTeams = ref(props.teams || []);
const localDepartments = ref(props.departments || []);
const loading = ref(false);
const currentView = ref('studio');

const fetchWorkflows = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/admin/attendance/workflows');
        localWorkflows.value = response.data.workflows || [];
        needsInit.value = (response.data.workflows || []).length === 0;
        localRoles.value = response.data.roles || [];
        localTeams.value = response.data.teams || [];
        localDepartments.value = response.data.departments || [];
    } catch (error) {
        console.error('Failed to load workflows:', error);
    } finally {
        loading.value = false;
    }
};

watch(() => props.workflows, (newWorkflows) => {
    localWorkflows.value = newWorkflows || [];
    needsInit.value = (newWorkflows || []).length === 0;
}, { deep: true });

onMounted(() => {
    if (!props.workflows || props.workflows.length === 0) {
        fetchWorkflows();
    }
});

const showWorkflowForm = ref(false);
const workflowForm = useForm({
    id: null,
    name: '',
    description: '',
    entity_type: 'generic',
    trigger_event: 'on_submit',
    approved_status: 'Approved',
    rejected_status: 'Rejected',
    is_active: true
});

const openCreateWorkflow = () => {
    workflowForm.reset();
    workflowForm.id = null;
    showWorkflowForm.value = true;
};

const openEditWorkflow = (workflow) => {
    workflowForm.id = workflow.id;
    workflowForm.name = workflow.name;
    workflowForm.description = workflow.description;
    workflowForm.entity_type = workflow.entity_type;
    workflowForm.trigger_event = workflow.trigger_event;
    workflowForm.approved_status = workflow.approved_status;
    workflowForm.rejected_status = workflow.rejected_status;
    showWorkflowForm.value = true;
};

const submitWorkflow = () => {
    const routeName = workflowForm.id 
        ? route('admin.attendance.workflows.update', workflowForm.id)
        : route('admin.attendance.workflows.store');
    
    const method = workflowForm.id ? 'put' : 'post';
    
    workflowForm[method](routeName, {
        onSuccess: () => {
            showWorkflowForm.value = false;
            toast.success(workflowForm.id ? "Workflow architecture committed" : "New workflow node initialized");
            router.reload();
        }
    });
};

const showStageModal = ref(false);
const activeWorkflow = ref(null);
const stageForm = useForm({
    id: null,
    name: '',
    approver_type: 'manager', 
    role_id: null,
    user_id: null,
    team_id: null,
    department_id: null,
    approval_strategy: 'all_must_approve',
    allow_self_approval: true,
    additional_approvers: [],
    user_label: ''
});

const tempApprover = ref({ type: 'specific_user' });

const addAdditionalUser = (user) => {
    if (!user) return;
    stageForm.additional_approvers.push({
        type: 'specific_user',
        user_id: user.id,
        label: user.name
    });
};

const addAdditionalTeam = (teamId) => {
    if (!teamId) return;
    const team = localTeams.value.find(t => t.id == teamId);
    if (team) {
        stageForm.additional_approvers.push({
            type: 'team',
            team_id: team.id,
            label: team.name
        });
    }
};

const removeAdditionalApprover = (index) => {
    stageForm.additional_approvers.splice(index, 1);
};

const openAddStage = (workflow) => {
    activeWorkflow.value = workflow;
    stageForm.reset();
    stageForm.id = null;
    stageForm.additional_approvers = [];
    showStageModal.value = true;
};

const openEditStage = (workflow, stage) => {
    activeWorkflow.value = workflow;
    stageForm.id = stage.id;
    stageForm.name = stage.name;
    stageForm.approver_type = stage.approver_type;
    stageForm.role_id = stage.role_id;
    stageForm.user_id = stage.user_id;
    stageForm.team_id = stage.team_id;
    stageForm.department_id = stage.department_id;
    stageForm.approval_strategy = stage.approval_strategy || 'all_must_approve';
    stageForm.allow_self_approval = !!stage.allow_self_approval;
    stageForm.additional_approvers = stage.additional_approvers || [];
    
    // Resolve labels
    const user = localUsers.value.find(u => u.id == stage.user_id);
    stageForm.user_label = user ? user.name : '';
    
    showStageModal.value = true;
};

// Logic Reset Watcher
watch(() => stageForm.approver_type, () => {
    stageForm.role_id = null;
    stageForm.user_id = null;
    stageForm.team_id = null;
    stageForm.department_id = null;
    stageForm.user_label = '';
});

const submitStage = () => {
    if (!activeWorkflow.value) return;
    const routeName = stageForm.id 
        ? route('admin.attendance.workflows.stages.update', stageForm.id)
        : route('admin.attendance.workflows.stages.store', activeWorkflow.value.id);
    
    stageForm[stageForm.id ? 'put' : 'post'](routeName, {
        onSuccess: () => {
            showStageModal.value = false;
            toast.success("Stage parameter synchronized");
            fetchWorkflows();
        }
    });
};

const showDeleteConfirm = ref(false);
const itemToDelete = ref(null);
const deleteType = ref('workflow'); // 'workflow' or 'stage'

const initiateDelete = (item, type) => {
    itemToDelete.value = item;
    deleteType.value = type;
    showDeleteConfirm.value = true;
};

const executeDelete = () => {
    const routeName = deleteType.value === 'workflow'
        ? route('admin.attendance.workflows.destroy', itemToDelete.value.id)
        : route('admin.attendance.workflows.stages.destroy', itemToDelete.value.id);

    router.delete(routeName, {
        onSuccess: () => {
            showDeleteConfirm.value = false;
            toast.success(`${deleteType.value} terminated from grid`);
            fetchWorkflows();
        }
    });
};

const moveStage = (workflow, stage, direction) => {
    const stages = [...workflow.stages];
    const index = stages.findIndex(s => s.id === stage.id);
    if (index === -1) return;

    if (direction === 'up' && index > 0) {
        [stages[index], stages[index - 1]] = [stages[index - 1], stages[index]];
    } else if (direction === 'down' && index < stages.length - 1) {
        [stages[index], stages[index + 1]] = [stages[index + 1], stages[index]];
    } else return;

    router.put(route('admin.attendance.workflows.reorder', workflow.id), {
        stages: stages.map((s, idx) => ({ id: s.id, stage_order: idx + 1 }))
    }, {
        onSuccess: () => {
            toast.success("Stage sequence recalibrated");
            fetchWorkflows();
        },
        preserveScroll: true
    });
};

const showInitModal = ref(false);
const confirmInit = () => {
    router.post(route('admin.attendance.workflows.init'), {}, {
        onSuccess: () => {
            showInitModal.value = false;
            toast.success("Standard architectures initialized");
            fetchWorkflows(); 
        }
    });
};
</script>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

.animate-content-fade {
    animation: contentFade 0.3s ease-out forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes contentFade {
    from { opacity: 0; transform: translateX(-5px); }
    to { opacity: 1; transform: translateX(0); }
}

.custom-scrollbar::-webkit-scrollbar {
    width: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #e2e8f0;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #cbd5e1;
}

.shadow-inner {
    box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.05);
}
</style>

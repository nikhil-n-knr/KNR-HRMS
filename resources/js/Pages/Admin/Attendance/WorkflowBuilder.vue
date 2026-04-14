<template>
  <component :is="embedded ? 'div' : AttendanceLayout" title="Workflows" activeTab="workflows" v-bind="$props">
    <Head title="Workflow Architect" />
    
    <div class="space-y-6">
      <!-- Universal Header -->
      <div class="flex items-center justify-between bg-white/90 backdrop-blur-xl p-4 rounded-3xl border border-slate-200/50 shadow-xl shadow-slate-200/40">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white shadow-lg shadow-slate-900/20 rotate-3 group-hover:rotate-0 transition-transform">
                <i class="fas fa-project-diagram text-lg"></i>
            </div>
            <div>
                <h1 class="text-md font-black text-slate-900 tracking-[0.2em] uppercase leading-none">Workflow Architect</h1>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-[0.3em] mt-2 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                    Operational Approval Protocols
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
             <button @click="initDefaults" v-if="workflows.length === 0" class="px-5 py-2.5 bg-slate-900 text-white rounded-xl hover:bg-slate-800 font-black text-[10px] uppercase tracking-[0.2em] transition-all flex items-center gap-2 shadow-lg shadow-slate-900/20">
                <i class="fas fa-magic"></i>
                Initialize Defaults
             </button>
             <button @click="openCreateWorkflow" class="px-5 py-2.5 bg-emerald-500 text-white rounded-xl hover:bg-emerald-600 font-black text-[10px] uppercase tracking-[0.2em] transition-all flex items-center gap-2 shadow-lg shadow-emerald-500/20">
                <i class="fas fa-plus"></i>
                New Workflow
             </button>
        </div>
      </div>

      <div class="grid grid-cols-12 gap-6 items-start">
          <!-- Entity Navigation Sidebar -->
          <div class="col-span-12 lg:col-span-3 space-y-4">
              <div class="bg-white/80 backdrop-blur-xl rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/50 p-4 sticky top-6">
                  <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em] px-4 mb-4">Registry Nodes</h3>
                  <div class="space-y-1">
                      <button v-for="type in entityTypes" :key="type.id" 
                          @click="selectedEntity = type.id" 
                          :class="[
                              'w-full flex items-center justify-between px-4 py-3.5 rounded-2xl transition-all duration-300 group',
                              selectedEntity === type.id ? 'bg-slate-900 text-white shadow-xl shadow-slate-900/20 translate-x-1' : 'text-slate-500 hover:bg-slate-50 hover:text-slate-900'
                          ]"
                      >
                          <div class="flex items-center gap-3">
                              <div :class="[
                                  'w-8 h-8 rounded-lg flex items-center justify-center transition-colors',
                                  selectedEntity === type.id ? 'bg-white/10' : 'bg-slate-100 group-hover:bg-whiteShadow border border-slate-200/50'
                              ]">
                                  <i :class="[type.icon, 'text-xs']"></i>
                              </div>
                              <span class="text-[11px] font-black uppercase tracking-widest">{{ type.label }}</span>
                          </div>
                          <span v-if="getWorkflowCount(type.id) > 0" :class="[
                              'text-[9px] font-black px-1.5 py-0.5 rounded-md',
                              selectedEntity === type.id ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500'
                          ]">
                              {{ getWorkflowCount(type.id) }}
                          </span>
                      </button>
                  </div>
              </div>
          </div>

          <!-- Workflow Content Area -->
          <div class="col-span-12 lg:col-span-9 space-y-6">
              <!-- No Workflows for Entity -->
              <div v-if="filteredWorkflows.length === 0" class="bg-white/60 backdrop-blur-xl border-2 border-dashed border-slate-200 p-16 rounded-[3rem] text-center">
                  <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-6 text-slate-300">
                      <i class="fas fa-route text-3xl"></i>
                  </div>
                  <h3 class="text-sm font-black text-slate-900 uppercase tracking-widest">No Node Protocols Defined</h3>
                  <p class="text-xs text-slate-500 font-medium max-w-xs mx-auto mt-4 leading-relaxed">System requires a defined approval sequence for <strong>{{ currentEntity?.label }}</strong> events to synchronize.</p>
                  <button @click="openCreateWorkflow" class="mt-8 px-8 py-3 bg-slate-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] hover:bg-slate-800 transition-all shadow-xl shadow-slate-900/20">
                      Build Flow Node
                  </button>
              </div>

              <!-- Workflow List -->
              <div v-else class="space-y-6 pb-20">
                  <div v-for="flow in filteredWorkflows" :key="flow.id" class="bg-white rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-slate-200/40 overflow-hidden group">
                      <!-- Workflow Header -->
                      <div class="px-8 py-6 bg-slate-900 flex items-center justify-between border-b border-white/5">
                          <div class="flex items-center gap-4">
                              <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center text-white border border-white/10">
                                  <i class="fas fa-stream text-sm"></i>
                              </div>
                              <div>
                                  <h3 class="text-sm font-black text-white tracking-widest uppercase">{{ flow.name }}</h3>
                                  <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">{{ flow.description || flow.entity_type }}</p>
                              </div>
                          </div>
                          <div class="flex items-center gap-3">
                              <button @click="cloneWorkflow(flow)" class="w-8 h-8 bg-white/5 text-white/40 rounded-lg hover:bg-white/10 transition-all flex items-center justify-center group/clone" title="Clone Protocol">
                                  <i class="fas fa-copy text-[10px] group-hover/clone:scale-110 transition-transform"></i>
                              </button>
                              <button @click="editWorkflow(flow)" class="w-8 h-8 bg-white/5 text-white/60 rounded-lg hover:bg-white/10 transition-all flex items-center justify-center">
                                  <i class="fas fa-pen text-[10px]"></i>
                              </button>
                              <button @click="deleteWorkflow(flow)" class="w-8 h-8 bg-red-500/10 text-red-400 rounded-lg hover:bg-red-500/20 transition-all flex items-center justify-center">
                                  <i class="fas fa-trash text-[10px]"></i>
                              </button>
                              <div :class="[flow.is_active ? 'bg-emerald-500 shadow-emerald-500/20' : 'bg-slate-600 shadow-slate-600/20', 'px-3 py-1 rounded-full text-[9px] font-black text-white uppercase tracking-widest shadow-lg']">
                                  {{ flow.is_active ? 'Active' : 'Inactive' }}
                              </div>
                          </div>
                      </div>

                      <!-- Stages Explorer -->
                      <div class="p-8 bg-slate-50/50">
                          <div class="flex items-center justify-between mb-8">
                                                                 <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">Sequential Chain ({{ flow.stages?.length || 0 }})</h4>

                                <button @click="openAddStage(flow)" class="text-[9px] font-black text-emerald-600 uppercase tracking-widest flex items-center gap-2 hover:gap-3 transition-all px-4 py-2 bg-emerald-50 rounded-xl border border-emerald-100">
                                    <i class="fas fa-plus-circle"></i> Inject Stage Node
                                </button>
                          </div>

                          <div class="relative pl-12 space-y-8 before:absolute before:left-[17px] before:top-2 before:bottom-2 before:w-[2px] before:bg-slate-200/60 transition-all">
                              <div v-for="(stage, idx) in flow.stages" :key="stage.id" class="relative bg-white p-5 rounded-2xl border border-slate-200/60 shadow-lg shadow-slate-200/20 group/stage hover:border-slate-900 transition-all">
                                  <!-- Connection Point -->
                                  <div class="absolute -left-[43px] top-1/2 -translate-y-1/2 w-8 h-8 rounded-full bg-white border-2 border-slate-900 flex items-center justify-center z-10 shadow-lg">
                                      <span class="text-[10px] font-black text-slate-900">{{ idx + 1 }}</span>
                                  </div>
                                  
                                  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                                      <div class="flex items-center gap-5">
                                          <div :class="[
                                              'w-12 h-12 rounded-2xl flex items-center justify-center text-white shadow-lg',
                                              idx % 2 === 0 ? 'bg-indigo-500 shadow-indigo-500/20' : 'bg-violet-500 shadow-violet-500/20'
                                          ]">
                                              <i :class="getApproverIcon(stage.approver_type)" class="text-sm"></i>
                                          </div>
                                          <div>
                                              <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Protocol Stage</span>
                                              <h5 class="text-xs font-black text-slate-900 uppercase tracking-widest mt-1">{{ stage.name }}</h5>
                                              <div class="flex flex-wrap items-center gap-3 mt-2">
                                                  <div class="flex items-center gap-2 px-2 py-0.5 bg-slate-100 rounded text-[9px] font-bold text-slate-600 uppercase">
                                                      <i class="fas fa-user-shield text-[8px]"></i>
                                                      {{ formatApprover(stage) }}
                                                  </div>
                                                  <div v-show="stage.additional_approvers?.length" class="flex items-center gap-2 px-2 py-0.5 bg-emerald-50 rounded text-[9px] font-bold text-emerald-600 uppercase shadow-sm border border-emerald-100/50">
                                                      <i class="fas fa-users text-[8px]"></i>
                                                      +{{ stage.additional_approvers?.length || 0 }} Matrix Logic
                                                  </div>
                                              </div>
                                          </div>
                                      </div>

                                      <div class="flex items-center gap-2 opacity-0 group-hover/stage:opacity-100 transition-opacity">
                                          <div class="flex flex-col gap-1 mr-4">
                                              <button @click="reorderStage(flow, stage, -1)" v-if="idx > 0" class="text-[8px] text-slate-400 hover:text-slate-900"><i class="fas fa-chevron-up"></i></button>
                                              <button @click="reorderStage(flow, stage, 1)" v-if="idx < flow.stages.length - 1" class="text-[8px] text-slate-400 hover:text-slate-900"><i class="fas fa-chevron-down"></i></button>
                                          </div>
                                          <button @click="openEditStage(flow, stage)" class="w-8 h-8 bg-slate-50 text-slate-400 rounded-lg hover:bg-slate-900 hover:text-white transition-all flex items-center justify-center">
                                              <i class="fas fa-sliders text-[10px]"></i>
                                          </button>
                                          <button @click="deleteStage(stage)" class="w-8 h-8 bg-red-50 text-red-400 rounded-lg hover:bg-red-600 hover:text-white transition-all flex items-center justify-center">
                                              <i class="fas fa-times text-[10px]"></i>
                                          </button>
                                      </div>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>

      <!-- Workflow Create/Edit Modal -->
       <Modal :show="workflowModal.show" @close="workflowModal.show = false" max-width="lg">
           <div class="p-8">
               <div class="flex items-center gap-4 mb-8">
                   <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-white shadow-xl rotate-3">
                       <i class="fas fa-route text-lg"></i>
                   </div>
                   <div>
                       <h2 class="text-sm font-black text-slate-900 uppercase tracking-[0.2em]">{{ workflowModal.editMode ? 'Recalibrate Flow' : 'Architect Context' }}</h2>
                       <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1.5">Node Logic Configuration</p>
                   </div>
               </div>

               <div class="space-y-6">
                   <div class="grid grid-cols-1 gap-6">
                        <div>
                            <InputLabel value="Sequence Identity" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2" />
                            <TextInput v-model="workflowModal.form.name" placeholder="e.g. Standard Leave Process" class="w-full" />
                            <InputError :message="workflowModal.form.errors.name" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel value="Operational Context (Entity)" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2" />
                            <select v-model="workflowModal.form.entity_type" class="w-full bg-slate-50 border-slate-200 rounded-2xl text-[11px] font-black uppercase py-3.5 tracking-widest">
                                <option v-for="type in entityTypes" :key="type.id" :value="type.id">{{ type.label }}</option>
                            </select>
                        </div>
                        <div>
                            <InputLabel value="Description" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2" />
                            <textarea v-model="workflowModal.form.description" class="w-full bg-slate-50 border-slate-200 rounded-2xl text-xs py-3.5 px-4 outline-none focus:ring-2 focus:ring-slate-900 min-h-[100px]" placeholder="Briefly describe the flow purpose..."></textarea>
                        </div>
                        <div class="flex items-center gap-3 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                             <input type="checkbox" v-model="workflowModal.form.is_active" class="w-5 h-5 rounded-lg border-slate-300 text-slate-900 focus:ring-slate-900" />
                             <div>
                                 <span class="text-xs font-black text-slate-900 uppercase tracking-widest">Active Status</span>
                                 <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1">Enable live protocol execution</p>
                             </div>
                        </div>
                   </div>

                   <div class="flex justify-end gap-3 pt-4">
                       <button @click="workflowModal.show = false" class="px-6 py-3 text-slate-500 font-black text-[10px] uppercase tracking-widest transition-all">Cancel</button>
                       <button @click="saveWorkflow" :disabled="workflowModal.form.processing" class="px-8 py-3 bg-slate-900 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-slate-800 transition-all shadow-xl shadow-slate-900/20">
                           {{ workflowModal.editMode ? 'Update Workflow' : 'Create Workflow' }}
                       </button>
                   </div>
               </div>
           </div>
       </Modal>

        <!-- Stage Create/Edit Modal (Enhanced Premium UX) -->
        <Modal :show="stageModal.show" @close="stageModal.show = false" max-width="3xl">
            <div class="relative overflow-hidden bg-slate-50">
                <!-- Decorative Blur Background -->
                <div class="absolute top-0 right-0 -mr-20 -mt-20 w-64 h-64 bg-indigo-500/10 rounded-full blur-[100px]"></div>
                <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-64 h-64 bg-violet-500/10 rounded-full blur-[100px]"></div>

                <div class="relative p-8 lg:p-10">
                    <!-- Header Section -->
                    <div class="flex items-center justify-between mb-10">
                        <div class="flex items-center gap-6">
                            <div class="w-16 h-16 bg-indigo-600 rounded-[2rem] flex items-center justify-center text-white shadow-2xl shadow-indigo-200 -rotate-6 transition-transform hover:rotate-0">
                                <i class="fas fa-layer-group text-2xl"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-slate-900 uppercase tracking-[0.2em] leading-tight">
                                    {{ stageModal.editMode ? 'Recalibrate Phase' : 'Architect Protocol' }}
                                </h2>
                                <p class="text-[11px] text-slate-400 font-bold uppercase tracking-[0.3em] mt-2 flex items-center gap-2">
                                    <span class="w-2 h-0.5 bg-indigo-500"></span>
                                    Decision Matrix Configuration Node
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Main Config Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        <!-- Left Column: Identity & Authority -->
                        <div class="lg:col-span-5 space-y-8">
                            <!-- Phase Identity Card -->
                            <div class="bg-white/70 backdrop-blur-xl p-6 rounded-[2rem] border border-white shadow-xl shadow-slate-200/40">
                                <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                    <i class="fas fa-id-card-alt text-indigo-500"></i> Phase Spectrum
                                </h3>
                                <div class="space-y-6">
                                    <div>
                                        <InputLabel value="Phase Identity Label" class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-3" />
                                        <TextInput v-model="stageModal.form.name" placeholder="e.g. Strategic Audit Review" class="w-full bg-slate-50/50 border-slate-200/50 rounded-2xl py-4 focus:bg-white" />
                                    </div>
                                    <div>
                                        <InputLabel value="Primary Authority Node" class="text-[9px] font-black uppercase tracking-widest text-slate-500 mb-3" />
                                        <div class="relative group">
                                            <select v-model="stageModal.form.approver_type" class="w-full bg-slate-50/50 border-slate-200/50 rounded-2xl text-[11px] font-black uppercase py-4 tracking-widest focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 appearance-none pl-6 cursor-pointer">
                                                <option value="manager">Reporting Manager</option>
                                                <option value="team_lead">Team Lead</option>
                                                <option value="role">Specific Role</option>
                                                <option value="department_head">Department Head</option>
                                                <option value="specific_user">Static High Command (User)</option>
                                                <option value="team">Team Matrix</option>
                                                <option value="department">Department Authority</option>
                                            </select>
                                            <div class="absolute right-6 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none group-hover:text-indigo-500 transition-colors">
                                                <i class="fas fa-chevron-down text-xs"></i>
                                            </div>
                                        </div>

                                        <!-- Authority Target Selector (Nested) -->
                                        <Transition name="fade">
                                            <div v-if="['role', 'specific_user', 'team', 'department'].includes(stageModal.form.approver_type)" class="mt-6 p-5 bg-indigo-50/50 rounded-2xl border border-indigo-100/50 animate-in slide-in-from-top-2 duration-300">
                                                <InputLabel :value="getAuthorityTargetLabel" class="text-[9px] font-black uppercase tracking-widest text-indigo-400 mb-3" />
                                                
                                                <div class="relative group">
                                                    <select v-if="stageModal.form.approver_type === 'role'" v-model="stageModal.form.role_id" class="w-full bg-white border-slate-200 rounded-xl text-[11px] font-bold py-3.5 pl-4 pr-10 appearance-none shadow-sm focus:ring-2 focus:ring-indigo-500/20">
                                                        <option value="">Select Target Role...</option>
                                                        <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                                    </select>

                                                    <select v-if="stageModal.form.approver_type === 'specific_user'" v-model="stageModal.form.user_id" class="w-full bg-white border-slate-200 rounded-xl text-[11px] font-bold py-3.5 pl-4 pr-10 appearance-none shadow-sm focus:ring-2 focus:ring-indigo-500/20">
                                                        <option value="">Select System User...</option>
                                                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                                                    </select>

                                                    <select v-if="stageModal.form.approver_type === 'team'" v-model="stageModal.form.team_id" class="w-full bg-white border-slate-200 rounded-xl text-[11px] font-bold py-3.5 pl-4 pr-10 appearance-none shadow-sm focus:ring-2 focus:ring-indigo-500/20">
                                                        <option value="">Select Command Team...</option>
                                                        <option v-for="t in teams" :key="t.id" :value="t.id">{{ t.name }}</option>
                                                    </select>

                                                    <select v-if="stageModal.form.approver_type === 'department'" v-model="stageModal.form.department_id" class="w-full bg-white border-slate-200 rounded-xl text-[11px] font-bold py-3.5 pl-4 pr-10 appearance-none shadow-sm focus:ring-2 focus:ring-indigo-500/20">
                                                        <option value="">Select Parent Department...</option>
                                                        <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                                                    </select>
                                                    
                                                    <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none">
                                                        <i class="fas fa-search text-[10px]"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </Transition>
                                    </div>
                                </div>
                            </div>

                            <!-- Precision Logic Card -->
                            <div class="bg-indigo-900 p-8 rounded-[2rem] text-white shadow-2xl shadow-indigo-900/40 relative overflow-hidden group">
                                <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-microchip text-7xl"></i>
                                </div>
                                <div class="relative z-10">
                                    <h3 class="text-[10px] font-black text-indigo-300 uppercase tracking-[0.3em] mb-6 flex items-center gap-2">
                                        <i class="fas fa-atom animate-spin-slow"></i> Governance Logic
                                    </h3>
                                    
                                    <div class="space-y-8">
                                        <div class="flex items-center justify-between bg-white/10 p-4 rounded-2xl hover:bg-white/15 transition-colors">
                                            <div>
                                                <h4 class="text-[11px] font-black uppercase tracking-widest">Self-Approval Protocol</h4>
                                                <p class="text-[9px] text-indigo-200 uppercase tracking-widest mt-1">Allow author to sign-off their node</p>
                                            </div>
                                            <div class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" v-model="stageModal.form.allow_self_approval" class="sr-only peer" />
                                                <div class="w-11 h-6 bg-indigo-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500 border border-white/20 shadow-inner"></div>
                                            </div>
                                        </div>

                                        <div>
                                            <div class="flex items-center justify-between mb-4">
                                                <InputLabel value="Consensus Matrix Strategy" class="text-[9px] font-black uppercase tracking-widest text-indigo-200" />
                                                <span class="text-[8px] bg-white/20 px-2 py-0.5 rounded uppercase font-black tracking-widest">Global Policy</span>
                                            </div>
                                            <div class="bg-white/5 p-1.5 rounded-2xl border border-white/10 flex gap-1">
                                                <button @click="stageModal.form.approval_strategy = 'all_must_approve'" 
                                                    :class="[
                                                        'flex-1 flex items-center justify-center gap-3 py-3 rounded-xl text-[10px] font-black transition-all duration-300 uppercase tracking-widest',
                                                        stageModal.form.approval_strategy === 'all_must_approve' ? 'bg-white text-indigo-900 shadow-xl scale-[1.02]' : 'hover:bg-white/5 text-indigo-200'
                                                    ]">
                                                    <i class="fas fa-users-check"></i>
                                                    Consensus
                                                </button>
                                                <button @click="stageModal.form.approval_strategy = 'any_can_approve'" 
                                                    :class="[
                                                        'flex-1 flex items-center justify-center gap-3 py-3 rounded-xl text-[10px] font-black transition-all duration-300 uppercase tracking-widest',
                                                        stageModal.form.approval_strategy === 'any_can_approve' ? 'bg-white text-indigo-900 shadow-xl scale-[1.02]' : 'hover:bg-white/5 text-indigo-200'
                                                    ]">
                                                    <i class="fas fa-bolt"></i>
                                                    Any One
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Extended Matrix -->
                        <div class="lg:col-span-7 h-full">
                            <div class="bg-white/80 backdrop-blur-xl border border-white rounded-[2rem] shadow-xl shadow-slate-200/40 p-1 flex flex-col h-full lg:sticky lg:top-0">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-2">
                                        <div>
                                            <h3 class="text-[10px] font-black text-slate-900 uppercase tracking-widest">Extended Matrix</h3>
                                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest mt-1">Multi-author verification</p>
                                        </div>
                                        <button @click="addAdditionalApprover" class="w-10 h-10 bg-emerald-500 text-white rounded-xl shadow-lg shadow-emerald-500/20 flex items-center justify-center hover:bg-emerald-600 hover:scale-105 active:scale-95 transition-all">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="flex-1 px-6 pb-6 space-y-3 overflow-y-auto max-h-[500px] custom-scrollbar">
                                    <TransitionGroup name="list">
                                        <div v-for="(extra, xidx) in stageModal.form.additional_approvers" :key="xidx" class="flex flex-col gap-3 bg-slate-50/80 rounded-2xl border border-slate-100 p-4 transition-all hover:bg-white hover:shadow-lg hover:shadow-slate-200/50 hover:border-slate-300 group relative">
                                            <div class="flex items-center justify-between border-b border-slate-100 pb-2 mb-1">
                                                <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest">Matrix Node #{{ xidx + 1 }}</span>
                                                <button @click="stageModal.form.additional_approvers.splice(xidx, 1)" class="text-slate-300 hover:text-red-500 transition-colors">
                                                    <i class="fas fa-trash-alt text-[10px]"></i>
                                                </button>
                                            </div>
                                            
                                            <div class="grid grid-cols-2 gap-3">
                                                <div>
                                                    <InputLabel value="Context" class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1.5" />
                                                    <select v-model="extra.type" class="w-full bg-white border-slate-200 rounded-lg text-[9px] font-black uppercase p-2 focus:ring-indigo-500/20">
                                                        <option value="role">Role</option>
                                                        <option value="specific_user">User</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <InputLabel value="Identity" class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-1.5" />
                                                    <select v-if="extra.type === 'role'" v-model="extra.role_id" class="w-full bg-white border-slate-200 rounded-lg text-[9px] font-black uppercase p-2 focus:ring-indigo-500/20">
                                                        <option value="">Select Role</option>
                                                        <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                                    </select>
                                                    <select v-if="extra.type === 'specific_user'" v-model="extra.user_id" class="w-full bg-white border-slate-200 rounded-lg text-[9px] font-black uppercase p-2 focus:ring-indigo-500/20">
                                                        <option value="">Select User</option>
                                                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </TransitionGroup>

                                    <div v-if="stageModal.form.additional_approvers?.length === 0" class="flex flex-col items-center justify-center py-16 px-6 text-center border-2 border-dashed border-slate-100 rounded-[2rem] bg-slate-50/50">
                                        <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center text-slate-200 shadow-sm border border-slate-50 mb-4">
                                            <i class="fas fa-layer-group text-lg"></i>
                                        </div>
                                        <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Isolated Protocol</h4>
                                        <p class="text-[9px] text-slate-300 font-bold uppercase tracking-tighter mt-2 leading-relaxed max-w-[150px]">No additional matrix nodes defined for this phase</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Bar -->
                    <div class="mt-12 pt-8 border-t border-slate-200/50 flex flex-col sm:flex-row items-center justify-between gap-8">
                        <div class="flex items-center gap-5">
                             <div class="flex -space-x-3">
                                 <div v-for="i in 3" :key="i" class="w-10 h-10 rounded-xl bg-slate-900 border-[3px] border-white flex items-center justify-center text-[10px] font-black text-white shadow-lg">
                                     {{ ['S', 'Y', 'N'][i-1] }}
                                 </div>
                             </div>
                             <div>
                                 <p class="text-[10px] font-black uppercase tracking-widest text-slate-900">Protocol Registry</p>
                                 <p class="text-[9px] font-bold uppercase tracking-widest text-slate-400 mt-0.5">Auto-Versioning active</p>
                             </div>
                        </div>
                        <div class="flex items-center gap-8 w-full sm:w-auto">
                            <button @click="stageModal.show = false" class="text-slate-400 hover:text-red-500 font-black text-[10px] uppercase tracking-[0.2em] transition-all">
                                Discard Changes
                            </button>
                            <button @click="saveStage" :disabled="stageModal.form.processing" class="flex-1 sm:flex-none px-16 py-5 bg-indigo-600 text-white rounded-[1.5rem] font-black text-[11px] uppercase tracking-[0.2em] hover:bg-indigo-700 active:scale-95 transition-all shadow-2xl shadow-indigo-200 flex items-center justify-center gap-4">
                                <i class="fas fa-save shadow-sm"></i>
                                {{ stageModal.editMode ? 'Update Phase Node' : 'Initialize Protocol' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- Workflow Clone Modal (Target Migration) -->
        <Modal :show="cloneModal.show" @close="cloneModal.show = false" max-width="lg">
            <div class="p-8">
                <div class="flex items-center gap-4 mb-8">
                    <div class="w-12 h-12 bg-emerald-600 rounded-2xl flex items-center justify-center text-white shadow-xl rotate-3">
                        <i class="fas fa-copy text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-sm font-black text-slate-900 uppercase tracking-[0.2em]">Migrate Protocol</h2>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mt-1.5">Clone Sequence to New Context</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <div>
                        <InputLabel value="New Sequence Identity" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2" />
                        <TextInput v-model="cloneForm.name" placeholder="e.g. Copied Leave Flow" class="w-full" />
                        <InputError :message="cloneForm.errors.name" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel value="Target Command Context (Entity)" class="text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2" />
                        <select v-model="cloneForm.entity_type" class="w-full bg-slate-50 border-slate-200 rounded-2xl text-[11px] font-black uppercase py-3.5 tracking-widest">
                            <option v-for="type in entityTypes" :key="type.id" :value="type.id">{{ type.label }}</option>
                        </select>
                        <InputError :message="cloneForm.errors.entity_type" class="mt-2" />
                    </div>

                    <div class="bg-indigo-50 p-4 rounded-2xl border border-indigo-100 flex gap-4">
                        <i class="fas fa-info-circle text-indigo-400 mt-0.5"></i>
                        <p class="text-[10px] text-indigo-700 font-bold uppercase leading-relaxed tracking-wider">
                            This will replicate all approval stages exactly as configured. The new protocol will be created as a Draft (Inactive).
                        </p>
                    </div>

                    <div class="flex justify-end gap-3 pt-4">
                        <button @click="cloneModal.show = false" class="px-6 py-3 text-slate-500 font-black text-[10px] uppercase tracking-widest transition-all">Cancel</button>
                        <button @click="confirmClone" :disabled="cloneForm.processing" class="px-8 py-3 bg-emerald-600 text-white rounded-2xl font-black text-[10px] uppercase tracking-widest hover:bg-emerald-700 transition-all shadow-xl shadow-emerald-600/20 flex items-center gap-2">
                            <i class="fas fa-magic text-[10px]"></i>
                            Begin Migration
                        </button>
                    </div>
                </div>
            </div>
        </Modal>
    </div>
  </component>
</template>

<script setup>
import { ref, computed, reactive, onMounted } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AttendanceLayout from '@/Layouts/AttendanceLayout.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    embedded: Boolean,
    workflows: { type: Array, default: () => [] },
    roles: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    teams: { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
    needsInit: Boolean
});

const selectedEntity = ref('leave_request');

const entityTypes = [
    { id: 'leave_request', label: 'Leaves', icon: 'fas fa-calendar-day' },
    { id: 'attendance_regularization', label: 'Regularizations', icon: 'fas fa-clock' },
    { id: 'timesheet', label: 'Timesheets', icon: 'fas fa-business-time' },
    { id: 'expense', label: 'Expenses', icon: 'fas fa-receipt' },
    { id: 'shift_swap', label: 'Swaps', icon: 'fas fa-exchange-alt' },
    { id: 'overtime_request', label: 'Overtime', icon: 'fas fa-history' },
    { id: 'wfh_request', label: 'WFH Tracking', icon: 'fas fa-home' },
    { id: 'holiday', label: 'Holiday Nodes', icon: 'fas fa-umbrella-beach' },
    { id: 'payroll', label: 'Payroll Finalization', icon: 'fas fa-money-check-alt' },
];

const currentEntity = computed(() => entityTypes.find(t => t.id === selectedEntity.value));
const filteredWorkflows = computed(() => props.workflows.filter(w => w.entity_type === selectedEntity.value));

const getWorkflowCount = (type) => props.workflows.filter(w => w.entity_type === type).length;

const initDefaults = () => {
    if(confirm("Initialize standard protocol registry? This will create base logic for core entities.")) {
        router.post(route('admin.attendance.workflows.init'));
    }
};

// --- Workflow Modal Logic ---
const workflowModal = reactive({
    show: false,
    editMode: false,
    form: useForm({
        id: null,
        name: '',
        description: '',
        entity_type: 'leave_request',
        is_active: true
    })
});

const cloneModal = reactive({
    show: false,
    sourceFlow: null
});

const cloneForm = useForm({
    name: '',
    entity_type: ''
});

const openCreateWorkflow = () => {
    workflowModal.editMode = false;
    workflowModal.form.reset();
    workflowModal.form.entity_type = selectedEntity.value;
    workflowModal.show = true;
};

const editWorkflow = (flow) => {
    workflowModal.editMode = true;
    workflowModal.form.id = flow.id;
    workflowModal.form.name = flow.name;
    workflowModal.form.description = flow.description;
    workflowModal.form.entity_type = flow.entity_type;
    workflowModal.form.is_active = !!flow.is_active;
    workflowModal.show = true;
};

const cloneWorkflow = (flow) => {
    cloneModal.sourceFlow = flow;
    cloneForm.name = `${flow.name} (Copy)`;
    cloneForm.entity_type = flow.entity_type;
    cloneModal.show = true;
};

const confirmClone = () => {
    cloneForm.post(route('admin.attendance.workflows.clone', cloneModal.sourceFlow.id), {
        onSuccess: () => {
            cloneModal.show = false;
        }
    });
};

const saveWorkflow = () => {
    if (workflowModal.editMode) {
        workflowModal.form.put(route('admin.attendance.workflows.update', workflowModal.form.id), {
            onSuccess: () => workflowModal.show = false,
        });
    } else {
        workflowModal.form.post(route('admin.attendance.workflows.store'), {
            onSuccess: () => workflowModal.show = false,
        });
    }
};

const deleteWorkflow = (flow) => {
    if(confirm("Archive this workflow protocol? Existing instances will remain readable but no new flows will initiate.")) {
        router.delete(route('admin.attendance.workflows.destroy', flow.id));
    }
};

// --- Stage Modal Logic ---
const stageModal = reactive({
    show: false,
    editMode: false,
    workflow: null,
    form: useForm({
        id: null,
        name: '',
        approver_type: 'manager',
        role_id: '',
        user_id: '',
        team_id: '',
        department_id: '',
        additional_approvers: [],
        allow_self_approval: true,
        approval_strategy: 'all_must_approve'
    })
});

const openAddStage = (flow) => {
    stageModal.workflow = flow;
    stageModal.editMode = false;
    stageModal.form.reset();
    stageModal.show = true;
};

const openEditStage = (flow, stage) => {
    stageModal.workflow = flow;
    stageModal.editMode = true;
    stageModal.form.id = stage.id;
    stageModal.form.name = stage.name;
    stageModal.form.approver_type = stage.approver_type;
    stageModal.form.role_id = stage.role_id;
    stageModal.form.user_id = stage.user_id;
    stageModal.form.team_id = stage.team_id;
    stageModal.form.department_id = stage.department_id;
    stageModal.form.additional_approvers = [...(stage.additional_approvers || [])];
    stageModal.form.allow_self_approval = !!stage.allow_self_approval;
    stageModal.form.approval_strategy = stage.approval_strategy || 'all_must_approve';
    stageModal.show = true;
};

const addAdditionalApprover = () => {
    stageModal.form.additional_approvers.push({ type: 'role', role_id: '', user_id: '' });
};

const getAuthorityTargetLabel = computed(() => {
    switch(stageModal.form.approver_type) {
        case 'role': return 'Target Authority Role';
        case 'specific_user': return 'Target Authority High Command';
        case 'team': return 'Target Matrix Team';
        case 'department': return 'Target Department Unit';
        default: return 'Target Identity';
    }
});

const saveStage = () => {
    if (stageModal.editMode) {
        stageModal.form.put(route('admin.attendance.workflows.stages.update', stageModal.form.id), {
            onSuccess: () => stageModal.show = false,
        });
    } else {
        stageModal.form.post(route('admin.attendance.workflows.stages.store', stageModal.workflow.id), {
            onSuccess: () => stageModal.show = false,
        });
    }
};

const deleteStage = (stage) => {
    if(confirm("Excise this stage node from logic chain?")) {
        router.delete(route('admin.attendance.workflows.stages.destroy', stage.id));
    }
};

const reorderStage = (flow, stage, direction) => {
    const stages = [...flow.stages];
    const currentIndex = stages.findIndex(s => s.id === stage.id);
    const targetIndex = currentIndex + direction;
    
    if (targetIndex < 0 || targetIndex >= stages.length) return;
    
    // Swap
    [stages[currentIndex], stages[targetIndex]] = [stages[targetIndex], stages[currentIndex]];
    
    // Re-map orders
    const payload = stages.map((s, idx) => ({
        id: s.id,
        stage_order: idx + 1
    }));
    
    router.put(route('admin.attendance.workflows.reorder', flow.id), {
        stages: payload
    }, {
        preserveScroll: true
    });
};

// --- Formatting Helpers ---
const getApproverIcon = (type) => {
    switch(type) {
        case 'manager': return 'fas fa-user-tie';
        case 'team_lead': return 'fas fa-user-ninja';
        case 'role': return 'fas fa-id-badge';
        case 'specific_user': return 'fas fa-user-lock';
        case 'department_head': return 'fas fa-landmark';
        case 'team': return 'fas fa-users-cog';
        case 'department': return 'fas fa-building';
        default: return 'fas fa-user-shield';
    }
};

const formatApprover = (stage) => {
    switch(stage.approver_type) {
        case 'manager': return 'Line Manager';
        case 'team_lead': return 'Team Lead';
        case 'role': return `Role: ${stage.role?.name || 'TBD'}`;
        case 'specific_user': return stage.user?.name || 'Assigned Agent';
        case 'department_head': return 'Unit Head';
        case 'team': return stage.team?.name || 'Team Authority';
        case 'department': return stage.department?.name || 'Dept Authority';
        default: return 'Defined Authority';
    }
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #E2E8F0; border-radius: 10px; }
</style>

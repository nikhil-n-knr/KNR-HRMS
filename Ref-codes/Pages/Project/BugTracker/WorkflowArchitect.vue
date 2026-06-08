<template>
    <div class="space-y-8 animate-in fade-in duration-500 font-inter">
        <!-- Header Section -->
        <div class="max-w-5xl mx-auto">
            <div class="bg-white p-10 rounded-[2.5rem] shadow-xl shadow-slate-200/50 relative overflow-hidden border border-slate-100">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-50/50 to-emerald-50/30"></div>
                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-black tracking-tighter text-slate-900 mb-2">Policy Master</h1>
                        <p class="text-slate-500 max-w-lg text-xs md:text-sm font-bold uppercase tracking-widest">Design the operational DNA of your bug tracking pipeline.</p>
                    </div>
                    <div class="flex w-full md:w-auto gap-3 shrink-0">
                         <button @click="saveOrder" :disabled="!isOrderDirty || isSaving" class="flex-1 md:flex-none justify-center bg-emerald-600 hover:bg-emerald-500 disabled:bg-slate-300 text-white px-6 py-3 rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl shadow-emerald-600/20 transition-all flex items-center gap-2 active:scale-95">
                            <CheckIcon v-if="!isSaving" class="w-4 h-4" />
                            <svg v-else class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Save
                        </button>
                        <button @click="showAddModal = true" :disabled="isSaving" class="flex-1 md:flex-none justify-center px-6 py-3 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-100 rounded-2xl font-black text-sm uppercase tracking-widest transition-all flex items-center gap-2 shadow-sm">
                            <PlusIcon class="w-4 h-4" />
                            New Stage
                        </button>
                    </div>
                </div>
                <!-- Decorative Elements -->
                <div class="absolute -top-12 -right-12 opacity-[0.03] text-indigo-900 pointer-events-none">
                    <CpuChipIcon class="w-64 h-64" />
                </div>
            </div>
        </div>

        <!-- Workflow List -->
        <div class="max-w-5xl mx-auto space-y-6 pb-24">
            <div v-for="(stage, index) in localStages" :key="stage.id" 
                 :id="'stage-' + stage.id"
                 :class="[
                     expandedStage === stage.id ? 'border-emerald-500 ring-4 ring-emerald-500/5 translate-y-[-4px] shadow-xl' : 'border-slate-200 hover:border-slate-300',
                     'bg-white rounded-[2rem] border transition-all duration-300 overflow-hidden'
                 ]">
                
                <!-- Stage Header / List Item -->
                <div @click="toggleStage(stage.id)" class="p-4 md:p-6 flex flex-col md:flex-row items-stretch md:items-center gap-4 md:gap-8 cursor-pointer hover:bg-slate-50/30 transition-colors group">
                    <!-- Index & Reorder -->
                    <div class="flex flex-row md:flex-col items-center justify-between md:justify-center p-3 bg-slate-50 rounded-2xl text-slate-400 group-hover:bg-emerald-50 group-hover:text-emerald-600 transition-all shrink-0 border border-slate-100 min-w-none md:min-w-[64px]">
                        <span class="text-sm font-black uppercase text-slate-300 group-hover:text-emerald-300 tracking-[0.2em]">0{{ index + 1 }}</span>
                        <div class="flex md:flex-row gap-4 md:gap-2 mt-0 md:mt-1">
                            <button @click.stop="moveUp(index)" :disabled="index === 0" class="hover:text-emerald-600 disabled:opacity-20 transition-all p-1">
                                <ChevronUpIcon class="w-5 h-5 md:w-4 md:h-4 stroke-[3]" />
                            </button>
                            <button @click.stop="moveDown(index)" :disabled="index === localStages.length - 1" class="hover:text-emerald-600 disabled:opacity-20 transition-all p-1">
                                <ChevronDownIcon class="w-5 h-5 md:w-4 md:h-4 stroke-[3]" />
                            </button>
                        </div>
                    </div>

                    <div class="flex-1 px-1">
                        <div class="flex flex-wrap items-center gap-3 mb-2">
                            <h3 class="font-black text-slate-900 uppercase tracking-tight text-base md:text-lg">{{ stage.name }}</h3>
                            <div class="flex gap-2 text-xs font-bold">
                                <span v-if="stage.is_final" class="px-2 py-0.5 bg-emerald-50 text-emerald-600 rounded border border-emerald-100 uppercase tracking-widest">Closed</span>
                                <span v-if="stage.requires_verification" class="px-2 py-0.5 bg-rose-50 text-rose-600 rounded border border-rose-100 uppercase tracking-widest flex items-center gap-1">
                                   <ShieldCheckIcon class="w-2.5 h-2.5" /> QA
                                </span>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-x-6 gap-y-2 text-[10px] md:text-xs font-black uppercase tracking-[0.15em] text-slate-400">
                            <span class="flex items-center gap-2">
                                <ClockIcon class="w-3.5 h-3.5" />
                                SLA: {{ stage.auto_close_days ? stage.auto_close_days + 'd' : 'Off' }}
                            </span>
                            <span class="flex items-center gap-2">
                                <UserGroupIcon class="w-3.5 h-3.5" />
                                Team: {{ getTeamName(stage.assigned_team_id) }}
                            </span>
                            <span v-if="stage.role_id" class="flex items-center gap-2 text-indigo-500">
                                <ShieldCheckIcon class="w-3.5 h-3.5" />
                                Approver: {{ getRoleName(stage.role_id) }}
                            </span>
                            <span v-if="stage.user_id" class="flex items-center gap-2 text-emerald-500">
                                <UserIcon class="w-3.5 h-3.5" />
                                Person: {{ getUserName(stage.user_id) }}
                            </span>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <button @click.stop="openPeopleManager(stage)" class="p-2.5 bg-slate-100 hover:bg-indigo-100 text-slate-400 hover:text-indigo-600 rounded-xl transition-all group/icon" title="Configure Participants">
                            <Cog6ToothIcon class="w-5 h-5 group-hover/icon:rotate-90 transition-transform duration-500" />
                        </button>
                        <div class="hidden md:flex items-center gap-2 text-slate-300 group-hover:text-emerald-500 transition-colors">
                            <ChevronRightIcon :class="expandedStage === stage.id ? 'rotate-90 text-emerald-600' : ''" class="w-6 h-6 transition-all stroke-[3]" />
                        </div>
                    </div>
                </div>

                <!-- Inline Configuration Panel -->
                <div v-if="expandedStage === stage.id" class="border-t border-slate-100 bg-slate-50/20 p-6 md:p-10 space-y-8 md:space-y-10 animate-in slide-in-from-top-4 duration-500">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-12 text-left">
                        <!-- Protocol Section -->
                        <div class="space-y-4 md:space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="h-6 w-1 bg-emerald-500 rounded-full"></div>
                                <h4 class="text-sm md:text-base font-black uppercase tracking-[0.25em] text-slate-800">Operational Protocol</h4>
                            </div>
                            <div class="space-y-3 md:space-y-4">
                                <div>
                                    <label class="block text-sm md:text-sm font-black text-slate-400 uppercase tracking-widest mb-3">Stage Identity</label>
                                    <input v-model="stage.name" type="text" class="w-full bg-white rounded-2xl border-slate-200 px-5 py-4 text-sm font-bold focus:ring-4 focus:ring-emerald-500/5 focus:border-emerald-500 transition-all shadow-sm" />
                                </div>
                                <div class="p-4 bg-white rounded-2xl border border-slate-100 shadow-sm">
                                    <label class="block text-sm md:text-sm font-black uppercase tracking-widest text-slate-600 mb-2">Stage Color</label>
                                    <input v-model="stage.color" type="color" class="h-10 w-full rounded-lg border-slate-200" />
                                </div>
                            </div>
                        </div>

                        <!-- Automation & Governance Section -->
                        <div class="space-y-4 md:space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="h-6 w-1 bg-indigo-500 rounded-full"></div>
                                <h4 class="text-sm md:text-base font-black uppercase tracking-[0.25em] text-slate-800">Governance Engine</h4>
                            </div>
                            <div class="space-y-4 md:space-y-6">
                                <div>
                                    <label class="block text-sm md:text-sm font-black text-slate-400 uppercase tracking-widest mb-3">SLA Autoclose Days</label>
                                    <input v-model="stage.auto_close_days" type="number" class="w-full bg-white rounded-2xl border-slate-200 px-5 py-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all shadow-sm" placeholder="0 = Off" />
                                </div>
                                <div class="p-6 bg-indigo-50 border border-indigo-100 rounded-[2rem] space-y-4">
                                    <label class="flex items-center justify-between cursor-pointer group">
                                        <div>
                                            <span class="text-xs font-black uppercase tracking-widest text-slate-900 group-hover:text-indigo-600 transition-colors">Approval Gate</span>
                                            <p class="text-[10px] font-bold text-slate-400 mt-0.5">Require manager authorization to exit</p>
                                        </div>
                                        <input v-model="stage.requires_approval" type="checkbox" class="h-6 w-6 rounded-lg border-slate-200 text-indigo-600 focus:ring-indigo-500" />
                                    </label>
                                    
                                    <transition name="fade">
                                        <div v-if="stage.requires_approval" class="pt-4 border-t border-indigo-200/50 space-y-3">
                                            <label class="block text-[10px] font-black text-indigo-400 uppercase tracking-widest">Designated Approver (Role)</label>
                                            <select v-model="stage.role_id" class="w-full bg-white rounded-xl border-indigo-100 px-4 py-2 text-xs font-bold focus:ring-indigo-500 transition-all">
                                                <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                            </select>
                                        </div>
                                    </transition>
                                </div>
                                <div>
                                    <label class="block text-sm md:text-sm font-black text-slate-400 uppercase tracking-widest mb-3">Default Team Assignment</label>
                                    <select v-model="stage.assigned_team_id" class="w-full bg-white rounded-2xl border-slate-200 px-5 py-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/5 focus:border-indigo-500 transition-all shadow-sm">
                                        <option :value="null">Transparent Routing</option>
                                        <option v-for="team in teams" :key="team.id" :value="team.id">{{ team.name }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Security Section -->
                        <div class="space-y-4 md:space-y-6">
                            <div class="flex items-center gap-3">
                                <div class="h-6 w-1 bg-rose-500 rounded-full"></div>
                                <h4 class="text-sm md:text-base font-black uppercase tracking-[0.25em] text-slate-800">Security & Portal</h4>
                            </div>
                            <div class="space-y-3 md:space-y-6">
                                <div>
                                    <label class="block text-sm md:text-sm font-black text-slate-400 uppercase tracking-widest mb-3">Authority Level</label>
                                    <select v-model="stage.role_id" class="w-full bg-white rounded-2xl border-slate-200 px-5 py-4 text-sm font-bold focus:ring-4 focus:ring-rose-500/5 focus:border-rose-500 transition-all shadow-sm">
                                        <option :value="null">Public Access</option>
                                        <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
                                    </select>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-1 gap-3">
                                    <label class="flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-100 shadow-sm cursor-pointer">
                                        <span class="text-sm md:text-sm font-black uppercase tracking-widest text-slate-600">Terminal</span>
                                        <input v-model="stage.is_final" type="checkbox" class="h-6 w-6 rounded-lg border-slate-200 text-emerald-600" />
                                    </label>
                                    <label class="flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-100 shadow-sm cursor-pointer">
                                        <span class="text-sm md:text-sm font-black uppercase tracking-widest text-slate-600">QA Gate</span>
                                        <input v-model="stage.requires_verification" type="checkbox" class="h-6 w-6 rounded-lg border-slate-200 text-rose-600" />
                                    </label>
                                    <label class="flex items-center justify-between p-4 bg-white rounded-2xl border border-slate-100 shadow-sm cursor-pointer sm:col-span-2 md:col-span-1">
                                        <span class="text-sm md:text-sm font-black uppercase tracking-widest text-slate-600">Portal Visible</span>
                                        <input v-model="stage.is_client_visible" type="checkbox" class="h-6 w-6 rounded-lg border-slate-200 text-emerald-600" />
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- NEW: Flow Control Section -->
                        <div class="md:col-span-3 pt-6 border-t border-slate-100">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12">
                                <div class="space-y-4 md:space-y-6">
                                    <div class="flex items-center gap-3">
                                        <div class="h-6 w-1 bg-amber-500 rounded-full"></div>
                                        <h4 class="text-sm md:text-base font-black uppercase tracking-[0.25em] text-slate-800">Support</h4>
                                    </div>
                                    <div>
                                        <label class="block text-sm md:text-sm font-black text-slate-400 uppercase tracking-widest mb-3">Assigned Mentor</label>
                                        <select v-model="stage.mentor_id" class="w-full bg-white rounded-2xl border-slate-200 px-5 py-4 text-sm font-bold focus:ring-4 focus:ring-amber-500/5 focus:border-amber-500 transition-all shadow-sm">
                                            <option :value="null">No Mentor (Standard flow)</option>
                                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="space-y-4 md:space-y-6">
                                    <div class="flex items-center gap-3">
                                        <div class="h-6 w-1 bg-emerald-500 rounded-full"></div>
                                        <h4 class="text-sm md:text-base font-black uppercase tracking-[0.25em] text-slate-800">Transitions</h4>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3 max-h-48 overflow-y-auto pr-2 custom-scrollbar">
                                        <label v-for="s in localStages.filter(ls => ls.id !== stage.id)" :key="s.id" class="flex items-center gap-3 p-3 bg-white border border-slate-100 rounded-xl cursor-pointer hover:bg-emerald-50 transition-colors">
                                            <input type="checkbox" :value="s.id" v-model="stage.transition_rules" class="h-4 w-4 rounded text-emerald-600 border-slate-300" />
                                            <span class="text-sm font-bold text-slate-600">{{ s.name }}</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-8 md:pt-10 border-t border-slate-100 flex flex-col md:flex-row justify-between items-center gap-6">
                        <button @click="deleteStage(stage.id)" class="flex items-center gap-2 text-rose-500 hover:text-rose-700 text-sm font-black uppercase tracking-widest transition-colors">
                            <TrashIcon class="w-4 h-4" />
                            Purge Stage
                        </button>
                        <div class="flex w-full md:w-auto gap-3">
                            <button @click="expandedStage = null" class="flex-1 md:flex-none px-6 py-4 bg-white text-slate-500 rounded-xl text-sm font-black uppercase tracking-widest border border-slate-200 hover:bg-slate-50 transition-all">Dismiss</button>
                            <button @click="updateStageInline(stage)" :disabled="isSaving" class="flex-[2] md:flex-none justify-center px-8 py-4 bg-emerald-600 text-white rounded-xl text-sm font-black uppercase tracking-widest shadow-xl shadow-emerald-500/20 active:translate-y-0.5 transition-all flex items-center gap-3">
                                <CloudArrowUpIcon v-if="!isSaving" class="w-4 h-4" />
                                <svg v-else class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Update Protocol
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Stage Modal -->
        <div v-if="showAddModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/30 backdrop-blur-sm animate-in fade-in duration-300">
            <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-lg overflow-hidden border border-slate-100 animate-in slide-in-from-bottom-8 duration-500">
                <div class="p-10 space-y-8">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-2xl font-black tracking-tight text-slate-900">Initialize New Stage</h3>
                            <p class="text-slate-400 text-base font-black uppercase tracking-widest mt-1">Expanding the operational pipeline</p>
                        </div>
                        <button @click="showAddModal = false" class="p-2 hover:bg-slate-100 rounded-xl transition-all">
                            <XMarkIcon class="w-6 h-6 text-slate-400" />
                        </button>
                    </div>

                    <div class="space-y-4">
                        <label class="block text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Operational Identity</label>
                        <input 
                            v-model="newStageName" 
                            @keyup.enter="confirmAddStage"
                            type="text" 
                            autofocus
                            placeholder="e.g. UAT Verification"
                            class="w-full bg-slate-50 rounded-2xl border-transparent px-6 py-5 text-lg font-bold focus:ring-4 focus:ring-emerald-500/5 focus:bg-white focus:border-emerald-500 transition-all" 
                        />
                    </div>

                    <div class="flex gap-4 pt-4">
                        <button @click="showAddModal = false" class="flex-1 py-5 bg-slate-100 text-slate-500 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all">Cancel</button>
                        <button @click="confirmAddStage" :disabled="!newStageName.trim() || isSaving" class="flex-[2] py-5 bg-emerald-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-emerald-500/20 hover:bg-emerald-700 active:scale-[0.98] transition-all flex items-center justify-center gap-3">
                            <PlusIcon v-if="!isSaving" class="w-4 h-4" />
                            <svg v-else class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Engage Stage
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Stage People / Participant Modal (Phase 11) -->
        <div v-if="showPeopleModal" class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/30 backdrop-blur-md animate-in fade-in duration-300">
            <div class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-2xl overflow-hidden border border-slate-100 animate-in zoom-in-95 duration-300">
                <div class="p-8 md:p-12 h-[80vh] overflow-y-auto custom-scrollbar">
                    <div class="flex justify-between items-start mb-8">
                        <div class="flex items-center gap-4">
                            <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl">
                                <UserGroupIcon class="w-8 h-8" />
                            </div>
                            <div>
                                <h3 class="text-2xl font-black tracking-tight text-slate-900">Stage Personnel</h3>
                                <p class="text-slate-400 text-xs font-bold uppercase tracking-widest mt-1">Configuring Triage / QA / Dev for: {{ selectedStage?.name }}</p>
                            </div>
                        </div>
                        <button @click="showPeopleModal = false" class="p-2 hover:bg-slate-100 rounded-xl transition-all">
                            <XMarkIcon class="w-6 h-6 text-slate-400" />
                        </button>
                    </div>

                    <div class="space-y-12">
                        <!-- Section: SLA & Priority -->
                        <div class="bg-indigo-50/30 p-8 rounded-[2.5rem] border border-indigo-100 shadow-sm">
                            <h3 class="flex items-center gap-3 text-sm font-black text-indigo-600 uppercase tracking-[0.2em] mb-6">
                                <ClockIcon class="w-5 h-5" />
                                Service Level & Priority
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest">SLA Autoclose Days</label>
                                    <input type="number" v-model="peopleForm.auto_close_days" class="w-full bg-white border-slate-200 rounded-2xl px-5 py-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 transition-all" />
                                    <p class="text-[10px] italic text-slate-400">Tickets will auto-verify after X days of inactivity in this stage.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Section: Stage Personnel (Searchable) -->
                        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm space-y-8">
                            <h3 class="flex items-center gap-3 text-sm font-black text-slate-900 uppercase tracking-[0.2em]">
                                <UserGroupIcon class="w-5 h-5 text-emerald-500" />
                                Stage Personnel (In-Charge)
                            </h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-4">
                                    <label class="block text-[10px] font-black text-indigo-500 uppercase tracking-widest">Participating Roles</label>
                                    <MultiUserSelect 
                                        v-model="selectedRoleIds" 
                                        :items="roles" 
                                        placeholder="Search roles (QA, Lead...)"
                                    />
                                </div>
                                <div class="space-y-4">
                                    <label class="block text-[10px] font-black text-emerald-500 uppercase tracking-widest">Participating Users</label>
                                    <MultiUserSelect 
                                        v-model="selectedUserIds" 
                                        :items="users" 
                                        placeholder="Search 100s of employees..."
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Section: Operational Protocol -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="bg-indigo-600 p-8 rounded-[2.5rem] text-white shadow-xl shadow-indigo-200 space-y-6">
                                <h3 class="flex items-center gap-3 text-sm font-black text-indigo-200 uppercase tracking-[0.2em]">
                                    <CpuChipIcon class="w-5 h-5" />
                                    Operational Protocol
                               </h3>
                                
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between p-4 bg-white/10 rounded-2xl border border-white/20">
                                        <div>
                                            <span class="text-xs font-black uppercase tracking-widest">Mandatory Verification</span>
                                            <p class="text-[10px] text-indigo-200 mt-1">Requires explicit human sign-off</p>
                                        </div>
                                        <div @click="peopleForm.requires_verification = !peopleForm.requires_verification"
                                             class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200"
                                             :class="peopleForm.requires_verification ? 'bg-emerald-500' : 'bg-indigo-400'">
                                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white transition duration-200 ease-in-out"
                                                  :class="peopleForm.requires_verification ? 'translate-x-5' : 'translate-x-0'"></span>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <label class="block text-[10px] font-black text-indigo-200 uppercase tracking-widest">Assigned Mentor</label>
                                        <select v-model="peopleForm.mentor_id" class="w-full bg-white text-slate-900 border-none rounded-2xl px-5 py-4 text-sm font-bold focus:ring-4 focus:ring-indigo-300 transition-all">
                                            <option :value="null">Choose from personnel...</option>
                                            <option v-for="u in mentorOptions" :key="u.id" :value="u.id">{{ u.name }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-6">
                                <!-- Security & Portal -->
                                <div class="bg-rose-50/50 p-8 rounded-[2.5rem] border border-rose-100 space-y-6">
                                    <h3 class="flex items-center gap-3 text-sm font-black text-rose-600 uppercase tracking-[0.2em]">
                                        <ShieldCheckIcon class="w-5 h-5" />
                                        Security & Portal Support
                                    </h3>
                                    
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="text-xs font-black uppercase tracking-widest text-slate-800">Is Client Visible?</span>
                                                <p class="text-[10px] text-slate-400 mt-0.5">Show this stage on client portal</p>
                                            </div>
                                            <div @click="peopleForm.is_client_visible = !peopleForm.is_client_visible"
                                                 class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200"
                                                 :class="peopleForm.is_client_visible ? 'bg-rose-500' : 'bg-slate-200'">
                                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white transition duration-200 ease-in-out"
                                                      :class="peopleForm.is_client_visible ? 'translate-x-5' : 'translate-x-0'"></span>
                                            </div>
                                        </div>

                                        <div class="flex items-center justify-between">
                                            <div>
                                                <span class="text-xs font-black uppercase tracking-widest text-slate-800">Notify Reporter</span>
                                                <p class="text-[10px] text-slate-400 mt-0.5">Auto-email client on stage entry</p>
                                            </div>
                                            <div @click="peopleForm.notify_client = !peopleForm.notify_client"
                                                 class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200"
                                                 :class="peopleForm.notify_client ? 'bg-indigo-600' : 'bg-slate-200'">
                                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white transition duration-200 ease-in-out"
                                                      :class="peopleForm.notify_client ? 'translate-x-5' : 'translate-x-0'"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-4 mt-12 pt-8 border-t border-slate-100">
                        <button @click="showPeopleModal = false" class="flex-1 py-5 bg-slate-100 text-slate-500 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-slate-200 transition-all uppercase tracking-widest">Cancel</button>
                        <button @click="savePeopleConfig" :disabled="isSaving" class="flex-[2] py-5 bg-indigo-600 text-white rounded-2xl text-xs font-black uppercase tracking-widest shadow-xl shadow-indigo-200 hover:bg-indigo-700 active:scale-[0.98] transition-all flex items-center justify-center gap-3">
                            <CloudArrowUpIcon v-if="!isSaving" class="w-4 h-4" />
                            <svg v-else class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Save Configuration
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import { 
    ChevronUpIcon, 
    ChevronDownIcon, 
    CheckIcon, 
    PlusIcon,
    ClockIcon, 
    UserGroupIcon, 
    ShieldCheckIcon, 
    LockClosedIcon, 
    ChevronRightIcon,
    CpuChipIcon,
    CloudArrowUpIcon,
    TrashIcon,
    SquaresPlusIcon,
    XMarkIcon,
    Cog6ToothIcon,
    UserIcon
} from '@heroicons/vue/24/outline';
import axios from 'axios';
import MultiUserSelect from '@/Components/MultiUserSelect.vue';
import { computed } from 'vue';

const props = defineProps({
    workflow: Object,
    teams: { type: Array, default: () => [] },
    roles: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] }
});

const localStages = ref(props.workflow?.stages || []);
const currentWorkflow = ref(props.workflow);
const teams = ref(props.teams);
const roles = ref(props.roles);
const users = ref(props.users);
const isOrderDirty = ref(false);
const expandedStage = ref(null);
const isSaving = ref(false);

// Phase 11 Phase: People Management
const showPeopleModal = ref(false);
const selectedStage = ref(null);
const peopleForm = ref({
    approver_type: 'manager',
    user_id: null,
    role_id: null,
    team_id: null,
    additional_approvers: [],
    stage_personnel: [],
    notify_client: false,
    auto_close_days: 0,
    requires_verification: false,
    mentor_id: null
});

const selectedUserIds = computed({
    get: () => (peopleForm.value.stage_personnel || []).filter(p => p.type === 'user').map(p => p.id),
    set: (ids) => {
        const others = (peopleForm.value.stage_personnel || []).filter(p => p.type !== 'user');
        peopleForm.value.stage_personnel = [...others, ...ids.map(id => ({ type: 'user', id: Number(id) }))];
    }
});

const selectedRoleIds = computed({
    get: () => (peopleForm.value.stage_personnel || []).filter(p => p.type === 'role').map(p => p.id),
    set: (ids) => {
        const others = (peopleForm.value.stage_personnel || []).filter(p => p.type !== 'role');
        peopleForm.value.stage_personnel = [...others, ...ids.map(id => ({ type: 'role', id: Number(id) }))];
    }
});

const mentorOptions = computed(() => {
    const userIds = selectedUserIds.value;
    if (!userIds.length) return users.value; // Fallback if none selected
    return users.value.filter(u => userIds.includes(u.id));
});

const openPeopleManager = (stage) => {
    selectedStage.value = stage;
    peopleForm.value = {
        approver_type: stage.approver_type || 'manager',
        user_id: stage.user_id,
        role_id: stage.role_id,
        team_id: stage.team_id,
        additional_approvers: stage.additional_approvers || [],
        stage_personnel: stage.stage_personnel || [],
        notify_client: !!stage.notify_client,
        auto_close_days: stage.auto_close_days || 0,
        requires_verification: !!stage.requires_verification,
        mentor_id: stage.mentor_id
    };
    showPeopleModal.value = true;
};

const togglePersonnel = (type, id) => {
    const index = peopleForm.value.stage_personnel.findIndex(p => p.type === type && p.id === id);
    if (index === -1) {
        peopleForm.value.stage_personnel.push({ type, id });
    } else {
        peopleForm.value.stage_personnel.splice(index, 1);
    }
};

const isPersonnelSelected = (type, id) => {
    return peopleForm.value.stage_personnel.some(p => p.type === type && p.id === id);
};

const savePeopleConfig = async () => {
    isSaving.value = true;
    try {
        await axios.post(route('workflow-architect.stages.people.update', selectedStage.value.id), peopleForm.value);
        
        // Update local state
        const idx = localStages.value.findIndex(s => s.id === selectedStage.value.id);
        if (idx !== -1) {
            localStages.value[idx] = { ...localStages.value[idx], ...peopleForm.value };
        }
        
        showPeopleModal.value = false;
        router.reload({ only: ['workflow'] });
    } catch (e) {
        console.error("Failed to save people config", e);
    } finally {
        isSaving.value = false;
    }
};

const newStageName = ref('');

const loadData = async () => {
    try {
        const { data } = await axios.get(route('workflow-architect.index'), {
            headers: { 'Accept': 'application/json' }
        });
        currentWorkflow.value = data.workflow;
        localStages.value = data.workflow.stages;
        teams.value = data.teams;
        roles.value = data.roles;
        users.value = data.users;
        isOrderDirty.value = false;
    } catch (e) {
        console.error("Failed to load workflow", e);
    }
};

const getRoleName = (id) => {
    return props.roles.find(r => r.id === id)?.name || 'Generic Role';
};

const getUserName = (id) => {
    return props.users.find(u => u.id === id)?.name || 'Specific User';
};

const getTeamName = (id) => {
    return props.teams.find(t => t.id === id)?.name || 'None';
};

const toggleStage = (id) => {
    expandedStage.value = expandedStage.value === id ? null : id;
};

const moveUp = (index) => {
    if (index === 0) return;
    const items = [...localStages.value];
    [items[index], items[index - 1]] = [items[index - 1], items[index]];
    localStages.value = items;
    isOrderDirty.value = true;
};

const moveDown = (index) => {
    if (index === localStages.value.length - 1) return;
    const items = [...localStages.value];
    [items[index], items[index + 1]] = [items[index + 1], items[index]];
    localStages.value = items;
    isOrderDirty.value = true;
};

const saveOrder = async () => {
    isSaving.value = true;
    try {
        const orderPayload = localStages.value.map((s, i) => ({ id: s.id, order: i + 1 }));
        await axios.post(route('workflow-architect.stages.reorder'), { stages: orderPayload });
        isOrderDirty.value = false;
        router.reload({ only: ['workflow'] }); // Refresh workflow data to sync order
    } catch (e) {
        console.error("Reorder failed", e);
    } finally {
        isSaving.value = false;
    }
};

const updateStageInline = async (stage) => {
    isSaving.value = true;
    try {
        await axios.put(route('workflow-architect.stages.update', stage.id), stage);
        expandedStage.value = null;
    } catch (e) {
        console.error("Update failed", e);
    } finally {
        isSaving.value = false;
    }
};

const confirmAddStage = async () => {
    if (!newStageName.value.trim()) return;

    isSaving.value = true;
    try {
        if (!currentWorkflow.value?.id) {
            alert("Application State Error: No active workflow context found. Please refresh.");
            return;
        }
        const { data } = await axios.post(route('workflow-architect.stages.store'), {
            workflow_id: currentWorkflow.value.id,
            name: newStageName.value
        });
        localStages.value.push(data.stage);
        expandedStage.value = data.stage.id;
        showAddModal.value = false;
        newStageName.value = '';
        
        await nextTick();
        document.getElementById('stage-' + data.stage.id)?.scrollIntoView({ behavior: 'smooth' });
    } catch (e) {
        console.error("Add failed", e);
    } finally {
        isSaving.value = false;
    }
};

const deleteStage = async (id) => {
    if (!confirm("Are you sure you want to purge this stage? This cannot be undone.")) return;

    isSaving.value = true;
    try {
        await axios.delete(route('workflow-architect.stages.destroy', id));
        localStages.value = localStages.value.filter(s => s.id !== id);
        expandedStage.value = null;
    } catch (e) {
        console.error("Delete failed", e);
    } finally {
        isSaving.value = false;
    }
};

onMounted(() => {
    if (localStages.value.length === 0) {
        loadData();
    }
});
</script>

<style scoped>
.animate-in { animation-fill-mode: both; }
@keyframes fade-in { from { opacity: 0; } to { opacity: 1; } }
@keyframes slide-in-from-top-4 { from { opacity: 0; transform: translateY(-1rem); } to { opacity: 1; transform: translateY(0); } }
@keyframes slide-in-from-bottom-8 { from { opacity: 0; transform: translateY(2rem); } to { opacity: 1; transform: translateY(0); } }
.fade-in { animation: fade-in 0.5s ease-out; }
.slide-in-from-top-4 { animation: slide-in-from-top-4 0.4s ease-out; }
.slide-in-from-bottom-8 { animation: slide-in-from-bottom-8 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>

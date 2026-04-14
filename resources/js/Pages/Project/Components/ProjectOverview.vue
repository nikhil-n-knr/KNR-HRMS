<template>
    <div class="space-y-6">
        <!-- Stats Row -->
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
            <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                     <p class="text-sm font-medium text-gray-500">Total Tasks</p>
                     <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ stats.total_tasks }}</h3>
                </div>
                <div class="p-3 bg-indigo-50 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                </div>
            </div>

             <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                     <p class="text-sm font-medium text-gray-500">Completed</p>
                     <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ stats.completed_tasks }}</h3>
                </div>
                <div class="p-3 bg-emerald-50 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </div>
            </div>

             <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                     <p class="text-sm font-medium text-gray-500">Overdue</p>
                     <h3 class="text-2xl font-bold text-red-600 mt-1">{{ stats.overdue_tasks }}</h3>
                </div>
                <div class="p-3 bg-red-50 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>

             <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                     <p class="text-sm font-medium text-gray-500">Team Size</p>
                     <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ stats.members_count }}</h3>
                </div>
                <div class="p-3 bg-blue-50 rounded-full">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                </div>
            </div>

            <!-- Contributors (Matrix + Financials) -->
             <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex flex-col justify-between gap-4">
                <div class="flex items-center justify-between">
                    <div>
                         <p class="text-sm font-medium text-gray-500">Contributors</p>
                         <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ stats.contributors_count }}</h3>
                    </div>
                     <div class="p-3 bg-orange-50 rounded-full">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                         </svg>
                    </div>
                </div>
                
                <!-- Financials (Time) -->
                <!-- Financials (Time) -->
                <div class="flex justify-between items-center pt-3 border-t border-gray-100 text-xs text-center gap-1">
                    <div class="bg-gray-50 p-1.5 rounded w-1/4">
                        <p class="text-sm text-gray-400 uppercase font-bold tracking-wider">Exp</p>
                        <p class="font-bold text-gray-900">{{ stats.expected_hours }}h</p>
                    </div>
                    <div class="bg-gray-50 p-1.5 rounded w-1/4">
                        <p class="text-sm text-gray-400 uppercase font-bold tracking-wider">Act</p>
                        <p class="font-bold text-emerald-600">{{ stats.invested_hours }}h</p>
                    </div>
                    <div class="bg-gray-50 p-1.5 rounded w-1/4">
                        <p class="text-sm text-gray-400 uppercase font-bold tracking-wider">Rem</p>
                        <p class="font-bold text-gray-500">{{ stats.pending_hours }}h</p>
                    </div>
                    <div class="bg-gray-50 p-1.5 rounded w-1/4">
                        <p class="text-sm text-gray-400 uppercase font-bold tracking-wider">Tot</p>
                        <p class="font-bold text-indigo-600">{{ stats.total_scope_hours }}h</p>
                    </div>
                </div>
            </div>

            <!-- Involved Teams -->
             <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between">
                <div>
                     <p class="text-sm font-medium text-gray-500">Involved Teams</p>
                     <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ stats.involved_teams_count }}</h3>
                </div>
                <div class="p-3 bg-purple-50 rounded-full">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                     </svg>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column (Wider) -->
            <div class="lg:col-span-2 space-y-6">
                 <!-- Detailed Contributors List -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 overflow-hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-900">Contributors</h3>
                        <span class="bg-orange-100 text-orange-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ contributors.length }}</span>
                    </div>
                    
                    <div class="space-y-4 max-h-[400px] overflow-y-auto custom-scrollbar pr-2">
                         <div v-for="user in contributors" :key="user.id" class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                             <div class="flex items-center gap-3 mb-3">
                                <img v-if="user.avatar" :src="user.avatar" :alt="user.name" class="h-8 w-8 rounded-full object-cover shadow-sm">
                                <div v-else class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                     <p class="text-sm font-bold text-gray-900 truncate">{{ user.name }}</p>
                                     <p class="text-xs text-gray-500 truncate">{{ user.email }}</p>
                                </div>
                             </div>
                             
                             <!-- Mini Stats Grid -->
                             <div class="grid grid-cols-2 sm:grid-cols-4 gap-1 text-center text-xs">
                                 <div class="bg-white p-1.5 rounded border border-gray-100">
                                     <p class="text-gray-400 font-bold uppercase text-sm">Exp</p>
                                     <p class="text-indigo-600 font-bold">{{ user.plan_hours }}h</p>
                                 </div>
                                 <div class="bg-white p-1.5 rounded border border-gray-100">
                                     <p class="text-gray-400 font-bold uppercase text-sm">Act</p>
                                     <p :class="[
                                         user.plan_hours == 0 && user.invested_hours > 0 ? 'text-orange-600 font-black' : 
                                         (user.invested_hours > user.plan_hours && user.plan_hours > 0 ? 'text-red-600 font-black' : 'text-emerald-600 font-bold')
                                     ]">
                                         {{ user.invested_hours }}h
                                         <span v-if="user.plan_hours == 0 && user.invested_hours > 0" class="block text-xs text-orange-500 font-normal leading-none mt-0.5">UNPLANNED</span>
                                         <span v-else-if="user.invested_hours > user.plan_hours && user.plan_hours > 0" class="block text-xs text-red-500 font-normal leading-none mt-0.5">OVERTIME</span>
                                     </p>
                                 </div>
                                 <div class="bg-white p-1.5 rounded border border-gray-100">
                                     <p class="text-gray-400 font-bold uppercase text-sm">Rem</p>
                                     <p class="text-gray-600 font-bold">{{ user.pending_hours }}h</p>
                                 </div>
                                 <div class="bg-white p-1.5 rounded border border-gray-100">
                                     <p class="text-gray-400 font-bold uppercase text-sm">Tot</p>
                                     <p class="text-gray-900 font-bold">{{ user.total_hours }}h</p>
                                 </div>
                             </div>
                         </div>
                         
                         <div v-if="contributors.length === 0" class="text-center py-4 text-gray-400 text-sm">
                            No active contributors in matrix.
                         </div>
                    </div>
                </div>

                <!-- Members (All Involved) -->
                 <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 overflow-hidden">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-900">Project Members</h3>
                        <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-0.5 rounded-full" title="Total Unique Participants">{{ members.length }}</span>
                    </div>
                    
                    <div class="space-y-4 max-h-[400px] overflow-y-auto custom-scrollbar pr-2">
                         <div v-for="member in members" :key="member.id" class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg transition-colors border border-transparent hover:border-gray-100">
                             <div class="relative">
                                <img v-if="member.profile_photo_url || member.avatar" :src="member.profile_photo_url || member.avatar" :alt="member.name" class="h-10 w-10 rounded-full object-cover border border-gray-100 shadow-sm">
                                <div v-else class="h-10 w-10 rounded-full bg-gray-100 border border-gray-100 flex items-center justify-center text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <span class="absolute bottom-0 right-0 h-2.5 w-2.5 bg-emerald-500 border-2 border-white rounded-full"></span>
                             </div>
                             <div class="flex-1 min-w-0">
                                 <p class="text-sm font-bold text-gray-900 truncate">{{ member.name }}</p>
                                 <p class="text-xs text-gray-500 truncate">{{ member.email }}</p>
                             </div>
                             <!-- Role/Type Badge (Inferred) -->
                             <span v-if="member.pivot" class="text-xs text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded">
                                {{ member.pivot.role || 'Member' }}
                             </span>
                         </div>
                         <div v-if="members.length === 0" class="text-center py-4 text-gray-400 text-sm">
                            No members found.
                         </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                <!-- Project Details -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 relative">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-bold text-gray-900">Project Details</h3>
                        <button v-can="'project-edit'" @click="openEditModal" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold hover:underline px-2 py-1 bg-indigo-50 rounded transition-colors">Edit</button>
                    </div>
                    <div class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Client</span>
                            <span class="font-medium text-gray-900">{{ project.client?.name || '--' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Code</span>
                            <span class="font-mono bg-gray-100 px-2 py-0.5 rounded text-gray-700 text-xs">{{ project.code }}</span>
                        </div>
                         <div class="flex justify-between">
                            <span class="text-gray-500">Created</span>
                            <span class="text-gray-900">{{ new Date(project.created_at).toLocaleDateString() }}</span>
                        </div>
                    </div>
                </div>

                <!-- Governance Control (Management Only) -->
                <div v-can="'project-manage'" class="bg-gradient-to-br from-slate-900 to-indigo-950 rounded-xl border border-indigo-900 shadow-2xl p-6 text-white relative overflow-hidden group">
                    <div class="absolute -right-8 -top-8 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-1000"></div>
                    
                    <div class="relative z-10 space-y-6">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xs font-black uppercase tracking-[0.2em] text-indigo-300">Governance & Stakeholder Integrity</h3>
                            <div class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></div>
                        </div>

                        <!-- Pending Sign-offs Indicator -->
                        <div v-if="project.documents?.filter(d => d.category === 'requirement' && !d.is_signed).length > 0" class="p-4 bg-emerald-950/50 border border-emerald-500/20 rounded-2xl">
                             <h4 class="text-[9px] font-black uppercase tracking-widest text-emerald-400 mb-3 flex items-center gap-2">
                                <i class="fas fa-exclamation-triangle"></i>
                                Pending Governance Vault ({{ project.documents?.filter(d => d.category === 'requirement' && !d.is_signed).length }})
                             </h4>
                             <div class="space-y-2">
                                <div v-for="doc in project.documents?.filter(d => d.category === 'requirement' && !d.is_signed)" :key="doc.id" class="flex justify-between items-center p-2 bg-white/5 rounded-lg border border-white/5">
                                    <span class="text-[10px] font-bold text-slate-300 truncate max-w-[150px]">{{ doc.name }}</span>
                                    <span class="text-[8px] font-black text-rose-400 uppercase tracking-tighter">Awaiting Sign-off</span>
                                </div>
                             </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Health Index Override</label>
                                <input v-model="govForm.project_health_index" type="range" class="w-full h-1.5 bg-white/10 rounded-lg appearance-none cursor-pointer accent-emerald-500">
                                <div class="flex justify-between text-[10px] font-bold mt-2">
                                    <span :class="getHealthColorClass(govForm.project_health_index)">{{ govForm.project_health_index }}% Satisfaction</span>
                                    <span class="text-slate-500">Node Logic: {{ project.project_health_index || 100 }}%</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Manual Progress</label>
                                <div class="flex items-center gap-4">
                                    <input v-model="govForm.manual_progress_percentage" type="number" min="0" max="100" class="w-20 bg-white/5 border-white/10 rounded-lg text-sm font-black focus:ring-1 focus:ring-emerald-500">
                                    <span class="text-[10px] text-slate-500 uppercase font-black">Force Percent</span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase tracking-widest text-slate-400 mb-2">Status Overdrive</label>
                                <select v-model="govForm.manual_status_label" class="w-full bg-white/5 border-white/10 rounded-lg text-xs font-bold focus:ring-1 focus:ring-emerald-500 py-2">
                                    <option value="Initialising" class="bg-slate-900">Initialising</option>
                                    <option value="In Development" class="bg-slate-900">In Development</option>
                                    <option value="UAT Phase" class="bg-slate-900">UAT Phase</option>
                                    <option value="Nearing Launch" class="bg-slate-900">Nearing Launch</option>
                                    <option value="Stabilization" class="bg-slate-900">Stabilization</option>
                                    <option value="Delayed (Internal)" class="bg-slate-900">Delayed (Critical Fix)</option>
                                </select>
                            </div>

                            <textarea v-model="govForm.reason" placeholder="Oversight Reason (Internal Log)..." rows="2" class="w-full bg-white/5 border-white/10 rounded-lg text-[10px] font-bold placeholder:text-slate-600 focus:ring-1 focus:ring-emerald-500"></textarea>

                            <button @click="submitGovernance" :disabled="govForm.processing" class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white text-[10px] font-black uppercase tracking-widest rounded-xl transition-all shadow-lg active:scale-95 disabled:opacity-50">
                                Commit Governance Artifact
                            </button>
                        </div>
                        
                        <div class="pt-4 border-t border-white/5">
                             <button @click="showHistory = !showHistory" class="text-[9px] font-black uppercase tracking-widest text-indigo-400 hover:text-white transition-colors flex items-center gap-2">
                                <i class="fas fa-history"></i>
                                View Dealing Audit Log
                             </button>
                        </div>
                    </div>
                </div>

                <!-- Active Sprint -->
                <div v-if="activeSprint" class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-indigo-50 to-white flex justify-between items-center">
                        <div>
                             <h3 class="font-bold text-gray-900">Active Sprint</h3>
                             <p class="text-xs text-gray-500">{{ activeSprint.name }}</p>
                        </div>
                        <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full uppercase">Running</span>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center justify-between text-sm text-gray-600 mb-2">
                             <span>Progress</span>
                             <span class="font-bold">--%</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2.5">
                            <div class="bg-indigo-600 h-2.5 rounded-full" style="width: 45%"></div>
                        </div>
                         <p class="text-xs text-center text-gray-400 mt-2">Burndown chart placeholder</p>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm flex flex-col h-[500px]">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="font-bold text-gray-900">Recent Activity</h3>
                    </div>
                    <div class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                         <div v-if="activity.length === 0" class="text-center text-gray-400 py-10">No recent activity</div>
                         <ul class="space-y-6 relative border-l border-gray-200 ml-3">
                            <li v-for="act in activity" :key="act.id" class="ml-6 relative">
                                <span class="absolute -left-[31px] top-1 h-2.5 w-2.5 rounded-full bg-indigo-200 border-2 border-white ring-2 ring-indigo-50"></span>
                                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-1">
                                     <div class="text-sm">
                                        <span class="font-bold text-gray-900">{{ act.user?.name || 'System' }}</span>
                                        <span class="text-gray-600"> {{ formatActivityType(act.type) }} </span>
                                        <span class="font-medium text-emerald-600 hover:underline cursor-pointer">
                                            {{ act.task?.title || '#'+act.task_id }}
                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-400 whitespace-nowrap">{{ formatTime(act.created_at) }}</span>
                                </div>
                                <p v-if="act.details" class="mt-1 text-xs text-gray-500 bg-gray-50 p-2 rounded border border-gray-100">
                                    {{ formatDetails(act) }}
                                </p>
                            </li>
                         </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Project Modal -->
        <Modal :show="showEditModal" @close="closeEditModal" title="Edit Project Details">
             <form @submit.prevent="submitEditForm" id="editProjectForm">
                 <div class="space-y-4">
                     <BaseInput
                        v-model="editForm.name"
                        label="Project Name"
                        color="indigo"
                        :error="editForm.errors.name"
                     />
                     <BaseInput
                        v-model="editForm.code"
                        label="Project Code"
                        color="indigo"
                        class="uppercase"
                        :error="editForm.errors.code"
                     />
                     <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Project Status</label>
                        <select v-model="editForm.status" class="w-full rounded-xl shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 px-4 bg-white/50 backdrop-blur-sm">
                            <option value="planning">Planning (Draft)</option>
                            <option value="active">Active (In Progress)</option>
                            <option value="on_hold">On Hold</option>
                            <option value="completed">Completed</option>
                        </select>
                        <p v-if="editForm.errors.status" class="text-xs text-red-600 animate-pulse mt-1">{{ editForm.errors.status }}</p>
                     </div>
                     <div class="space-y-1">
                        <label class="block text-sm font-medium text-gray-700">Project Description</label>
                        <textarea
                            v-model="editForm.description"
                            class="w-full rounded-xl shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2.5 px-4 bg-white/50 backdrop-blur-sm"
                            rows="4"
                            placeholder="Brief description of the project goals..."
                        ></textarea>
                        <p v-if="editForm.errors.description" class="text-xs text-red-600 animate-pulse mt-1">{{ editForm.errors.description }}</p>
                     </div>
                 </div>
             </form>
             <template #footer>
                 <div class="flex justify-end gap-3 w-full">
                     <SecondaryButton @click="closeEditModal">Cancel</SecondaryButton>
                     <PrimaryButton type="submit" form="editProjectForm" :disabled="editForm.processing">Save Changes</PrimaryButton>
                 </div>
             </template>
        </Modal>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import ProjectLayout from '@/Layouts/ProjectLayout.vue';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

defineOptions({ layout: ProjectLayout });

const props = defineProps({
    project: Object,
    stats: Object,
    members: Array,
    contributors: Array, // New Prop
    activity: Array,
    activeSprint: Object
});

const showEditModal = ref(false);

const editForm = useForm({
    id: '',
    name: '',
    code: '',
    description: '',
    status: '',
    client_id: ''
});

const govForm = useForm({
    manual_progress_percentage: props.project.manual_progress_percentage || 0,
    manual_status_label: props.project.manual_status_label || 'In Development',
    project_health_index: props.project.project_health_index || 100,
    reason: ''
});

const showHistory = ref(false);

const getHealthColorClass = (val) => {
    if (val >= 80) return 'text-emerald-400';
    if (val >= 40) return 'text-amber-400';
    return 'text-rose-400';
};

const submitGovernance = () => {
    govForm.put(route('projects.portal.governance.update', props.project.id), {
        preserveScroll: true,
        onSuccess: () => {
            govForm.reason = '';
            showHistory.value = false;
        }
    });
};

const openEditModal = () => {
    editForm.id = props.project.id;
    editForm.name = props.project.name || '';
    editForm.code = props.project.code || '';
    editForm.description = props.project.description || '';
    editForm.status = props.project.status || 'planning';
    editForm.client_id = props.project.client_id;
    editForm.clearErrors();
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
};

const submitEditForm = () => {
    if (editForm.id) {
        editForm.defaults({ id: editForm.id });
        editForm.put(route('projects.update', { project: editForm.id }), {
            preserveScroll: true,
            onSuccess: () => closeEditModal()
        });
    }
};

const formatActivityType = (type) => {
    return type.replace('_', ' '); 
};

const formatTime = (date) => {
    return new Date(date).toLocaleString();
};

const formatDetails = (act) => {
    if (typeof act.details === 'string') return act.details;
    if (Array.isArray(act.details)) return act.details.join(', ');
    return JSON.stringify(act.details); 
};

const formatCurrency = (amount, currency = 'USD') => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 0
    }).format(amount || 0);
};
</script>
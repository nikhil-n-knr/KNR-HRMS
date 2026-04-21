<template>
    <Modal :show="show" @close="close" maxWidth="5xl">
        <div class="flex flex-col max-h-[88vh]">

            <!-- Header -->
            <div class="flex items-center justify-between px-8 py-5 border-b border-gray-100 shrink-0">
                <div class="flex-1">
                    <h2 class="text-lg font-black text-gray-900 uppercase tracking-widest flex items-center gap-2.5">
                        <svg class="w-6 h-6 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                        Record Delivery Extension
                    </h2>
                    <p class="text-xs text-gray-400 uppercase tracking-widest mt-1">
                        Planned: <strong>{{ formatDate(originalStartDate) || '—' }}</strong>
                        → <strong>{{ formatDate(originalEndDate) || '—' }}</strong>
                        <span v-if="originalEfforts !== null && originalEfforts !== undefined"> · {{ Number(originalEfforts).toFixed(2) }}h planned effort</span>
                    </p>
                    <!-- Auto-Sync banner -->
                    <div v-if="suggestedExtendedDate || suggestedHoursAdded > 0" class="mt-2 flex items-center gap-2 px-3 py-1.5 bg-indigo-50 border border-indigo-200 rounded-xl w-fit">
                        <svg class="w-3 h-3 text-indigo-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <p class="text-[10px] font-black text-indigo-700 uppercase tracking-wider">
                            Auto-Synced
                            <span v-if="suggestedExtendedDate"> · New date <strong>{{ formatDate(suggestedExtendedDate) }}</strong></span>
                            <span v-if="suggestedHoursAdded > 0"> · +<strong>{{ suggestedHoursAdded }}h</strong> effort request</span>
                            pre-filled below
                        </p>
                    </div>
                </div>
                <button @click="close" class="text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-xl hover:bg-gray-100 shrink-0 ml-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>


            <div class="overflow-y-auto flex-1 custom-scrollbar">
                <form @submit.prevent="submit" class="p-8 space-y-8">

                    <div v-if="extensionHistory.length" class="rounded-2xl border border-indigo-100 bg-indigo-50/40 p-5 space-y-4">
                        <div class="flex items-center justify-between gap-4">
                            <div>
                                <p class="text-[10px] font-black text-indigo-700 uppercase tracking-[0.2em]">Drift History</p>
                                <p class="text-[10px] font-bold text-gray-500 uppercase">Previous extension rounds on this task</p>
                            </div>
                            <button type="button" @click="emit('view-audit')" class="text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:text-indigo-700">
                                View Full Audit
                            </button>
                        </div>
                        <div class="flex gap-3 overflow-x-auto pb-1">
                            <div v-for="(extension, index) in extensionHistory" :key="extension.id" class="min-w-[220px] rounded-2xl border border-white bg-white p-4 shadow-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-[10px] font-black text-gray-500 uppercase tracking-widest">Round {{ index + 1 }}</p>
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[9px] font-black uppercase tracking-wide"
                                        :class="badgeClass(extension.category)">
                                        {{ extension.category_label || extension.category || 'Extension' }}
                                    </span>
                                </div>
                                <p class="mt-3 text-xs font-black text-gray-800">{{ formatDate(extension.created_at || extension.recorded_at || extension.date) }}</p>
                                <div class="mt-3 flex flex-wrap gap-2 text-[10px] font-black uppercase tracking-wider">
                                    <span class="rounded-lg bg-amber-100 px-2 py-1 text-amber-700">+{{ Number(extension.days_added || extension.days || 0) }}d</span>
                                    <span class="rounded-lg bg-rose-100 px-2 py-1 text-rose-700">+{{ Number(extension.hours_added || extension.hours || 0) }}h</span>
                                </div>
                                <p class="mt-3 line-clamp-2 text-[10px] font-bold text-gray-500">{{ extension.reason }}</p>
                            </div>
                        </div>
                    </div>


                    <!-- Step 1: Category Selection -->
                    <div>
                        <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-3">Step 1 — What caused the extension?</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">

                            <!-- Category 1: Priority Conflict -->
                            <button type="button" @click="form.category = 'priority_conflict'"
                                class="p-4 rounded-2xl border-2 transition-all text-left group relative overflow-hidden"
                                :class="form.category === 'priority_conflict' ? 'border-violet-500 bg-violet-50 shadow-md' : 'border-gray-100 hover:border-violet-200 hover:bg-violet-50/30'">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-all"
                                        :class="form.category === 'priority_conflict' ? 'bg-violet-600 text-white' : 'bg-gray-100 text-gray-400 group-hover:bg-violet-100 group-hover:text-violet-600'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-widest" :class="form.category === 'priority_conflict' ? 'text-violet-800' : 'text-gray-700'">Priority Conflict</p>
                                        <p class="text-[10px] text-gray-400 mt-1 leading-relaxed">Resource was pulled to other work. Actual hours invested were less than planned — same scope, timeline pushed out.</p>
                                    </div>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-1">
                                    <span class="px-2 py-0.5 bg-violet-100 text-violet-700 text-[9px] font-black uppercase rounded-full tracking-wider">⏱ Time Deficit</span>
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-[9px] font-black uppercase rounded-full tracking-wider">↔ Days Extended</span>
                                </div>
                            </button>

                            <!-- Category 2: Scope Change -->
                            <button type="button" @click="form.category = 'scope_change'"
                                class="p-4 rounded-2xl border-2 transition-all text-left group relative overflow-hidden"
                                :class="form.category === 'scope_change' ? 'border-amber-500 bg-amber-50 shadow-md' : 'border-gray-100 hover:border-amber-200 hover:bg-amber-50/30'">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-all"
                                        :class="form.category === 'scope_change' ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-400 group-hover:bg-amber-100 group-hover:text-amber-600'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-widest" :class="form.category === 'scope_change' ? 'text-amber-800' : 'text-gray-700'">Scope Change</p>
                                        <p class="text-[10px] text-gray-400 mt-1 leading-relaxed">Wrong estimation, new features, or client alterations. More hours needed, possibly more resources and days.</p>
                                    </div>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-1">
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[9px] font-black uppercase rounded-full tracking-wider">+ Hours</span>
                                    <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-[9px] font-black uppercase rounded-full tracking-wider">+ Days</span>
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-500 text-[9px] font-black uppercase rounded-full tracking-wider">± Resources</span>
                                </div>
                            </button>

                            <!-- Category 3: Complexity Drag -->
                            <button type="button" @click="form.category = 'complexity_drag'"
                                class="p-4 rounded-2xl border-2 transition-all text-left group relative overflow-hidden"
                                :class="form.category === 'complexity_drag' ? 'border-rose-500 bg-rose-50 shadow-md' : 'border-gray-100 hover:border-rose-200 hover:bg-rose-50/30'">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 transition-all"
                                        :class="form.category === 'complexity_drag' ? 'bg-rose-600 text-white' : 'bg-gray-100 text-gray-400 group-hover:bg-rose-100 group-hover:text-rose-600'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-widest" :class="form.category === 'complexity_drag' ? 'text-rose-800' : 'text-gray-700'">Complexity Drag</p>
                                        <p class="text-[10px] text-gray-400 mt-1 leading-relaxed">Work is technically harder than estimated. Execution is slower than planned — both hours and days consumed proportionally more.</p>
                                    </div>
                                </div>
                                <div class="mt-3 flex flex-wrap gap-1">
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-700 text-[9px] font-black uppercase rounded-full tracking-wider">↑ Effort Rate</span>
                                    <span class="px-2 py-0.5 bg-rose-100 text-rose-700 text-[9px] font-black uppercase rounded-full tracking-wider">↑ Time Rate</span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Step 2: Category-specific inputs -->
                    <transition name="fade-slide" mode="out-in">

                        <!-- PRIORITY CONFLICT Fields -->
                        <div v-if="form.category === 'priority_conflict'" key="priority_conflict" class="space-y-4">
                            <p class="text-[10px] font-black text-violet-600 uppercase tracking-[0.2em]">Step 2 — Quantify the Time Deficit</p>
                            <div class="p-4 bg-violet-50/50 border border-violet-100 rounded-2xl space-y-4">

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <InputLabel value="Planned Hours (This Period)" class="text-violet-700" />
                                        <TextInput type="number" v-model="meta.planned_hours" class="mt-1 block w-full" min="0" @input="calcDeficit" />
                                        <p class="text-[9px] text-gray-400 mt-1">Hours that were allocated</p>
                                    </div>
                                    <div>
                                        <InputLabel value="Actual Hours Invested" class="text-violet-700" />
                                        <TextInput type="number" v-model="meta.actual_hours" class="mt-1 block w-full" min="0" @input="calcDeficit" />
                                        <p class="text-[9px] text-gray-400 mt-1">Hours actually worked on this task/project</p>
                                    </div>
                                    <div>
                                        <InputLabel value="Hours Deficit (Auto)" class="text-rose-600" />
                                        <div class="mt-1 flex items-center h-[42px] px-3 bg-rose-50 border border-rose-200 rounded-lg">
                                            <span class="font-black text-rose-700 text-sm">{{ deficitHours }}h</span>
                                            <span class="text-[10px] text-rose-400 ml-2 uppercase font-bold">gap</span>
                                        </div>
                                        <p class="text-[9px] text-gray-400 mt-1">Planned − Actual</p>
                                    </div>
                                </div>

                                <div>
                                    <InputLabel value="Priority that Caused the Conflict" class="text-violet-700" />
                                    <TextInput type="text" v-model="meta.conflicting_priority" class="mt-1 block w-full" placeholder="e.g. Production hotfix for Client X, Sprint demo prep..." />
                                </div>
                            </div>
                        </div>

                        <!-- SCOPE CHANGE Fields -->
                        <div v-else-if="form.category === 'scope_change'" key="scope_change" class="space-y-4">
                            <p class="text-[10px] font-black text-amber-600 uppercase tracking-[0.2em]">Step 2 — Define the Scope Delta</p>
                            <div class="p-4 bg-amber-50/50 border border-amber-100 rounded-2xl space-y-4">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel value="Original Estimated Hours" class="text-amber-700" />
                                        <TextInput type="number" v-model="meta.original_estimated_hours" class="mt-1 block w-full" min="0"
                                            :placeholder="originalEfforts || '0'" />
                                        <p class="text-[9px] text-gray-400 mt-1">From the original plan/estimation</p>
                                    </div>
                                    <div>
                                        <InputLabel value="Additional Hours Required" class="text-amber-700" />
                                        <TextInput type="number" v-model="form.hours_added" class="mt-1 block w-full" min="0" />
                                        <p class="text-[9px] text-gray-400 mt-1">Extra effort needed beyond original</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel value="Scope Change Type" class="text-amber-700" />
                                        <select v-model="meta.scope_change_type" class="mt-1 block w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500">
                                            <option value="">Select type...</option>
                                            <option value="new_feature">New Feature Added</option>
                                            <option value="client_alteration">Client Requested Alteration</option>
                                            <option value="wrong_estimation">Wrong Initial Estimation</option>
                                            <option value="design_rework">Design/Architecture Rework</option>
                                            <option value="dependency">New Dependency Introduced</option>
                                            <option value="integration">Integration Complexity</option>
                                        </select>
                                    </div>
                                    <div>
                                        <InputLabel value="Additional Resources Needed" class="text-amber-700" />
                                        <TextInput type="number" v-model="meta.additional_resources" class="mt-1 block w-full" min="0" placeholder="0 = no extra headcount" />
                                        <p class="text-[9px] text-gray-400 mt-1">Extra team members required</p>
                                    </div>
                                </div>

                                <div>
                                    <InputLabel value="Scope Description" class="text-amber-700" />
                                    <textarea v-model="meta.scope_description" rows="2"
                                        @input="errors.scope_description = ''"
                                        class="mt-1 block w-full border-gray-300 rounded-lg text-sm focus:ring-amber-500 focus:border-amber-500"
                                        :class="{'border-red-500 focus:border-red-500 focus:ring-red-500': errors.scope_description}"
                                        placeholder="Describe what changed: new module, client asked to add X, estimation missed Y..."></textarea>
                                    <InputError :message="errors.scope_description" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- COMPLEXITY DRAG Fields -->
                        <div v-else-if="form.category === 'complexity_drag'" key="complexity_drag" class="space-y-4">
                            <p class="text-[10px] font-black text-rose-600 uppercase tracking-[0.2em]">Step 2 — Measure the Slowdown</p>
                            <div class="p-4 bg-rose-50/50 border border-rose-100 rounded-2xl space-y-4">

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <InputLabel value="Planned Effort (hrs/unit)" class="text-rose-700" />
                                        <TextInput type="number" v-model="meta.planned_rate" class="mt-1 block w-full" min="0" step="0.5" placeholder="e.g. 8 hrs/day" @input="calcDragRatio" />
                                        <p class="text-[9px] text-gray-400 mt-1">Expected hrs per day/unit</p>
                                    </div>
                                    <div>
                                        <InputLabel value="Actual Effort (hrs/unit)" class="text-rose-700" />
                                        <TextInput type="number" v-model="meta.actual_rate" class="mt-1 block w-full" min="0" step="0.5" placeholder="e.g. 12 hrs/day" @input="calcDragRatio" />
                                        <p class="text-[9px] text-gray-400 mt-1">What it's actually consuming</p>
                                    </div>
                                    <div>
                                        <InputLabel value="Slowdown Ratio (Auto)" class="text-rose-600" />
                                        <div class="mt-1 flex items-center h-[42px] px-3 rounded-lg border"
                                            :class="dragRatio > 1 ? 'bg-rose-50 border-rose-200' : 'bg-gray-50 border-gray-200'">
                                            <span class="font-black text-sm" :class="dragRatio > 1 ? 'text-rose-700' : 'text-gray-400'">{{ dragRatio }}×</span>
                                            <span class="text-[10px] ml-2 uppercase font-bold" :class="dragRatio > 1 ? 'text-rose-400' : 'text-gray-300'">slower</span>
                                        </div>
                                        <p class="text-[9px] text-gray-400 mt-1">Actual ÷ Planned</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel value="Extra Hours Consumed" class="text-rose-700" />
                                        <TextInput type="number" v-model="form.hours_added" class="mt-1 block w-full" min="0" />
                                        <p class="text-[9px] text-gray-400 mt-1">Additional hours beyond planned effort</p>
                                    </div>
                                    <div>
                                        <InputLabel value="Complexity Factor" class="text-rose-700" />
                                        <select v-model="meta.complexity_factor" class="mt-1 block w-full border-gray-300 rounded-lg text-sm focus:ring-rose-500 focus:border-rose-500">
                                            <option value="">Select complexity...</option>
                                            <option value="technical_debt">Legacy / Technical Debt</option>
                                            <option value="undocumented_system">Undocumented System</option>
                                            <option value="third_party">Third-Party Integration Issues</option>
                                            <option value="performance">Performance Optimization Required</option>
                                            <option value="security">Security/Compliance Constraints</option>
                                            <option value="data_migration">Data Migration Challenges</option>
                                            <option value="architecture">Architecture Refactoring</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Placeholder when no category selected -->
                        <div v-else key="empty" class="p-8 text-center border-2 border-dashed border-gray-100 rounded-2xl">
                            <svg class="w-10 h-10 text-gray-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Select a category above to continue</p>
                        </div>
                    </transition>

                    <!-- Step 3: Common Timeline Fields (shown once category is selected) -->
                    <div v-if="form.category" class="space-y-4">
                        <div class="flex items-center justify-between gap-4">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em]">Step 3 — New Timeline Commitment</p>
                            <button type="button" @click="syncFromTask" class="px-3 py-1.5 rounded-xl border border-indigo-200 bg-white text-[10px] font-black uppercase tracking-widest text-indigo-600 hover:bg-indigo-50 transition-all">
                                Sync from Task
                            </button>
                        </div>

                        <!-- Date Row -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 border border-gray-100 rounded-2xl">
                            <div>
                                <InputLabel value="Original Start Date" />
                                <TextInput type="date" v-model="form.original_start_date" class="mt-1 block w-full bg-white" />
                                <p class="text-[9px] text-gray-400 mt-1">Task/Project baseline start</p>
                            </div>
                            <div>
                                <InputLabel value="Original End Date" />
                                <TextInput type="date" v-model="form.original_end_date" class="mt-1 block w-full bg-white" />
                                <p class="text-[9px] text-gray-400 mt-1">Committed deadline before this extension</p>
                            </div>
                            <div>
                                <InputLabel value="New Extended End Date *" />
                                <TextInput type="date" v-model="form.extended_end_date" class="mt-1 block w-full"
                                    :class="{'border-red-500 focus:border-red-500 focus:ring-red-500': errors.extended_end_date}"
                                    @input="errors.extended_end_date = ''"
                                    :min="form.original_end_date || undefined" />
                                <InputError :message="errors.extended_end_date" class="mt-2" />
                                <p class="text-[9px] text-gray-400 mt-1">Requested new delivery date</p>
                            </div>
                        </div>

                        <!-- Days Added + Hours Added (summary row) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <InputLabel value="Days Extended" />
                                <TextInput type="number" v-model="form.days_added" class="mt-1 block w-full" min="0"
                                    :placeholder="autoDays > 0 ? `Auto: +${autoDays}d` : '0'" />
                                <p class="text-[9px] text-gray-400 mt-1">Calendar days the deadline is pushed</p>
                            </div>
                            <div v-if="form.category !== 'priority_conflict'">
                                <InputLabel value="Extra Hours Added" />
                                <TextInput type="number" v-model="form.hours_added" class="mt-1 block w-full" min="0" />
                                <p class="text-[9px] text-gray-400 mt-1">Additional effort beyond original estimate</p>
                            </div>
                            <div v-else>
                                <InputLabel value="Extra Hours to Recover (Deficit)" />
                                <div class="mt-1 flex items-center gap-2">
                                    <TextInput type="number" v-model="form.hours_added" class="block w-full" min="0" :placeholder="String(deficitHours)" />
                                    <button type="button" @click="form.hours_added = deficitHours"
                                        class="shrink-0 px-3 py-2 bg-violet-100 text-violet-700 rounded-lg text-xs font-black hover:bg-violet-200 transition-all whitespace-nowrap">
                                        Use {{ deficitHours }}h
                                    </button>
                                </div>
                                <p class="text-[9px] text-gray-400 mt-1">Hours that need to be recovered/rescheduled</p>
                            </div>
                        </div>

                        <!-- Dynamic summary chip -->
                        <div v-if="form.extended_end_date && form.original_end_date" class="flex items-center gap-3 p-3 rounded-xl border"
                            :class="categoryColor.bg + ' ' + categoryColor.border">
                            <svg class="w-4 h-4 shrink-0" :class="categoryColor.icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <p class="text-[10px] font-black uppercase tracking-wider" :class="categoryColor.text">
                                Timeline extends by <strong>+{{ autoDays }} days</strong>
                                <template v-if="form.hours_added > 0"> · +{{ form.hours_added }}h additional effort</template>
                                <template v-if="form.category === 'scope_change' && meta.additional_resources > 0"> · +{{ meta.additional_resources }} resource(s)</template>
                            </p>
                        </div>

                        <!-- Reason / Notes -->
                        <div>
                            <InputLabel value="Detailed Reason *" />
                            <textarea v-model="form.reason" rows="3" required
                                @input="errors.reason = ''"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl text-sm p-3"
                                :class="{'border-red-500 focus:border-red-500 focus:ring-red-500': errors.reason}"
                                :placeholder="reasonPlaceholder"></textarea>
                            <InputError :message="errors.reason" class="mt-2" />
                        </div>
                    </div>

                    <!-- Error -->
                    <p v-if="errorMsg" class="text-xs text-red-600 font-bold">{{ errorMsg }}</p>

                </form>
            </div>

            <!-- Footer -->
            <div class="px-8 py-5 border-t border-gray-100 bg-gray-50/80 flex items-center justify-between shrink-0">
                <div v-if="form.category" class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full" :class="categoryColor.dot"></span>
                    <span class="text-[10px] font-black uppercase tracking-widest" :class="categoryColor.text">{{ categoryLabels[form.category] }}</span>
                </div>
                <div v-else></div>
                <div class="flex gap-3">
                    <SecondaryButton @click="close" class="px-6">Cancel</SecondaryButton>
                    <PrimaryButton @click="handleConfirm" :disabled="processing || !form.category" :class="{ 'opacity-25': processing }" class="px-8">
                        {{ processing ? 'Recording...' : 'Confirm Extension' }}
                    </PrimaryButton>
                </div>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { reactive, ref, computed, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';

// ── Props ─────────────────────────────────────────────────────────────────────
const props = defineProps({
    show:                  Boolean,
    projectId:             [Number, String],
    taskId:                [Number, String],
    originalStartDate:     { type: String, default: null },
    originalEndDate:       { type: String, default: null },
    originalEfforts:       { type: [Number, String], default: null },
    suggestedExtendedDate: { type: String, default: '' }, // Auto-sync: pre-fill extended_end_date
    suggestedTotalEfforts: { type: [Number, String], default: null },
    extensionHistory:      { type: Array, default: () => [] },
});

const emit = defineEmits(['close', 'success', 'view-audit']);
const toast = useToastStore();
const processing = ref(false);
const errorMsg = ref('');
const errors = reactive({
    category: '',
    reason: '',
    extended_end_date: '',
    scope_description: '',
});

// ── Category metadata ─────────────────────────────────────────────────────────
const categoryLabels = {
    priority_conflict: 'Priority Conflict',
    scope_change:      'Scope Change / Wrong Estimation',
    complexity_drag:   'Complexity Drag',
};

const categoryColor = computed(() => {
    const map = {
        priority_conflict: { bg: 'bg-violet-50', border: 'border-violet-200', text: 'text-violet-700', icon: 'text-violet-500', dot: 'bg-violet-500' },
        scope_change:      { bg: 'bg-amber-50',  border: 'border-amber-200',  text: 'text-amber-700',  icon: 'text-amber-500',  dot: 'bg-amber-500'  },
        complexity_drag:   { bg: 'bg-rose-50',   border: 'border-rose-200',   text: 'text-rose-700',   icon: 'text-rose-500',   dot: 'bg-rose-500'   },
    };
    return map[form.category] || { bg: 'bg-gray-50', border: 'border-gray-200', text: 'text-gray-700', icon: 'text-gray-400', dot: 'bg-gray-400' };
});

const reasonPlaceholder = computed(() => {
    const map = {
        priority_conflict: 'e.g. Sprint planning was disrupted by production hotfix on Client Y, resulting in 3 days of zero progress on this task...',
        scope_change:      'e.g. Client requested addition of bulk-export module after UI approval. Requires new backend endpoint, 3 new screens...',
        complexity_drag:   'e.g. Legacy codebase has no documentation, each integration point requires reverse-engineering. Average task taking 2× longer...',
    };
    return map[form.category] || 'Provide a detailed explanation...';
});

// ── Main form ─────────────────────────────────────────────────────────────────
const form = reactive({
    category:             '',
    hours_added:          0,
    days_added:           0,
    reason:               '',
    original_start_date:  '',
    original_end_date:    '',
    extended_end_date:    '',
});

// Category-specific metadata (collected separately, submitted as extension_meta)
const meta = reactive({
    // Priority Conflict
    planned_hours:         null,
    actual_hours:          null,
    conflicting_priority:  '',
    // Scope Change
    original_estimated_hours: null,
    scope_change_type:     '',
    additional_resources:  0,
    scope_description:     '',
    // Complexity Drag
    planned_rate:          null,
    actual_rate:           null,
    complexity_factor:     '',
});

// ── Computed helpers ──────────────────────────────────────────────────────────
const deficitHours = computed(() => {
    const p = Number(meta.planned_hours || 0);
    const a = Number(meta.actual_hours || 0);
    return Math.max(0, p - a);
});

const dragRatio = computed(() => {
    const p = Number(meta.planned_rate || 0);
    const a = Number(meta.actual_rate || 0);
    if (!p || !a) return '—';
    return (a / p).toFixed(2);
});

const suggestedHoursAdded = computed(() => {
    const target = Number(props.suggestedTotalEfforts || 0);
    const current = Number(props.originalEfforts || 0);
    return target > current ? target - current : 0;
});

const resolvedProjectId = computed(() => {
    if (props.projectId === null || props.projectId === undefined || props.projectId === '') {
        return null;
    }

    return props.projectId;
});

const autoDays = computed(() => {
    if (!form.extended_end_date || !form.original_end_date) return 0;
    const o = new Date(form.original_end_date);
    const e = new Date(form.extended_end_date);
    return Math.max(0, Math.round((e - o) / (1000 * 60 * 60 * 24)));
});

function calcDeficit() { /* reactive, just reads meta.planned_hours / meta.actual_hours */ }
function calcDragRatio() { /* reactive */ }

// ── Watchers ──────────────────────────────────────────────────────────────────
watch(() => props.show, (shown) => {
    if (!shown) return;
    // Pre-fill dates from task context (auto-synced or manual)
    form.category             = '';
    form.hours_added          = suggestedHoursAdded.value > 0 ? suggestedHoursAdded.value : 0;
    form.days_added           = 0;
    form.reason               = '';
    form.original_start_date  = props.originalStartDate || '';
    form.original_end_date    = props.originalEndDate  || '';
    // If a suggested date was passed (e.g. user clicked locked due date), pre-fill it
    form.extended_end_date    = props.suggestedExtendedDate || '';
    errorMsg.value            = '';

    // Reset meta
    Object.assign(meta, {
        planned_hours: null, actual_hours: null, conflicting_priority: '',
        original_estimated_hours: props.originalEfforts || null,
        scope_change_type: '', additional_resources: 0, scope_description: '',
        planned_rate: null, actual_rate: null, complexity_factor: '',
    });
});

// Auto-sync days_added from date diff
watch(() => form.extended_end_date, () => {
    if (autoDays.value > 0) form.days_added = autoDays.value;
});

// ── Submission Logic ──────────────────────────────────────────────────────────
const handleConfirm = async () => {
    console.error('PROD_DEBUG: handleConfirm initiated');
    
    if (processing.value) return;

    // Clear previous errors properly
    Object.keys(errors).forEach(key => delete errors[key]);
    errorMsg.value = '';

    console.log('FORM_STATE:', JSON.parse(JSON.stringify(form)));
    console.log('RESOLVED_PROJECT:', resolvedProjectId.value);

    // 1. Validations
    if (!form.category) { 
        errorMsg.value = 'Please select a category above.';
        toast.warning(errorMsg.value);
        return; 
    }
    
    if (!form.reason || !form.reason.trim() || form.reason.trim().length < 5) { 
        errors.reason = 'Please provide a detailed reason (at least 5 characters).'; 
        toast.warning(errors.reason);
        return; 
    }
    
    if (!form.extended_end_date) { 
        errors.extended_end_date = 'New Extended End Date is required.'; 
        toast.warning(errors.extended_end_date);
        return; 
    }
    
    if (!resolvedProjectId.value) { 
        errorMsg.value = 'Project context missing.'; 
        toast.error(errorMsg.value);
        return; 
    }

    if (form.category === 'scope_change' && (!meta.scope_description || !meta.scope_description.trim())) {
        errors.scope_description = 'Please describe the scope change delta (Step 2).';
        toast.warning(errors.scope_description);
        return;
    }

    // 2. Prepare Payload
    const typeMap = {
        priority_conflict: 'time',
        scope_change:      'both',
        complexity_drag:   'both',
    };

    const payload = {
        task_id:              props.taskId || null,
        category:             form.category,
        type:                 typeMap[form.category] || 'both',
        reason:               form.reason,
        notes:                form.notes,
        original_start_date:  form.original_start_date || null,
        original_end_date:    form.original_end_date   || null,
        extended_end_date:    form.extended_end_date,
        days_added:           Number(form.days_added) || 0,
        hours_added:          Number(form.hours_added) || 0,
        extension_meta:       buildMeta()
    };

    console.error('DEBUG_PAYLOAD:', payload);

    try {
        processing.value = true;
        const res = await axios.post(route('projects.extensions.store', { project: resolvedProjectId.value }), payload);
        
        console.error('SERVER_SUCCESS:', res.data);
        toast.success('Extension recorded successfully');
        emit('success');
        close();
    } catch (e) {
        console.error('SERVER_ERROR:', e.response?.data || e.message);
        
        if (e.response?.status === 422 && e.response?.data?.errors) {
            Object.assign(errors, e.response.data.errors);
            const firstErr = Object.values(e.response.data.errors)[0];
            errorMsg.value = Array.isArray(firstErr) ? firstErr[0] : firstErr;
        } else {
            errorMsg.value = e.response?.data?.message || 'Failed to record extension.';
        }
        
        toast.error(errorMsg.value);
    } finally {
        processing.value = false;
    }
};

function buildMeta() {
    if (form.category === 'priority_conflict') {
        return {
            planned_hours:        Number(meta.planned_hours)  || 0,
            actual_hours:         Number(meta.actual_hours)   || 0,
            deficit_hours:        deficitHours.value,
            conflicting_priority: meta.conflicting_priority,
        };
    }
    if (form.category === 'scope_change') {
        return {
            original_estimated_hours: Number(meta.original_estimated_hours) || 0,
            scope_change_type:        meta.scope_change_type,
            additional_resources:     Number(meta.additional_resources) || 0,
            scope_description:        meta.scope_description,
        };
    }
    if (form.category === 'complexity_drag') {
        return {
            planned_rate:     Number(meta.planned_rate) || 0,
            actual_rate:      Number(meta.actual_rate)  || 0,
            slowdown_ratio:   Number(dragRatio.value)   || 0,
            complexity_factor: meta.complexity_factor,
        };
    }
    return {};
}

const close = () => emit('close');

const syncFromTask = () => {
    form.original_start_date = props.originalStartDate || '';
    form.original_end_date = props.originalEndDate || '';
    form.extended_end_date = props.suggestedExtendedDate || form.extended_end_date || props.originalEndDate || '';

    if (suggestedHoursAdded.value > 0) {
        form.hours_added = suggestedHoursAdded.value;
    }

    meta.original_estimated_hours = props.originalEfforts || meta.original_estimated_hours || null;
};

const badgeClass = (category) => {
    if (category === 'priority_conflict') return 'bg-violet-100 text-violet-700';
    if (category === 'scope_change') return 'bg-amber-100 text-amber-700';
    if (category === 'complexity_drag') return 'bg-rose-100 text-rose-700';
    return 'bg-gray-100 text-gray-600';
};

const formatDate = (d) => {
    if (!d) return null;
    return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.fade-slide-enter-from   { opacity: 0; transform: translateY(6px); }
.fade-slide-leave-to     { opacity: 0; transform: translateY(-6px); }
</style>

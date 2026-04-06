<template>
    <div class="h-full flex flex-col bg-white">
        <transition name="slide-up">
            <div v-if="selectedBugs.length > 0" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-slate-900/95 text-white px-6 py-4 rounded-3xl shadow-[0_20px_60px_rgba(0,0,0,0.4)] z-[100] flex items-center gap-8 border border-white/10 backdrop-blur-xl ring-1 ring-white/5">
                <div class="flex items-center gap-3 pr-8 border-r border-slate-700">
                    <span class="bg-emerald-500 text-white text-sm font-black px-2.5 py-1 rounded-full shadow-lg shadow-emerald-500/50">{{ selectedBugs.length }}</span>
                    <span class="text-xs font-black uppercase tracking-[0.1em] text-slate-300">Targeted</span>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2" @click.stop>
                         <button 
                            @click="showBulkStageMenu = !showBulkStageMenu"
                            class="px-4 py-2 text-sm font-black uppercase tracking-widest bg-emerald-600 hover:bg-emerald-500 rounded-xl transition-all flex items-center gap-2 shadow-lg shadow-emerald-600/20"
                        >
                            <ArrowsRightLeftIcon class="w-3.5 h-3.5" />
                            Change Stage
                        </button>
                    </div>

                    <button @click.stop="bulkReassign" class="px-4 py-2 text-sm font-black uppercase tracking-widest bg-slate-800 hover:bg-slate-700 rounded-xl transition-all flex items-center gap-2 border border-slate-700">
                        <UserPlusIcon class="w-3.5 h-3.5 text-slate-400" />
                        Reassign
                    </button>

                    <button @click.stop="bulkNotify" class="px-4 py-2 text-sm font-black uppercase tracking-widest bg-slate-800 hover:bg-slate-700 rounded-xl transition-all flex items-center gap-2 border border-slate-700">
                        <EnvelopeIcon class="w-3.5 h-3.5 text-slate-400" />
                        Notify Group
                    </button>
                    
                    <button @click="selectedBugs = []" class="ml-4 text-sm font-black uppercase tracking-widest text-slate-500 hover:text-white transition-colors">
                        Cancel Action
                    </button>
                </div>

                <!-- Inline Stage Menu for Bulk -->
                <div v-if="showBulkStageMenu" @click.stop class="absolute bottom-full mb-4 left-1/2 -translate-x-1/2 bg-slate-900 border border-slate-700 rounded-2xl p-2 w-48 shadow-2xl">
                    <button 
                        v-for="stage in stages" 
                        :key="stage.id"
                        @click="bulkMoveToStage(stage.id); showBulkStageMenu = false"
                        class="w-full text-left px-4 py-2.5 text-sm font-black uppercase tracking-widest text-slate-300 hover:bg-slate-800 hover:text-white rounded-xl transition-colors"
                    >
                        {{ stage.name }}
                    </button>
                </div>
            </div>
        </transition>

        <div class="border-b border-gray-200 px-4 md:px-6 py-3 bg-gray-50 flex flex-col lg:flex-row gap-4 lg:items-center bg-white/50 backdrop-blur-sm sticky top-0 z-40 shadow-sm">
             <div class="flex flex-wrap lg:flex-nowrap gap-3 md:gap-4 items-center flex-1">
                 <!-- View Toggles -->
                <div class="flex bg-white border border-gray-200 rounded-2xl p-0.5 shadow-sm overflow-hidden flex-shrink-0">
                    <button @click="viewMode = 'list'" 
                         :class="[viewMode === 'list' ? 'bg-emerald-500 text-white shadow-lg' : 'text-gray-400 hover:text-gray-600', 'p-2 rounded-xl transition-all']"
                         title="List View">
                        <ListBulletIcon class="w-5 h-5" />
                    </button>
                    <button @click="viewMode = 'kanban'" 
                         :class="[viewMode === 'kanban' ? 'bg-emerald-500 text-white shadow-lg' : 'text-gray-400 hover:text-gray-600', 'p-2 rounded-xl transition-all']"
                         title="Kanban Board">
                        <Squares2X2Icon class="w-5 h-5" />
                    </button>
                    <button @click="viewMode = 'timeline'" 
                         :class="[viewMode === 'timeline' ? 'bg-emerald-500 text-white shadow-lg' : 'text-gray-400 hover:text-gray-600', 'p-2 rounded-xl transition-all']"
                         title="Timeline">
                        <ClockIcon class="w-5 h-5" />
                    </button>
                    <button @click="viewMode = 'pulse'" 
                         :class="[viewMode === 'pulse' ? 'bg-indigo-600 text-white shadow-lg' : 'text-gray-400 hover:text-gray-600', 'p-2 rounded-xl transition-all']"
                         title="Deployment Pulse">
                        <RocketLaunchIcon class="w-5 h-5" />
                    </button>
                </div>

                <!-- My Work Toggle -->
                <div class="flex bg-gray-200/50 rounded-2xl p-0.5 flex-shrink-0">
                    <button 
                        @click="myWorkOnly = false"
                        :class="[!myWorkOnly ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500', 'px-3 md:px-4 py-2 text-sm md:text-sm font-black uppercase tracking-widest rounded-[14px] transition-all']"
                    >All</button>
                    <button 
                        @click="myWorkOnly = true"
                        :class="[myWorkOnly ? 'bg-emerald-600 text-white shadow-lg shadow-emerald-100' : 'text-slate-500', 'px-3 md:px-4 py-2 text-sm md:text-sm font-black uppercase tracking-widest rounded-[14px] transition-all']"
                    >Mine</button>
                </div>

                <div class="hidden md:block h-8 w-px bg-gray-300"></div>

                <!-- Search / Cmd+K -->
                <div class="relative flex-1 min-w-[200px] group transition-all duration-300 order-last lg:order-none w-full lg:w-auto">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                        <MagnifyingGlassIcon class="h-4 w-4 text-gray-400" />
                    </div>
                    <input 
                        v-model="search" 
                        @input="debouncedSearch" 
                        type="text" 
                        class="block w-full bg-white rounded-2xl border-gray-200 pl-11 pr-10 md:pr-20 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-50 transition-all text-sm py-2.5 font-bold" 
                        placeholder="Search tickets..." 
                    />
                    <div class="hidden md:flex absolute inset-y-0 right-3 items-center gap-1.5">
                        <kbd class="px-2 py-1 bg-gray-100 border border-gray-200 rounded text-sm font-black text-gray-500">Ctrl</kbd>
                        <kbd class="px-2 py-1 bg-gray-100 border border-gray-200 rounded text-sm font-black text-gray-500">K</kbd>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 justify-between lg:justify-end">
                <div class="flex gap-2 relative items-center">
                    <!-- Filters Button -->
                    <button @click="showFilterPanel = true" class="relative p-2.5 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-2xl transition-all border border-transparent hover:border-indigo-100" title="Advanced Filters">
                        <FunnelIcon class="w-5 h-5" />
                        <span v-if="activeFiltersCount > 0" class="absolute top-1 right-1 flex h-3 w-3 items-center justify-center rounded-full bg-indigo-600 text-xs font-black text-white ring-2 ring-white">{{ activeFiltersCount }}</span>
                    </button>

                    <!-- Export Dropdown -->
                    <div class="relative">
                        <button @click="showExportMenu = !showExportMenu" class="p-2.5 md:px-6 md:py-2.5 bg-white border border-gray-200 rounded-2xl text-sm font-black uppercase tracking-widest text-slate-700 hover:bg-gray-50 transition-all shadow-sm flex items-center gap-3">
                            <span class="hidden md:inline">Export</span>
                            <ChevronDownIcon class="w-3.5 h-3.5" />
                        </button>
                        <div v-if="showExportMenu" class="absolute right-0 mt-2 w-48 bg-white border border-gray-100 rounded-2xl shadow-2xl py-2 z-50">
                            <button v-for="fmt in ['CSV', 'Excel', 'PDF']" :key="fmt" @click="exportData(fmt)" class="w-full text-left px-5 py-3 text-sm font-black uppercase tracking-widest text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                                Save as {{ fmt }}
                            </button>
                        </div>
                    </div>
                </div>

                <PrimaryButton @click="showCreateModal = true" class="!py-2.5 !px-5 md:!py-3 md:!px-8 !rounded-2xl !bg-emerald-600 !shadow-xl !shadow-emerald-100 transition-all flex-shrink-0">
                    <span class="mr-1 md:mr-2 font-black">+</span> <span class="text-sm md:text-sm">Ticket</span>
                </PrimaryButton>
            </div>
        </div>
        <!-- Custom Context Views (Saved Filters) -->
        <div class="bg-white border-b border-gray-100 flex items-center px-6 py-2 gap-4 flex-none overflow-x-auto">
            <span class="text-sm font-black uppercase tracking-widest text-slate-400">Saved Views</span>
            <div class="h-4 w-px bg-gray-200"></div>
            <button 
                @click="clearCustomView"
                :class="[!activeCustomView ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200' : 'text-slate-500 hover:bg-slate-50', 'px-3 py-1.5 text-sm font-bold rounded-lg transition-all flex items-center gap-2 whitespace-nowrap']"
            >
                All Bugs
            </button>
            <button 
                v-for="view in custom_views" 
                :key="view.id"
                @click="applyCustomView(view)"
                class="group relative"
            >
                <div :class="[activeCustomView?.id === view.id ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200' : 'text-slate-600 hover:bg-slate-50 border border-slate-200', 'px-3 py-1.5 text-sm font-bold rounded-lg transition-all flex items-center gap-2 pr-8 whitespace-nowrap']">
                    {{ view.name }}
                    <span 
                        v-if="!view.id || !view.id.toString().startsWith('system-')"
                        @click.stop="deleteCustomView(view.id)" 
                        class="absolute right-1 top-1/2 -translate-y-1/2 p-1 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded opacity-0 group-hover:opacity-100 transition-all"
                    >
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                    </span>
                </div>
            </button>
            <button 
                @click="showSaveViewModal = true"
                class="px-2 py-1.5 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 rounded flex items-center gap-1 transition-all group ml-auto"
                title="Save Current Filters as View"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" /></svg>
                <span class="text-sm font-black uppercase tracking-widest hidden group-hover:inline">Save View</span>
            </button>
        </div>

        <Modal :show="showSaveViewModal" @close="showSaveViewModal = false" maxWidth="sm">
            <div class="p-6">
                <h2 class="text-lg font-bold text-slate-900 mb-4">Save Custom View</h2>
                <div class="mb-4">
                    <label class="block text-xs font-black uppercase tracking-widest text-slate-500 mb-2">View Name</label>
                    <input 
                        v-model="newViewName" 
                        type="text" 
                        placeholder="e.g., My High Priority Bugs"
                        class="w-full border-slate-200 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                        @keyup.enter="saveCustomView"
                    />
                </div>
                <div class="flex justify-end gap-3 mt-6">
                    <SecondaryButton @click="showSaveViewModal = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="saveCustomView" class="!bg-indigo-600 hover:!bg-indigo-700">Save View</PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Bulk Reassign Modal -->
        <Modal :show="showBulkReassignModal" @close="showBulkReassignModal = false" maxWidth="lg">
            <div class="p-6">
                <h2 class="text-xl font-black text-slate-900 mb-2 uppercase tracking-tight">Bulk Reassign</h2>
                <p class="text-slate-500 text-sm mb-6">Assign <span class="font-bold text-indigo-600">{{ selectedBugs.length }}</span> selected tickets to a new owner.</p>
                
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-8 max-h-[400px] overflow-y-auto pr-2">
                    <button 
                        v-for="user in availableAssignees" 
                        :key="user.id"
                        @click="selectedAssigneeForBulk = user.id"
                        :class="[selectedAssigneeForBulk === user.id ? 'ring-2 ring-indigo-500 bg-indigo-50 shadow-lg' : 'bg-white border border-slate-200', 'p-4 rounded-2xl flex flex-col items-center gap-2 hover:border-indigo-300 transition-all']"
                    >
                        <img v-if="user.avatar" :src="user.avatar" class="w-12 h-12 rounded-full shadow-sm" />
                        <div v-else class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center font-bold text-slate-400 capitalize">{{ user.name.charAt(0) }}</div>
                        <span class="text-sm font-bold text-slate-700 text-center line-clamp-1 w-full">{{ user.name }}</span>
                    </button>
                    
                    <div v-if="!availableAssignees.length" class="col-span-full py-12 text-center text-slate-400">
                        No team members available for this project.
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-6 border-t border-slate-100">
                    <SecondaryButton @click="showBulkReassignModal = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="executeBulkReassign" :disabled="!selectedAssigneeForBulk" class="!bg-indigo-600">
                        Apply Reassignment
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Main Content Area (Full Height, No Margins) -->
        <div class="flex-1 overflow-hidden relative">

                <!-- Split View Container (Inside relative flex-1) -->
                <div class="flex h-full"> 
                    
                    <!-- Left Pane: List / Kanban -->
                    <div :class="[selectedBugId ? 'w-1/2 hidden md:flex border-r border-gray-200' : 'w-full flex', 'transition-all duration-300 flex-col bg-white overflow-hidden']">
                         <!-- Bug List -->
                        <div v-if="viewMode === 'list'" class="flex-1 overflow-y-auto">
                            <table class="min-w-full divide-y divide-gray-200 table-fixed">
                                <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                                    <tr>
                                        <th class="w-12 px-4 md:px-6 py-3"></th>
                                        <th class="px-4 md:px-6 py-3 text-left text-xs font-black uppercase tracking-widest text-gray-500">Details</th>
                                        <th v-if="!selectedBugId" class="px-6 py-3 text-left text-xs font-black uppercase tracking-widest text-gray-500 hidden lg:table-cell">Personnel</th>
                                        <th v-if="!selectedBugId" class="px-6 py-3 text-left text-xs font-black uppercase tracking-widest text-gray-500 hidden sm:table-cell">Status</th>
                                        <th class="w-24 px-4 md:px-6 py-3 text-right text-xs font-black uppercase tracking-widest text-gray-500">Tier</th>
                                    </tr>
                                </thead>
                                
                                <tbody v-for="(bugsInGroup, groupName) in groupedBugs" :key="groupName" class="bg-white divide-y divide-gray-200">
                                    <!-- Group Header -->
                                    <tr class="bg-emerald-50/50 border-y border-emerald-100">
                                        <td class="px-6 py-2">
                                            <input 
                                                type="checkbox" 
                                                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500"
                                                :checked="bugsInGroup.length > 0 && bugsInGroup.every(b => selectedBugs.includes(b.id))"
                                                @change="toggleSelectAll(bugsInGroup)"
                                            />
                                        </td>
                                        <td colspan="4" class="px-2 py-2">
                                            <span class="text-xs font-bold text-emerald-900 uppercase tracking-widest pl-2">
                                                {{ groupName }} 
                                                <span class="ml-2 text-sm bg-white text-emerald-500 px-1.5 py-0.5 rounded-full border border-emerald-200">
                                                    {{ bugsInGroup.length }}
                                                </span>
                                            </span>
                                        </td>
                                    </tr>

                                    <tr v-for="bug in bugsInGroup" :key="bug.id" 
                                        @click="openBug(bug)"
                                        :class="[
                                            selectedBugId === bug.id ? 'bg-emerald-50 ring-1 ring-inset ring-emerald-200' : 'hover:bg-gray-50',
                                            'cursor-pointer transition-all border-b border-gray-100'
                                        ]"
                                    >
                                        <td class="px-6 py-4 group/row" @click.stop>
                                            <div class="flex items-center gap-3">
                                                <input 
                                                    v-model="selectedBugs" 
                                                    :value="bug.id" 
                                                    type="checkbox" 
                                                    class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 h-4 w-4" 
                                                />
                                                <button 
                                                    v-if="!bug.assignee_id && !selectedBugId"
                                                    @click="assignToMe(bug)" 
                                                    class="opacity-0 group-hover/row:opacity-100 transition-all p-1 bg-emerald-50 text-emerald-600 rounded-lg hover:bg-emerald-600 hover:text-white"
                                                    title="Assign to Me"
                                                >
                                                    <UserCircleIcon class="w-4 h-4" />
                                                </button>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="relative group/id">
                                                    <span class="text-sm font-black font-mono text-gray-300 bg-gray-50 px-1.5 py-0.5 rounded border border-gray-100 group-hover/row:text-emerald-500 group-hover/row:border-emerald-200 transition-all cursor-pointer">#{{ bug.id }}</span>
                                                    <button @click.stop="copyId(bug.id)" class="absolute -top-6 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-sm font-black px-2 py-1 rounded opacity-0 group-hover/id:opacity-100 transition-all whitespace-nowrap">Copy Link</button>
                                                </div>
                                                <div class="text-sm font-bold text-slate-800 truncate max-w-[280px] group-hover/row:text-emerald-700 transition-colors">{{ bug.subject }}</div>
                                            </div>
                                            <div class="text-sm mt-1.5 flex items-center gap-3 font-black uppercase tracking-widest leading-none">
                                                <span class="text-slate-400">{{ bug.project.name }}</span>
                                                <span v-if="bug.module" class="h-1 w-1 bg-slate-300 rounded-full"></span>
                                                <span v-if="bug.module" class="text-emerald-600">{{ bug.module.name }}</span>
                                            </div>
                                        </td>
                                        <td v-if="!selectedBugId" class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                                            <div class="flex items-center">
                                                <div class="h-7 w-7 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-700 text-sm font-bold border border-emerald-200">
                                                    {{ bug.reporter?.name?.charAt(0) || '?' }}
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-xs font-semibold text-gray-900">{{ bug.reporter?.name }}</div>
                                                    <div class="text-sm text-gray-500">Assignee: {{ bug.assignee?.name || 'Unassigned' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td v-if="!selectedBugId" class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                                            <div class="relative group/status inline-block">
                                                <span @click.stop="activeStatusMenu = bug.id" class="px-3 py-1.5 text-sm font-black rounded-xl border-2 shadow-sm uppercase tracking-[0.1em] cursor-pointer transition-all hover:ring-4 hover:ring-opacity-20" 
                                                    :class="[
                                                        bug.stage?.is_final ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-teal-50 text-teal-700 border-teal-100',
                                                        activeStatusMenu === bug.id ? 'ring-4 ring-emerald-100 ring-opacity-50' : ''
                                                    ]">
                                                    {{ bug.stage?.name }}
                                                </span>
                                                
                                                <div v-if="activeStatusMenu === bug.id" @click.stop class="absolute top-full left-0 mt-2 w-48 bg-white border border-gray-100 rounded-2xl shadow-2xl py-2 z-50 animate-in fade-in zoom-in-95">
                                                    <button 
                                                        v-for="s in stages" 
                                                        :key="s.id"
                                                        @click.stop="updateStageInline(bug, s)"
                                                        class="w-full text-left px-5 py-2.5 text-sm font-black uppercase tracking-widest text-slate-600 hover:bg-emerald-50 hover:text-emerald-700 transition-colors"
                                                    >
                                                        {{ s.name }}
                                                    </button>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-4">
                                                <!-- SLA Indicator -->
                                                <div v-if="!bug.stage?.is_final" class="hidden md:flex items-center gap-2 px-2 py-1 rounded-lg border group/sla relative"
                                                    :class="isSLABreached(bug) ? 'bg-rose-50 border-rose-100' : 'bg-amber-50 border-amber-100'">
                                                    <span class="h-2 w-2 rounded-full animate-pulse" :class="isSLABreached(bug) ? 'bg-rose-500' : 'bg-amber-500'"></span>
                                                    <span class="text-sm font-black uppercase" :class="isSLABreached(bug) ? 'text-rose-600' : 'text-amber-600'">{{ getSLATime(bug) }}</span>
                                                    
                                                    <button v-if="isSLABreached(bug)" @click.stop="escalate(bug)" class="absolute -top-8 right-0 bg-rose-600 text-white text-xs font-black px-2 py-1 rounded opacity-0 group-hover/sla:opacity-100 transition-all flex items-center gap-1 shadow-lg z-50">
                                                        <FireIcon class="w-2.5 h-2.5" />
                                                        Escalate
                                                    </button>
                                                </div>

                                                <button 
                                                    @click.stop="openForensics(bug)"
                                                    class="p-2 text-slate-300 hover:text-emerald-600 hover:bg-emerald-50 rounded-xl transition-all"
                                                    title="Advanced Forensics"
                                                >
                                                    <CommandLineIcon class="w-4 h-4" />
                                                </button>
                                                <span :class="[getSeverityClass(bug.severity), 'px-3 py-1.5 text-sm font-black rounded-xl border border-transparent uppercase tracking-widest shadow-sm min-w-[80px] text-center']">
                                                    {{ bug.severity }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                         <!-- Timeline View -->
                        <BugTimeline 
                            v-else-if="viewMode === 'timeline'" 
                            :bugs="bugs.data || bugs" 
                            @open="openBug" 
                        />

                        <!-- Pulse View -->
                        <div v-else-if="viewMode === 'pulse'" class="flex-1 overflow-y-auto p-12 bg-slate-50/50">
                            <LiveDeploymentPulse :projectId="store.selectedProject" />
                        </div>

                         <!-- Kanban View (default else) -->
                        <div v-else class="h-full overflow-hidden bg-gray-100 p-4">
                            <BugKanban 
                                :bugs="bugs.data || bugs" 
                                :stages="stages" 
                                @update-stage="updateStage" 
                                @open="openBug" 
                            />
                        </div>
                    </div>

                    <!-- Right Pane: Detail View / Forensics -->
                    <div v-if="selectedBugId" class="w-1/2 h-full bg-white shadow-xl z-20 flex flex-col">
                        <BugForensics 
                            v-if="showForensics"
                            :bug="selectedBugData"
                            @close="closeDetail"
                        />
                        <BugDetailPane 
                            v-else
                            :bug-id="selectedBugId" 
                            :stages="stages" 
                            @close="closeDetail" 
                            @updated="refreshList" 
                        />
                    </div>

        <!-- Detail Modal (Only for mobile or specific override? For now, removing to enforce Split View) -->
        <!-- 
        <BugDetailModal 
            :show="!!selectedBugId" 
            ....
        /> 
        -->

                </div>
         </div>

         <Modal :show="showResolutionModal" @close="cancelResolution" maxWidth="md">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900">Resolution Required</h2>
                <p class="text-sm text-gray-500 mb-4">
                    You are closing this ticket. Please provide a resolution note.
                </p>
                
                <div class="mt-4">
                    <InputLabel value="Resolution Note" />
                    <textarea 
                        v-model="resolutionNote" 
                        rows="3" 
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm"
                        placeholder="Fixed by updating..."
                    ></textarea>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="cancelResolution">Cancel</SecondaryButton>
                    <PrimaryButton @click="confirmResolution" :disabled="!resolutionNote.trim()">
                        Confirm & Close
                    </PrimaryButton>
                </div>
            </div>
        </Modal>


        <BugCreateModal 
            :show="showCreateModal" 
            :projects="projects" 
            @close="showCreateModal = false" 
        />

        <BugTransitionModal
            :show="showTransitionModal"
            :bug="transitionBug"
            :targetStage="transitionTargetStage"
            :users="$page.props.lookup?.users || {}"
            @close="showTransitionModal = false"
            @completed="handleTransitionCompleted"
        />



        <CommandPalette 
            :is-open="showCommandPalette" 
            :bugs="bugs?.data || bugs || []"
            @close="showCommandPalette = false"
            @open-palette="showCommandPalette = true"
            @openBug="openBug"
        />

        <BugFilterPanel
            :show="showFilterPanel"
            :filters="filters"
            :lookup="props.lookup || {}"
            @close="showFilterPanel = false"
            @apply="applyDeepFilters"
        />
    </div>
</template>

<script setup>
// import MainLayout from '@/Layouts/MainLayout.vue'; // Not needed
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { useBugTrackerStore } from '@/Stores/bugTrackerStore';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import debounce from 'lodash/debounce';
import axios from 'axios';
// import BugDetailModal from './Components/BugDetailModal.vue';
import BugCreateModal from './Components/BugCreateModal.vue';
import BugTransitionModal from './Components/BugTransitionModal.vue';
import BugFilterPanel from './Components/BugFilterPanel.vue';
import BugDetailPane from './Components/BugDetailPane.vue';
import BugKanban from './Components/BugKanban.vue';
import BugTimeline from './Components/BugTimeline.vue';
import BugForensics from './Components/BugForensics.vue';
import CommandPalette from './Components/CommandPalette.vue';
import { 
    ListBulletIcon, 
    Squares2X2Icon,
    MagnifyingGlassPlusIcon,
    ClockIcon,
    MagnifyingGlassIcon,
    ShareIcon,
    ChevronDownIcon,
    CommandLineIcon,
    UserCircleIcon,
    FireIcon,
    ArrowsRightLeftIcon,
    UserPlusIcon,
    EnvelopeIcon,
    FunnelIcon
} from '@heroicons/vue/24/outline';

const props = defineProps(['bugs', 'filters', 'projects', 'stages', 'custom_views', 'lookup']);

const showCreateModal = ref(false);
const showCommandPalette = ref(false);
const selectedBugId = ref(null);
const showForensics = ref(false);
const selectedBugData = ref(null);
const viewMode = ref('list');
const myWorkOnly = ref(props.filters.my_work === 'true' || props.filters.my_work === true);
const activeStatusMenu = ref(null);
const showExportMenu = ref(false);
const showBulkStageMenu = ref(false);
const showBulkReassignModal = ref(false);
const availableAssignees = ref([]);
const selectedAssigneeForBulk = ref(null);

const shareCopied = ref(false);
const showFilterPanel = ref(false);

const activeFiltersCount = computed(() => {
    let count = 0;
    if (props.filters.stages && (Array.isArray(props.filters.stages) ? props.filters.stages.length : 1) > 0) count++;
    if (props.filters.severity && (Array.isArray(props.filters.severity) ? props.filters.severity.length : 1) > 0) count++;
    if (props.filters.priorities && (Array.isArray(props.filters.priorities) ? props.filters.priorities.length : 1) > 0) count++;
    if (props.filters.assignee_ids && (Array.isArray(props.filters.assignee_ids) ? props.filters.assignee_ids.length : 1) > 0) count++;
    if (props.filters.reporter_ids && (Array.isArray(props.filters.reporter_ids) ? props.filters.reporter_ids.length : 1) > 0) count++;
    return count;
});

const applyDeepFilters = (newFilters) => {
    showFilterPanel.value = false;
    router.get(route('bugs.index'), {
        ...props.filters,
        ...newFilters,
        tab: 'tracker',
        project_id: store.selectedProject || '',
        module_id: store.selectedModule || '',
        my_work: myWorkOnly.value
    }, { preserveState: true, preserveScroll: true });
};

const activeCustomView = ref(null);
const showSaveViewModal = ref(false);
const newViewName = ref('');

const page = usePage();
const store = useBugTrackerStore();

const search = ref('');
const filters = ref({
    project_id: props.filters.project_id || store.selectedProject || '',
    module_id: props.filters.module_id || store.selectedModule || '',
    severity: props.filters.severity || '',
    timeframe: props.filters.timeframe || store.timeFrame || 'sprint'
});

const debouncedSearch = debounce(() => {
    router.get(route('bugs.index'), { 
        ...filters.value,
        search: search.value,
        my_work: myWorkOnly.value,
        tab: 'tracker'
    }, { preserveState: true, replace: true });
}, 300);

watch(myWorkOnly, () => {
    // Immediate refresh for toggle
    router.get(route('bugs.index'), { 
        ...filters.value,
        search: search.value,
        my_work: myWorkOnly.value,
        tab: 'tracker'
    }, { preserveState: true, replace: true });
});

// Global Store Context Synchronization
watch(() => store.selectedProject, (newVal) => {
    router.get(route('bugs.index'), { ...filters.value, project_id: newVal, my_work: myWorkOnly.value, tab: 'tracker' });
});

watch(() => store.selectedModule, (newVal) => {
    router.get(route('bugs.index'), { ...filters.value, module_id: newVal, my_work: myWorkOnly.value, tab: 'tracker' });
});

watch(() => store.timeFrame, (newVal) => {
    router.get(route('bugs.index'), { ...filters.value, timeframe: newVal, my_work: myWorkOnly.value, tab: 'tracker' });
});

// Transition Modal State
const showTransitionModal = ref(false);
const transitionBug = ref(null);
const transitionTargetStage = ref(null);

const refreshList = () => {
    router.reload({ only: ['bugs'] });
};

const updateStage = async ({ bug, stage }) => {
    transitionBug.value = bug;
    transitionTargetStage.value = stage;
    showTransitionModal.value = true;
};

const updateStageInline = async (bug, stage) => {
    activeStatusMenu.value = null;
    await updateStage({ bug, stage });
};

const handleTransitionCompleted = () => {
    showTransitionModal.value = false;
    transitionBug.value = null;
    transitionTargetStage.value = null;
    refreshList();
    if(selectedBugData.value) {
        // Refresh the detail pane too if open
        openBug(selectedBugData.value);
    }
};

const groupBy = ref('none');
const selectedBugs = ref([]);

const groupedBugs = computed(() => {
    let data = props.bugs.data || props.bugs || [];
    
    // Apply "My Work" filter if active (Now handled server-side, but keep as fallback for instant feel if needed, 
    // though server-side is more accurate for pagination)
    // if (myWorkOnly.value) {
    //     const employeeId = page.props.auth?.user?.employee?.id;
    //     data = data.filter(b => b.assignee_id === employeeId);
    // }

    // Secondary Filter: If store has project/module, and they aren't in the server-side query yet
    if (store.selectedProject && !props.filters.project_id) {
        data = data.filter(b => b.project_id == store.selectedProject);
    }
    if (store.selectedModule && !props.filters.module_id) {
        data = data.filter(b => b.module_id == store.selectedModule);
    }

    if (groupBy.value === 'none') return { 'Current View': data };
    
    return data.reduce((acc, bug) => {
        let key = 'Unassigned';
        if (groupBy.value === 'module') key = bug.module?.name || 'No Module';
        if (groupBy.value === 'stage') key = bug.stage?.name || 'No Stage';
        if (groupBy.value === 'assignee') key = bug.assignee?.name || 'Unassigned';
        if (groupBy.value === 'severity') key = bug.severity.charAt(0).toUpperCase() + bug.severity.slice(1);
        
        if (!acc[key]) acc[key] = [];
        acc[key].push(bug);
        return acc;
    }, {});
});

const assignToMe = async (bug) => {
    const employeeId = page.props.auth?.user?.employee?.id;
    try {
        await axios.put(route('bugs.stage.update', bug.id), {
            assignee_id: employeeId
        });
        refreshList();
    } catch (e) {
        console.error("Manual assignment failed", e);
    }
};

const saveCustomView = async () => {
    if (!newViewName.value.trim()) return;
    try {
        await axios.post(route('bugs.views.store'), {
            name: newViewName.value,
            filters: { ...filters.value, myWorkOnly: myWorkOnly.value, search: search.value, groupBy: groupBy.value }
        });
        showSaveViewModal.value = false;
        newViewName.value = '';
        router.reload({ only: ['custom_views'] });
    } catch (e) {
        console.error("Failed to save view", e);
    }
};

const deleteCustomView = async (id) => {
    if (typeof id === 'string' && id.startsWith('system-')) {
        alert("System views cannot be deleted.");
        return;
    }
    if (!confirm("Are you sure you want to delete this preserved view?")) return;
    try {
        await axios.delete(route('bugs.views.destroy', id));
        if (activeCustomView.value?.id === id) {
            clearCustomView();
        } else {
            router.reload({ only: ['custom_views'] });
        }
    } catch (e) {
        console.error("Failed to delete view", e);
    }
};

const applyCustomView = (view) => {
    activeCustomView.value = view;
    Object.assign(filters.value, view.filters);
    myWorkOnly.value = view.filters.myWorkOnly || false;
    search.value = view.filters.search || '';
    groupBy.value = view.filters.groupBy || 'none';
    debouncedSearch();
};

const clearCustomView = () => {
    activeCustomView.value = null;
    filters.value = {
        project_id: store.selectedProject || '',
        module_id: store.selectedModule || '',
        severity: '',
        timeframe: store.timeFrame || 'sprint'
    };
    myWorkOnly.value = false;
    search.value = '';
    groupBy.value = 'none';
    debouncedSearch();
};

const copyId = (id) => {
    const url = `${window.location.origin}/projects/bugs?bug=${id}`;
    navigator.clipboard.writeText(url);
    // Add a toast notification here if available
};

const getSLATime = (bug) => {
    if (bug.stage?.is_final) return 'Resolved';
    if (!bug.sla_due_at) return 'No SLA';
    
    const due = new Date(bug.sla_due_at);
    const now = new Date();
    const diff = due - now;
    
    if (bug.is_sla_breached || diff < 0) return 'BREACHED';
    
    const hours = Math.floor(diff / (1000 * 60 * 60));
    if (hours > 24) return `${Math.floor(hours/24)}d left`;
    return `${hours}h left`;
};

const isSLABreached = (bug) => {
    if (bug.stage?.is_final) return false;
    if (bug.is_sla_breached) return true;
    if (!bug.sla_due_at) return false;
    return new Date() > new Date(bug.sla_due_at);
};

const escalate = async (bug) => {
    if (!confirm(`Are you sure you want to escalate #${bug.id} to Project Management?`)) return;
    try {
        await axios.post(route('bugs.comments.store', bug.id), {
            body: `<strong>Escalation:</strong> Ticket has been formally escalated to project management for review.`,
            is_public: false
        });
        alert(`Ticket #${bug.id} has been formally escalated to track.`);
    } catch (e) {
        console.error("Escalation failed", e);
    }
};

const shareCurrentView = () => {
    const url = new URL(window.location.href);
    if (activeCustomView.value) {
        url.searchParams.set('view', activeCustomView.value.id);
    }
    navigator.clipboard.writeText(url.toString());
    shareCopied.value = true;
    setTimeout(() => shareCopied.value = false, 2000);
};

const exportData = (format) => {
    showExportMenu.value = false;
    router.visit(route('bugs.export.pdf'), { data: { format } }); // Assume general export route
};

const bulkReassign = async () => {
    if (!selectedBugs.value.length) return;
    try {
        const response = await axios.get(route('bugs.assignees'), {
            params: { project_id: store.selectedProject }
        });
        availableAssignees.value = response.data;
        showBulkReassignModal.value = true;
    } catch (e) {
        console.error("Failed to fetch assignees", e);
    }
};

const executeBulkReassign = async () => {
    if (!selectedAssigneeForBulk.value) return;
    try {
        await axios.post(route('bugs.bulk.update'), {
            ids: selectedBugs.value,
            assignee_id: selectedAssigneeForBulk.value,
            notify: true
        });
        showBulkReassignModal.value = false;
        selectedAssigneeForBulk.value = null;
        selectedBugs.value = [];
        refreshList();
    } catch (e) {
        console.error("Bulk reassign failed", e);
    }
};

const bulkNotify = async () => {
    if (!selectedBugs.value.length) return;
    if (!confirm("This will notify all stakeholders and add a system note to all selected bugs. Proceed?")) return;
    try {
        await axios.post(route('bugs.bulk.update'), {
            ids: selectedBugs.value,
            notify: true
        });
        selectedBugs.value = [];
        refreshList();
    } catch (e) {
        console.error("Bulk notify failed", e);
    }
};

const toggleSelectAll = (bugsInGroup) => {
    const ids = bugsInGroup.map(b => b.id);
    const allSelected = ids.every(id => selectedBugs.value.includes(id));
    if (allSelected) {
        selectedBugs.value = selectedBugs.value.filter(id => !ids.includes(id));
    } else {
        selectedBugs.value = [...new Set([...selectedBugs.value, ...ids])];
    }
};

const bulkMoveToStage = async (stageId) => {
    if (!selectedBugs.value.length) return;
    try {
        await axios.post(route('bugs.bulk.update'), {
            ids: selectedBugs.value,
            workflow_stage_id: stageId
        });
        selectedBugs.value = [];
        refreshList();
    } catch (e) {
        console.error("Bulk update failed", e);
    }
};

onMounted(() => {
    // Handle specific custom view from URL
    const urlParams = new URLSearchParams(window.location.search);
    const viewId = urlParams.get('view');
    if (viewId === 'pulse') {
        viewMode.value = 'pulse';
    } else if (viewId === 'system-manager-approval') {
        // System View: Manager Approval
        filters.value.stages = props.stages?.filter(s => s.name.toLowerCase().includes('approval')).map(s => s.id) || [];
        activeCustomView.value = { id: 'system-manager-approval', name: 'Pending Approvals' };
    } else if (viewId && props.custom_views) {
        const view = props.custom_views.find(v => v.id === viewId);
        if (view) {
            activeCustomView.value = view;
            if (view.filters) {
                 Object.assign(filters.value, view.filters);
                 if (view.filters.groupBy) groupBy.value = view.filters.groupBy;
                 if (view.filters.myWorkOnly) myWorkOnly.value = view.filters.myWorkOnly;
            }
        }
    }

    // Priority 1: If URL has filters, update store to match
    if (props.filters.project_id) store.selectedProject = props.filters.project_id;
    if (props.filters.module_id) store.selectedModule = props.filters.module_id;
    if (props.filters.timeframe) store.timeFrame = props.filters.timeframe;

    // Priority 2: If fresh visit (no URL filters) and store has history, apply store context
    const hasUrlFilters = props.filters.project_id || props.filters.module_id || props.filters.timeframe || props.filters.search || viewId;
    if (!hasUrlFilters && (store.selectedProject || store.selectedModule)) {
         router.get(route('bugs.index'), { 
            tab: 'tracker',
            project_id: store.selectedProject,
            module_id: store.selectedModule,
            timeframe: store.timeFrame
        }, { preserveState: true, replace: true });
    }

    window.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            showCommandPalette.value = true;
        }
    });

    // Handle clicks outside menus
    window.addEventListener('click', () => {
        activeStatusMenu.value = null;
        showExportMenu.value = false;
        showBulkStageMenu.value = false;
    });
});

const processStageUpdate = async (bug, stage, note = null) => {
    try {
        await axios.put(route('bugs.stage.update', bug.id), {
            stage_id: stage.id,
            resolution_note: note
        });
        refreshList();
    } catch (e) {
        console.error("Failed to update stage", e);
        refreshList();
    }
};

const getSeverityClass = (severity) => {
    const map = {
        critical: 'bg-rose-100 text-rose-800 border-rose-200',
        high: 'bg-orange-100 text-orange-800 border-orange-200',
        medium: 'bg-teal-100 text-teal-800 border-teal-200',
        low: 'bg-slate-100 text-slate-800 border-slate-200'
    };
    return map[severity] || 'bg-gray-100 text-gray-800';
};

const closeDetail = () => {
    selectedBugId.value = null;
    showForensics.value = false;
    selectedBugData.value = null;
};

const openBug = (bug) => {
    selectedBugId.value = bug.id;
    showForensics.value = false;
};

const openForensics = (bug) => {
    selectedBugId.value = bug.id;
    selectedBugData.value = bug;
    showForensics.value = true;
};
</script>

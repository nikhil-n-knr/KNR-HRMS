<template>
    <div class="flex flex-col md:flex-row justify-between items-center px-6 py-4 bg-white/80 backdrop-blur-xl border-b border-indigo-100 z-10 shadow-sm gap-4">
        <div class="flex items-center gap-4">
            <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-800 to-purple-700">
                {{ currentSprint?.name || (currentSprint === 'backlog' ? 'Backlog' : 'Project Sprint Board') }}
            </h2>
            
            <!-- Sprint Selector -->
            <div class="relative group">
                    <button class="flex items-center gap-2 text-sm font-medium text-gray-600 hover:text-indigo-600 bg-white/50 px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm transition-all">
                        <span>Switch Sprint</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                    <div class="absolute top-full left-0 pt-2 w-56 hidden group-hover:block z-50">
                        <div class="bg-white rounded-xl shadow-xl border border-gray-100 p-1 max-h-[400px] overflow-y-auto custom-scrollbar">
                        <div class="text-xs font-bold text-gray-400 px-3 py-2 uppercase tracking-wide">Active</div>
                         
                        <div v-if="activeSprints && activeSprints.length > 0">
                            <Link v-for="s in activeSprints" :key="s.id" :href="route('projects.board', { project: project.id, sprint: s.id })" class="block px-3 py-2 text-sm text-indigo-700 hover:bg-indigo-50 rounded-lg font-bold flex justify-between items-center group/item">
                                <span>{{ s.name }}</span>
                                <span v-if="s.id === currentSprint?.id" class="text-xs bg-indigo-100 text-indigo-600 px-1.5 rounded">Current</span>
                            </Link>
                        </div>
                        <div v-else class="px-3 py-2 text-sm text-gray-400 italic">No Active Sprint</div>

                        <div class="text-xs font-bold text-gray-400 px-3 py-2 uppercase tracking-wide border-t mt-1 pt-2">Values</div>
                        <Link :href="route('projects.board', { project: project.id, sprint: 'backlog' })" class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg">
                            Backlog
                        </Link>

                        <div class="text-xs font-bold text-gray-400 px-3 py-2 uppercase tracking-wide border-t mt-1 pt-2">Planned</div>
                        <Link 
                            v-for="s in plannedSprints" 
                            :key="s.id" 
                            :href="route('projects.board', { project: project.id, sprint: s.id })" 
                            class="block px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg"
                        >
                            {{ s.name }}
                        </Link>
                        </div>
                    </div>
            </div>
            
             <!-- Quick Edit Actions for Current Sprint -->
            <div v-if="currentSprint && currentSprint !== 'backlog'" class="flex items-center gap-1">
                <button @click="$emit('editSprint', currentSprint)" class="p-1.5 text-gray-400 hover:text-indigo-600 rounded hover:bg-white/50 transition-colors" title="Edit Sprint Details">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" /></svg>
                </button>
                 <button @click="$emit('deleteSprint', currentSprint)" class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-white/50 transition-colors" title="Delete Sprint">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                </button>
            </div>

            <span v-if="currentSprint?.goal" class="hidden md:inline-block text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-md max-w-xs truncate" :title="currentSprint.goal">
                🎯 {{ currentSprint.goal }}
            </span>
        </div>

        <div class="flex gap-3">
             <!-- Report Toggle -->
             <button 
                @click="$emit('toggleReports')"
                class="px-4 py-2 bg-white text-gray-700 border border-gray-200 hover:bg-gray-50 rounded-xl text-sm font-semibold shadow-sm transition-all flex items-center gap-2"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 011.414.586l4 4a1 1 0 01.586 1.414V19a2 2 0 01-2 2z" /></svg>
                Reports
            </button>

            <!-- Sprint Actions -->
                <button 
                v-if="currentSprint && currentSprint !== 'backlog' && currentSprint.status === 'planned'"
                @click="$emit('startSprint')"
                class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold shadow-lg shadow-emerald-500/30 transition-all flex items-center gap-2"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" /></svg>
                Start Sprint
            </button>

            <button 
                v-if="currentSprint && currentSprint !== 'backlog' && currentSprint.status === 'active'"
                @click="$emit('completeSprint')"
                class="px-4 py-2 bg-white text-indigo-600 border border-indigo-200 hover:bg-indigo-50 rounded-xl text-sm font-semibold shadow-sm transition-all"
            >
                Complete Sprint
            </button>

            <!-- Add Task -->
            <button 
                @click="$emit('openCreateModal')"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-semibold shadow-lg shadow-indigo-500/30 hover:shadow-indigo-500/40 hover:-translate-y-0.5 transition-all flex items-center gap-2"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
                </svg>
                Add Task
            </button>
            
            <!-- Settings Dropdown -->
            <div class="relative group">
                    <button class="p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors border border-transparent hover:border-indigo-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    </button>
                    <div class="absolute right-0 top-full pt-2 w-48 hidden group-hover:block z-50">
                        <div class="bg-white rounded-xl shadow-xl border border-gray-100 p-1">
                            <button @click="$emit('openSprintManager')" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            Manage Sprints
                        </button>
                        <button @click="$emit('openStageManager')" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            Manage Stages
                        </button>
                        <button @click="$emit('openPriorityManager')" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                            Manage Priorities
                        </button>
                        <button @click="$emit('openActivityLog')" class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg flex items-center gap-2 border-t mt-1 pt-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Sprint Board Activity Log
                        </button>
                    </div>
                    </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    project: Object,
    currentSprint: [Object, String],
    activeSprints: Array, // Updated Prop
    plannedSprints: Array
});

defineEmits([
    'startSprint', 
    'completeSprint', 
    'openCreateModal', 
    'openSprintManager', 
    'openStageManager', 
    'openPriorityManager',
    'editSprint',
    'deleteSprint',
    'toggleReports',
    'openActivityLog'
]);
</script>

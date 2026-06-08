<template>
    <div class="min-h-screen bg-[#f8fafc] p-4 lg:p-8 pb-32">
        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                <div class="space-y-2">
                    <div class="flex items-center gap-2 text-sm font-medium text-indigo-600 uppercase tracking-widest">
                        <Link :href="route('projects.tasks.index', project.id)" class="hover:underline">Tasks</Link>
                        <span>/</span>
                        <span>Performance Analytics</span>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-black text-slate-900 tracking-tight">
                        {{ task.title }}
                    </h1>
                    <p class="text-slate-500 font-medium">Task Health & Lifecycle Intelligence</p>
                </div>

                <div class="flex items-center gap-3">
                    <Link :href="route('projects.tasks.index', project.id)"
                       class="px-6 py-3 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-700 hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        Back
                    </Link>
                </div>
            </div>

            <!-- Section 1: Lifecycle Intelligence (Overall) -->
            <div class="space-y-6">
                <div class="flex items-center gap-3">
                    <div class="h-8 w-1 bg-indigo-600 rounded-full"></div>
                    <h2 class="text-lg font-black text-slate-800 uppercase tracking-tight">Lifecycle Overview</h2>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white/80 backdrop-blur-xl border border-white shadow-xl shadow-slate-200/50 rounded-3xl p-6">
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400 italic">Total Matrix Plan</p>
                        <div class="mt-4 flex items-baseline gap-2">
                            <span class="text-3xl font-black text-slate-900">{{ analytics.overall.planned }}</span>
                            <span class="text-sm font-bold text-slate-500 tracking-wide">HRS</span>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-xl border border-white shadow-xl shadow-slate-200/50 rounded-3xl p-6">
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400 italic">Total Invested effort</p>
                        <div class="mt-4 flex items-baseline gap-2">
                            <span class="text-3xl font-black text-slate-900">{{ analytics.overall.actual }}</span>
                            <span v-if="analytics.overall.pending > 0" class="text-lg font-bold text-amber-500 ml-1"> +{{ analytics.overall.pending }}</span>
                            <span class="text-sm font-bold text-slate-500 tracking-wide ml-1">HRS</span>
                        </div>
                    </div>

                    <div class="bg-white/80 backdrop-blur-xl border border-white shadow-xl shadow-slate-200/50 rounded-3xl p-6">
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400 italic">Task duration</p>
                        <div class="mt-4 flex items-baseline gap-2">
                            <span class="text-3xl font-black text-slate-900">{{ analytics.overall.days }}</span>
                            <span class="text-sm font-bold text-slate-500 tracking-wide">DAYS</span>
                        </div>
                    </div>

                    <div :class="analytics.overall.drift.hours > 0 ? 'bg-rose-50 border-rose-100' : 'bg-emerald-50 border-emerald-100'" class="backdrop-blur-xl border shadow-xl shadow-slate-200/50 rounded-3xl p-6 transition-colors">
                        <p class="text-xs font-black uppercase tracking-widest text-slate-400 italic">Governance Drift</p>
                        <div class="mt-4 flex items-baseline gap-2">
                            <span :class="analytics.overall.drift.hours > 0 ? 'text-rose-600' : 'text-emerald-600'" class="text-3xl font-black">{{ analytics.overall.drift.hours > 0 ? '+' : '' }}{{ analytics.overall.drift.hours }}</span>
                            <span class="text-sm font-bold text-slate-500 tracking-wide">HRS</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Grid: Contributors & Checklists (Always Overall) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    <!-- Contributor Lifecycle Report -->
                    <div class="bg-white rounded-[2rem] border border-slate-200 overflow-hidden shadow-sm">
                        <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                            <h3 class="text-sm font-black uppercase tracking-widest text-slate-700 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Contributor Lifecycle Intelligence
                            </h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/30">
                                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Resource</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Planned</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Invested</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Deviation</th>
                                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="c in analytics.contributors" :key="c.id" class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <img :src="c.avatar" class="h-8 w-8 rounded-full border border-slate-200" v-if="c.avatar"/>
                                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500" v-else>{{ c.name[0] }}</div>
                                                <span class="text-sm font-bold text-slate-700">{{ c.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center font-mono text-xs text-slate-500">{{ c.planned }}h</td>
                                        <td class="px-6 py-4 text-center font-mono text-sm font-bold text-slate-700">{{ c.actual }}h</td>
                                        <td class="px-6 py-4 text-center">
                                            <div v-if="c.over_consumption > 0" class="flex flex-col items-center">
                                                <span class="text-rose-600 font-black text-sm">+{{ c.over_consumption }}h</span>
                                                <span class="text-[9px] font-black text-rose-400 uppercase tracking-tighter">Excess</span>
                                            </div>
                                            <span v-else class="text-emerald-500 font-bold text-xs">-{{ c.remaining }}h</span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span v-if="c.over_consumption > 0" class="px-3 py-1 bg-rose-600 text-white text-[10px] font-black rounded-full uppercase shadow-lg shadow-rose-200">Overloaded</span>
                                            <span v-else class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] font-black rounded-full uppercase">Optimal</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="space-y-8">
                    <!-- Context Sidebar -->
                    <div class="bg-indigo-900 rounded-[2rem] p-6 text-white shadow-xl shadow-indigo-200 overflow-hidden relative">
                        <div class="absolute -top-12 -right-12 w-32 h-32 bg-white/10 rounded-full blur-3xl"></div>
                        <h4 class="text-xs font-black uppercase tracking-widest text-indigo-300">Context Intelligence</h4>
                        <div class="mt-6 space-y-4">
                            <div class="flex justify-between items-center py-2 border-b border-white/10">
                                <span class="text-xs font-bold text-indigo-200">Estimated Hours</span>
                                <span class="text-xs font-black uppercase tracking-wider">{{ analytics.meta.estimated_hours }} HRS</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-white/10">
                                <span class="text-xs font-bold text-indigo-200">Scrum Points</span>
                                <span class="px-2 py-0.5 bg-white text-indigo-900 rounded font-black text-[10px]">{{ analytics.overall.scrum_points || 0 }} PTS</span>
                            </div>
                            <div class="flex justify-between items-center py-2 border-b border-white/10">
                                <span class="text-xs font-bold text-indigo-200">Engagement</span>
                                <span class="text-xs font-black uppercase tracking-wider">{{ analytics.meta.comments_count }} Comments</span>
                            </div>
                        </div>

                        <!-- Repo Links -->
                        <div class="mt-6 pt-6 border-t border-white/10 space-y-3">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-indigo-300">Code Intelligence</h4>
                            <div class="grid grid-cols-1 gap-2">
                                <a v-if="analytics.meta.git_branch_url" :href="analytics.meta.git_branch_url" target="_blank" 
                                   class="flex items-center gap-2 p-2 bg-white/10 rounded-xl hover:bg-white/20 transition-all text-[10px] font-bold">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>
                                    Repository Branch
                                </a>
                                <a v-if="analytics.meta.git_pr_url" :href="analytics.meta.git_pr_url" target="_blank" 
                                   class="flex items-center gap-2 p-2 bg-white/10 rounded-xl hover:bg-white/20 transition-all text-[10px] font-bold">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 11V9a2 2 0 00-2-2m2 4v4a2 2 0 104 0v-1m-4-3a2 2 0 012-2h1m-1 4a2 2 0 01-2-2z"/></svg>
                                    Pull Request Link
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit & Checklists -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white rounded-[2rem] border border-slate-200 overflow-hidden shadow-sm">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-700">Sub Tasks Execution</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div v-for="item in analytics.checklists" :key="item.id" class="border border-slate-100 rounded-2xl overflow-hidden bg-white shadow-sm p-4 hover:shadow-md transition-all">
                            <div class="flex items-start gap-3">
                                <div :class="item.is_completed ? 'bg-emerald-500 shadow-emerald-200' : 'bg-slate-100'" class="w-6 h-6 rounded-lg flex items-center justify-center transition-all shadow-lg shrink-0 mt-0.5">
                                    <svg v-if="item.is_completed" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-white" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p :class="item.is_completed ? 'text-slate-400 line-through' : 'text-slate-700'" class="text-sm font-bold break-words">{{ item.content }}</p>
                                    
                                    <!-- Details -->
                                    <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-400 font-semibold uppercase tracking-wider">
                                        <span>Plan {{ formatMinutesToHours(item.planned_minutes) }}</span>
                                        <span class="text-slate-200">|</span>
                                        <span>Actual {{ formatMinutesToHours(item.actual_minutes) }}</span>
                                        <span class="text-slate-200">|</span>
                                        <span>Work Date {{ formatDate(item.work_date) }}</span>
                                        <template v-if="item.verification_status">
                                            <span class="text-slate-200">|</span>
                                            <span :class="{
                                                'text-yellow-600 bg-yellow-50 px-1.5 py-0.5 rounded': item.verification_status === 'pending',
                                                'text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded': item.verification_status === 'approved',
                                                'text-rose-600 bg-rose-50 px-1.5 py-0.5 rounded': item.verification_status === 'rejected'
                                            }">
                                                Verification: {{ item.verification_status }}
                                            </span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[2rem] border border-slate-200 overflow-hidden shadow-sm">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                        <h3 class="text-sm font-black uppercase tracking-widest text-slate-700">Pulse Audit Trail</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-6 relative">
                            <div class="absolute left-[7px] top-2 bottom-2 w-0.5 bg-slate-100"></div>
                            <div v-for="act in analytics.activities.slice(0, 10)" :key="act.id" class="relative pl-6">
                                <div class="absolute left-0 top-1.5 w-4 h-4 rounded-full border-2 border-white bg-slate-200 z-10"></div>
                                <div class="space-y-1">
                                    <p class="text-xs font-bold text-slate-800">{{ getActionDescription(act) }}</p>
                                    
                                    <!-- Detailed Changes -->
                                    <div v-if="act.type === 'update' && act.details?.changes" class="grid grid-cols-1 gap-1 pl-2 border-l-2 border-slate-50 mt-1">
                                        <div v-for="(change, field) in act.details.changes" :key="field" class="text-[9px] flex items-center gap-1">
                                            <span class="font-black text-slate-400 uppercase tracking-tighter">{{ field.replace('_', ' ') }}:</span>
                                            <span class="text-slate-400 line-through decoration-rose-300">{{ change.old || 'N/A' }}</span>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-2 w-2 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                            <span class="text-indigo-600 font-bold">{{ change.new }}</span>
                                        </div>
                                    </div>

                                    <!-- Comment Snippet -->
                                    <div v-if="act.type === 'comment' && act.details?.body_snippet" class="mt-1 pl-2 border-l-2 border-indigo-100 italic text-[10px] text-slate-500">
                                        "{{ act.details.body_snippet }}..."
                                    </div>

                                    <!-- Move Details -->
                                    <div v-if="act.type === 'move' && act.details?.from" class="mt-1 flex items-center gap-1 text-[9px] font-bold">
                                        <span class="text-slate-400">{{ act.details.from }}</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-2 w-2 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                        <span class="text-indigo-600">{{ act.details.to }}</span>
                                    </div>

                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">{{ act.user?.name || 'System' }} • {{ new Date(act.created_at).toLocaleString() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Range Analysis (Searchable Section at the Bottom) -->
            <div class="bg-white rounded-[2rem] border-2 border-indigo-100 shadow-2xl shadow-indigo-100 overflow-hidden">
                <div class="p-8 border-b border-slate-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 bg-indigo-50/30">
                    <div class="space-y-1">
                        <h3 class="text-lg font-black text-slate-800 uppercase tracking-tight flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            Periodic Range Analysis
                        </h3>
                        <p class="text-xs text-slate-500 font-medium tracking-wide italic">Custom drift report for specific date range</p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-2xl p-1.5 shadow-sm">
                            <div class="flex flex-col px-3 border-r border-slate-100">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">From</span>
                                <input type="date" v-model="filterState.start_date" class="border-none p-0 bg-transparent text-xs font-bold text-slate-700 focus:ring-0">
                            </div>
                            <div class="flex flex-col px-3">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">To</span>
                                <input type="date" v-model="filterState.end_date" class="border-none p-0 bg-transparent text-xs font-bold text-slate-700 focus:ring-0">
                            </div>
                            <button @click="applyFilters" class="px-6 py-2.5 bg-indigo-600 text-white text-xs font-black rounded-xl hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 uppercase tracking-widest">
                                Search Range
                            </button>
                        </div>
                        <a :href="route('projects.tasks.analytics.export', { project: project.id, task: task.id, ...filterState })" 
                           class="px-4 py-3 bg-emerald-500 text-white rounded-2xl text-xs font-black hover:bg-emerald-600 transition-all shadow-lg shadow-emerald-100 uppercase tracking-widest flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Export
                        </a>
                    </div>
                </div>

                <div class="p-8 space-y-8">
                    <!-- Periodic Metrics -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Matrix Plan (Period)</span>
                            <p class="text-3xl font-black text-slate-900 mt-2">{{ analytics.periodic.planned }} <span class="text-sm text-slate-500">HRS</span></p>
                        </div>
                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-100 text-center">
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Actual Logged (Period)</span>
                            <p class="text-3xl font-black text-slate-900 mt-2">{{ analytics.periodic.actual }} <span class="text-sm text-slate-500">HRS</span></p>
                        </div>
                        <div :class="analytics.periodic.actual > analytics.periodic.planned ? 'bg-rose-50 border-rose-100 text-rose-600' : 'bg-emerald-50 border-emerald-100 text-emerald-600'" class="p-6 rounded-2xl border text-center transition-colors">
                            <span class="text-[10px] font-black uppercase tracking-widest opacity-60">Periodic Deviation</span>
                            <p class="text-3xl font-black mt-2">
                                {{ analytics.periodic.actual > analytics.periodic.planned ? '+' : '' }}{{ (analytics.periodic.actual - analytics.periodic.planned).toFixed(2) }}
                                <span class="text-sm">HRS</span>
                            </p>
                        </div>
                    </div>

                    <!-- Periodic Contributor Breakdown -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 bg-indigo-500 rounded-full"></div>
                            <h4 class="text-xs font-black text-slate-700 uppercase tracking-widest">Period Resource Intelligence</h4>
                        </div>
                        <div class="overflow-hidden border border-slate-100 rounded-2xl shadow-sm">
                            <table class="w-full text-left border-collapse bg-white">
                                <thead>
                                    <tr class="bg-slate-50/50">
                                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest">Resource</th>
                                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Plan (Range)</th>
                                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Logged (Range)</th>
                                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-center">Drift</th>
                                        <th class="px-6 py-4 text-[9px] font-black text-slate-400 uppercase tracking-widest text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr v-for="row in analytics.periodic.contributors" :key="row.id" class="hover:bg-indigo-50/20 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <img :src="row.avatar" class="h-8 w-8 rounded-full border border-slate-200" v-if="row.avatar"/>
                                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-[10px] font-bold text-slate-500" v-else>{{ row.name[0] }}</div>
                                                <span class="text-sm font-bold text-slate-700">{{ row.name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center font-mono text-xs text-slate-500">{{ row.planned }}h</td>
                                        <td class="px-6 py-4 text-center font-mono text-sm font-bold text-slate-700">{{ row.actual }}h</td>
                                        <td class="px-6 py-4 text-center">
                                            <span :class="row.deviation > 0 ? 'text-rose-600' : 'text-emerald-500'" class="font-black text-xs">
                                                {{ row.deviation > 0 ? '+' : '' }}{{ row.deviation }}h
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span v-if="row.deviation > 0" class="px-2 py-0.5 bg-rose-50 text-rose-600 text-[9px] font-black rounded uppercase">Overworked</span>
                                            <span v-else-if="row.deviation < 0" class="px-2 py-0.5 bg-amber-50 text-amber-600 text-[9px] font-black rounded uppercase">Under plan</span>
                                            <span v-else class="px-2 py-0.5 bg-emerald-50 text-emerald-600 text-[9px] font-black rounded uppercase">Exact</span>
                                        </td>
                                    </tr>
                                    <tr v-if="analytics.periodic.contributors.length === 0">
                                        <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-xs italic font-medium">
                                            No activity or planning found for the selected range.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import ProjectLayout from '@/Layouts/ProjectLayout.vue';

defineOptions({ layout: ProjectLayout });

const props = defineProps({
    project: Object,
    task: Object,
    analytics: Object,
    filters: Object
});

const filterState = reactive({
    start_date: props.filters.start_date,
    end_date: props.filters.end_date
});

const applyFilters = () => {
    router.get(route('projects.tasks.analytics', { project: props.project.id, task: props.task.id }), filterState, {
        preserveState: true,
        preserveScroll: true
    });
};

const formatMinutesToHours = (mins) => {
    if (!mins) return '0.00h';
    return (mins / 60).toFixed(2) + 'h';
};

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
    } catch (e) {
        return dateStr;
    }
};

const getActionDescription = (act) => {
    switch (act.type) {
        case 'create': return 'Created the task';
        case 'update': 
            if (act.details?.changes) {
                const count = Object.keys(act.details.changes).length;
                return `Updated ${count} property${count > 1 ? 'ies' : ''}`;
            }
            if (act.details?.message) return act.details.message;
            return 'Updated task details';
        case 'move': 
            if (act.details?.from && act.details?.to) {
                return `Moved from ${act.details.from} to ${act.details.to}`;
            }
            return 'Moved to new stage';
        case 'comment': return 'Added a new comment';
        case 'system': return act.details?.message || 'System updated task';
        default: return act.type.charAt(0).toUpperCase() + act.type.slice(1);
    }
};
</script>

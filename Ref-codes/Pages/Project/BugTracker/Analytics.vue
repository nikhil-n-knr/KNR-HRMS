<template>
    <Head title="Bug Analytics" />
    <div class="p-6 space-y-10 animate-in fade-in duration-700 font-inter">
        <!-- Dashboard Control Deck -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 bg-white p-6 rounded-[2rem] border border-slate-100 shadow-xl shadow-slate-200/20">
            <div>
                <h2 class="text-3xl font-black text-slate-900 tracking-tighter">Strategic Intelligence</h2>
                <p class="text-slate-400 font-bold uppercase tracking-widest text-xs mt-1">Operational Metrics & Performance Benchmarks</p>
            </div>
            
            <div class="flex flex-wrap lg:flex-nowrap items-center gap-4 w-full lg:w-auto">
                <!-- Date Filters -->
                <div class="flex items-center gap-2 bg-slate-50 p-1.5 rounded-xl border border-slate-200/60 w-full sm:w-auto">
                    <input type="date" v-model="activeFilters.date_start" class="bg-transparent border-none text-sm font-bold text-slate-600 focus:ring-0 px-3 py-2 w-full sm:w-[140px]" title="From Date" />
                    <span class="text-slate-300 font-black">-</span>
                    <input type="date" v-model="activeFilters.date_end" class="bg-transparent border-none text-sm font-bold text-slate-600 focus:ring-0 px-3 py-2 w-full sm:w-[140px]" title="To Date" />
                </div>

                <!-- Project Filter -->
                <div class="w-full sm:w-auto" v-if="lookup && lookup.projects">
                    <select v-model="activeFilters.project_id" class="w-full sm:w-[200px] bg-slate-50 border border-slate-200/60 text-slate-600 text-sm font-bold rounded-xl focus:ring-0 px-4 py-3.5 appearance-none">
                        <option value="">All Projects Portfolio</option>
                        <option v-for="(name, id) in lookup.projects" :key="id" :value="id">{{ name }}</option>
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex gap-2 w-full sm:w-auto">
                    <button @click="applyFilters" class="flex-1 sm:flex-none px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-black uppercase tracking-widest transition-all shadow-md shadow-indigo-600/20">
                        Apply
                    </button>
                    <button @click="resetFilters" class="px-4 py-3 bg-slate-100 hover:bg-slate-200 text-slate-500 rounded-xl text-sm font-black uppercase tracking-widest transition-all" title="Reset Filters">
                        <XMarkIcon class="w-5 h-5" />
                    </button>
                    <button @click="exportToCSV(status_breakdown, 'Analytics_Snapshot', {'stage.name': 'Stage', 'total': 'Total'})" class="px-4 py-3 bg-white border border-slate-200 rounded-xl text-slate-500 hover:bg-slate-50 transition-all flex items-center justify-center shadow-sm" title="Export Current View">
                        <DocumentArrowDownIcon class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </div>

        <!-- KPI Power Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <div v-for="kpi in kpis" :key="kpi.label" 
                 class="group relative bg-white p-6 md:p-10 rounded-[2rem] md:rounded-[2.5rem] shadow-xl shadow-slate-200/20 border border-slate-100 overflow-hidden hover:border-emerald-500/30 transition-all">
                <div :class="['h-12 w-12 md:h-14 md:w-14 rounded-2xl flex items-center justify-center mb-6 md:mb-8 bg-slate-50 transition-transform group-hover:scale-110', kpi.iconBg]">
                    <component :is="kpi.icon" :class="['w-6 h-6 md:w-7 md:h-7', kpi.iconColor]" />
                </div>
                <div class="text-sm md:text-sm font-black text-slate-400 uppercase tracking-widest">{{ kpi.label }}</div>
                <div class="text-3xl md:text-4xl font-black text-slate-900 mt-2 tracking-tighter">{{ kpi.value }}</div>
                
                <div class="mt-4 flex items-center gap-2">
                    <span :class="['text-sm md:text-sm font-black uppercase tracking-widest px-2 py-0.5 rounded-md', kpi.trendColor]">
                        {{ kpi.trend }}
                    </span>
                    <span class="text-xs md:text-sm font-bold text-slate-300 uppercase tracking-widest whitespace-nowrap">vs last week</span>
                </div>
            </div>
        </div>

        <!-- Project Portfolio Breakdown (Visible when no specific project is selected) -->
        <div v-if="!activeFilters.project_id && project_breakdown && project_breakdown.length > 0" class="bg-white rounded-[3rem] p-8 md:p-12 border border-slate-100 shadow-xl shadow-slate-200/30">
            <div class="flex justify-between items-center mb-8">
                <div>
                    <h3 class="text-2xl font-black tracking-tight text-slate-900">Project Portfolio Health</h3>
                    <p class="text-slate-400 text-xs font-black uppercase tracking-widest mt-1">Cross-Project Comparative Analytics</p>
                </div>
                <button @click="exportToCSV(project_breakdown, 'Project_Portfolio', {'project.name': 'Project', 'total_bugs': 'Total Bugs', 'resolved_bugs': 'Resolved Bugs', 'open_critical': 'Open Critical', 'avg_ttr_hours': 'Avg TTR (Hours)', 'tech_debt': 'Tech Debt Index'})" class="px-4 py-2 bg-slate-50 hover:bg-slate-100 text-slate-500 rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-sm">
                    Export Grid
                </button>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest">Project Name</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Total Signal Mass</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Resolution Rate</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Avg TTR</th>
                            <th class="px-6 py-4 text-xs font-black text-slate-400 uppercase tracking-widest text-right">Tech Debt</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="proj in project_breakdown" :key="proj.project_id" @click="activeFilters.project_id = proj.project_id; applyFilters()" class="group hover:bg-slate-50/60 transition-colors cursor-pointer">
                            <td class="px-6 py-5">
                                <div class="text-sm font-black text-slate-900 group-hover:text-indigo-600 transition-colors">{{ proj.project?.name || 'Unknown Project' }}</div>
                            </td>
                            <td class="px-6 py-5 text-right font-black text-slate-700">{{ proj.total_bugs }}</td>
                            <td class="px-6 py-5 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <span class="text-xs font-bold text-slate-500">{{ Math.round((proj.resolved_bugs / proj.total_bugs) * 100) || 0 }}%</span>
                                    <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-emerald-500" :style="{ width: `${(proj.resolved_bugs / proj.total_bugs) * 100}%` }"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-right font-bold text-slate-500">
                                {{ proj.avg_ttr_hours ? proj.avg_ttr_hours + 'h' : 'N/A' }}
                            </td>
                            <td class="px-6 py-5 text-right">
                                <span :class="['px-3 py-1 rounded-lg text-xs font-black uppercase tracking-widest', proj.tech_debt > 30 ? 'bg-rose-50 text-rose-600' : 'bg-slate-50 text-slate-500']">
                                    {{ proj.tech_debt }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Architectural Intelligence Deck (God Mode Metrics) -->
        <div class="space-y-6">
            <div class="flex items-center gap-2.5">
                <span class="px-3 py-1 bg-indigo-500/10 text-indigo-400 text-xs font-black uppercase tracking-[0.2em] rounded-lg border border-indigo-500/20">Executive Cockpit</span>
                <h3 class="text-xl font-black text-slate-900 tracking-tight">Architectural Intelligence</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Cost of Quality Card -->
                <div class="group bg-gradient-to-br from-slate-900 via-slate-950 to-indigo-950 p-8 rounded-[2rem] border border-white/5 shadow-2xl relative overflow-hidden transition-all hover:-translate-y-1">
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <BanknotesIcon class="w-32 h-32 text-indigo-400" />
                    </div>
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-8">
                                <span class="text-xs font-black text-indigo-400 uppercase tracking-widest">Financial Impact</span>
                                <div class="h-10 w-10 bg-white/5 rounded-xl flex items-center justify-center border border-white/10">
                                    <BanknotesIcon class="w-5 h-5 text-indigo-400" />
                                </div>
                            </div>
                            <h4 class="text-slate-400 font-bold uppercase tracking-widest text-xs">Cost of Quality (CoQ)</h4>
                            <div v-if="godModeLoading" class="h-10 w-32 bg-white/5 animate-pulse rounded-lg mt-2"></div>
                            <div v-else class="text-4xl font-black text-white tracking-tighter mt-2">
                                ${{ godModeData.cost_of_quality.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) }}
                            </div>
                        </div>
                        <p class="text-slate-500 text-xs font-medium mt-6 leading-relaxed">
                            Cumulative engineering cost spent resolving defects (hours multiplied by internal cost rates).
                        </p>
                    </div>
                </div>

                <!-- Technical Debt Card -->
                <div class="group bg-gradient-to-br from-slate-900 via-slate-950 to-rose-950 p-8 rounded-[2rem] border border-white/5 shadow-2xl relative overflow-hidden transition-all hover:-translate-y-1">
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <CpuChipIcon class="w-32 h-32 text-rose-400" />
                    </div>
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-8">
                                <span class="text-xs font-black text-rose-400 uppercase tracking-widest">Risk Exposure</span>
                                <div class="h-10 w-10 bg-white/5 rounded-xl flex items-center justify-center border border-white/10">
                                    <CpuChipIcon class="w-5 h-5 text-rose-400" />
                                </div>
                            </div>
                            <h4 class="text-slate-400 font-bold uppercase tracking-widest text-xs">Technical Debt Index</h4>
                            <div v-if="godModeLoading" class="h-10 w-20 bg-white/5 animate-pulse rounded-lg mt-2"></div>
                            <div v-else class="text-4xl font-black text-white tracking-tighter mt-2 flex items-baseline gap-2">
                                <span>{{ godModeData.technical_debt_score }}</span>
                                <span :class="['text-xs font-black uppercase tracking-widest px-2 py-0.5 rounded', godModeData.technical_debt_score > 50 ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20']">
                                    {{ godModeData.technical_debt_score > 50 ? 'High Debt' : 'Healthy' }}
                                </span>
                            </div>
                        </div>
                        <p class="text-slate-500 text-xs font-medium mt-6 leading-relaxed">
                            Weighted risk index of all unresolved anomalies (Critical = 10x, High = 5x, Medium = 2x, Low = 1x).
                        </p>
                    </div>
                </div>

                <!-- Engineering Velocity Card -->
                <div class="group bg-gradient-to-br from-slate-900 via-slate-950 to-emerald-950 p-8 rounded-[2rem] border border-white/5 shadow-2xl relative overflow-hidden transition-all hover:-translate-y-1">
                    <div class="absolute top-0 right-0 p-8 opacity-5">
                        <ClockIcon class="w-32 h-32 text-emerald-400" />
                    </div>
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div>
                            <div class="flex items-center justify-between mb-8">
                                <span class="text-xs font-black text-emerald-400 uppercase tracking-widest">Process Efficiency</span>
                                <div class="h-10 w-10 bg-white/5 rounded-xl flex items-center justify-center border border-white/10">
                                    <ClockIcon class="w-5 h-5 text-emerald-400" />
                                </div>
                            </div>
                            <h4 class="text-slate-400 font-bold uppercase tracking-widest text-xs">Lead vs Cycle Velocity</h4>
                            <div v-if="godModeLoading" class="h-10 w-44 bg-white/5 animate-pulse rounded-lg mt-2"></div>
                            <div v-else class="grid grid-cols-2 gap-4 mt-2">
                                <div>
                                    <div class="text-3xl font-black text-white tracking-tighter">{{ godModeData.avg_lead_time_hours }}h</div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Avg Lead (TTR)</div>
                                </div>
                                <div>
                                    <div class="text-3xl font-black text-white tracking-tighter">{{ godModeData.avg_cycle_time_hours }}h</div>
                                    <div class="text-[9px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Avg Cycle</div>
                                </div>
                            </div>
                        </div>
                        <p class="text-slate-500 text-xs font-medium mt-6 leading-relaxed">
                            Lead time calculates from ticket creation to terminal state; Cycle time captures direct resolution speed from start of development.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Central Intelligence Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 md:gap-8">
            <!-- Team Velocity Chart -->
            <div class="lg:col-span-2 bg-slate-900 rounded-[2rem] md:rounded-[3rem] p-8 md:p-12 text-white shadow-2xl shadow-slate-900/40 relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8 md:mb-12">
                        <div>
                            <h3 class="text-xl md:text-2xl font-black tracking-tight">Team Velocity</h3>
                            <p class="text-slate-400 text-sm font-black uppercase tracking-widest mt-1">7-Day Operational Pulse</p>
                        </div>
                        <div class="flex gap-4 md:gap-6 text-xs md:text-sm font-black uppercase tracking-[0.2em] text-slate-500">
                            <div class="flex items-center gap-2"><div class="h-2 w-2 rounded-full bg-slate-700"></div> Incoming</div>
                            <div class="flex items-center gap-2"><div class="h-2 w-2 rounded-full bg-emerald-500"></div> Resolved</div>
                        </div>
                    </div>

                    <div class="h-64 md:h-80 flex items-end justify-between space-x-3 md:space-x-6 relative">
                        <div class="absolute inset-0 flex flex-col justify-between pointer-events-none opacity-[0.03]">
                            <div v-for="i in 4" :key="i" class="w-full h-px bg-white"></div>
                        </div>

                        <div v-for="(label, idx) in velocity.labels" :key="idx" class="flex flex-col items-center flex-1 group">
                             <div class="w-full flex justify-center items-end gap-1 md:gap-2 h-full z-10">
                                <div class="w-1.5 md:w-2 mb-0 bg-white/5 rounded-t-lg" :style="{ height: (velocity.created[idx] / maxVelocity * 100) + '%' }"></div>
                                <div class="w-3 md:w-5 mb-0 bg-emerald-500 rounded-t-lg shadow-[0_0_20px_rgba(16,185,129,0.3)]" :style="{ height: (velocity.resolved[idx] / maxVelocity * 100) + '%' }"></div>
                            </div>
                            <div class="mt-6 md:mt-8 text-xs md:text-sm font-black text-slate-500 uppercase tracking-widest tabular-nums">
                                {{ formatDate(label).split(' ')[0] }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hotspots -->
            <div class="bg-white rounded-[2rem] md:rounded-[3rem] p-8 md:p-12 border border-slate-100 shadow-xl shadow-slate-200/30 flex flex-col">
                <div class="flex justify-between items-center mb-8 md:mb-10">
                    <h3 class="text-xl font-black tracking-tight">Zone Hotspots</h3>
                    <FireIcon class="w-5 h-5 md:w-6 md:h-6 text-rose-500 pulse" />
                </div>
                
                <div class="flex-1 min-h-[300px] flex flex-col items-center gap-8 relative">
                    <div class="w-full h-48 md:h-64 relative flex items-center justify-center">
                        <canvas ref="hotspotCanvas"></canvas>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-3xl font-black text-slate-900">{{ Math.min(hotspots.length, 10) }}</span>
                            <span class="text-sm font-black uppercase tracking-widest text-slate-400">Zones</span>
                        </div>
                    </div>
                    <div class="w-full space-y-2.5 custom-scrollbar overflow-y-auto max-h-48 pr-2">
                        <div v-for="(hotspot, idx) in hotspots" :key="idx" class="flex items-center justify-between group">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-2 h-2 rounded-full shrink-0" :style="{ backgroundColor: chartColors[idx % chartColors.length] }"></span>
                                <span class="text-sm font-black uppercase tracking-widest text-slate-500 truncate group-hover:text-slate-900 transition-colors" :title="hotspot.module?.name">
                                    {{ hotspot.module?.name }}
                                </span>
                            </div>
                            <span class="text-xs font-black text-slate-900 shrink-0">{{ hotspot.total }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Heatmap Row -->
        <ModuleHeatmap :projectId="projectId" />

        <!-- Report Builder Row -->
        <ReportBuilder :projectId="projectId" class="mt-8" />

        <!-- Operational Risk Row -->
        <div class="bg-white rounded-[3rem] p-8 md:p-12 shadow-2xl shadow-slate-200/30 border border-slate-100">
            <div class="flex justify-between items-center mb-8 md:mb-12">
                <div>
                    <h3 class="text-2xl font-black tracking-tight text-slate-900">Operational Risk Watchlist</h3>
                    <p class="text-slate-400 text-xs font-black uppercase tracking-widest mt-1">Analyzing SLA breach probability</p>
                </div>
                <div class="h-12 w-12 bg-rose-50 rounded-2xl flex items-center justify-center">
                    <ShieldExclamationIcon class="w-6 h-6 text-rose-500" />
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b border-slate-50">
                            <th class="px-6 py-5 text-sm font-black text-slate-400 uppercase tracking-[0.2em] w-16">Ref</th>
                            <th class="px-6 py-5 text-sm font-black text-slate-400 uppercase tracking-[0.2em]">Subject</th>
                            <th class="px-6 py-5 text-sm font-black text-slate-400 uppercase tracking-[0.2em] text-right">Exposure</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <tr v-for="bug in sla_breaches" :key="bug.id" class="group hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-6">
                                <span class="bg-slate-100 px-2 py-1 rounded text-sm font-black text-slate-500">BT-{{ bug.id.toString().padStart(4, '0') }}</span>
                            </td>
                            <td class="px-6 py-6">
                                <div class="text-xs font-black text-slate-900 truncate max-w-[200px] md:max-w-[400px]">{{ bug.subject }}</div>
                                <div class="text-sm font-bold text-slate-400 uppercase tracking-widest mt-0.5">{{ bug.module?.name }}</div>
                            </td>
                            <td class="px-6 py-6 text-right">
                                <span :class="['px-3 py-1.5 rounded-lg text-sm font-black uppercase tracking-[0.1em]', getExposureDays(bug) > 10 ? 'bg-rose-50 text-rose-600' : 'bg-orange-50 text-orange-600']">
                                    {{ getExposureDays(bug) }} Days
                                </span>
                            </td>
                        </tr>
                        <tr v-if="sla_breaches.length === 0">
                            <td colspan="3" class="py-16 text-center">
                                <div class="flex flex-col items-center opacity-30">
                                    <HeartIcon class="w-8 h-8 mb-3" />
                                    <p class="text-xs font-black uppercase tracking-widest">No Operational Breaches</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Human Capital Analytics Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Developer Leaderboard -->
            <div class="bg-white rounded-[3rem] p-8 md:p-12 shadow-2xl shadow-slate-200/30 border border-slate-100 flex flex-col">
                <div class="flex justify-between items-center mb-8 md:mb-10">
                    <div>
                        <h3 class="text-2xl font-black tracking-tight text-slate-900">Top Resolvers</h3>
                        <p class="text-slate-400 text-xs font-black uppercase tracking-widest mt-1">Resolution Throughput</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="exportToCSV(leaderboard, 'Top_Resolvers', {'assignee.name': 'Assignee', 'total_resolved': 'Resolved'})" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-slate-50 rounded-lg transition-colors">
                            <DocumentArrowDownIcon class="w-5 h-5" />
                        </button>
                        <div class="h-12 w-12 bg-emerald-50 rounded-2xl flex items-center justify-center">
                            <TrophyIcon class="w-6 h-6 text-emerald-500" />
                        </div>
                    </div>
                </div>

                <div class="space-y-4 flex-1">
                    <div v-for="(dev, idx) in leaderboard" :key="idx" 
                         @click="activeFilters.assignee_id = dev.assignee_id; applyFilters()"
                         class="flex items-center justify-between p-4 rounded-2xl bg-slate-50/50 hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100 cursor-pointer">
                        <div class="flex items-center gap-4">
                            <div class="font-black text-lg text-slate-300 w-6 text-center">#{{ idx + 1 }}</div>
                            <img :src="dev.assignee?.avatar || `https://ui-avatars.com/api/?name=${dev.assignee?.first_name}+${dev.assignee?.last_name}&background=random`" 
                                 class="w-10 h-10 rounded-full border-2 border-white shadow-sm" />
                            <div>
                                <h4 class="text-sm font-black text-slate-900">{{ dev.assignee?.name }}</h4>
                                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Engineer</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-2xl font-black text-emerald-600">{{ dev.total_resolved }}</span>
                            <span class="text-xs font-black uppercase tracking-widest text-slate-400">Resolved</span>
                        </div>
                    </div>
                    
                    <div v-if="!leaderboard || leaderboard.length === 0" class="flex-1 flex flex-col items-center justify-center opacity-30 h-40">
                        <p class="text-xs font-black uppercase tracking-widest">No resolution data available</p>
                    </div>
                </div>
            </div>

            <!-- Reporter Leaderboard -->
            <div class="bg-white rounded-[3rem] p-8 md:p-12 shadow-2xl shadow-slate-200/30 border border-slate-100 flex flex-col">
                <div class="flex justify-between items-center mb-8 md:mb-10">
                    <div>
                        <h3 class="text-2xl font-black tracking-tight text-slate-900">Top Reporters</h3>
                        <p class="text-slate-400 text-xs font-black uppercase tracking-widest mt-1">Issue Identification</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="exportToCSV(reporter_leaderboard, 'Top_Reporters', {'reporter.name': 'Reporter', 'total_reported': 'Reported'})" class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-slate-50 rounded-lg transition-colors">
                            <DocumentArrowDownIcon class="w-5 h-5" />
                        </button>
                        <div class="h-12 w-12 bg-indigo-50 rounded-2xl flex items-center justify-center">
                            <BugAntIcon class="w-6 h-6 text-indigo-500" />
                        </div>
                    </div>
                </div>

                <div class="space-y-4 flex-1">
                    <div v-for="(rep, idx) in reporter_leaderboard" :key="idx" 
                         @click="activeFilters.reporter_id = rep.reporter_id; applyFilters()"
                         class="flex items-center justify-between p-4 rounded-2xl bg-slate-50/50 hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100 cursor-pointer">
                        <div class="flex items-center gap-4">
                            <div class="font-black text-lg text-slate-300 w-6 text-center">#{{ idx + 1 }}</div>
                            <img :src="rep.reporter?.avatar || `https://ui-avatars.com/api/?name=${rep.reporter?.first_name}+${rep.reporter?.last_name}&background=random`" 
                                 class="w-10 h-10 rounded-full border-2 border-white shadow-sm" />
                            <div>
                                <h4 class="text-sm font-black text-slate-900">{{ rep.reporter?.name }}</h4>
                                <p class="text-sm font-bold text-slate-400 uppercase tracking-widest">Reporter</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <span class="text-2xl font-black text-indigo-600">{{ rep.total_reported }}</span>
                            <span class="text-xs font-black uppercase tracking-widest text-slate-400">Raised</span>
                        </div>
                    </div>
                    
                    <div v-if="!reporter_leaderboard || reporter_leaderboard.length === 0" class="flex-1 flex flex-col items-center justify-center opacity-30 h-40">
                        <p class="text-xs font-black uppercase tracking-widest">No reporting data available</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, ref, onMounted, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import Chart from 'chart.js/auto';
import { 
    BugAntIcon, 
    CheckCircleIcon, 
    FireIcon, 
    ShieldExclamationIcon, 
    HeartIcon,
    DocumentArrowDownIcon,
    ChartBarIcon,
    CpuChipIcon,
    Bars3CenterLeftIcon,
    BoltIcon,
    ArrowTrendingUpIcon,
    ClockIcon,
    TrophyIcon,
    BanknotesIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';
import axios from 'axios';
import ModuleHeatmap from './Components/ModuleHeatmap.vue';
import ReportBuilder from './Components/ReportBuilder.vue';

const props = defineProps(['projectId', 'hotspots', 'status_breakdown', 'sla_breaches', 'velocity', 'lookup', 'avg_resolution_hours', 'leaderboard', 'reporter_leaderboard', 'project_breakdown', 'filters']);
const emit = defineEmits(['switch-to-intelligence']);

const activeFilters = ref({
    date_start: props.filters?.date_start || '',
    date_end: props.filters?.date_end || '',
    reporter_id: props.filters?.reporter_id || '',
    assignee_id: props.filters?.assignee_id || '',
    project_id: props.projectId || props.filters?.project_id || ''
});

const applyFilters = () => {
    // Preserve other URL parameters (like tab) but update filters
    const currentUrl = new URL(window.location.href);
    Object.keys(activeFilters.value).forEach(key => {
        if (activeFilters.value[key]) {
            currentUrl.searchParams.set(key, activeFilters.value[key]);
        } else {
            currentUrl.searchParams.delete(key);
        }
    });
    router.visit(currentUrl.toString(), { preserveScroll: true, preserveState: true });
};

const resetFilters = () => {
    activeFilters.value = {
        date_start: '',
        date_end: '',
        reporter_id: '',
        assignee_id: '',
        project_id: ''
    };
    applyFilters();
};

const exportToCSV = (data, filename, mappings) => {
    if (!data || !data.length) return;
    
    // Build Header
    const headers = Object.values(mappings);
    let csvContent = headers.join(',') + '\n';
    
    // Build Rows
    data.forEach(item => {
        const row = Object.keys(mappings).map(key => {
            // Support dot notation (e.g. 'module.name')
            const val = key.split('.').reduce((o, i) => o ? o[i] : '', item);
            return `"${(val || '').toString().replace(/"/g, '""')}"`;
        });
        csvContent += row.join(',') + '\n';
    });
    
    // Download
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `${filename}_${dayjs().format('YYYYMMDD')}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
};

const godModeData = ref({
    cost_of_quality: 0,
    avg_lead_time_hours: 0,
    avg_cycle_time_hours: 0,
    technical_debt_score: 0
});
const godModeLoading = ref(true);

const fetchGodModeData = async () => {
    godModeLoading.value = true;
    try {
        const response = await axios.get(route('bugs.analytics.god-mode'), {
            params: { project_id: props.projectId }
        });
        godModeData.value = response.data;
    } catch (e) {
        console.error("Failed to fetch godMode data", e);
    } finally {
        godModeLoading.value = false;
    }
};

const totalBugs = computed(() => {
    return props.status_breakdown.reduce((acc, curr) => acc + curr.total, 0);
});

const resolvedRatio = computed(() => {
    if (totalBugs.value === 0) return 0;
    const closed = props.status_breakdown.find(s => (s.stage?.name || '').toLowerCase().includes('close'));
    return closed ? Math.round((closed.total / totalBugs.value) * 100) : 0;
});

const maxVelocity = computed(() => {
    if (!props.velocity) return 10;
    return Math.max(...props.velocity.created, ...props.velocity.resolved, 1);
});

const kpis = computed(() => [
    { 
        label: 'Total Signal Mass', 
        value: totalBugs.value, 
        icon: Bars3CenterLeftIcon, 
        iconBg: 'bg-slate-50', 
        iconColor: 'text-slate-900',
        trend: '+12%',
        trendColor: 'bg-emerald-50 text-emerald-600'
    },
    { 
        label: 'Avg Resolution Time', 
        value: props.avg_resolution_hours + 'h', 
        icon: ClockIcon, 
        iconBg: 'bg-indigo-50', 
        iconColor: 'text-indigo-600',
        trend: '30d Avg',
        trendColor: 'bg-slate-100 text-slate-600'
    },
    { 
        label: 'Survival Rate', 
        value: (100 - (props.sla_breaches.length / (totalBugs.value || 1) * 100)).toFixed(1) + '%', 
        icon: ArrowTrendingUpIcon, 
        iconBg: 'bg-emerald-50', 
        iconColor: 'text-emerald-600',
        trend: '+2.4%',
        trendColor: 'bg-emerald-50 text-emerald-600'
    },
    { 
        label: 'Vulnerability Index', 
        value: props.sla_breaches.length, 
        icon: ShieldExclamationIcon, 
        iconBg: 'bg-rose-50', 
        iconColor: 'text-rose-600',
        trend: '-4',
        trendColor: 'bg-rose-50 text-rose-600'
    }
]);

const getExposureDays = (bug) => {
    return Math.floor(dayjs().diff(dayjs(bug.created_at), 'day'));
};

const formatDate = (date) => dayjs(date).format('DD MMM');

// ChartJS Implementation for Zone Hotspots
const hotspotCanvas = ref(null);
let hotspotChart = null;

const chartColors = [
    '#34d399', // emerald-400
    '#f43f5e', // rose-500
    '#f59e0b', // amber-500
    '#3b82f6', // blue-500
    '#8b5cf6', // violet-500
    '#06b6d4', // cyan-500
    '#ec4899', // pink-500
    '#84cc16', // lime-500
    '#f97316', // orange-500
    '#94a3b8'  // slate-400
];

const initHotspotChart = () => {
    if (!hotspotCanvas.value || !props.hotspots) return;
    if (hotspotChart) hotspotChart.destroy();
    
    const ctx = hotspotCanvas.value.getContext('2d');
    
    hotspotChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: props.hotspots.map(h => h.module?.name || 'Unknown Zone'),
            datasets: [{
                data: props.hotspots.map(h => h.total),
                backgroundColor: chartColors,
                borderWidth: 0,
                hoverOffset: 12,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: {
                legend: {
                    display: false // Using custom HTML legend
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 16,
                    titleFont: { family: 'Inter', size: 10, weight: '900' },
                    bodyFont: { family: 'Inter', size: 14, weight: '700' },
                    cornerRadius: 16,
                    displayColors: true,
                    boxPadding: 6
                }
            }
        }
    });
};

onMounted(() => {
    initHotspotChart();
    fetchGodModeData();
});

watch(() => props.hotspots, () => {
    initHotspotChart();
}, { deep: true });

watch(() => props.projectId, () => {
    fetchGodModeData();
});
</script>

<style scoped>
@keyframes pulse-soft { 0%, 100% { opacity: 1; } 50% { opacity: 0.5; } }
.pulse { animation: pulse-soft 2s infinite; }
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
</style>

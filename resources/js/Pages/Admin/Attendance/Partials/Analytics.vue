<template>
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-10 animate-fade-in relative z-10">
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white/90 backdrop-blur-md p-5 rounded-2xl border border-slate-200 shadow-sm sticky top-32">
                <div class="flex items-center gap-3 mb-6 px-1">
                    <div class="w-8 h-8 bg-slate-900 rounded-xl flex items-center justify-center text-white shadow-lg group-hover:bg-emerald-600 transition-colors">
                        <i class="fas fa-filter text-sm"></i>
                    </div>
                    <h3 class="text-base font-black text-slate-800 tracking-tight uppercase leading-none">Parameters</h3>
                </div>
                
                <div class="space-y-6">
                    <!-- Date Range -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] px-1">Temporal Range</label>
                        <div class="grid grid-cols-1 gap-2">
                            <div class="relative group">
                                <i class="fas fa-calendar-alt absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 text-sm group-focus-within:text-emerald-500 transition-colors"></i>
                                <input v-model="filters.start_date" type="date" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-0 pl-9 pr-3 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase h-9 shadow-inner">
                            </div>
                            <div class="relative group">
                                <i class="fas fa-calendar-check absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 text-sm group-focus-within:text-emerald-500 transition-colors"></i>
                                <input v-model="filters.end_date" type="date" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-0 pl-9 pr-3 text-sm font-black text-slate-700 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all uppercase h-9 shadow-inner">
                            </div>
                        </div>
                    </div>

                    <!-- Department -->
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] px-1">Sector Focus</label>
                        <div class="relative group">
                            <i class="fas fa-sitemap absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 text-sm group-focus-within:text-emerald-500 transition-colors"></i>
                            <select v-model="filters.department_id" class="w-full bg-slate-50 border border-slate-200 rounded-xl py-0 pl-9 pr-8 text-sm font-black uppercase text-slate-600 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all appearance-none cursor-pointer h-9 shadow-inner">
                                <option value="">ALL_SECTORS</option>
                                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name.toUpperCase() }}</option>
                            </select>
                            <i class="fas fa-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-300 pointer-events-none text-xs"></i>
                        </div>
                    </div>

                    <!-- Employee Search -->
                    <div class="space-y-2 relative">
                        <label class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] px-1">Entity Precision</label>
                        
                        <div v-if="selectedEmployeeName" class="flex items-center justify-between p-2.5 bg-emerald-50 border border-emerald-100 rounded-xl group animate-in slide-in-from-left-2 duration-300 shadow-inner">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-slate-900 flex items-center justify-center text-white text-xs shadow-sm">
                                    <i class="fas fa-user-check"></i>
                                </div>
                                <span class="text-sm font-black text-emerald-900 truncate max-w-[100px] uppercase tracking-tight">{{ selectedEmployeeName }}</span>
                            </div>
                            <button @click="clearEmployee" class="w-6 h-6 rounded-lg bg-white border border-emerald-200 text-emerald-400 hover:text-rose-600 transition-all flex items-center justify-center shadow-sm">
                                <i class="fas fa-times text-sm"></i>
                            </button>
                        </div>
                        <div v-else class="relative group">
                            <i class="fas fa-user-tag absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-300 text-sm group-focus-within:text-emerald-500 transition-colors"></i>
                            <input 
                                v-model="employeeSearch" 
                                @input="searchEmployees($event.target.value)"
                                type="text" 
                                placeholder="SEARCH ENTITY..." 
                                class="w-full bg-slate-50 border border-slate-200 rounded-xl py-0 pl-9 pr-4 text-sm font-black text-slate-700 h-9 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all shadow-inner"
                            >
                            <!-- Autocomplete Dropdown -->
                            <Transition
                                enter-active-class="transition duration-200 ease-out"
                                enter-from-class="translate-y-2 opacity-0"
                                enter-to-class="translate-y-0 opacity-100"
                            >
                                <div v-if="employeeList.length" class="absolute z-50 w-full mt-2 bg-white/90 backdrop-blur-xl border border-slate-200 shadow-xl rounded-xl overflow-hidden py-1">
                                    <div 
                                        v-for="emp in employeeList" :key="emp.id"
                                        @click="selectEmployee(emp)"
                                        class="px-4 py-2.5 hover:bg-emerald-50 cursor-pointer text-sm font-black text-slate-600 hover:text-emerald-600 transition-all flex items-center gap-2"
                                    >
                                        <div class="w-5 h-5 rounded-md bg-slate-100 flex items-center justify-center text-xs">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        {{ emp.name.toUpperCase() }}
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>

                <!-- Live Status Indicator -->
                <div class="mt-6 pt-5 border-t border-slate-100">
                    <div class="flex items-center gap-2.5 px-3 py-1.5 bg-slate-50 rounded-xl border border-slate-100 shadow-inner">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.8)]"></div>
                        <span class="text-[7.5px] font-black text-slate-500 uppercase tracking-[0.25em]">Neural Connection Live</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts & Intelligence Area -->
        <div class="lg:col-span-3 space-y-10">
            <div v-if="loading" class="h-[600px] flex items-center justify-center bg-white/40 backdrop-blur-xl rounded-[40px] border border-white shadow-2xl">
                <div class="flex flex-col items-center gap-6">
                    <div class="w-16 h-16 border-4 border-emerald-500/10 border-t-emerald-600 rounded-full animate-spin"></div>
                    <p class="text-sm font-black text-emerald-600 uppercase tracking-[0.3em]">Decoding Signal...</p>
                </div>
            </div>

            <!-- ORGANIZATION VIEW -->
            <div v-else-if="viewType === 'organization' && stats" class="space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-700">
                <!-- Overview Tactical Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-9 h-9 bg-slate-50 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-slate-900 group-hover:text-white transition-all duration-500 shadow-inner border border-slate-100">
                                <i class="fas fa-database text-base"></i>
                            </div>
                            <span class="text-xs font-black text-slate-500 bg-slate-50 px-2 py-0.5 rounded border border-slate-200 uppercase tracking-widest leading-none">Aggregate</span>
                        </div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5 leading-none">Total Payload</p>
                        <div class="text-2xl font-black text-slate-800 tracking-tighter tabular-nums leading-none group-hover:text-emerald-700 transition-colors">{{ stats.overview.total_logs }} <span class="text-xs text-slate-300 ml-1 uppercase font-black">Units</span></div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center text-emerald-500 group-hover:bg-slate-900 group-hover:text-white transition-all duration-500 shadow-inner border border-emerald-100">
                                <i class="fas fa-users-check text-base"></i>
                            </div>
                            <span class="text-xs font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 uppercase tracking-widest leading-none">Stability</span>
                        </div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5 leading-none">Mean Presence</p>
                        <div class="text-2xl font-black text-emerald-600 tracking-tighter tabular-nums leading-none">{{ stats.overview.avg_daily_present }} <span class="text-xs text-emerald-400/40 ml-1 uppercase font-black">Avg</span></div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-9 h-9 bg-amber-50 rounded-xl flex items-center justify-center text-amber-500 group-hover:bg-slate-900 group-hover:text-white transition-all duration-500 shadow-inner border border-amber-100">
                                <i class="fas fa-clock-rotate-left text-base"></i>
                            </div>
                            <span class="text-xs font-black text-amber-600 bg-amber-50 px-2 py-0.5 rounded border border-amber-200 uppercase tracking-widest leading-none">Skew</span>
                        </div>
                        <p class="text-xs font-black text-slate-400 uppercase tracking-[0.2em] mb-1.5 leading-none">Latency Index</p>
                        <div class="text-2xl font-black text-amber-500 tracking-tighter tabular-nums leading-none">{{ stats.overview.late_rate }}<span class="text-sm ml-1 uppercase font-black text-amber-400/40">%</span></div>
                    </div>
                </div>

                <!-- Visualization Layer -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Main Trend -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all duration-300 hover:border-emerald-500">
                        <h4 class="text-xs font-black text-slate-800 tracking-wider flex items-center gap-3 mb-8 uppercase">
                            <i class="fas fa-chart-line text-emerald-500"></i>
                            System Propagation
                        </h4>
                        <div class="h-64 relative">
                             <Line 
                                :data="{
                                    labels: stats.trend.labels,
                                    datasets: [{
                                        label: 'Active Entities',
                                        data: stats.trend.present,
                                        borderColor: '#10b981',
                                        borderWidth: 3,
                                        pointBackgroundColor: '#fff',
                                        pointBorderColor: '#10b981',
                                        pointBorderWidth: 2,
                                        pointRadius: 4,
                                        pointHoverRadius: 6,
                                        fill: true,
                                        backgroundColor: 'rgba(16, 185, 129, 0.05)',
                                        tension: 0.4
                                    }]
                                }" 
                                :options="{
                                    ...commonChartOptions,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        x: { grid: { display: false }, ticks: { font: { size: 9, weight: '800' }, color: '#94a3b8' } },
                                        y: { grid: { color: 'rgba(0,0,0,0.03)' }, ticks: { font: { size: 9, weight: '800' }, color: '#94a3b8' } }
                                    }
                                }" 
                             />
                        </div>
                    </div>

                    <!-- Sector Health -->
                    <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm transition-all duration-300 hover:border-emerald-500">
                        <h4 class="text-xs font-black text-slate-800 tracking-wider flex items-center gap-3 mb-8 uppercase">
                            <i class="fas fa-chart-bar text-emerald-600"></i>
                            Sector Calibration
                        </h4>
                        <div class="h-64 relative">
                             <Bar 
                                :data="{
                                    labels: stats.department_health.labels,
                                    datasets: [{
                                        label: 'Operational Hours',
                                        data: stats.department_health.data,
                                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                                        hoverBackgroundColor: '#10b981',
                                        borderRadius: 12,
                                        barThickness: 20
                                    }]
                                }" 
                                :options="{
                                    ...commonChartOptions,
                                    plugins: { legend: { display: false } },
                                    scales: {
                                        x: { grid: { display: false }, ticks: { font: { size: 9, weight: '800' }, color: '#94a3b8' } },
                                        y: { grid: { color: 'rgba(0,0,0,0.03)' }, ticks: { font: { size: 9, weight: '800' }, color: '#94a3b8' } }
                                    }
                                }" 
                             />
                        </div>
                    </div>
                </div>

                <!-- Neural Hub (Cross-Module Intelligence) -->
                <div v-if="stats.modules" class="pt-8">
                    <div class="flex items-center gap-4 mb-8">
                        <div class="h-[1px] flex-1 bg-slate-100"></div>
                        <h3 class="text-sm font-black text-slate-400 uppercase tracking-[0.4em] whitespace-nowrap">Cross-Module Integration Matrix</h3>
                        <div class="h-[1px] flex-1 bg-slate-100"></div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div v-for="(mod, key) in stats.modules" :key="key" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                            <div class="flex justify-between items-center mb-5">
                                <h4 class="text-sm font-black text-slate-800 tracking-wider uppercase flex items-center gap-2">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_5px_rgba(16,185,129,0.5)]"></span>
                                    {{ key }} Analytics
                                </h4>
                                <span class="text-[7.5px] font-black px-2 py-0.5 bg-slate-50 rounded border border-slate-100 text-slate-400 uppercase tracking-widest leading-none">{{ mod.unit }}</span>
                            </div>
                            
                            <div class="mb-5 flex items-end gap-2">
                                <span class="text-3xl font-black text-slate-900 tracking-tighter tabular-nums leading-none">{{ mod.total }}</span>
                                <span class="text-xs font-black text-slate-400 uppercase tracking-widest mb-1 leading-none">Aggregate</span>
                            </div>
                            
                            <!-- Distribution Waveform -->
                            <div class="space-y-2 mb-6">
                                <div class="flex justify-between text-xs font-black text-slate-400 uppercase tracking-[0.2em]">
                                    <span>Signal Low</span>
                                    <span>Signal High</span>
                                </div>
                                <div class="flex h-1.5 rounded-full overflow-hidden bg-slate-50 border border-slate-100 p-0.5 shadow-inner">
                                     <div class="bg-slate-400/20 rounded-full transition-all duration-1000" :style="{width: (mod.distribution.below / (mod.distribution.below + mod.distribution.above || 1)) * 100 + '%'}"></div> 
                                     <div class="flex-1 bg-emerald-500 rounded-full transition-all duration-1000 shadow-[0_0_5px_rgba(16,185,129,0.3)]"></div>
                                </div>
                            </div>

                            <!-- Top Contributors Terminal -->
                            <div class="bg-slate-900 rounded-xl p-4 shadow-lg">
                                <span class="text-xs font-black text-slate-500 uppercase tracking-[0.3em] mb-3 block leading-none">Primary Contributor Node</span>
                                <ul class="space-y-2">
                                    <li v-for="(p, i) in mod.top_performers" :key="i" class="flex justify-between items-center border-b border-white/5 pb-1.5 last:border-0 group/row">
                                        <div class="flex items-center gap-2 w-2/3">
                                            <span class="text-xs font-black text-slate-700 font-mono">0{{ i+1 }}</span>
                                            <span class="text-sm font-black text-slate-300 uppercase truncate group-hover/row:text-emerald-400 transition-colors leading-none tracking-tight">{{ p.name }}</span>
                                        </div>
                                        <span class="font-black text-emerald-400 text-sm font-mono tracking-tighter tabular-nums leading-none">{{ p.value }}<span class="text-xs opacity-40 ml-0.5 uppercase tracking-tighter">{{ mod.unit }}</span></span>
                                    </li>
                                    <li v-if="mod.top_performers.length === 0" class="text-xs font-black text-slate-600 uppercase tracking-widest italic text-center py-1">No_Signal</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- INDIVIDUAL VIEW -->
            <div v-else-if="viewType === 'individual' && stats" class="space-y-8 animate-in fade-in slide-in-from-bottom-5 duration-700">
                <!-- Entity Bio Card -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-12 h-12 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 mb-4 group-hover:rotate-6 group-hover:bg-slate-900 group-hover:text-white transition-all duration-500 shadow-inner border border-slate-100">
                                <i class="fas fa-stopwatch text-xl"></i>
                            </div>
                            <span class="text-xs uppercase font-black text-slate-400 tracking-[0.2em] mb-1.5 leading-none">Mean Duration</span>
                            <div class="text-2xl font-black text-slate-900 tracking-tighter tabular-nums leading-none mb-3 group-hover:text-emerald-700 transition-colors">{{ stats.stats.mean_hours }}<span class="text-sm ml-0.5 uppercase">h</span></div>
                            <div class="px-3 py-1 bg-slate-50 rounded-lg text-xs font-black text-slate-500 uppercase tracking-widest border border-slate-200 shadow-sm">Target: {{ stats.stats.median_hours }}h</div>
                        </div>
                    </div>

                    <div class="bg-slate-900 p-5 rounded-2xl shadow-xl group relative overflow-hidden">
                        <div class="absolute -top-10 -right-10 w-24 h-24 bg-emerald-500/20 blur-[40px] rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                        <div class="flex flex-col items-center text-center relative z-10">
                            <div class="w-12 h-12 rounded-xl bg-white/10 flex items-center justify-center text-emerald-400 mb-4 shadow-lg border border-white/5 backdrop-blur-md">
                                <i class="fas fa-fingerprint text-xl"></i>
                            </div>
                            <span class="text-xs uppercase font-black text-emerald-500 tracking-[0.2em] mb-1.5 leading-none">Consistency Index</span>
                            <div class="text-2xl font-black text-white tracking-tighter tabular-nums leading-none mb-5">{{ stats.stats.consistency_score }}</div>
                            <div class="w-full h-1 bg-white/5 rounded-full overflow-hidden border border-white/5 p-px shadow-inner">
                                <div class="bg-emerald-500 h-full transition-all duration-1000 shadow-[0_0_8px_rgba(16,185,129,0.8)]" :style="{width: stats.stats.consistency_score + '%'}"></div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm group hover:border-emerald-500 transition-all duration-300">
                        <div class="flex flex-col items-center text-center">
                            <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500 mb-4 transition-all duration-500 group-hover:scale-110 shadow-inner border border-amber-100">
                                <i class="fas fa-circle-exclamation text-xl"></i>
                            </div>
                            <span class="text-xs uppercase font-black text-slate-400 tracking-[0.2em] mb-1.5 leading-none">Latency Triggered</span>
                            <div class="text-2xl font-black text-amber-500 tracking-tighter tabular-nums leading-none mb-3">{{ stats.stats.late_days }}</div>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Signal Pool: {{ stats.stats.total_days }} Logs</p>
                        </div>
                    </div>
                </div>

                <!-- Gamification Identity Matrix -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Protocol Points & Badges -->
                    <div class="bg-slate-900 rounded-2xl p-8 text-white shadow-md relative overflow-hidden group">
                        <div class="absolute -top-10 -right-10 w-32 h-32 bg-emerald-600/30 blur-[60px] rounded-full group-hover:scale-125 transition-all duration-700"></div>
                        
                        <h4 class="text-sm font-black text-emerald-400 uppercase tracking-[0.4em] mb-8 flex items-center gap-2.5 relative z-10">
                            <i class="fas fa-shield-halved"></i>
                            Neural Identity Score
                        </h4>

                        <div class="flex items-end gap-3 mb-10 relative z-10">
                            <span class="text-5xl font-black tracking-tighter text-transparent bg-clip-text bg-gradient-to-br from-white to-white/40 leading-none tabular-nums">{{ stats.stats.total_points }}</span>
                            <span class="text-sm font-black text-slate-500 uppercase tracking-widest mb-1.5">Synapse_Pts</span>
                        </div>
                        
                        <!-- Badges Grid -->
                        <div class="grid grid-cols-2 gap-3 relative z-10">
                            <div v-for="(b, i) in stats.gamification.badges" :key="i" class="bg-white/5 border border-white/10 backdrop-blur-sm p-3 rounded-xl flex items-center gap-3 hover:bg-white/10 transition-all cursor-default group/badge">
                                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center text-emerald-400 shadow-[0_0_10px_rgba(16,185,129,0.2)] group-hover/badge:scale-110 transition-transform">
                                    <i class="fas fa-award text-[14px]"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-black text-white tracking-widest uppercase mb-0.5 truncate">{{ b.name }}</p>
                                    <p class="text-xs font-black text-slate-500 uppercase tracking-widest">Verified_Protocol</p>
                                </div>
                            </div>
                            <div v-if="stats.gamification.badges.length === 0" class="col-span-2 text-xs font-black text-slate-600 uppercase tracking-widest italic text-center py-3 bg-white/5 rounded-xl border border-dashed border-white/10">Identity_Badges_Locked</div>
                        </div>
                    </div>

                    <!-- Thermal Resonance (Streaks) -->
                    <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm group transition-all duration-300 hover:border-emerald-500 flex flex-col">
                        <h4 class="text-xs font-black text-slate-800 tracking-tight flex items-center gap-3 mb-8 uppercase">
                            <i class="fas fa-fire-flame-curved text-amber-500 animate-pulse"></i>
                            Thermal Resonance
                        </h4>
                        
                        <div class="space-y-4 flex-1 flex flex-col justify-center">
                            <div v-for="streak in stats.gamification.streaks" :key="streak.streak_type" class="group/streak relative">
                                <div class="flex items-center justify-between p-5 bg-slate-50 rounded-xl border border-slate-100 shadow-sm hover:border-emerald-200 hover:bg-emerald-50/30 transition-all duration-300">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-lg bg-white shadow-sm flex items-center justify-center text-amber-500 border border-slate-50">
                                            <i class="fas fa-bolt-lightning text-xl"></i>
                                        </div>
                                        <div>
                                            <span class="text-xs font-black text-amber-400 uppercase tracking-widest mb-1 block leading-none">{{ streak.streak_type.replace('_', ' ') }}</span>
                                            <span class="text-2xl font-black text-slate-800 tracking-tighter tabular-nums">{{ streak.current_streak }} <span class="text-sm text-slate-300 ml-0.5 uppercase">Days</span></span>
                                        </div>
                                    </div>
                                    <div class="w-8 h-8 rounded-full border-2 border-emerald-500 border-t-transparent animate-spin-slow"></div>
                                </div>
                            </div>
                            
                            <div v-if="stats.gamification.streaks.length === 0" class="flex-1 flex flex-col items-center justify-center text-center opacity-40 py-4">
                                <i class="fas fa-ice-cream text-4xl mb-4 text-slate-200"></i>
                                <p class="text-sm font-black text-slate-400 uppercase tracking-[0.3em]">Resonance Neutralized</p>
                                <p class="text-xs font-black text-slate-300 uppercase tracking-widest mt-1.5">Awaiting active routine synchronization...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Signal Pattern Analysis -->
                <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm transition-all duration-300 hover:border-emerald-500">
                    <div class="flex justify-between items-center mb-10">
                        <h4 class="text-xs font-black text-slate-800 tracking-wider flex items-center gap-3 uppercase">
                            <i class="fas fa-wave-square text-emerald-500"></i>
                            Work Pattern Decomposition
                        </h4>
                        <div class="px-3 py-1 bg-slate-50 rounded-lg text-xs font-black text-slate-400 uppercase tracking-widest border border-slate-100">Frequency: Daily_Pulse</div>
                    </div>
                    
                    <div class="h-80 relative">
                         <Line 
                            :data="{
                                labels: stats.trend.labels,
                                datasets: [
                                    {
                                        label: 'Daily Amplitude (Hours)',
                                        data: stats.trend.data,
                                        borderColor: '#10b981',
                                        borderWidth: 3,
                                        pointRadius: 0,
                                        fill: true,
                                        backgroundColor: (context) => {
                                            const chart = context.chart;
                                            const {ctx, chartArea} = chart;
                                            if (!chartArea) return null;
                                            const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
                                            gradient.addColorStop(0, 'rgba(16, 185, 129, 0.1)');
                                            gradient.addColorStop(1, 'rgba(16, 185, 129, 0)');
                                            return gradient;
                                        },
                                        tension: 0.4
                                    },
                                    {
                                        label: 'Mean Equilibrium',
                                        data: Array(stats.trend.labels.length).fill(stats.trend.mean_line),
                                        borderColor: '#10b981',
                                        borderWidth: 1.5,
                                        borderDash: [6, 6],
                                        pointRadius: 0,
                                        fill: false
                                    }
                                ]
                            }" 
                            :options="{
                                ...commonChartOptions,
                                plugins: { legend: { display: false } },
                                scales: {
                                    x: { grid: { display: false }, ticks: { font: { size: 9, weight: '800' }, color: '#94a3b8' } },
                                    y: { border: { dash: [4, 4] }, grid: { color: 'rgba(0,0,0,0.03)' }, ticks: { font: { size: 9, weight: '800' }, color: '#94a3b8' } }
                                }
                            }" 
                        />
                    </div>
                    <div class="mt-8 pt-6 border-t border-slate-50 flex justify-between items-center">
                        <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Dotted Vector: Mean Equilibrium Index ({{ stats.trend.mean_line }}h)</p>
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-xs font-black text-slate-500 uppercase tracking-widest">Active Resonance</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue';
import axios from 'axios';
import debounce from 'lodash/debounce';
import { 
  Chart as ChartJS, 
  Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, PointElement, LineElement, ArcElement 
} from 'chart.js';
import { Bar, Doughnut, Line } from 'vue-chartjs';

ChartJS.register(CategoryScale, LinearScale, BarElement, PointElement, LineElement, Title, Tooltip, Legend, ArcElement);

const props = defineProps({
    departments: Array
});

const loading = ref(false);
const viewType = ref('organization'); // 'organization' | 'individual'
const stats = ref(null);

const today = new Date();
const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);

const filters = reactive({
    start_date: firstDay.toISOString().split('T')[0],
    end_date: today.toISOString().split('T')[0],
    department_id: '',
    employee_id: '' 
});

// Employee Search
const employeeSearch = ref('');
const employeeList = ref([]); 
const selectedEmployeeName = ref('');

const searchEmployees = debounce(async (query) => {
    if (!query) {
        employeeList.value = [];
        return;
    }
    try {
        const res = await axios.get(`/api/employees/search?query=${query}`);
        employeeList.value = res.data;
    } catch (e) {
        console.error(e);
    }
}, 300);

const selectEmployee = (emp) => {
    filters.employee_id = emp.id;
    selectedEmployeeName.value = emp.name;
    employeeSearch.value = '';
    employeeList.value = [];
    viewType.value = 'individual';
};

const clearEmployee = () => {
    filters.employee_id = '';
    selectedEmployeeName.value = '';
    viewType.value = 'organization'; 
};

// Data Fetching
const fetchData = async () => {
    loading.value = true;
    try {
        const res = await axios.get('/admin/attendance/analytics/data', { params: filters });
        stats.value = res.data;
        viewType.value = res.data.type; 
    } catch (e) {
        console.error("Analytics Error", e);
    } finally {
        loading.value = false;
    }
};

watch(filters, debounce(() => {
    fetchData();
}, 600), { deep: true });

onMounted(() => {
    fetchData();
});

// Chart Config
const commonChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { labels: { font: { size: 10, weight: '900' }, color: '#94a3b8' } }
    }
};
</script>

<style scoped>
.animate-spin-slow {
    animation: spin 3s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

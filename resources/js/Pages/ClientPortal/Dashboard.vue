<template>
    <div class="min-h-screen bg-slate-50 flex font-inter selection:bg-emerald-100 selection:text-emerald-900">
        <!-- Sidebar -->
        <aside class="hidden lg:flex w-72 bg-slate-900 flex-col fixed inset-y-0 z-50 transition-all duration-500 overflow-hidden">
            <div class="p-8 flex items-center gap-4 border-b border-white/5">
                <div class="h-10 w-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20">
                    <CpuChipIcon class="h-6 w-6 text-slate-900" />
                </div>
                <div class="flex flex-col">
                    <h1 class="text-white font-black text-lg tracking-tighter leading-none">OneHub Connect Portals</h1>
                    <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest mt-1">Enterprise Hub v2.5</span>
                </div>
            </div>

            <nav class="flex-1 p-6 space-y-2 overflow-y-auto custom-scrollbar">
                <button v-for="tab in tabs" :key="tab.id"
                    @click="activeTab = tab.id"
                    class="w-full flex items-center gap-4 px-4 py-3.5 rounded-2xl transition-all group relative overflow-hidden"
                    :class="activeTab === tab.id ? 'bg-white text-slate-900 shadow-xl' : 'text-slate-400 hover:text-white hover:bg-white/5'">
                    <component :is="tab.icon" class="h-5 w-5 transition-transform group-hover:scale-110" />
                    <span class="text-xs font-black uppercase tracking-widest">{{ tab.label }}</span>
                    <div v-if="tab.badge" class="ml-auto bg-rose-500 text-white text-[9px] px-2 py-0.5 rounded-lg shadow-lg">
                        {{ tab.badge }}
                    </div>
                </button>

                <div class="pt-8 pb-4">
                    <p class="px-4 text-[9px] font-black text-slate-500 uppercase tracking-[0.2em] mb-4">Operations</p>
                    <button @click="showWizard = true" class="w-full flex items-center gap-4 px-4 py-4 bg-emerald-500/10 text-emerald-400 rounded-2xl border border-emerald-500/20 hover:bg-emerald-500 hover:text-slate-900 transition-all group">
                        <PlusIcon class="h-5 w-5" />
                        <span class="text-xs font-black uppercase tracking-widest">Raise Ticket</span>
                    </button>
                    <button @click="showVault = true" class="w-full mt-3 flex items-center gap-4 px-4 py-4 bg-white/5 text-slate-400 rounded-2xl hover:bg-white/10 hover:text-white transition-all border border-white/5 group">
                        <BookOpenIcon class="h-5 w-5" />
                        <span class="text-xs font-black uppercase tracking-widest">Knowledge Library</span>
                    </button>
                </div>
            </nav>

            <div class="p-6 border-t border-white/5 bg-slate-950/30">
                <div class="bg-white/5 p-4 rounded-3xl flex items-center gap-3 border border-white/5">
                    <div class="h-10 w-10 rounded-xl bg-slate-800 flex items-center justify-center text-slate-400 text-sm font-black uppercase border border-white/5 shadow-inner">
                        {{ client?.name?.[0] || 'C' }}
                    </div>
                    <div class="flex-1 overflow-hidden">
                        <p class="text-[11px] font-black text-white truncate">{{ client?.name || 'Stakeholder' }}</p>
                        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-tighter truncate">Verified Stakeholder</p>
                    </div>
                    <button @click="logout" class="text-slate-600 hover:text-rose-500 transition-colors">
                        <ArrowRightOnRectangleIcon class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 lg:pl-72 flex flex-col min-w-0">
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-slate-100 flex items-center justify-between px-8 sticky top-0 z-40">
                <div class="flex items-center gap-6 flex-1">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden h-10 w-10 bg-slate-50 rounded-xl flex items-center justify-center text-slate-600 border border-slate-100">
                        <Bars3Icon class="h-5 w-5" />
                    </button>
                    <div class="hidden md:flex items-center gap-3 px-4 py-2 bg-slate-50 rounded-xl w-96 border border-slate-100 focus-within:border-emerald-500/30 focus-within:bg-white transition-all group">
                        <MagnifyingGlassIcon class="h-4 w-4 text-slate-400 group-focus-within:text-emerald-500" />
                        <input type="text" placeholder="Search operational telemetry or vault artifacts..." class="bg-transparent border-none focus:ring-0 text-xs font-medium w-full placeholder:text-slate-300">
                    </div>
                </div>
                <div class="flex items-center gap-6">
                    <div class="hidden sm:flex items-center gap-6">
                        <div v-for="stat in summaryStats" :key="stat.label" class="flex flex-col items-end">
                            <span class="text-[8px] font-black text-slate-400 uppercase tracking-widest">{{ stat.label }}</span>
                            <span class="text-xs font-black text-slate-900 tracking-tighter">{{ stat.value }}</span>
                        </div>
                    </div>
                    <div class="h-6 w-px bg-slate-100 mx-1 hidden sm:block"></div>
                    
                    <!-- Notifications Popover -->
                    <div class="relative">
                        <button @click="showNotifications = !showNotifications" class="h-10 w-10 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-all border border-slate-100 relative group">
                            <BellIcon class="h-5 w-5" />
                            <span v-if="notifications.length > 0" class="absolute top-2.5 right-2.5 h-1.5 w-1.5 bg-rose-500 rounded-full border border-white group-hover:scale-125 transition-transform animate-pulse"></span>
                        </button>

                        <transition name="pop">
                            <div v-if="showNotifications" class="absolute right-0 mt-3 w-80 bg-white rounded-3xl shadow-2xl border border-slate-100 z-50 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-300">
                                <div class="p-5 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                                    <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-900">Intelligence Stream</h3>
                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 text-[8px] font-black rounded-md">{{ notifications.length }} Unread</span>
                                </div>
                                <div class="max-h-96 overflow-y-auto custom-scrollbar">
                                    <div v-if="notifications.length === 0" class="p-10 text-center text-slate-300 italic text-[10px] uppercase font-black uppercase tracking-widest">No New Signals</div>
                                    <div v-for="note in notifications" :key="note.id" class="p-4 border-b border-slate-50 hover:bg-slate-50 transition-colors cursor-pointer group">
                                        <p class="text-xs font-bold text-slate-900 group-hover:text-emerald-600 transition-colors">{{ note.data.message || 'New system update detected' }}</p>
                                        <span class="text-[9px] font-black text-slate-400 uppercase mt-1">{{ dayjs(note.created_at).fromNow() }}</span>
                                    </div>
                                </div>
                                <button v-if="notifications.length > 0" class="w-full p-4 text-center text-[9px] font-black uppercase tracking-widest bg-slate-900 text-white hover:bg-emerald-600 transition-colors">Mark all as acknowledged</button>
                            </div>
                        </transition>
                    </div>
                </div>
            </header>

            <main class="flex-1 p-6 lg:p-10">
                <div class="max-w-[1600px] mx-auto space-y-8">
                    <transition name="fade-slide" mode="out-in">
                        <!-- Dashboard View -->
                        <div v-if="activeTab === 'dashboard'" key="dashboard" class="space-y-8">
                            <!-- Hero Section (Calculations from DB) -->
                            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 bg-white p-8 rounded-[2rem] border border-slate-100 shadow-sm relative overflow-hidden group">
                                <div class="space-y-1 relative z-10">
                                    <div class="inline-flex items-center gap-2 px-2 py-1 bg-slate-900 text-white rounded-lg text-[9px] font-black uppercase tracking-widest mb-2 shadow-xl shadow-slate-900/20">
                                        <span class="h-1 w-1 rounded-full bg-emerald-400 animate-pulse"></span>
                                        Live Pulse Synchronized
                                    </div>
                                    <h1 class="text-3xl font-black text-slate-900 tracking-tighter leading-tight">{{ greeting }} {{ client?.name || 'Stakeholder' }}</h1>
                                    <p class="text-slate-400 text-xs font-medium max-w-lg leading-relaxed">System integrity is maintained across <span class="text-emerald-600 font-bold tabular-nums">{{ stats.active_signals }}</span> active signals in your corporate pipeline.</p>
                                </div>
                                <div class="flex items-center gap-3 relative z-10">
                                    <button @click="activeTab = 'documents'" class="px-6 py-3 bg-slate-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-600 active:scale-95 transition-all shadow-xl shadow-slate-900/10">Governance Check</button>
                                    <button @click="showWizard = true" class="px-6 py-3 bg-white border border-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-slate-50 transition-all border-b-4 border-slate-200 hover:border-b-0 translate-y-[-2px] hover:translate-y-0 active:scale-95">Signal Broadcast</button>
                                </div>
                                <!-- Abstract BG Gradient -->
                                <div class="absolute -right-20 -top-20 h-64 w-64 bg-emerald-50/50 rounded-full blur-[80px] group-hover:bg-emerald-100/50 transition-all duration-700"></div>
                            </div>

                            <!-- Dashboard Stats Grid -->
                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                                <!-- Projects: Real Data -->
                                <div class="lg:col-span-8 space-y-6">
                                    <div class="flex items-center justify-between px-2">
                                        <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] flex items-center gap-3 italic">
                                            Operational Cluster Status
                                            <span class="h-px w-12 bg-slate-200"></span>
                                            <span class="text-slate-900 font-black not-italic">{{ projects.length }} Active Nodes</span>
                                        </h2>
                                    </div>

                                    <div v-for="project in projects" :key="project.id" class="bg-white rounded-[2.5rem] border border-slate-100 p-8 flex flex-col md:flex-row items-center gap-10 group hover:border-emerald-200 hover:shadow-2xl hover:shadow-emerald-500/5 transition-all duration-700 relative overflow-hidden">
                                        <div class="flex-1 min-w-0 space-y-6">
                                            <div class="flex justify-between items-start">
                                                <div class="space-y-1">
                                                    <h3 class="text-2xl font-black text-slate-900 tracking-tighter group-hover:text-emerald-600 transition-colors truncate">{{ project.name }}</h3>
                                                    <div class="flex items-center gap-3 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                                                        <span class="px-2 py-0.5 bg-slate-50 border border-slate-100 rounded text-slate-500">ID: PRJ-{{ project.id.toString().padStart(4, '0') }}</span>
                                                        <span class="h-1 w-1 bg-slate-200 rounded-full"></span>
                                                        <span :class="project.status === 'Completed' ? 'text-emerald-500 px-2 py-0.5 bg-emerald-50 rounded' : 'text-indigo-500 px-2 py-0.5 bg-indigo-50 rounded'">{{ project.manual_status_label || 'Executing' }}</span>
                                                    </div>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-3xl font-black text-slate-900 tracking-tighter flex items-center gap-1 group-hover:scale-110 transition-transform tabular-nums origin-right">
                                                        {{ project.manual_progress_percentage || 0 }}<span class="text-lg opacity-30">%</span>
                                                    </div>
                                                    <p class="text-[8px] font-black text-slate-300 uppercase mt-1 tracking-widest">Saturation</p>
                                                </div>
                                            </div>

                                            <div class="space-y-4">
                                                <div class="h-3 bg-slate-50 rounded-full border border-slate-100 p-0.5 overflow-hidden shadow-inner">
                                                    <div class="h-full bg-gradient-to-r from-emerald-500 to-indigo-600 rounded-full transition-all duration-1000 shadow-[0_0_15px_rgba(16,185,129,0.3)]"
                                                         :style="{ width: (project.manual_progress_percentage || 0) + '%' }"></div>
                                                </div>
                                                <div class="flex justify-between px-1">
                                                    <div v-for="(step, idx) in ['Initiated', 'Dev Phase', 'Verification', 'Public']" :key="step" class="flex flex-col items-center gap-2">
                                                        <div class="h-1.5 w-12 sm:w-24 rounded-full transition-all duration-500" 
                                                             :class="(project.manual_progress_percentage >= (idx + 1) * 25) ? 'bg-emerald-500 shadow-lg shadow-emerald-500/20' : 'bg-slate-100'"></div>
                                                        <span class="text-[8px] font-black uppercase tracking-tighter transition-colors" 
                                                              :class="(project.manual_progress_percentage >= (idx + 1) * 25) ? 'text-emerald-600' : 'text-slate-300'">{{ step }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="hidden md:block w-px h-24 bg-gradient-to-b from-transparent via-slate-100 to-transparent"></div>

                                        <!-- Health Indicator -->
                                        <div class="flex flex-col items-center justify-center p-6 bg-slate-50 rounded-[2rem] border border-slate-100 min-w-[140px] shadow-inner group-hover:bg-white transition-colors">
                                            <div class="relative h-16 w-16 mb-3">
                                                <svg class="h-full w-full transform -rotate-90">
                                                    <circle cx="32" cy="32" r="28" fill="transparent" stroke="currentColor" stroke-width="4" class="text-slate-100" />
                                                    <circle cx="32" cy="32" r="28" fill="transparent" stroke="currentColor" stroke-width="4" stroke-dasharray="175.9" :stroke-dashoffset="175.9 - (175.9 * (project.project_health_index || 0) / 100)" class="text-emerald-500 transition-all duration-1000" />
                                                </svg>
                                                <div class="absolute inset-0 flex items-center justify-center text-xs font-black text-slate-900 tabular-nums italic">
                                                    {{ project.project_health_index }}
                                                </div>
                                            </div>
                                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Node Health</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Operational Feed (Right) -->
                                <div class="lg:col-span-4 space-y-6">
                                    <div class="bg-white rounded-[2.5rem] border border-slate-100 shadow-sm flex flex-col h-[750px] overflow-hidden">
                                        <div class="p-8 border-b border-slate-50 flex items-center justify-between shrink-0 bg-slate-900 text-white">
                                            <div>
                                                <h3 class="text-xs font-black uppercase tracking-widest italic tracking-tighter">Operational Signals</h3>
                                                <p class="text-[10px] font-bold text-emerald-400 uppercase mt-1 tracking-widest underline decoration-emerald-400/30">Live Triage Stream</p>
                                            </div>
                                            <button @click="exportReport" class="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center text-white hover:bg-emerald-500 hover:text-slate-900 transition-all border border-white/10 group">
                                                <ArrowDownTrayIcon class="h-5 w-5 group-hover:-translate-y-1 transition-transform" />
                                            </button>
                                        </div>
                                        <div class="flex-1 overflow-y-auto p-6 custom-scrollbar space-y-4 bg-slate-50/10">
                                            <div v-for="ticket in recent_bugs" :key="ticket.id" 
                                                @click="openTicket(ticket)"
                                                class="bg-white p-5 rounded-2xl border border-slate-100 hover:border-emerald-400 hover:shadow-xl hover:shadow-emerald-500/5 cursor-pointer transition-all group relative overflow-hidden">
                                                <div class="absolute top-0 right-0 h-1 w-0 bg-emerald-500 group-hover:w-full transition-all duration-500"></div>
                                                <div class="flex justify-between items-start mb-3">
                                                    <span class="text-[9px] font-black px-3 py-1 bg-slate-900 text-white rounded-lg uppercase tracking-widest">SIG-{{ ticket.id }}</span>
                                                    <span class="text-[9px] font-bold text-slate-400 italic lowercase">{{ dayjs(ticket.updated_at).fromNow() }}</span>
                                                </div>
                                                <h4 class="text-sm font-black text-slate-900 leading-tight line-clamp-2 mb-3 group-hover:text-emerald-700 transition-colors">{{ ticket.subject }}</h4>
                                                <div class="flex items-center gap-4">
                                                    <div class="flex items-center gap-2">
                                                        <span class="h-2 w-2 rounded-full shadow-[0_0_8px] animate-pulse" :class="ticket.severity === 'critical' ? 'bg-rose-500 shadow-rose-500/50' : 'bg-amber-500 shadow-amber-500/50'"></span>
                                                        <span class="text-[9px] font-black text-slate-400 uppercase italic">{{ ticket.severity }}</span>
                                                    </div>
                                                    <span class="h-4 w-px bg-slate-100"></span>
                                                    <span class="text-[9px] font-bold text-emerald-600 truncate max-w-[120px] uppercase tracking-widest">{{ ticket.project?.name }}</span>
                                                </div>
                                                <div class="mt-3 flex items-center gap-2">
                                                    <span class="text-[8px] font-black uppercase tracking-widest px-2 py-1 rounded" :class="ticket.stage?.requires_verification && !ticket.stage?.is_final ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-500'">
                                                        {{ ticket.stage?.requires_verification && !ticket.stage?.is_final ? 'Awaiting Your Verification' : (ticket.stage?.name || 'Unassigned Stage') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="p-6 bg-white border-t border-slate-50 shrink-0">
                                            <button @click="showWizard = true" class="w-full py-4 bg-slate-50 border-2 border-dashed border-slate-200 rounded-2xl text-[10px] font-black uppercase tracking-widest text-slate-400 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-all flex items-center justify-center gap-3">
                                                <BoltIcon class="h-4 w-4" />
                                                Broadcast Intel Signal 🛰️
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vault & KB Articles -->
                        <div v-else-if="activeTab === 'documents'" key="documents" class="animate-in fade-in slide-in-from-bottom-4 duration-700">
                             <DocumentVault :documents="documents" :projects="projects" />
                        </div>

                        <div v-else-if="activeTab === 'profile'" key="profile" class="animate-in fade-in slide-in-from-bottom-4 duration-700">
                             <ClientProfile :client="client" :projects="projects" />
                        </div>

                        <div v-else-if="activeTab === 'chat'" key="chat" class="animate-in fade-in slide-in-from-bottom-4 duration-700">
                             <SupportChat :recent_bugs="recent_bugs" />
                        </div>
                    </transition>
                </div>
            </main>
        </div>

        <!-- Overlays -->
        <KnowledgeVault :show="showVault" :kb_articles="kb_articles" @close="showVault = false" />
        
        <Modal :show="showWizard" max-width="2xl" @close="showWizard = false">
            <TicketCreator :projects="projects" @close="showWizard = false" @chat="activeTab = 'chat'; showWizard = false" />
        </Modal>
        
        <!-- Signal Detail Terminal (Expanded View) -->
        <transition name="pop">
            <div v-if="selectedTicket" class="fixed inset-0 z-[200] bg-slate-900/40 backdrop-blur-xl flex items-center justify-center p-4 lg:p-12 overflow-hidden">
                 <div class="bg-white w-full max-w-7xl h-full max-h-[900px] rounded-[3rem] shadow-[0_0_100px_rgba(0,0,0,0.2)] relative overflow-hidden animate-in zoom-in duration-500 border border-white/50 flex flex-col lg:flex-row">
                     
                     <!-- Left Panel: Intel & Description -->
                     <div class="w-full lg:w-[450px] bg-slate-50/50 border-r border-slate-100 flex flex-col overflow-y-auto custom-scrollbar shrink-0">
                        <div class="p-8 space-y-8">
                             <div class="flex items-center justify-between">
                                <span class="px-4 py-1.5 bg-slate-950 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-xl shadow-slate-900/20">Signal Terminal #{{ selectedTicket.id }}</span>
                                <button @click="selectedTicket = null" class="h-10 w-10 bg-white hover:bg-rose-50 hover:text-rose-600 rounded-xl flex items-center justify-center transition-all group border border-slate-100 lg:hidden">
                                     <XMarkIcon class="h-5 w-5" />
                                </button>
                             </div>

                             <div class="space-y-4">
                                 <div>
                                    <p class="text-[10px] font-black text-emerald-600 uppercase tracking-widest italic mb-1">{{ selectedTicket.project?.name }}</p>
                                    <h2 class="text-3xl font-black text-slate-900 tracking-tighter leading-tight italic">{{ selectedTicket.subject }}</h2>
                                 </div>
                                 <div class="flex flex-wrap gap-2">
                                     <span class="px-3 py-1 bg-white border border-slate-100 rounded-lg text-[9px] font-black text-slate-500 uppercase tracking-widest flex items-center gap-2">
                                         <span class="h-1.5 w-1.5 rounded-full" :class="selectedTicket.severity === 'critical' ? 'bg-rose-500' : 'bg-amber-500'"></span>
                                         {{ selectedTicket.severity }} Priority
                                     </span>
                                     <span class="px-3 py-1 bg-white border border-slate-100 rounded-lg text-[9px] font-black text-slate-500 uppercase tracking-widest">
                                         Module: {{ selectedTicket.module?.name || 'General' }}
                                     </span>
                                 </div>
                             </div>

                             <div class="p-6 bg-white rounded-[2rem] border border-slate-100 shadow-sm space-y-4">
                                <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400 flex items-center justify-between">
                                    Issue Breakdown
                                    <div class="group/info relative">
                                        <i class="fas fa-info-circle text-slate-200"></i>
                                        <div class="absolute right-0 bottom-full mb-2 w-48 p-2 bg-slate-900 text-[8px] text-white rounded-lg opacity-0 group-hover:opacity-100 transition-opacity">Technical logs provided by the reporting terminal.</div>
                                    </div>
                                </h3>
                                <p class="text-[13px] font-medium text-slate-600 leading-relaxed whitespace-pre-wrap">{{ selectedTicket.description || 'No additional telemetry logs were broadcasted with this signal.' }}</p>
                             </div>

                             <div class="space-y-4">
                                <h3 class="text-[10px] font-black uppercase tracking-widest text-slate-400 italic">Movement Timeline</h3>
                                <div class="space-y-3 relative before:absolute before:inset-y-0 before:left-3.5 before:w-px before:bg-slate-100">
                                    <div v-for="move in selectedTicket.transitions" :key="move.id" class="relative pl-10 flex flex-col gap-1">
                                        <div class="absolute left-2.5 top-1.5 h-2 w-2 rounded-full border-2 border-white bg-slate-300"></div>
                                        <p class="text-[11px] font-black text-slate-800 leading-none">{{ move.to_stage?.name }}</p>
                                        <p class="text-[9px] font-bold text-slate-400 lowercase">{{ dayjs(move.created_at).fromNow() }}</p>
                                    </div>
                                    <div class="relative pl-10 flex flex-col gap-1">
                                        <div class="absolute left-2.5 top-1.5 h-2 w-2 rounded-full border-2 border-white bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]"></div>
                                        <p class="text-[11px] font-black text-emerald-600 leading-none">Signal Initialized</p>
                                        <p class="text-[9px] font-bold text-slate-400 lowercase">{{ dayjs(selectedTicket.created_at).fromNow() }}</p>
                                    </div>
                                </div>
                             </div>
                        </div>
                     </div>

                     <!-- Middle Panel: Communication Hub -->
                     <div class="flex-1 flex flex-col min-w-0 bg-white relative">
                        <div class="p-8 border-b border-slate-50 flex items-center justify-between shrink-0">
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-widest italic">Signal Chatter</h3>
                                <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mt-1">Direct Exchange with Engineering Hub</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span class="text-[9px] font-black text-slate-400 uppercase">Priority Channel Active</span>
                            </div>
                        </div>

                        <!-- Chat Feed -->
                        <div class="flex-1 overflow-y-auto p-8 space-y-6 custom-scrollbar flex flex-col-reverse">
                            <div v-for="comment in (selectedTicket.comments || [])" :key="comment.id" 
                                :class="['flex gap-4 max-w-[90%] animate-in fade-in slide-in-from-bottom-2 duration-300', comment.user_type.includes('ClientUser') ? 'ml-auto flex-row-reverse' : '']">
                                <div class="h-9 w-9 shrink-0 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center font-black text-[9px] text-slate-500 uppercase">
                                    {{ comment.author?.name?.charAt(0) }}
                                </div>
                                <div :class="[
                                    'p-4 rounded-2xl relative shadow-sm',
                                    comment.user_type.includes('ClientUser') ? 'bg-slate-900 text-white rounded-tr-none' : 'bg-slate-50 text-slate-700 rounded-tl-none border border-slate-100'
                                ]">
                                    <p class="text-xs font-bold leading-relaxed">{{ comment.body }}</p>
                                    <p class="text-[8px] font-black uppercase tracking-widest mt-2 opacity-40 text-right">
                                        {{ dayjs(comment.created_at).format('h:mm A') }}
                                    </p>
                                </div>
                            </div>
                            <div v-if="!(selectedTicket.comments?.length)" class="flex-1 flex flex-col items-center justify-center opacity-20 py-20">
                                <ChatBubbleLeftRightIcon class="h-12 w-12 mb-4" />
                                <p class="text-[10px] font-black uppercase tracking-widest italic">No intel signals exchanged yet.</p>
                            </div>
                        </div>

                        <!-- Chat Input -->
                        <div class="p-8 bg-white border-t border-slate-50 shrink-0">
                            <div class="flex items-center gap-3 bg-slate-100 rounded-2xl p-2 border border-slate-200 focus-within:bg-white focus-within:ring-4 focus-within:ring-emerald-500/5 transition-all group">
                                <input v-model="modalComment" @keyup.enter="postModalComment" type="text" placeholder="Broadcast a signal..." class="flex-1 bg-transparent border-none focus:ring-0 text-[11px] font-black px-4 py-3 placeholder:text-slate-300 italic uppercase tracking-widest">
                                <button @click="postModalComment" :disabled="!modalComment.trim() || modalProcessing" class="h-10 w-10 bg-slate-900 text-white rounded-xl flex items-center justify-center hover:bg-emerald-600 transition-all disabled:opacity-20 active:scale-95 shadow-lg shadow-slate-900/10">
                                    <PaperAirplaneIcon class="h-4 w-4" />
                                </button>
                            </div>
                        </div>
                     </div>

                     <!-- Right Panel: Tactical Tracker -->
                     <div class="w-full lg:w-[320px] bg-slate-900 text-white flex flex-col shrink-0 relative overflow-hidden group/right">
                        <!-- BG Decor -->
                        <div class="absolute -right-20 -top-20 h-64 w-64 bg-emerald-500/10 rounded-full blur-[80px] pointer-events-none group-hover/right:bg-emerald-500/20 transition-all duration-1000"></div>

                        <div class="p-8 border-b border-white/5 flex items-center justify-between shrink-0 relative z-10">
                            <div>
                                <h3 class="text-[10px] font-black uppercase tracking-widest italic text-slate-500">Node Configuration</h3>
                                <p class="text-xs font-black text-white mt-1">Live Progress Telemetry</p>
                            </div>
                            <button @click="selectedTicket = null" class="h-10 w-10 bg-white/5 hover:bg-white hover:text-slate-900 rounded-xl flex items-center justify-center transition-all group border border-white/5 hidden lg:flex">
                                <XMarkIcon class="h-5 w-5" />
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto p-8 space-y-10 custom-scrollbar relative z-10">
                            <div class="space-y-6">
                                <div v-for="stage in orderedStages" :key="stage.id" class="flex gap-6 relative group/stage">
                                    <!-- Connecting Line -->
                                    <div v-if="stage.stage_order < orderedStages.length" 
                                        class="absolute left-3.5 top-8 bottom-0 w-[2px]"
                                        :class="(selectedTicket.stage?.stage_order || 0) > stage.stage_order ? 'bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.5)]' : 'bg-white/10'"></div>
                                    
                                    <div class="h-8 w-8 rounded-xl border-2 flex items-center justify-center shrink-0 transition-all duration-700"
                                        :class="(selectedTicket.stage?.stage_order || 0) >= stage.stage_order ? 'bg-emerald-500 border-emerald-400 text-slate-950 scale-110 shadow-lg shadow-emerald-500/20' : 'bg-slate-950 border-white/5 text-slate-600'">
                                        <CheckIcon v-if="(selectedTicket.stage?.stage_order || 0) > stage.stage_order" class="h-4 w-4" />
                                        <div v-else class="text-[10px] font-black uppercase italic">{{ stage.stage_order }}</div>
                                    </div>
                                    <div class="flex-1 pt-1">
                                        <p class="text-[11px] font-black uppercase tracking-widest leading-none group-hover/stage:text-emerald-400 transition-colors"
                                            :class="(selectedTicket.stage?.stage_order || 0) >= stage.stage_order ? 'text-white' : 'text-slate-600'">{{ stage.name }}</p>
                                        <p class="text-[8px] font-bold text-slate-500 mt-2 uppercase tracking-tighter leading-relaxed">
                                            {{ stage.requires_verification ? 'Action: Verification Needed' : (stage.is_final ? 'Terminal Status' : 'Internal Ops Check') }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6 bg-white/5 rounded-[2rem] border border-white/5 space-y-4">
                                <h4 class="text-[9px] font-black uppercase tracking-[0.2em] text-slate-500">Current Assignment</h4>
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-xl bg-slate-800 border border-white/5 flex items-center justify-center font-black text-[10px] text-slate-400">
                                        {{ selectedTicket.assignee?.name?.[0] || 'U' }}
                                    </div>
                                    <div>
                                        <p class="text-[11px] font-black text-white italic truncate">{{ selectedTicket.assignee?.name || 'Waiting for Node' }}</p>
                                        <p class="text-[9px] font-bold text-slate-600 uppercase tracking-widest italic">Dev Engineer</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions: Action Hub -->
                        <div class="p-8 bg-slate-950/50 border-t border-white/5 shrink-0 relative z-10">
                            <div v-if="isAwaitingVerification" class="space-y-4">
                                <div class="p-5 bg-emerald-500 text-slate-950 rounded-[1.5rem] shadow-2xl shadow-emerald-500/20">
                                    <p class="text-[10px] font-black uppercase tracking-widest mb-1 italic">Action Hub</p>
                                    <p class="text-xs font-bold leading-tight">Verification signal detected. Your audit is required to finalize this resolution.</p>
                                </div>
                                <div class="flex gap-3">
                                    <button @click="verifySelectedTicket('reject')" :disabled="verificationForm.processing" class="flex-1 py-4 bg-white/5 hover:bg-rose-500 hover:text-white text-white rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all border border-white/5 active:scale-95 disabled:opacity-20">Reject Intel</button>
                                    <button @click="verifySelectedTicket('approve')" :disabled="verificationForm.processing" class="flex-1 py-4 bg-white text-slate-900 rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-emerald-400 transition-all shadow-xl active:scale-95 disabled:opacity-20 leading-none">
                                        Verify & Close
                                    </button>
                                </div>
                            </div>
                            <div v-else class="text-center">
                                <p class="text-[8px] font-black text-slate-600 uppercase tracking-[0.2em] italic mb-4">Signal under surveillance</p>
                                <button @click="selectedTicket = null" class="w-full py-4 bg-white/5 hover:bg-white/10 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all border border-white/5">Acknowledge & Sync</button>
                            </div>
                        </div>
                     </div>

                 </div>
            </div>
        </transition>

        <!-- Mobile Floating Nav -->
        <div class="lg:hidden fixed bottom-6 inset-x-6 z-[100] h-16 bg-slate-900 flex items-center justify-around px-8 rounded-3xl shadow-2xl border border-white/10 backdrop-blur-3xl bg-slate-900/90">
            <button v-for="tab in tabs" :key="tab.id"
                @click="activeTab = tab.id"
                class="flex flex-col items-center gap-1 transition-all"
                :class="activeTab === tab.id ? 'text-emerald-400' : 'text-white/40'">
                <component :is="tab.icon" class="h-6 w-6" />
                <span class="text-[7px] font-black uppercase tracking-[0.2em]">{{ tab.label.split(' ')[0] }}</span>
            </button>
            <button @click="showWizard = true" class="h-14 w-14 bg-emerald-500 rounded-2xl flex items-center justify-center text-slate-900 -mt-10 shadow-2xl shadow-emerald-500/40 border-4 border-slate-50 active:scale-90 transition-transform">
                <PlusIcon class="h-7 w-7" />
            </button>
        </div>
    </div>
</template>

<script setup>
import { router, useForm } from '@inertiajs/vue3';
import { 
    CpuChipIcon, 
    ArrowRightOnRectangleIcon, 
    PlusIcon, 
    ShieldCheckIcon,
    ChatBubbleLeftRightIcon,
    BookOpenIcon,
    XMarkIcon,
    BoltIcon,
    ArrowDownTrayIcon,
    MagnifyingGlassIcon,
    BellIcon,
    Bars3Icon,
    ChartBarIcon,
    CheckIcon,
    PaperAirplaneIcon
} from '@heroicons/vue/24/outline';
import KnowledgeVault from './Components/KnowledgeVault.vue';
import ClientProfile from './Components/ClientProfile.vue';
import DocumentVault from './Components/DocumentVault.vue';
import SupportChat from './Components/SupportChat.vue';
import TicketCreator from './TicketCreator.vue';
import Modal from '@/Components/Modal.vue';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import axios from 'axios';

dayjs.extend(relativeTime);

const props = defineProps(['client', 'projects', 'documents', 'stats', 'recent_bugs', 'kb_articles', 'notifications', 'all_stages', 'awaiting_verification']);

const activeTab = ref('dashboard');
const showWizard = ref(false);
const showVault = ref(false);
const showNotifications = ref(false);
const selectedTicket = ref(null);
const mobileMenuOpen = ref(false);
const verificationForm = useForm({ action: '' });

const modalComment = ref('');
const modalProcessing = ref(false);

const tabs = computed(() => [
    { id: 'dashboard', label: 'Dashboard Hub', icon: ChartBarIcon },
    { id: 'documents', label: 'Governance Vault', icon: ShieldCheckIcon, badge: props.documents?.filter(d => d.category === 'requirement' && !d.is_signed).length || 0 },
    { id: 'chat', label: 'Support Hub', icon: ChatBubbleLeftRightIcon },
    { id: 'profile', label: 'Corporate Profile', icon: CpuChipIcon }
]);

const greeting = computed(() => {
    const hour = dayjs().hour();
    if (hour < 12) return 'Good Morning,';
    if (hour < 17) return 'Good Afternoon,';
    return 'Good Evening,';
});

const summaryStats = computed(() => [
    { label: 'Cloud Integrity', value: (props.stats?.avg_health || 100) + '%' },
    { label: 'Compliance Index', value: (props.stats?.compliance_rate || 0) + '%' },
    { label: 'Signal Stream', value: props.stats?.active_signals || 0 }
]);

const orderedStages = computed(() => {
    return [...(props.all_stages || [])].sort((a, b) => (a.stage_order || 0) - (b.stage_order || 0));
});

const selectedTicketProgress = computed(() => {
    if (!selectedTicket.value || !orderedStages.value.length) return 0;
    const currentOrder = selectedTicket.value.stage?.stage_order || 0;
    const maxOrder = orderedStages.value[orderedStages.value.length - 1]?.stage_order || 1;
    const pct = Math.round((currentOrder / maxOrder) * 100);
    return Math.max(0, Math.min(100, pct));
});

const isAwaitingVerification = computed(() => {
    return Boolean(selectedTicket.value?.stage?.requires_verification && !selectedTicket.value?.stage?.is_final);
});

const openTicket = (ticket) => {
    selectedTicket.value = ticket;
};

const postModalComment = async () => {
    if (!modalComment.value.trim() || !selectedTicket.value) return;
    
    modalProcessing.value = true;
    try {
        const { data } = await axios.post(route('portal.tickets.comments.store', { bug: selectedTicket.value.id }), {
            body: modalComment.value,
            is_public: true
        });
        
        if (!selectedTicket.value.comments) selectedTicket.value.comments = [];
        selectedTicket.value.comments.unshift(data);
        modalComment.value = '';
    } catch (e) {
        console.error("Signal broadcast failed", e);
    } finally {
        modalProcessing.value = false;
    }
};

const verifySelectedTicket = (action) => {
    if (!selectedTicket.value) return;

    verificationForm.action = action;
    verificationForm.post(route('portal.tickets.verify', { ticket: selectedTicket.value.id }), {
        preserveScroll: true,
        onSuccess: () => {
            const updated = (props.recent_bugs || []).find((t) => t.id === selectedTicket.value?.id);
            selectedTicket.value = updated || null;
        }
    });
};

const logout = () => {
    router.post(route('portal.logout'));
};

const exportReport = () => {
    alert("Operational Signal Report (CSV) generation initiated. Secure link will be sent to your primary terminal.");
};

onMounted(() => {
    if (window.Echo && props.client?.id) {
        window.Echo.private(`client.${props.client.id}`)
            .listen('.ProjectProgressUpdated', (e) => {
                router.reload({ preserveScroll: true });
            });
    }
});

onUnmounted(() => {
    if (window.Echo && props.client?.id) {
        window.Echo.leave(`client.${props.client.id}`);
    }
});
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@700&display=swap');
.font-inter { font-family: 'Inter', sans-serif; }
.font-mono { font-family: 'JetBrains Mono', monospace; }

.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

.pop-enter-active, .pop-leave-active { transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
.pop-enter-from { opacity: 0; transform: scale(0.95) translateY(10px); }
.pop-leave-to { opacity: 0; transform: scale(0.95) translateY(10px); }

.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
.fade-slide-enter-from { opacity: 0; transform: translateY(20px); }
.fade-slide-leave-to { opacity: 0; transform: translateY(-20px); }
</style>
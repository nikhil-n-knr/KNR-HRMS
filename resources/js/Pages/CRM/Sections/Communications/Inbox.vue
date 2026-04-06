<template>
    <div class="flex flex-col h-[calc(100vh-160px)] bg-white rounded-[40px] shadow-sm border border-gray-100 overflow-hidden">
        <!-- Inbox Toolbar -->
        <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/30">
            <div class="flex items-center gap-6">
                <h2 class="text-xl font-black text-gray-900 tracking-tight">Email Hub</h2>
                <div class="h-6 w-px bg-gray-200"></div>
                <select v-model="selectedAccount" class="bg-transparent border-none text-xs font-black uppercase tracking-widest text-gray-500 focus:ring-0 cursor-pointer">
                    <option :value="null">All Accounts</option>
                    <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.email }}</option>
                </select>
            </div>
            <div class="flex items-center gap-3">
                <button @click="syncEmails" class="p-2.5 rounded-xl bg-white border border-gray-100 text-gray-400 hover:text-indigo-600 transition-all shadow-sm">
                    <i class="fas fa-sync-alt" :class="{ 'fa-spin': syncing }"></i>
                </button>
                <button @click="showLogOffline = true" class="px-5 py-2.5 bg-white border border-gray-100 text-gray-600 rounded-xl font-black text-xs uppercase tracking-widest hover:bg-gray-50 hover:shadow-sm transition-all flex items-center gap-2">
                    <i class="fas fa-file-invoice"></i> Log Offline
                </button>
                <button @click="showCompose = true" class="px-6 py-2.5 bg-indigo-600 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2">
                    <i class="fas fa-plus"></i> Compose
                </button>
            </div>
        </div>

        <div class="flex-1 flex overflow-hidden">
            <!-- Thread List -->
            <div class="w-full lg:w-96 border-r border-gray-100 overflow-y-auto bg-gray-50/20">
                <div v-if="threads.data?.length > 0">
                    <div 
                        v-for="thread in threads.data" 
                        :key="thread.id"
                        @click="selectThread(thread)"
                        :class="[
                            'p-6 border-b border-gray-50 cursor-pointer transition-all hover:bg-white',
                            selectedThread?.id === thread.id ? 'bg-white ring-1 ring-inset ring-indigo-100 shadow-sm' : ''
                        ]"
                    >
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-[10px] font-black uppercase tracking-widest text-indigo-500">
                                {{ latestMsg(thread)?.from_name || latestMsg(thread)?.from_email || 'Unknown' }}
                            </span>
                            <span class="text-[10px] font-bold text-gray-300 uppercase">
                                {{ formatTime(thread.updated_at) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 mb-1">
                            <span v-if="thread.mapping_status === 'matched'" class="px-2 py-0.5 bg-emerald-100 text-emerald-700 rounded text-[9px] font-bold uppercase tracking-wider"><i class="fas fa-check-circle mr-1"></i>Matched</span>
                            <span v-else-if="thread.mapping_status === 'pending'" class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded text-[9px] font-bold uppercase tracking-wider"><i class="fas fa-question-circle mr-1"></i>Pending</span>
                            <span v-else class="px-2 py-0.5 bg-sky-100 text-sky-700 rounded text-[9px] font-bold uppercase tracking-wider"><i class="fas fa-plus-circle mr-1"></i>New</span>
                            <h4 class="text-sm font-bold text-gray-900 truncate">{{ thread.subject }}</h4>
                        </div>
                        <p class="text-xs text-gray-400 line-clamp-2 leading-relaxed">
                            {{ getExcerpt(thread) }}
                        </p>
                        <div v-if="thread.account" class="mt-2">
                            <span class="text-[9px] font-bold text-gray-300 uppercase">{{ thread.account.email_address }}</span>
                        </div>
                    </div>
                </div>
                <div v-else class="h-full flex flex-col items-center justify-center p-12 text-center">
                    <i class="fas fa-inbox text-3xl text-gray-100 mb-4"></i>
                    <p class="text-xs font-black text-gray-300 uppercase tracking-widest mb-6">Inbox Clean</p>
                    <button @click="syncEmails" :disabled="syncing" class="px-6 py-3 bg-indigo-600 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-100 flex items-center gap-2 mx-auto disabled:opacity-50">
                        <i class="fas fa-sync-alt" :class="{'fa-spin': syncing}"></i>
                        {{ syncing ? 'Syncing...' : 'Sync Now' }}
                    </button>
                </div>
            </div>

            <!-- Message View -->
            <div class="flex-1 flex flex-col bg-white overflow-hidden relative">
                <div v-if="selectedThread" class="h-full flex flex-col">
                    <!-- Thread Header -->
                    <div class="px-8 py-6 border-b border-gray-50 flex items-center justify-between flex-wrap gap-3">
                        <div>
                            <h3 class="text-lg font-black text-gray-900 tracking-tight">{{ selectedThread.subject }}</h3>
                            <div class="flex items-center gap-4 mt-1">
                                <span class="text-[10px] font-bold text-gray-400">{{ latestMsg(selectedThread)?.from_email }}</span>
                                <span class="w-1 h-1 rounded-full bg-gray-200"></span>
                                <span v-if="selectedThread.mapping_status === 'matched'" class="text-[10px] font-black text-emerald-500 uppercase tracking-widest flex items-center gap-1"><i class="fas fa-check-circle"></i> Linked Target</span>
                                <span v-else @click="showLinkTarget = true" class="text-[10px] font-black text-amber-500 uppercase tracking-widest flex items-center gap-1 cursor-pointer hover:text-amber-600 transition-colors"><i class="fas fa-link"></i> Link to Client</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button @click="openTransferModal(selectedThread)" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 text-white text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all shadow-lg shadow-slate-200">
                                <i class="fas fa-exchange-alt text-indigo-400"></i>
                                <span>Transfer</span>
                            </button>
                            <div class="w-px h-6 bg-gray-100 mx-1"></div>
                            <button class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 hover:text-indigo-600 flex items-center justify-center border border-gray-100 shadow-sm transition-all">
                                <i class="fas fa-reply"></i>
                            </button>
                            <button class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 hover:text-rose-600 flex items-center justify-center border border-gray-100 shadow-sm transition-all">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Messages List -->
                    <div class="flex-1 overflow-y-auto p-8 space-y-8 relative">
                        <!-- Loading Overlay -->
                        <div v-if="loadingThread" class="absolute inset-0 bg-white/60 backdrop-blur-sm z-20 flex flex-col items-center justify-center space-y-4 animate-pulse">
                            <i class="fas fa-spinner fa-spin text-4xl text-indigo-600"></i>
                            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-400">Decrypting Neural History...</p>
                        </div>

                        <div class="w-full space-y-6">
                            <template v-if="selectedThread.messages && selectedThread.messages.length > 0">
                                <div v-for="(msg, index) in selectedThread.messages" :key="msg.id" class="flex flex-col bg-white border border-gray-100 shadow-sm rounded-2xl overflow-hidden mt-6">
                                    <!-- Email Header -->
                                    <div class="px-8 py-5 border-b border-gray-50 flex items-center justify-between bg-gray-50/40">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-full flex items-center justify-center font-black text-sm shadow-sm border bg-white text-indigo-500 border-gray-100 flex-shrink-0">
                                                {{ (msg.from_name || msg.from_email || '?')[0].toUpperCase() }}
                                            </div>
                                            <div>
                                                <h5 class="text-sm font-black text-gray-900">{{ msg.from_name || msg.from_email }}</h5>
                                                <p class="text-xs text-gray-400 font-medium">to: {{ msg.to_emails ? msg.to_emails.join(', ') : 'me' }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-4 text-right">
                                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ formatDate(msg.sent_at || msg.created_at) }}</span>
                                            <span class="text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded shadow-sm border"
                                                  :class="msg.direction === 'outbound' ? 'bg-indigo-50 border-indigo-100 text-indigo-600' : 'bg-emerald-50 border-emerald-100 text-emerald-600'">
                                                {{ msg.direction === 'outbound' ? 'Sent' : 'Received' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Email Body -->
                                    <div class="p-0">
                                        <!-- HTML Emails (in isolated iframe) -->
                                        <div v-if="msg.body_html" class="w-full bg-white text-left overflow-x-auto relative">
                                            <iframe
                                                class="w-full bg-white border-none block"
                                                style="min-height: 400px;"
                                                :srcdoc="prepareHtml(msg.body_html)"
                                                sandbox="allow-popups allow-popups-to-escape-sandbox allow-same-origin"
                                                @load="resizeIframe"
                                            ></iframe>
                                        </div>
                                        
                                        <!-- Text Emails -->
                                        <div v-else-if="msg.body_text" 
                                             class="p-8 text-[14px] font-medium leading-relaxed text-gray-800 text-left whitespace-pre-wrap break-words"
                                             v-html="formatRawText(msg.body_text)">
                                        </div>
                                        
                                        <div v-else class="p-8 text-sm text-gray-400 italic text-left">
                                            (No viewable data available for this email)
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <div v-else-if="!loadingThread" class="h-64 flex flex-col items-center justify-center text-center space-y-4 opacity-50">
                                <i class="fas fa-envelope-open text-4xl text-gray-200"></i>
                                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest leading-relaxed">Empty Thread.<br>No communications recorded.</p>
                            </div>
                        </div>
                    </div>


                    <!-- Quick Reply Bar -->
                    <div class="p-8 border-t border-gray-50 flex flex-col gap-4 bg-gray-50/30">
                        <div class="flex items-center gap-3">
                            <button @click="showTemplatePicker = true" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-600 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm flex items-center gap-2"><i class="fas fa-file-alt"></i> Insert Template</button>
                            <button @click="showMeetingPlanner = true" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-600 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm flex items-center gap-2"><i class="fas fa-calendar-alt"></i> Schedule Meeting</button>
                            <button @click="showActivityLogger = true" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-xs font-bold text-gray-600 hover:text-indigo-600 hover:border-indigo-200 transition-all shadow-sm flex items-center gap-2"><i class="fas fa-sticky-note"></i> Log Note/Task</button>
                        </div>
                        <div class="flex gap-4">
                            <textarea 
                                v-model="quickReplyBody"
                                placeholder="Type a quick response..." 
                                class="flex-1 bg-white border border-gray-100 rounded-2xl p-4 text-sm font-medium shadow-inner focus:ring-4 focus:ring-indigo-500/10 placeholder-gray-300 min-h-[60px]"
                            ></textarea>
                            <button @click="submitReply" :disabled="replying || !quickReplyBody" class="w-14 h-14 bg-indigo-600 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition-all self-end disabled:opacity-50">
                                <i v-if="replying" class="fas fa-spinner fa-spin"></i>
                                <i v-else class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-else class="h-full flex flex-col items-center justify-center text-center p-12">
                    <div class="w-32 h-32 bg-gray-50 rounded-full flex items-center justify-center mb-8 border border-gray-100">
                        <i class="fas fa-paper-plane text-4xl text-gray-100"></i>
                    </div>
                    <h3 class="text-xl font-black text-gray-900 tracking-tight mb-2">No conversation selected</h3>
                    <p class="text-sm text-gray-400 font-bold uppercase tracking-widest">Select a thread from the left to begin engagement.</p>
                </div>
            </div>
        </div>

        <!-- Compose Modal placeholder -->
        <div v-if="showCompose" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-[40px] shadow-2xl max-w-2xl w-full overflow-hidden border border-white">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight">New Message</h3>
                    <button @click="showCompose = false" class="text-gray-400 hover:text-gray-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-8 space-y-6">
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            <input v-model="composeForm.to" type="text" placeholder="Recipient" class="flex-1 bg-gray-50 border-none rounded-xl p-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all shadow-inner">
                            <button @click="showComposeCc = !showComposeCc" class="text-[10px] font-black uppercase tracking-widest text-indigo-500 hover:text-indigo-700 transition-colors">CC</button>
                            <button @click="showComposeBcc = !showComposeBcc" class="text-[10px] font-black uppercase tracking-widest text-indigo-500 hover:text-indigo-700 transition-colors">BCC</button>
                        </div>
                        <input v-if="showComposeCc" v-model="composeForm.cc" type="text" placeholder="CC Recipients (comma separated)" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all shadow-inner">
                        <input v-if="showComposeBcc" v-model="composeForm.bcc" type="text" placeholder="BCC Recipients (comma separated)" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all shadow-inner">
                        
                        <input v-model="composeForm.subject" type="text" placeholder="Subject Line" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 transition-all shadow-inner">
                        <textarea v-model="composeForm.body" placeholder="Compose your message here..." class="w-full bg-gray-50 border-none rounded-2xl p-6 text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 transition-all shadow-inner h-64"></textarea>
                    </div>
                    <div class="flex justify-between items-center pt-4">
                        <div class="flex gap-2 text-gray-400">
                            <button class="w-10 h-10 hover:text-indigo-600 transition-colors"><i class="fas fa-paperclip"></i></button>
                            <button @click="saveDraft" class="px-6 py-4 bg-gray-100 text-gray-600 rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-gray-200 transition-all ml-2">
                                Draft
                            </button>
                        </div>
                        <button @click="submitCompose" :disabled="composing" class="px-10 py-4 bg-indigo-600 text-white rounded-2xl font-black text-sm uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100 flex items-center gap-2">
                            <i v-if="composing" class="fas fa-spinner fa-spin"></i>
                            <i v-else class="fas fa-bolt"></i> 
                            {{ composing ? 'Sending...' : 'Send Now' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Log Offline Modal -->
        <div v-if="showLogOffline" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-[40px] shadow-2xl max-w-2xl w-full overflow-hidden border border-white">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight">Log External Communication</h3>
                    <button @click="showLogOffline = false" class="text-gray-400 hover:text-gray-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-8 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <select v-model="logForm.direction" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/10">
                            <option value="outbound">Outbound (Sent)</option>
                            <option value="inbound">Inbound (Received)</option>
                        </select>
                        <select v-model="logForm.source" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/10">
                            <option value="direct">Direct Email</option>
                            <option value="phone">Phone Call Followup</option>
                            <option value="offline">In-Person Meeting</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <input v-model="logForm.from" type="text" placeholder="From Email" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
                    <input v-model="logForm.subject" type="text" placeholder="Subject Line" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
                    <textarea v-model="logForm.body" placeholder="Summary of communication..." class="w-full bg-gray-50 border-none rounded-2xl p-6 text-sm font-medium focus:ring-4 focus:ring-indigo-500/10 shadow-inner h-40"></textarea>
                    
                    <div class="flex justify-end gap-3 pt-4">
                        <button @click="showLogOffline = false" class="px-6 py-3 rounded-xl text-xs font-black uppercase tracking-widest text-gray-500 hover:bg-gray-50">Cancel</button>
                        <button @click="submitLog" class="px-8 py-3 bg-emerald-500 text-white rounded-xl font-black text-xs uppercase tracking-widest hover:bg-emerald-600 shadow-lg shadow-emerald-100">
                            Save Log
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Link Target Modal -->
        <div v-if="showLinkTarget" class="fixed inset-0 z-[110] bg-slate-900/80 backdrop-blur-md flex items-center justify-center p-4">
            <div class="bg-white rounded-[40px] shadow-2xl max-w-md w-full overflow-hidden border border-white">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight">Manual Contact Link</h3>
                    <button @click="showLinkTarget = false" class="text-gray-400 hover:text-gray-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-8 space-y-6">
                    <div>
                        <p class="text-sm font-bold text-gray-500 mb-4">Search your CRM for a matching Client, Lead, or Deal to associate this email thread.</p>
                        <div class="relative">
                            <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                            <input v-model="linkTargetQuery" type="text" placeholder="Search by name, email, or company..." class="w-full bg-gray-50 border-none rounded-xl py-4 pl-12 pr-4 text-sm font-bold focus:ring-4 focus:ring-indigo-500/10 shadow-inner">
                        </div>
                    </div>
                    
                    <!-- Search Results Placeholder -->
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        <div v-if="linkTargetQuery.length > 2" class="p-4 border-2 border-indigo-600 bg-indigo-50/50 rounded-2xl flex items-center justify-between cursor-pointer">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center text-indigo-600 font-black text-[10px] border border-gray-100">
                                    {{ linkTargetQuery[0].toUpperCase() }}
                                </div>
                                <div>
                                    <span class="font-bold text-gray-900 text-sm block">{{ linkTargetQuery }} (Mock Match)</span>
                                    <span class="text-[10px] text-gray-400 font-semibold uppercase">Contact</span>
                                </div>
                            </div>
                            <button @click="showLinkTarget = false" class="text-[10px] font-black uppercase text-indigo-600 bg-white px-3 py-1.5 rounded-lg shadow-sm">Link</button>
                        </div>
                        <div v-if="linkTargetQuery.length > 2" class="text-center py-4">
                            <button class="text-[10px] font-black uppercase tracking-widest text-indigo-500 hover:text-indigo-700"><i class="fas fa-plus mr-1"></i>Create New Contact</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transfer Modal -->
        <div v-show="showTransfer" class="fixed inset-0 z-[150] bg-slate-900/60 backdrop-blur-md flex items-center justify-center p-4">
            <div class="bg-white rounded-[40px] shadow-2xl max-w-lg w-full overflow-hidden border border-white">
                <div class="p-8 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 relative">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight text-center w-full">Stewardship Transfer</h3>
                    <button @click="showTransfer = false" class="absolute right-8 w-10 h-10 rounded-full bg-white border border-gray-100 flex items-center justify-center text-gray-400 hover:text-rose-500 hover:shadow-lg transition-all shadow-sm">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-10 space-y-6">
                    <div class="flex flex-col items-center text-center gap-2 mb-4">
                        <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-100/50 mb-2">
                            <i class="fas fa-people-arrows text-indigo-600 text-2xl"></i>
                        </div>
                        <h4 class="font-black text-gray-900 text-lg">Shift Engagement Ownership</h4>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-widest">Select the new custodian for this communication flow</p>
                    </div>

                    <!-- User Selection (Scrollable) -->
                    <div class="space-y-2 max-h-[280px] overflow-y-auto pr-2 custom-scrollbar">
                        <div v-for="user in users" :key="user.id" 
                            @click="transferUserId = user.id"
                            class="flex items-center justify-between p-5 rounded-3xl border-2 transition-all cursor-pointer group"
                            :class="transferUserId === user.id ? 'border-indigo-600 bg-indigo-50/50 shadow-xl shadow-indigo-500/10' : 'border-gray-50 bg-gray-50/30 hover:border-indigo-200 hover:bg-white'"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-indigo-600 font-black text-xs border border-gray-100 group-hover:scale-110 transition-transform shadow-sm">
                                    {{ (user.name || '?')[0].toUpperCase() }}
                                </div>
                                <span class="font-black text-gray-900 text-sm tracking-tight">{{ user.name }}</span>
                            </div>
                            <div v-if="transferUserId === user.id" class="w-6 h-6 bg-indigo-600 rounded-full flex items-center justify-center shadow-lg">
                                <i class="fas fa-check text-white text-[10px]"></i>
                            </div>
                        </div>
                        <div v-if="!users || users.length === 0" class="text-center py-10 opacity-40 italic text-xs">No active personnel available.</div>
                    </div>

                    <!-- Transfer Options -->
                    <div class="space-y-4 bg-gray-50/50 rounded-3xl p-8 border border-gray-100">
                        <label class="flex items-center gap-4 cursor-pointer group">
                            <input v-model="transferOptions.allHistory" type="checkbox" class="w-6 h-6 rounded-lg border-gray-200 text-indigo-600 focus:ring-indigo-500/20 transition-all cursor-pointer">
                            <div class="flex flex-col">
                                <span class="text-xs font-black text-gray-900 group-hover:text-indigo-600 transition-colors uppercase tracking-widest">Transfer Entire Client History</span>
                                <span class="text-[10px] text-gray-400 font-bold">Relocate all threads & contacts for this client</span>
                            </div>
                        </label>
                        <div class="h-px bg-gray-100"></div>
                        <label class="flex items-center gap-4 cursor-pointer group">
                            <input v-model="transferOptions.activities" type="checkbox" class="w-6 h-6 rounded-lg border-gray-200 text-indigo-600 focus:ring-indigo-500/20 transition-all cursor-pointer">
                            <span class="text-xs font-bold text-gray-500 group-hover:text-gray-900 transition-colors">Transfer open tasks & meetings</span>
                        </label>
                        <label class="flex items-center gap-4 cursor-pointer group">
                            <input v-model="transferOptions.keepHistory" type="checkbox" class="w-6 h-6 rounded-lg border-gray-200 text-indigo-600 focus:ring-indigo-500/20 transition-all cursor-pointer">
                            <span class="text-xs font-bold text-gray-500 group-hover:text-gray-900 transition-colors">Keep monitoring access (Read-only)</span>
                        </label>
                    </div>

                    <button 
                        @click="submitTransfer" 
                        :disabled="!transferUserId"
                        class="w-full py-5 bg-indigo-600 text-white rounded-[20px] font-black text-xs uppercase tracking-[0.2em] hover:bg-indigo-700 transition-all shadow-2xl shadow-indigo-200 disabled:opacity-30 disabled:shadow-none active:scale-95"
                    >
                        Execute Transfer Protocol
                    </button>
                    <button @click="showTransfer = false" class="w-full py-3 text-[10px] font-black text-gray-400 uppercase tracking-widest hover:text-gray-600 transition-colors">Abort Procedure</button>
                </div>
            </div>
        </div>

        <!-- Template Picker Modal -->
        <div v-if="showTemplatePicker" class="fixed inset-0 z-[120] bg-gray-900/40 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-[40px] shadow-2xl max-w-md w-full overflow-hidden border border-white">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight">Neural Templates</h3>
                    <button @click="showTemplatePicker = false" class="text-gray-400 hover:text-gray-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-8 space-y-3">
                    <div v-for="tpl in commonTemplates" :key="tpl.name" 
                        @click="applyTemplate(tpl)"
                        class="p-5 rounded-2xl border border-gray-50 bg-gray-50/30 hover:bg-indigo-50 hover:border-indigo-100 transition-all cursor-pointer group"
                    >
                        <h4 class="font-black text-xs uppercase tracking-widest text-gray-900 group-hover:text-indigo-600 mb-1">{{ tpl.name }}</h4>
                        <p class="text-[11px] text-gray-400 line-clamp-1 leading-relaxed">{{ tpl.body }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Meeting Planner Modal -->
        <div v-if="showMeetingPlanner" class="fixed inset-0 z-[120] bg-gray-900/40 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-[40px] shadow-2xl max-w-md w-full overflow-hidden border border-white">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight">Schedule Followup</h3>
                    <button @click="showMeetingPlanner = false" class="text-gray-400 hover:text-gray-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-8 space-y-5">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Engagement Title</label>
                        <input v-model="meetingForm.title" type="text" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold shadow-inner">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Launch Date</label>
                            <input v-model="meetingForm.start_time" type="datetime-local" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold shadow-inner">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Duration (Min)</label>
                            <select v-model="meetingForm.duration" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold shadow-inner">
                                <option :value="15">15 Minutes</option>
                                <option :value="30">30 Minutes</option>
                                <option :value="60">1 Hour</option>
                                <option :value="120">2 Hours</option>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Coordination Channel</label>
                        <input v-model="meetingForm.location" type="text" placeholder="e.g. Google Meet, HQ Boardroom" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold shadow-inner">
                    </div>
                    <button @click="submitMeeting" class="w-full py-5 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all">Establish Event</button>
                </div>
            </div>
        </div>

        <!-- Activity Logger Modal -->
        <div v-if="showActivityLogger" class="fixed inset-0 z-[120] bg-gray-900/40 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-[40px] shadow-2xl max-w-md w-full overflow-hidden border border-white">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight">Timeline Log</h3>
                    <button @click="showActivityLogger = false" class="text-gray-400 hover:text-gray-900">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-8 space-y-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Entry Type</label>
                            <select v-model="activityForm.type" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold shadow-inner">
                                <option value="note">Internal Note</option>
                                <option value="task">Actionable Task</option>
                            </select>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Priority</label>
                            <select v-model="activityForm.priority" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold shadow-inner">
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">Critical</option>
                            </select>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Headline</label>
                        <input v-model="activityForm.subject" type="text" placeholder="Summary of the log..." class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold shadow-inner">
                    </div>
                    <div v-if="activityForm.type === 'task'" class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Deadline</label>
                        <input v-model="activityForm.due_date" type="date" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold shadow-inner">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest ml-1">Narrative Details</label>
                        <textarea v-model="activityForm.description" class="w-full bg-gray-50 border-none rounded-2xl p-5 text-sm font-medium shadow-inner h-32" placeholder="Contextual details for the team..."></textarea>
                    </div>
                    <button @click="submitActivity" class="w-full py-5 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all">Append to Timeline</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { formatDistanceToNow, format } from 'date-fns';

const props = defineProps({
    threads: { type: Object, default: () => ({ data: [] }) },
    accounts: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
    compose: { type: Boolean, default: false },
    section: String,
    tab: String,
    selected_account_id: [Number, String]
});

const selectedAccount = ref(props.selected_account_id || null);

// Account Filter Watcher
import { watch } from 'vue';
watch(selectedAccount, (newVal) => {
    router.get(route('crm.comms.hub'), { 
        section: props.section, 
        tab: props.tab, 
        account_id: newVal 
    }, { preserveScroll: true, preserveState: true });
});
const selectedThread = ref(null);
const showCompose = ref(props.compose);
const showLogOffline = ref(false);
const showTransfer = ref(false);
const showLinkTarget = ref(false);
const linkTargetQuery = ref('');
const transferUserId = ref(null);
const transferOptions = ref({
    activities: true,
    notifyClient: false,
    keepHistory: true,
    allHistory: false
});
const showMeetingPlanner = ref(false);
const showActivityLogger = ref(false);
const showTemplatePicker = ref(false);

const meetingForm = ref({
    title: 'Follow-up Call',
    start_time: '',
    duration: 30, // minutes
    location: 'Google Meet',
    notes: ''
});

const activityForm = ref({
    type: 'note',
    subject: '',
    description: '',
    priority: 'medium',
    due_date: format(new Date(), 'yyyy-MM-dd')
});

const commonTemplates = [
    { name: 'Introduction', body: "Hi there,\n\nI'm reaching out from [Company Name] regarding our recent discussion. I'd love to connect and explore how we can help you achieve your goals.\n\nBest regards,\n[My Name]" },
    { name: 'Follow-up', body: "Hi,\n\nJust wanted to follow up on my previous message. Let me know if you've had a chance to review the details I sent over.\n\nLooking forward to hearing from you." },
    { name: 'Meeting Request', body: "Hello,\n\nI'd like to schedule a 15-minute call to discuss your requirements in more detail. Does anytime next Tuesday or Wednesday work for you?\n\nWarmly,\n[My Name]" },
    { name: 'Issue Resolved', body: "Hi,\n\nHappy to inform you that the issue you reported has been successfully resolved. Please let us know if you encounter any other difficulties.\n\nBest,\nSupport Team" }
];

const applyTemplate = (tpl) => {
    quickReplyBody.value += (quickReplyBody.value ? '\n\n' : '') + tpl.body;
    showTemplatePicker.value = false;
};

const submitMeeting = () => {
    if (!meetingForm.value.start_time) return;
    
    // Calculate end_time based on duration
    const start = new Date(meetingForm.value.start_time);
    const end = new Date(start.getTime() + meetingForm.value.duration * 60000);
    
    router.post(route('crm.meetings.store'), {
        ...meetingForm.value,
        end_time: format(end, 'yyyy-MM-dd HH:mm:ss'),
        start_time: format(start, 'yyyy-MM-dd HH:mm:ss'),
        trackable_type: 'EmailThread',
        trackable_id: selectedThread.value.id,
        client_id: selectedThread.value.client_id || null, // If linked
    }, {
        onSuccess: () => {
            showMeetingPlanner.value = false;
            // Optionally add a note to the thread
        }
    });
};

const submitActivity = () => {
    if (!activityForm.value.subject) return;

    router.post(route('crm.activities.store'), {
        ...activityForm.value,
        activityable_type: 'App\\Models\\CRM\\EmailThread',
        activityable_id: selectedThread.value.id
    }, {
        onSuccess: () => {
            showActivityLogger.value = false;
        }
    });
};

const syncing = ref(false);

const openTransferModal = (thread) => {
    selectedThread.value = thread;
    transferUserId.value = null; // Reset selected user on modal open
    showTransfer.value = true;
};

const submitTransfer = () => {
    if (!selectedThread.value || !transferUserId.value) return;
    router.post(route('crm.comms.hub.transfer'), {
        type: 'thread',
        id: selectedThread.value.id,
        user_id: transferUserId.value,
        options: transferOptions.value
    }, {
        onSuccess: () => {
            showTransfer.value = false;
            selectedThread.value = null;
        }
    });
};

const logForm = ref({
    to: [],
    from: '',
    subject: '',
    body: '',
    direction: 'outbound',
    sent_at: format(new Date(), 'yyyy-MM-dd HH:mm:ss'),
    source: 'offline',
    trackable_type: 'Lead',
    trackable_id: null
});

const submitLog = () => {
    router.post(route('crm.emails.store-manual'), logForm.value, {
        onSuccess: () => {
            showLogOffline.value = false;
            // reset form if needed
        }
    });
};

const selectThread = async (thread) => {
    selectedThread.value = thread;
    loadingThread.value = true;
    try {
        const response = await fetch(route('crm.comms.hub.thread', { thread: thread.id }));
        if (response.ok) {
            const fullThread = await response.json();
            selectedThread.value = { ...fullThread };
        }
    } catch (e) {
        console.error("Failed to fetch thread:", e);
    } finally {
        loadingThread.value = false;
    }
};

const quickReplyBody = ref('');
const replying = ref(false);
const loadingThread = ref(false);

const showComposeCc = ref(false);
const showComposeBcc = ref(false);
const composing = ref(false);

const composeForm = ref({
    to: '',
    cc: '',
    bcc: '',
    subject: '',
    body: ''
});

const submitCompose = () => {
    if (!composeForm.value.to || !composeForm.value.body) return;
    composing.value = true;
    router.post(route('crm.comms.hub.reply'), {
        is_new: true,
        to: composeForm.value.to,
        cc: composeForm.value.cc,
        bcc: composeForm.value.bcc,
        subject: composeForm.value.subject,
        body: composeForm.value.body
    }, {
        onSuccess: () => {
            composing.value = false;
            showCompose.value = false;
            composeForm.value = { to: '', cc: '', bcc: '', subject: '', body: '' };
        },
        onError: () => composing.value = false
    });
};

const saveDraft = () => {
    router.post(route('crm.comms.hub.reply'), {
        is_draft: true,
        to: composeForm.value.to,
        cc: composeForm.value.cc,
        bcc: composeForm.value.bcc,
        subject: composeForm.value.subject,
        body: composeForm.value.body
    }, {
        onSuccess: () => {
            showCompose.value = false;
        }
    });
};

const submitReply = () => {
    if (!selectedThread.value || !quickReplyBody.value) return;
    replying.value = true;
    router.post(route('crm.comms.hub.reply'), {
        thread_id: selectedThread.value.id,
        body: quickReplyBody.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            quickReplyBody.value = '';
            replying.value = false;
        },
        onError: () => replying.value = false
    });
};


const syncEmails = () => {
    syncing.value = true;
    router.post(route('crm.comms.hub.sync'), {
        account_id: selectedAccount.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
             syncing.value = false;
        },
        onError: () => syncing.value = false
    });
};

const formatTime = (date) => {
    if (!date) return '';
    return format(new Date(date), 'HH:mm');
};

const formatDate = (date) => {
    if (!date) return '';
    return formatDistanceToNow(new Date(date), { addSuffix: true });
};

// Get latest message from thread — handles both 'messages' array and 'latest_message' key
const latestMsg = (thread) => {
    if (thread.latest_message) return thread.latest_message;
    if (thread.messages && thread.messages.length > 0) return thread.messages[0];
    return null;
};

// Get readable excerpt from latest message
const getExcerpt = (thread) => {
    const msg = latestMsg(thread);
    if (!msg) return 'No content preview...';
    if (msg.excerpt) return msg.excerpt;
    if (msg.body_text) return msg.body_text.substring(0, 120);
    if (msg.body_html) return msg.body_html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim().substring(0, 120);
    return 'No content preview...';
};

const formatRawText = (text) => {
    if (!text) return '<p class="text-gray-400"><i>(No content preview available for this sector)</i></p>';
    // Convert URLs to clickable links
    const urlRegex = /(https?:\/\/[^\s]+)/g;
    return `<span>${text.replace(urlRegex, '<a href="$1" target="_blank" class="underline opacity-80 hover:opacity-100">$1</a>')}</span>`;
};

const prepareHtml = (html) => {
    if (!html) return '';
    let processed = html;

    // Sanitize: Strip scripts and event handlers to avoid sandbox errors
    processed = processed
        .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, '')
        .replace(/\son\w+="[^"]*"/gi, '')
        .replace(/\son\w+='[^']*'/gi, '');
    
    // Inject <base target="_blank"> so all links open in a new tab
    if (processed.includes('<head>')) {
        processed = processed.replace('<head>', '<head><base target="_blank">');
    } else {
        processed = '<base target="_blank">' + processed;
    }
    
    // Inject basic styling defaults for the iframe to look clean
    const styleInject = `<style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; 
            margin: 0; 
            padding: 24px; 
            word-wrap: break-word; 
            color: #1f2937;
            background-color: #ffffff;
            line-height: 1.5;
        }
        img { max-width: 100%; height: auto; border-radius: 8px; }
        a { color: #4f46e5; text-decoration: none; }
        a:hover { text-decoration: underline; }
        table { max-width: 100% !important; border-collapse: collapse; }
    </style>`;
    
    if (processed.includes('</head>')) {
        processed = processed.replace('</head>', styleInject + '</head>');
    } else {
        processed = styleInject + processed;
    }
    return processed;
};

const resizeIframe = (event) => {
    try {
        const iframe = event.target;
        if (iframe.contentWindow && iframe.contentWindow.document) {
            // First collapse height to get true measurement if it shrank
            iframe.style.height = '10px';
            setTimeout(() => {
                const height = iframe.contentWindow.document.documentElement.scrollHeight || iframe.contentWindow.document.body.scrollHeight;
                iframe.style.height = Math.max(height + 20, 200) + 'px';
            }, 50);
        }
    } catch (e) {
        console.error('Resize iframe error', e);
        event.target.style.height = '800px';
    }
};
</script>


<style scoped>
.email-content-container :deep(img) {
    max-width: 100% !important;
    height: auto !important;
    border-radius: 12px;
}
.email-content-container :deep(table) {
    max-width: 100% !important;
    overflow-x: auto;
    display: block;
}
.email-content-container :deep(a) {
    color: #4f46e5;
    text-decoration: underline;
}
</style>


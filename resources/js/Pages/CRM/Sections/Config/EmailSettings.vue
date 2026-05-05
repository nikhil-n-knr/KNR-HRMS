<template>
    <div class="flex flex-col h-[calc(100vh-120px)] overflow-hidden bg-gray-50/50 relative font-sans text-left">
        <!-- Floating Info Button -->
        <div class="absolute top-4 right-8 z-50 group no-print">
            <button class="w-8 h-8 bg-white border border-gray-200 rounded-full flex items-center justify-center text-gray-400 hover:text-indigo-500 hover:border-indigo-200 shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                <i class="fas fa-info text-xs"></i>
            </button>
            <div class="absolute right-0 top-10 w-80 bg-gray-900 text-white text-sm p-6 rounded-[32px] shadow-2xl opacity-0 group-hover:opacity-100 transition-all pointer-events-none z-50 transform origin-top-right scale-95 group-hover:scale-100 border border-white/10 font-medium font-sans">
                <div class="font-black tracking-[0.2em] uppercase mb-3 text-indigo-400 border-b border-indigo-500/20 pb-3 text-left">Communication Governance</div>
                <p class="text-gray-300 leading-relaxed mb-4 text-left font-sans">The Communication Node manages the transmission protocols for your global CRM outreach. Configure SMTP gateways and real-time notification pulses to ensure stakeholder resonance.</p>
                <div class="space-y-2 text-left">
                    <div class="flex items-center gap-3 text-left font-sans"><div class="w-2 h-2 rounded-full bg-indigo-500 shadow-lg shadow-indigo-500/50"></div> <span>Secure SMTP Transmission</span></div>
                    <div class="flex items-center gap-3 text-left font-sans"><div class="w-2 h-2 rounded-full bg-emerald-500 shadow-lg shadow-emerald-500/50"></div> <span>Real-Time Intelligence Pulse</span></div>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Global Header -->
            <div class="px-8 py-8 border-b border-gray-100 bg-white relative z-40 no-print text-left font-sans">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 text-left font-sans">
                    <div class="text-left font-sans">
                        <h2 class="text-3xl font-black text-gray-900 tracking-tight text-left font-sans">Gateway Governance</h2>
                        <div class="flex items-center mt-3 text-left font-sans">
                            <i class="fas fa-satellite-dish text-indigo-500 mr-3 text-left font-sans"></i>
                            <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] text-left font-sans text-left font-sans">Communication Node: Synchronized</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 text-left font-sans">
                         <button @click="saveSettings" class="px-8 py-3.5 bg-indigo-600 text-white rounded-2xl hover:bg-indigo-700 transition-all font-black text-sm tracking-widest uppercase shadow-xl shadow-indigo-100 flex items-center group active:scale-95 text-left font-sans text-left font-sans">
                            <i class="fas fa-save mr-2 text-xs text-left font-sans text-left font-sans"></i>
                            Commit Node Configuration
                        </button>
                        <button @click="printView" class="w-12 h-12 bg-white border border-gray-200 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-gray-50 hover:text-indigo-600 hover:border-indigo-100 transition-all shadow-sm group relative text-left font-sans">
                            <i class="fas fa-print text-sm text-left font-sans"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Settings Surface -->
            <div class="flex-1 overflow-auto p-8 text-left font-sans" id="print-area">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 text-left font-sans text-left font-sans">
                    <!-- Gateway Config -->
                    <div class="bg-white p-12 rounded-[50px] shadow-sm border border-gray-100 space-y-10 text-left font-sans text-left font-sans border-l-8 border-l-indigo-600">
                        <div class="flex items-center gap-6 text-left font-sans text-left font-sans">
                            <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-3xl flex items-center justify-center shadow-inner text-left font-sans text-left font-sans">
                                <i class="fas fa-server text-xl text-left font-sans text-left font-sans"></i>
                            </div>
                            <div class="text-left font-sans text-left font-sans">
                                <h4 class="text-2xl font-black text-gray-900 tracking-tight text-left font-sans text-left font-sans">Transmission Protocol</h4>
                                <p class="text-sm font-black text-gray-400 uppercase tracking-[0.2em] leading-none mt-2 text-left font-sans text-left font-sans">Master SMTP Gateway Configuration</p>
                            </div>
                        </div>

                        <div class="space-y-8 text-left font-sans text-left font-sans">
                            <div class="space-y-3 text-left font-sans text-left font-sans">
                                 <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Transmission Architecture (Host)</label>
                                 <input v-model="form.smtp_host" type="text" class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4.5 px-6 text-sm font-semibold shadow-inner focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-left font-sans text-left font-sans" placeholder="smtp.global-node.io">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-8 text-left font-sans text-left font-sans">
                                <div class="space-y-3 text-left font-sans text-left font-sans">
                                     <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Port Identity</label>
                                     <input v-model="form.smtp_port" type="text" class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4.5 px-6 text-sm font-black shadow-inner focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-left font-sans text-left font-sans" placeholder="587">
                                </div>
                                <div class="space-y-3 text-left font-sans text-left font-sans">
                                     <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Encryption Cipher</label>
                                     <select v-model="form.smtp_encryption" class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4.5 px-6 text-sm font-black shadow-inner focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-left font-sans text-left font-sans appearance-none cursor-pointer">
                                        <option value="tls">SECURE TLS PROTOCOL</option>
                                        <option value="ssl">LEGACY SSL PROTOCOL</option>
                                     </select>
                                </div>
                            </div>

                            <div class="space-y-3 text-left font-sans text-left font-sans">
                                 <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Master Identity (Sender Email)</label>
                                 <input v-model="form.sender_email" type="email" class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4.5 px-6 text-sm font-semibold shadow-inner focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-left font-sans text-left font-sans text-left font-sans" placeholder="notifications@enterprise-hub.com">
                            </div>

                            <div class="space-y-3 text-left font-sans text-left font-sans text-left font-sans">
                                 <label class="block text-base font-black text-gray-400 uppercase tracking-widest ml-1 text-left font-sans">Visible Entity (Sender Name)</label>
                                 <input v-model="form.sender_name" type="text" class="w-full bg-gray-50 border-gray-100 rounded-2xl py-4.5 px-6 text-sm font-semibold shadow-inner focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition-all text-left font-sans text-left font-sans text-left font-sans" placeholder="OPSCORE Intelligence Hub">
                            </div>
                        </div>

                        <div class="p-6 bg-indigo-50 rounded-[30px] border border-indigo-100 text-left font-sans text-left font-sans text-left font-sans">
                             <div class="flex items-center gap-4 text-left font-sans text-left font-sans font-sans">
                                 <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse text-left font-sans"></div>
                                 <p class="text-sm font-black text-indigo-900 uppercase tracking-widest text-left font-sans">Gateway Status: Operational Verification Required</p>
                             </div>
                        </div>
                    </div>

                    <!-- Pulse Config -->
                    <div class="space-y-10 text-left font-sans text-left font-sans text-left font-sans">
                         <div class="bg-gray-900 p-12 rounded-[50px] text-white shadow-2xl relative overflow-hidden group text-left font-sans text-left font-sans">
                            <i class="fas fa-satellite-dish absolute -right-4 -top-4 text-[150px] text-white/5 group-hover:text-indigo-500/20 transition-all duration-700 text-left font-sans text-left font-sans"></i>
                            <div class="relative z-10 text-left font-sans">
                                <h4 class="text-2xl font-black mb-2 tracking-tight text-left font-sans">Intelligence Pulse Notifications</h4>
                                <p class="text-sm text-indigo-400 font-bold uppercase tracking-[0.2em] mb-10 text-left font-sans text-left font-sans">Real-Time Event Broadcast Triggers</p>
                                
                                <div class="space-y-5 text-left font-sans">
                                     <div v-for="(val, key) in notifications" :key="key" class="flex justify-between items-center p-6 bg-white/5 rounded-3xl border border-white/10 hover:bg-white/10 transition-all text-left font-sans">
                                        <div class="text-left font-sans text-left font-sans">
                                            <span class="text-sm font-black uppercase tracking-widest text-white block text-left font-sans">{{ key.replace(/_/g, ' ') }}</span>
                                            <p class="text-xs font-bold text-gray-500 uppercase mt-1 text-left font-sans">Strategic Broadcast</p>
                                        </div>
                                        <button @click="notifications[key] = !notifications[key]" 
                                                :class="['w-14 h-7 rounded-full transition-all relative p-1 active:scale-95 text-left font-sans', notifications[key] ? 'bg-indigo-500 shadow-lg shadow-indigo-500/50' : 'bg-white/10']">
                                            <div :class="['w-5 h-5 bg-white rounded-full shadow-sm transition-all', notifications[key] ? 'translate-x-[28px]' : 'translate-x-0']"></div>
                                        </button>
                                     </div>
                                </div>
                            </div>
                         </div>

                         <div class="bg-white p-12 rounded-[50px] border border-gray-100 shadow-sm text-left font-sans text-left font-sans border-r-8 border-r-indigo-100">
                            <h4 class="text-sm font-black text-gray-900 mb-6 tracking-tight uppercase tracking-[0.2em] text-left font-sans">Diagnostic Protocol</h4>
                            <p class="text-xs text-gray-500 mb-8 italic font-medium leading-relaxed text-left font-sans">Transmit a zero-payload sync packet to verify the primary gateway resonance.</p>
                            <div class="flex gap-4 text-left font-sans">
                                <input type="email" placeholder="Target destination..." class="flex-1 bg-gray-50 border-none rounded-2xl px-6 text-sm font-semibold shadow-inner text-left font-sans">
                                <button class="px-10 py-4.5 bg-indigo-50 text-indigo-600 rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-indigo-600 hover:text-white transition-all shadow-xl shadow-indigo-50/50 active:scale-95 text-left font-sans">
                                    EXECUTE TEST
                                </button>
                            </div>
                         </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive } from 'vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    settings: { type: Object, default: () => ({}) }
});

const form = useForm({
    smtp_host: props.settings?.smtp_host || '',
    smtp_port: props.settings?.smtp_port || '',
    smtp_encryption: props.settings?.smtp_encryption || 'tls',
    sender_email: props.settings?.sender_email || '',
    sender_name: props.settings?.sender_name || ''
});

const notifications = reactive({
    new_lead_alert: true,
    deal_won_blast: true,
    ticket_escalation: false,
    daily_digest: true
});

const saveSettings = () => {
    form.post(route('crm.settings.update'), {
        preserveScroll: true
    });
};

const printView = () => window.print();
</script>

<style scoped>
/* Custom Hide Scrollbar */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.05); border-radius: 10px; }
::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.1); }

@media print {
    #print-area { padding: 0 !important; }
    .no-print { display: none !important; }
}
</style>

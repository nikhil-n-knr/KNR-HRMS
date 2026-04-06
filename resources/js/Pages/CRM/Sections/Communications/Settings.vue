<template>
    <div class="h-[calc(100vh-160px)] flex flex-col space-y-8">
        <!-- Settings Header -->
        <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-8 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center text-gray-500 shadow-sm border border-gray-100">
                    <i class="fas fa-plug text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight">External Links</h2>
                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Provider Integration & Sync Protocol</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-8">
            <!-- Connect Providers -->
            <div class="col-span-12 lg:col-span-8 space-y-8">
                <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-10 overflow-hidden relative">
                    <div class="absolute -right-20 -top-20 opacity-[0.03] text-indigo-600 rotate-12">
                        <i class="fas fa-share-nodes text-[400px]"></i>
                    </div>
                    
                    <h3 class="text-xl font-black text-gray-900 mb-2 tracking-tight relative z-10">Sync Your Ecosystem</h3>
                    <p class="text-sm text-gray-400 font-bold uppercase tracking-widest mb-10 relative z-10">Synchronize communications across all channels</p>

                    <div class="space-y-6 relative z-10">
                        <!-- Gmail -->
                        <div class="p-6 bg-gray-50 rounded-3xl border border-gray-100 flex items-center justify-between hover:bg-white transition-all hover:shadow-xl hover:shadow-indigo-500/5 group">
                            <div class="flex items-center gap-6">
                                <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center shadow-sm border border-gray-100 group-hover:scale-110 transition-transform">
                                    <i class="fab fa-google text-2xl text-[#DB4437]"></i>
                                </div>
                                <div class="text-left">
                                    <h4 class="text-base font-black text-gray-900">Google Workspace</h4>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Direct OAuth Integration</p>
                                </div>
                            </div>
                            <a :href="route('crm.comms.auth.redirect', { provider: 'google' })" class="px-8 py-3 bg-white border border-gray-100 rounded-xl text-xs font-black uppercase tracking-widest text-gray-900 shadow-sm hover:bg-gray-900 hover:text-white transition-all whitespace-nowrap">Connect Gmail</a>
                        </div>

                        <!-- Outlook -->
                        <div class="p-6 bg-gray-50 rounded-3xl border border-gray-100 flex items-center justify-between hover:bg-white transition-all hover:shadow-xl hover:shadow-indigo-500/5 group">
                            <div class="flex items-center gap-6">
                                <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center shadow-sm border border-gray-100 group-hover:scale-110 transition-transform">
                                    <i class="fab fa-windows text-2xl text-[#0078D4]"></i>
                                </div>
                                <div class="text-left">
                                    <h4 class="text-base font-black text-gray-900">Microsoft Outlook</h4>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Exchange & O365 Support</p>
                                </div>
                            </div>
                            <a :href="route('crm.comms.auth.redirect', { provider: 'outlook' })" class="px-8 py-3 bg-white border border-gray-100 rounded-xl text-xs font-black uppercase tracking-widest text-gray-900 shadow-sm hover:bg-gray-900 hover:text-white transition-all whitespace-nowrap">Link Outlook</a>
                        </div>

                        <!-- IMAP/SMTP -->
                        <div class="p-6 bg-gray-50 rounded-3xl border border-gray-100 flex items-center justify-between hover:bg-white transition-all hover:shadow-xl hover:shadow-indigo-500/5 group">
                            <div class="flex items-center gap-6">
                                <div class="w-14 h-14 bg-white rounded-2xl flex items-center justify-center shadow-sm border border-gray-100 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-server text-xl text-indigo-500"></i>
                                </div>
                                <div class="text-left">
                                    <h4 class="text-base font-black text-gray-900">Custom IMAP / SMTP</h4>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Enterprise Mail Protocol</p>
                                </div>
                            </div>
                            <button @click="showCustomModal = true" class="px-8 py-3 bg-white border border-gray-100 rounded-xl text-xs font-black uppercase tracking-widest text-gray-900 shadow-sm hover:bg-gray-900 hover:text-white transition-all whitespace-nowrap">Configure Manually</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Connections -->
            <div class="col-span-12 lg:col-span-4">
                <div class="bg-gray-900 rounded-[40px] p-10 text-white min-h-[400px] shadow-2xl shadow-indigo-200">
                    <h3 class="text-xl font-black mb-8 tracking-tight">Active Links</h3>
                    
                    <div v-if="accounts.length > 0" class="space-y-6">
                        <div v-for="acc in accounts" :key="acc.id" class="p-6 rounded-3xl bg-white/5 border border-white/10 flex flex-col gap-4 hover:bg-white/10 transition-all">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-[10px] font-black uppercase tracking-[0.2em] mb-1" :class="acc.is_active ? 'text-indigo-400' : 'text-gray-500'">{{ acc.provider }}</p>
                                    <p class="text-sm font-black truncate max-w-[200px]" :class="!acc.is_active ? 'text-gray-400 line-through opacity-70' : ''">{{ acc.email }}</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <button @click="toggleAccount(acc)" class="w-10 h-6 rounded-full relative transition-colors" :class="acc.is_active ? 'bg-emerald-500' : 'bg-gray-600'">
                                        <span class="absolute top-1 w-4 h-4 rounded-full bg-white transition-all shadow-sm" :class="acc.is_active ? 'right-1' : 'left-1'"></span>
                                    </button>
                                    <button v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'super_admin'" @click="unlinkAccount(acc.id)" class="w-8 h-8 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center hover:bg-rose-500 hover:text-white transition-colors" title="Unlink Account">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Admin Handler Assignment Info -->
                            <div v-if="$page.props.auth.user.role === 'admin' || $page.props.auth.user.role === 'super_admin'" class="bg-gray-950/50 p-4 rounded-xl border border-white/5 space-y-3">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-[10px] text-gray-400 font-black uppercase tracking-widest">
                                        <i class="fas fa-users-gear text-indigo-400"></i>
                                        <span>Active Personnel Linked:</span>
                                    </div>
                                    <span class="text-xs font-black text-white">{{ acc.user_ids.length }} Units</span>
                                </div>
                                <div class="flex flex-wrap gap-1.5">
                                    <span v-for="name in acc.user_names" :key="name" class="px-2 py-0.5 bg-indigo-500/20 border border-indigo-500/30 rounded-md text-[9px] font-bold text-indigo-200">{{ name }}</span>
                                    <span v-if="acc.user_ids.length === 0" class="text-[9px] font-black text-rose-400 uppercase tracking-widest italic opacity-60">No handlers linked</span>
                                </div>
                                <Link :href="route('crm.comms.hub', { section: 'communications', tab: 'assignments' })" class="block w-full text-center py-2 bg-white/5 hover:bg-white/10 rounded-lg text-[9px] font-black text-indigo-400 uppercase tracking-widest border border-white/5 transition-all mt-2">Manage Mapping & Reports</Link>
                            </div>
                            <div v-else class="text-[10px] font-bold text-gray-400 uppercase tracking-widest flex items-center gap-2">
                                <i class="fas fa-user-check text-indigo-400"></i> {{ acc.user_ids.includes($page.props.auth.user.id) ? 'Assigned to you' : 'Global Access Channel' }}
                            </div>
                        </div>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center py-20 text-center opacity-40">
                         <i class="fas fa-link-slash text-4xl mb-6"></i>
                         <p class="text-xs font-bold uppercase tracking-widest leading-relaxed">No active external neural links detected.</p>
                    </div>

                    <div class="mt-10 pt-10 border-t border-white/5">
                        <p class="text-xs text-indigo-300 font-bold uppercase tracking-widest mb-4">Encryption Status</p>
                        <div class="flex items-center gap-3">
                            <i class="fas fa-shield-halved text-emerald-500"></i>
                            <span class="text-xs font-medium text-gray-400">Military-grade AES-256 Protocol</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom SMTP Modal -->
        <div v-if="showCustomModal" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white rounded-[40px] shadow-2xl max-w-xl w-full overflow-hidden border border-white">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-xl font-black text-gray-900 tracking-tight">Enterprise Mail Protocol</h3>
                    <button @click="showCustomModal = false" class="text-gray-400 hover:text-gray-900 transition-rotate duration-300 hover:rotate-90">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="p-10 space-y-6">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                             <label class="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">IMAP Host</label>
                             <input v-model="form.host" type="text" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold shadow-inner" placeholder="imap.server.com">
                        </div>
                        <div class="space-y-2">
                             <label class="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">IMAP Port</label>
                             <input v-model="form.port" type="text" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold shadow-inner" placeholder="993">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 pt-4 border-t border-gray-50">
                        <div class="space-y-2">
                             <label class="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">SMTP Host</label>
                             <input v-model="form.smtp_host" type="text" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold shadow-inner" placeholder="smtp.server.com">
                        </div>
                        <div class="space-y-2">
                             <label class="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">SMTP Port</label>
                             <input v-model="form.smtp_port" type="text" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold shadow-inner" placeholder="465 / 587">
                        </div>
                    </div>

                    <div class="space-y-2">
                         <label class="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Email Address</label>
                         <input v-model="form.email" type="email" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold shadow-inner" placeholder="user@domain.com">
                    </div>
                    <div class="space-y-2">
                         <label class="block text-xs font-black text-gray-400 uppercase tracking-widest ml-1">Password / App Key</label>
                         <input v-model="form.password" type="password" class="w-full bg-gray-50 border-none rounded-xl p-4 text-sm font-bold shadow-inner" placeholder="••••••••">
                    </div>
                    
                    <button @click="submitImap" :disabled="form.processing" class="w-full py-5 bg-indigo-600 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-xl shadow-indigo-100 hover:bg-indigo-700 transition-all disabled:opacity-50 inline-flex justify-center items-center gap-2">
                        <i v-if="form.processing" class="fas fa-spinner fa-spin"></i>
                        Validate and Link Securely
                    </button>
                    <p v-if="form.errors.email" class="text-sm font-bold text-rose-500 mt-2 text-center">{{ form.errors.email }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    accounts: { type: Array, default: () => [] },
    users: { type: Array, default: () => [] },
});

const showCustomModal = ref(false);

const form = useForm({
    host: 'mail.onehubconnect.in',
    port: '993',
    smtp_host: 'mail.onehubconnect.in',
    smtp_port: '465',
    email: '',
    password: ''
});


const submitImap = () => {
    form.post(route('crm.comms.settings.imap'), {
        preserveScroll: true,
        onSuccess: () => {
            showCustomModal.value = false;
            form.reset('password');
        }
    });
};

const toggleAccount = (acc) => {
    router.post(route('crm.comms.settings.accounts.toggle', acc.id), {}, { preserveScroll: true });
};

const assignAccount = (acc) => {
    router.post(route('crm.comms.settings.accounts.assign', acc.id), { user_id: acc.user_id }, { preserveScroll: true });
};

const unlinkAccount = (id) => {
    if (confirm('Are you sure you want to permanently unlink this account and purge all downloaded emails? This action cannot be reversed.')) {
        router.delete(route('crm.comms.settings.accounts.unlink', id), { preserveScroll: true });
    }
};
</script>

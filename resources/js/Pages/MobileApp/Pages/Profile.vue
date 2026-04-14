<template>
    <AppLayout>
        <div class="space-y-6 pb-24">
            <!-- Profile Info -->
            <section class="flex flex-col items-center py-6">
                <div class="relative">
                    <div class="w-28 h-28 rounded-[2.5rem] bg-emerald-100 border-4 border-white shadow-xl overflow-hidden shadow-emerald-200/50">
                        <img v-if="user.avatar" :src="user.avatar" alt="Avatar" class="w-full h-full object-cover">
                        <div v-else class="w-full h-full flex items-center justify-center text-emerald-600 text-3xl font-black">
                            {{ user.name?.charAt(0) }}
                        </div>
                    </div>
                </div>
                <h2 class="text-xl font-black text-slate-900 mt-4 uppercase tracking-tight">{{ user.name }}</h2>
                <p class="text-[10px] text-slate-400 font-black uppercase tracking-[0.2em] italic">{{ user.employee?.designation || 'Operational Agent' }}</p>
            </section>

            <!-- Employee Details -->
            <div class="nature-card p-6 space-y-5">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-emerald-600 shadow-sm border border-white">
                        <i class="fas fa-id-badge text-xs"></i>
                    </div>
                    <div>
                        <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest">Employee Security ID</p>
                        <p class="text-sm font-black text-slate-700 tabular-nums">{{ user.employee?.employee_id || 'N/A' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center text-emerald-600 shadow-sm border border-white">
                        <i class="fas fa-sitemap text-xs"></i>
                    </div>
                    <div>
                        <p class="text-[8px] text-slate-400 font-black uppercase tracking-widest">Department Matrix</p>
                        <p class="text-sm font-black text-slate-700 uppercase tracking-tight">{{ user.employee?.department || 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Premium Action Grid -->
            <div class="grid grid-cols-2 gap-4 px-1">
                <button 
                    @click="showPayslipList = true"
                    class="nature-card p-5 flex flex-col items-center justify-center gap-2 active:scale-95 transition-all text-emerald-600"
                >
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center shadow-sm">
                        <i class="fas fa-file-invoice-dollar text-sm"></i>
                    </div>
                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-500">Payslip Hub</span>
                </button>
                <div class="nature-card p-5 flex flex-col items-center justify-center gap-2 opacity-50 border-dashed">
                    <div class="w-10 h-10 rounded-xl bg-slate-50 flex items-center justify-center">
                        <i class="fas fa-shield-halved text-sm text-slate-400"></i>
                    </div>
                    <span class="text-[9px] font-black uppercase tracking-widest text-slate-400">Security</span>
                </div>
            </div>

            <!-- Settings / Actions -->
            <div class="nature-card overflow-hidden !rounded-[1.5rem] bg-white">
                <button 
                    @click="showSmtpSettings = !showSmtpSettings"
                    class="w-full p-5 flex items-center justify-between hover:bg-slate-50 transition-colors border-b border-slate-50"
                >
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center"><i class="fas fa-envelope-open-text text-xs"></i></div>
                        <span class="text-xs font-black text-slate-700 uppercase tracking-tight">Email SMTP Assets</span>
                    </div>
                    <i :class="showSmtpSettings ? 'fa-chevron-down' : 'fa-chevron-right'" class="fas text-slate-300 text-[10px] transition-transform"></i>
                </button>

                <!-- SMTP Management -->
                <div v-if="showSmtpSettings" class="p-5 bg-slate-50/50 space-y-4 border-b border-slate-50">
                    <div v-for="acc in emailAccounts" :key="acc.id" class="flex justify-between items-center p-3 bg-white rounded-xl border border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="text-emerald-500 text-xs"><i class="fas fa-envelope"></i></div>
                            <p class="text-[10px] font-black uppercase text-slate-700">{{ acc.email_address }}</p>
                        </div>
                        <button @click="toggleAccount(acc)" class="text-slate-400"><i :class="acc.is_active ? 'fa-toggle-on text-emerald-500' : 'fa-toggle-off'" class="fas"></i></button>
                    </div>
                    <button @click="showAddSmtp = true" class="w-full py-3 border-2 border-dashed border-slate-200 rounded-xl text-slate-400 text-[9px] font-black uppercase tracking-widest">+ Link SMTP Node</button>
                </div>

                <!-- Logout -->
                <button 
                    @click="logout"
                    class="w-full p-5 flex items-center justify-between hover:bg-rose-50 transition-colors text-rose-500"
                >
                    <div class="flex items-center gap-4">
                        <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center"><i class="fas fa-sign-out-alt text-xs"></i></div>
                        <span class="text-xs font-black uppercase tracking-tight">Protocol Terminate / Logout</span>
                    </div>
                </button>
            </div>

            <p class="text-center text-[10px] text-slate-400 pb-12">
                Version 1.0.0 (Build 2026.04) <br>
                Powered by LEAP Ecosystem
            </p>

            <!-- Payslip List Modal -->
            <transition name="slide-up">
                <div v-if="showPayslipList" class="fixed inset-0 z-[3000] flex flex-col justify-end">
                    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-[2px]" @click="showPayslipList = false"></div>
                    <div class="nature-card !rounded-t-[3rem] !rounded-b-none p-8 max-h-[90vh] overflow-y-auto relative z-[3001] pb-12 shadow-2xl">
                        <div class="w-12 h-1.5 bg-slate-100 rounded-full mx-auto mb-8"></div>
                        <div class="flex justify-between items-center mb-8">
                            <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight italic">Earning Hub</h2>
                            <button @click="showPayslipList = false" class="text-slate-400 text-xs uppercase font-black tracking-widest">Close</button>
                        </div>

                        <div v-if="payslips.length > 0" class="space-y-3">
                            <div v-for="pay in payslips" :key="pay.id" class="flex justify-between items-center p-4 bg-slate-50/50 rounded-2xl border border-white active:scale-95 transition-all" @click="downloadPayslip(pay)">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-xl bg-white border border-slate-100 flex items-center justify-center text-emerald-600 shadow-sm">
                                        <i class="fas fa-file-pdf"></i>
                                    </div>
                                    <div>
                                        <h4 class="text-[11px] font-black text-slate-900 uppercase tracking-tight">Fiscal Record • {{ pay.month_label }}</h4>
                                        <p class="text-[8px] text-slate-400 font-bold uppercase tracking-widest mt-1">Status: {{ pay.status }} | Net: ₹{{ pay.net_pay.toLocaleString() }}</p>
                                    </div>
                                </div>
                                <i class="fas fa-download text-emerald-500 text-xs"></i>
                            </div>
                        </div>
                        <div v-else class="text-center py-12">
                            <i class="fas fa-ghost text-slate-200 text-3xl mb-4"></i>
                            <p class="text-[10px] text-slate-400 font-black uppercase tracking-widest">No Statements Archived</p>
                        </div>
                    </div>
                </div>
            </transition>

            <!-- Modals -->
            <transition name="slide-up">
                <div v-if="showAddSmtp" class="fixed inset-0 z-[3000] flex flex-col justify-end">
                    <div class="absolute inset-0 bg-slate-900/60" @click="showAddSmtp = false"></div>
                    <div class="nature-card !rounded-t-[3rem] !rounded-b-none p-8 max-h-[90vh] overflow-y-auto relative z-[3001] pb-12 shadow-2xl">
                        <div class="w-12 h-1.5 bg-slate-100 rounded-full mx-auto mb-8"></div>
                        <h2 class="text-xl font-black text-slate-900 uppercase tracking-tight italic mb-8">Link Matrix Node</h2>
                        <form @submit.prevent="saveAccount" class="space-y-5">
                            <input v-model="smtpForm.email" type="email" placeholder="AGENT@DOMAIN.COM" class="nature-input text-[10px]" />
                            <div class="grid grid-cols-2 gap-4">
                                <input v-model="smtpForm.host" type="text" placeholder="imap.gmail.com" class="nature-input text-[10px]" />
                                <input v-model="smtpForm.port" type="number" placeholder="993" class="nature-input text-[10px]" />
                            </div>
                            <input v-model="smtpForm.smtp_host" type="text" placeholder="smtp.gmail.com" class="nature-input text-[10px]" />
                            <input v-model="smtpForm.password" type="password" placeholder="PASSWORD" class="nature-input text-[10px]" />
                            <button :disabled="saving" class="nature-button-primary">{{ saving ? 'Syncing...' : 'Synchronize Node' }}</button>
                        </form>
                    </div>
                </div>
            </transition>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import AppLayout from '../App.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const showSmtpSettings = ref(false);
const showAddSmtp = ref(false);
const showPayslipList = ref(false);
const emailAccounts = ref([]);
const payslips = ref([]);
const saving = ref(false);

const smtpForm = ref({
    email: '',
    host: '',
    port: 993,
    smtp_host: '',
    password: ''
});

const fetchAccounts = async () => {
    try {
        const response = await axios.get('/api/mobile/v1/email-accounts');
        emailAccounts.value = response.data;
    } catch (err) {
        console.error("Failed to fetch SMTP accounts:", err);
    }
};

const fetchPayslips = async () => {
    try {
        const response = await axios.get('/api/mobile/v1/payslips');
        payslips.value = response.data;
    } catch (err) {
        console.error("Failed to fetch payslips:", err);
    }
};

const saveAccount = async () => {
    saving.value = true;
    try {
        await axios.post('/api/mobile/v1/email-accounts', smtpForm.value);
        showAddSmtp.value = false;
        fetchAccounts();
    } catch (err) {
        alert("Verification Failed.");
    } finally {
        saving.value = false;
    }
};

const toggleAccount = async (acc) => {
    try {
        await axios.post(`/api/mobile/v1/email-accounts/${acc.id}/toggle`);
        acc.is_active = !acc.is_active;
    } catch (err) {
        alert("Update failed");
    }
};

const downloadPayslip = (payslip) => {
    const month = payslip.month; // From mapped API
    const year = payslip.year;   // From mapped API
    window.open(`/api/mobile/v1/payslips/download?month=${month}&year=${year}`, '_blank');
};

const logout = async () => {
    if (confirm('Logout?')) {
        try {
            await axios.post('/api/mobile/v1/logout');
            window.location.href = '/m/login';
        } catch (err) {
            router.post('/logout');
        }
    }
};

onMounted(() => {
    fetchAccounts();
    fetchPayslips();
});
</script>

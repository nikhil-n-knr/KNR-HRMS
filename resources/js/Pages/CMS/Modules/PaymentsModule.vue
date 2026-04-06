<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">Payments</h2>
                <p class="text-xs text-gray-500 mt-0.5">Configure Razorpay, COD, bank transfer, and custom payment gateways.</p>
            </div>
            <button @click="save" :disabled="saving" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all flex items-center gap-2 disabled:opacity-50">
                <i v-if="saving" class="fas fa-spinner fa-spin text-xs"></i>
                <i v-else class="fas fa-save text-xs"></i> Save Config
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-8 space-y-5">
            <!-- Payment method cards -->
            <div class="grid grid-cols-2 gap-5">
                <!-- Razorpay -->
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4" :class="config.razorpay_enabled ? 'ring-2 ring-indigo-500/30' : ''">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center">
                                <span class="text-white font-black text-sm">Rp</span>
                            </div>
                            <div>
                                <h3 class="font-black text-gray-900">Razorpay</h3>
                                <p class="text-sm text-gray-400">UPI, Cards, Netbanking, Wallets</p>
                            </div>
                        </div>
                        <label class="relative cursor-pointer">
                            <input type="checkbox" v-model="config.razorpay_enabled" class="sr-only peer" />
                            <div class="w-11 h-6 bg-gray-200 peer-checked:bg-indigo-600 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5 after:shadow-sm"></div>
                        </label>
                    </div>
                    <div v-if="config.razorpay_enabled" class="space-y-3 pt-2 border-t border-gray-100">
                        <div>
                            <label class="field-label">Key ID</label>
                            <input v-model="config.razorpay_key_id" class="field-input font-mono" placeholder="rzp_live_..." />
                        </div>
                        <div>
                            <label class="field-label">Key Secret</label>
                            <div class="relative">
                                <input :type="showSecret ? 'text' : 'password'" v-model="config.razorpay_key_secret" class="field-input font-mono pr-10" placeholder="••••••••••••••••" />
                                <button @click="showSecret = !showSecret" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600">
                                    <i :class="showSecret ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-xs"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Mode</label>
                            <div class="flex gap-2">
                                <button @click="config.razorpay_mode = 'test'" class="flex-1 py-1.5 rounded-xl text-xs font-black border-2 transition-all" :class="config.razorpay_mode === 'test' ? 'border-amber-400 bg-amber-50 text-amber-700' : 'border-gray-200 text-gray-400'">Test Mode</button>
                                <button @click="config.razorpay_mode = 'live'" class="flex-1 py-1.5 rounded-xl text-xs font-black border-2 transition-all" :class="config.razorpay_mode === 'live' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : 'border-gray-200 text-gray-400'">Live Mode</button>
                            </div>
                        </div>
                        <div class="flex gap-2 flex-wrap">
                            <label v-for="method in razorpayMethods" :key="method" class="flex items-center gap-1.5 cursor-pointer">
                                <input type="checkbox" v-model="config.razorpay_methods" :value="method" class="w-3.5 h-3.5 accent-indigo-600" />
                                <span class="text-sm font-bold text-gray-600">{{ method }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- COD -->
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4" :class="config.cod_enabled ? 'ring-2 ring-amber-500/30' : ''">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-500 flex items-center justify-center">
                                <i class="fas fa-money-bill-wave text-white text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-gray-900">Cash on Delivery</h3>
                                <p class="text-sm text-gray-400">Pay when order arrives</p>
                            </div>
                        </div>
                        <label class="relative cursor-pointer">
                            <input type="checkbox" v-model="config.cod_enabled" class="sr-only peer" />
                            <div class="w-11 h-6 bg-gray-200 peer-checked:bg-amber-500 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5 after:shadow-sm"></div>
                        </label>
                    </div>
                    <div v-if="config.cod_enabled" class="space-y-3 pt-2 border-t border-gray-100">
                        <div><label class="field-label">COD Charge (₹)</label><input v-model.number="config.cod_charge" type="number" class="field-input" placeholder="0" /></div>
                        <div><label class="field-label">Max Order for COD (₹)</label><input v-model.number="config.cod_max_order" type="number" class="field-input" placeholder="50000" /></div>
                        <div><label class="field-label">Pincode Restrictions <span class="text-gray-300 font-normal">(comma separated, leave blank for all)</span></label>
                            <input v-model="config.cod_pincodes" class="field-input" placeholder="400001, 400002..." />
                        </div>
                    </div>
                </div>

                <!-- Bank Transfer -->
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4" :class="config.bank_enabled ? 'ring-2 ring-emerald-500/30' : ''">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-600 flex items-center justify-center">
                                <i class="fas fa-university text-white text-sm"></i>
                            </div>
                            <div>
                                <h3 class="font-black text-gray-900">Bank Transfer / NEFT</h3>
                                <p class="text-sm text-gray-400">Manual bank payment</p>
                            </div>
                        </div>
                        <label class="relative cursor-pointer">
                            <input type="checkbox" v-model="config.bank_enabled" class="sr-only peer" />
                            <div class="w-11 h-6 bg-gray-200 peer-checked:bg-emerald-600 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-5 after:h-5 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5 after:shadow-sm"></div>
                        </label>
                    </div>
                    <div v-if="config.bank_enabled" class="space-y-3 pt-2 border-t border-gray-100">
                        <div class="grid grid-cols-2 gap-3">
                            <div><label class="field-label">Account Name</label><input v-model="config.bank_name" class="field-input" /></div>
                            <div><label class="field-label">Account Number</label><input v-model="config.bank_account" class="field-input font-mono" /></div>
                            <div><label class="field-label">IFSC Code</label><input v-model="config.bank_ifsc" class="field-input font-mono" placeholder="HDFC0001234" /></div>
                            <div><label class="field-label">Bank Name</label><input v-model="config.bank_bank" class="field-input" placeholder="HDFC Bank" /></div>
                        </div>
                    </div>
                </div>

                <!-- Summary card -->
                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-6 text-white shadow-xl">
                    <h3 class="font-black text-lg mb-4">Active Payment Mix</h3>
                    <div class="space-y-3">
                        <div v-for="m in paymentMix" :key="m.name" class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center shrink-0"><i :class="m.icon + ' text-white text-xs'"></i></div>
                            <div class="flex-1">
                                <div class="flex justify-between text-xs font-bold mb-1"><span>{{ m.name }}</span><span>{{ m.pct }}%</span></div>
                                <div class="h-1.5 bg-white/20 rounded-full overflow-hidden"><div class="h-full rounded-full bg-white/80" :style="{width: m.pct + '%'}"></div></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 pt-4 border-t border-white/20">
                        <p class="text-xs font-bold text-white/70 mb-1">Avg Transaction Fee</p>
                        <p class="text-2xl font-black">2.1%</p>
                    </div>
                </div>
            </div>

            <!-- Refund policy -->
            <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                <h3 class="font-black text-gray-900 text-sm flex items-center gap-2"><i class="fas fa-undo text-red-500"></i> Refund Settings</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div><label class="field-label">Refund Window (days)</label><input v-model.number="config.refund_days" type="number" class="field-input" placeholder="7" /></div>
                    <div>
                        <label class="field-label">Auto-Refund on Cancel</label>
                        <select v-model="config.auto_refund" class="field-input">
                            <option value="immediate">Immediate</option>
                            <option value="manual">Manual Approval</option>
                            <option value="disabled">Disabled</option>
                        </select>
                    </div>
                    <div><label class="field-label">Refund Processing Fee (%)</label><input v-model.number="config.refund_fee_pct" type="number" class="field-input" placeholder="0" /></div>
                </div>
            </div>
        </div>

        <transition enter-active-class="transition" enter-from-class="opacity-0 translate-y-4" leave-active-class="transition" leave-to-class="opacity-0 translate-y-4">
            <div v-if="toast" class="fixed bottom-8 left-1/2 -translate-x-1/2 bg-indigo-600 text-white px-6 py-3 rounded-xl shadow-xl text-sm font-black flex items-center gap-2 z-50">
                <i class="fas fa-check-circle"></i> Payment config saved!
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({ payment_config: { type: Object, default: () => ({}) } });
const saving    = ref(false);
const toast     = ref(false);
const showSecret = ref(false);

const config = ref({
    razorpay_enabled:    props.payment_config?.razorpay_enabled    ?? true,
    razorpay_key_id:     props.payment_config?.razorpay_key_id     ?? '',
    razorpay_key_secret: props.payment_config?.razorpay_key_secret ?? '',
    razorpay_mode:       props.payment_config?.razorpay_mode       ?? 'test',
    razorpay_methods:    props.payment_config?.razorpay_methods    ?? ['UPI','Cards','Netbanking'],
    cod_enabled:         props.payment_config?.cod_enabled         ?? true,
    cod_charge:          props.payment_config?.cod_charge          ?? 0,
    cod_max_order:       props.payment_config?.cod_max_order       ?? 50000,
    cod_pincodes:        props.payment_config?.cod_pincodes        ?? '',
    bank_enabled:        props.payment_config?.bank_enabled        ?? false,
    bank_name:           props.payment_config?.bank_name           ?? '',
    bank_account:        props.payment_config?.bank_account        ?? '',
    bank_ifsc:           props.payment_config?.bank_ifsc           ?? '',
    bank_bank:           props.payment_config?.bank_bank           ?? '',
    refund_days:         props.payment_config?.refund_days         ?? 7,
    auto_refund:         props.payment_config?.auto_refund         ?? 'manual',
    refund_fee_pct:      props.payment_config?.refund_fee_pct      ?? 0,
});

const razorpayMethods = ['UPI', 'Cards', 'Netbanking', 'Wallets', 'EMI', 'BNPL'];

const paymentMix = [
    { name:'UPI / QR',   icon:'fas fa-qrcode',       pct:52 },
    { name:'Credit/Debit',icon:'fas fa-credit-card',  pct:28 },
    { name:'COD',         icon:'fas fa-money-bill',   pct:14 },
    { name:'Netbanking',  icon:'fas fa-university',  pct:6  },
];

const save = async () => {
    saving.value = true;
    try { await axios.post(route('cms.settings.payment'), config.value); } catch {}
    toast.value = true; setTimeout(() => toast.value = false, 3000);
    saving.value = false;
};
</script>

<style scoped>
.field-label { display:block; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#374151; margin-bottom:.25rem; }
.field-input { width:100%; background:#f9fafb; border:1px solid #e5e7eb; border-radius:.5rem; padding:.5rem .75rem; font-size:.875rem; outline:none; }
.field-input:focus { border-color:#6366f1; box-shadow:0 0 0 2px rgba(99,102,241,.15); }
</style>

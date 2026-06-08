<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import BankingModal from '@/Components/Modals/BankingModal.vue';
import { 
    BanknotesIcon, 
    CreditCardIcon, 
    PencilSquareIcon, 
    ShieldCheckIcon,
    ArrowPathIcon,
    CurrencyRupeeIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
    employee: { type: Object, required: true }
});

const emit = defineEmits(['refresh']);
const toast = useToastStore();

const showModal = ref(false);
</script>

<template>
    <div class="space-y-6 animate-in fade-in slide-in-from-bottom-5 duration-700 font-outfit">
        <div class="grid grid-cols-1 gap-6">
            <!-- Strategic Banking Terminal -->
            <div class="bg-white/80 backdrop-blur-xl rounded-[2.5rem] border border-slate-100 p-8 shadow-2xl shadow-slate-200/40 relative group overflow-hidden">
                <div class="absolute -right-12 -top-12 w-48 h-48 bg-slate-50 rounded-full blur-3xl opacity-50"></div>
                
                <div class="flex justify-between items-center mb-10 relative z-10">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-slate-900 rounded-2xl flex items-center justify-center text-emerald-400 shadow-xl group-hover:rotate-6 transition-transform">
                            <BanknotesIcon class="w-6 h-6" />
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-slate-900 uppercase tracking-[0.2em]">Banking Interface</h3>
                            <p class="text-xs font-black text-slate-400 uppercase tracking-widest mt-1">Financial Disbursement Nodes</p>
                        </div>
                    </div>
                    <button @click="showModal = true" class="px-5 py-2.5 bg-emerald-50 text-emerald-600 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-emerald-600 hover:text-white transition-all shadow-sm active:scale-95 flex items-center gap-2">
                        <PencilSquareIcon class="w-4 h-4" />
                        Modify
                    </button>
                </div>
                
                <div v-if="employee.bank_details && employee.bank_details.length > 0" class="space-y-10 relative z-10">
                    <div v-for="bank in employee.bank_details" :key="bank.id" class="p-8 bg-slate-900 rounded-[2rem] text-white shadow-2xl shadow-slate-300 relative overflow-hidden group/card hover:scale-[1.02] transition-transform duration-500">
                        <!-- Card Hologram -->
                        <div class="absolute -right-20 -bottom-20 w-64 h-64 bg-white/5 rounded-full blur-3xl opacity-0 group-hover/card:opacity-100 transition-opacity"></div>
                        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 to-transparent"></div>

                        <div class="flex items-start justify-between relative z-10">
                            <div>
                                <div class="text-xs font-black text-slate-400 uppercase tracking-[0.3em] mb-4">Financial Institution</div>
                                <div class="text-[15px] font-black text-white uppercase tracking-wider flex items-center gap-3">
                                    {{ bank.bank_name }}
                                    <ShieldCheckIcon v-if="bank.is_primary" class="w-5 h-5 text-emerald-400" />
                                </div>
                                <div class="text-xs font-black text-slate-500 uppercase tracking-widest mt-1 italic">{{ bank.branch_name }}</div>
                            </div>
                            <div class="p-3 bg-white/10 rounded-xl">
                                <CreditCardIcon class="w-8 h-8 text-slate-300" />
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-12 mt-12 relative z-10">
                            <div class="space-y-2">
                                <dt class="text-xs font-black text-slate-500 uppercase tracking-[0.2em]">Account Signature</dt>
                                <dd class="text-[14px] font-black text-white uppercase tracking-widest font-mono">{{ bank.account_number || 'NULL_HASH' }}</dd>
                            </div>
                            <div class="space-y-2">
                                <dt class="text-xs font-black text-slate-500 uppercase tracking-[0.2em]">Routing Protocol (IFSC/BIC)</dt>
                                <dd class="text-[14px] font-black text-white uppercase tracking-widest font-mono text-right">{{ bank.ifsc_code || bank.bic_code || 'NULL_CODE' }}</dd>
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-white/5 flex justify-between items-center relative z-10">
                            <div v-if="bank.is_primary" class="text-xs font-black text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded border border-emerald-500/20 uppercase tracking-widest flex items-center gap-2">
                                <div class="w-1 h-1 rounded-full bg-emerald-400 animate-pulse"></div>
                                Active_Primary_Payload
                            </div>
                            <div v-else class="text-xs font-black text-slate-400 uppercase tracking-widest">Secondary_Segment</div>
                            <div class="flex items-center gap-2">
                                <CurrencyRupeeIcon class="w-4 h-4 text-slate-600" />
                                <span class="text-xs font-black text-slate-600 uppercase tracking-widest">Liquidity_Ready</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-20 grayscale opacity-30 relative z-10">
                    <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-8 border border-slate-100 shadow-inner">
                        <CreditCardIcon class="w-10 h-10 text-slate-300" />
                    </div>
                    <p class="text-xs font-black uppercase tracking-[0.4em]">Zero financial accounts mapped</p>
                    <button @click="showModal = true" class="mt-6 text-emerald-600 text-xs font-black uppercase tracking-widest hover:underline flex items-center justify-center gap-2 mx-auto">
                        <ArrowPathIcon class="w-4 h-4" />
                        Initialize Banking Node
                    </button>
                </div>
            </div>
        </div>

        <BankingModal
            :show="showModal"
            :employee="employee"
            :detail="employee.bank_details"
            @close="showModal = false"
            @saved="$emit('refresh')"
        />
    </div>
</template>

<style scoped>
/* Any custom typography or animations */
</style>

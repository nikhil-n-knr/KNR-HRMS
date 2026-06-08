<template>
  <div class="space-y-6">
      <!-- Alerts -->
      <div v-if="pendingPayments.length > 0" class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-lg flex items-start gap-3">
          <svg class="w-5 h-5 text-amber-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
          <div>
              <h3 class="font-bold text-amber-800">Payments Pending</h3>
              <p class="text-sm text-amber-700 mt-1">
                  You have {{ pendingPayments.length }} compliance payments due for this month. 
                  <span class="font-medium">Due Date: 15th of next month.</span>
              </p>
          </div>
      </div>

      <!-- Variance Insights -->
      <div v-for="(insight, idx) in insights" :key="idx" class="bg-white border-l-4 p-4 rounded-lg shadow-sm flex items-start gap-3" :class="insight.color === 'text-red-600' ? 'border-red-500' : 'border-emerald-500'">
           <div class="mt-0.5">
               <svg v-if="insight.color === 'text-red-600'" class="w-5 h-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" /></svg>
               <svg v-else class="w-5 h-5 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
           </div>
           <div>
               <h3 class="font-bold text-gray-800 text-sm">Audit Insight</h3>
               <p class="text-sm text-gray-600" :class="insight.color">{{ insight.message }}</p>
           </div>
      </div>

      <!-- Quick Timeline (Visual) -->
      <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
          <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-6">Current Cycle Status</h3>
          <div class="flex items-center justify-between relative">
              <!-- Line -->
              <div class="absolute left-0 right-0 top-1/2 h-0.5 bg-gray-100 -z-10"></div>
              
              <!-- Steps -->
              <div class="flex flex-col items-center gap-2 bg-white px-2">
                  <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs ring-4 ring-white">1</div>
                  <span class="text-xs font-medium text-gray-900">Payroll Run</span>
              </div>
              <div class="flex flex-col items-center gap-2 bg-white px-2">
                  <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center font-bold text-xs ring-4 ring-white">2</div>
                  <span class="text-xs font-medium text-gray-900">ECR Generated</span>
              </div>
               <div class="flex flex-col items-center gap-2 bg-white px-2">
                  <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs ring-4 ring-white animate-pulse">3</div>
                  <span class="text-xs font-medium text-blue-700 font-bold">Payments</span>
              </div>
               <div class="flex flex-col items-center gap-2 bg-white px-2">
                  <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center font-bold text-xs ring-4 ring-white">4</div>
                  <span class="text-xs font-medium text-gray-500">Returns Filed</span>
              </div>
          </div>
      </div>

      <!-- Challan History Table -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
          <div class="p-6 flex justify-between items-center border-b border-gray-100">
              <h3 class="font-bold text-gray-800">Challan History</h3>
              <button @click="openRecordModal()" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-wide">
                  + Record Manual Payment
              </button>
          </div>
          <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                  <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ref No (TRRN)</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                      <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                  </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 bg-white">
                  <tr v-for="c in challans" :key="c.id" class="hover:bg-gray-50 transition">
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                          {{ getMonthName(c.month) }} {{ c.year }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                          <span class="uppercase font-bold text-xs bg-gray-100 px-2 py-1 rounded">{{ c.type }}</span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">
                          {{ formatCurrency(c.amount_paid) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">
                          {{ c.transaction_ref || '--' }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                          <span v-if="c.status === 'paid'" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                              Paid
                          </span>
                           <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                              Pending
                          </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                           <a v-if="c.document_path" href="#" class="text-indigo-600 hover:text-indigo-900 text-xs font-bold uppercase">View Receipt</a>
                           <button v-else @click="openRecordModal(c)" class="text-emerald-600 hover:text-emerald-900 text-xs font-bold uppercase">Mark Paid</button>
                      </td>
                  </tr>
                  <tr v-if="!challans || challans.length === 0">
                      <td colspan="6" class="px-6 py-10 text-center text-gray-500 text-sm">
                          No challans recorded yet.
                      </td>
                  </tr>
              </tbody>
          </table>
      </div>

      <!-- Payment Modal -->
      <Modal :show="showPaymentModal" @close="showPaymentModal = false">
          <div class="p-6">
              <h2 class="text-lg font-medium text-gray-900">
                  Record {{ form.type.toUpperCase() }} Payment
              </h2>
                <div class="grid grid-cols-2 gap-4 mt-6">
                     <div>
                        <InputLabel value="Month" />
                        <select v-model="form.month" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option v-for="m in 12" :key="m" :value="m">{{ getMonthName(m) }}</option>
                        </select>
                    </div>
                    <div>
                        <InputLabel value="Year" />
                        <TextInput type="number" v-model="form.year" class="mt-1 block w-full" />
                    </div>
                     <div>
                        <InputLabel value="Type" />
                        <select v-model="form.type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="pf">Provident Fund (PF)</option>
                            <option value="esi">ESI</option>
                            <option value="pt">Professional Tax</option>
                        </select>
                    </div>
                     <div>
                        <InputLabel value="Amount Paid" />
                        <TextInput type="number" v-model="form.amount_paid" class="mt-1 block w-full" />
                    </div>
                     <div class="col-span-2">
                        <InputLabel value="Transaction Ref (TRRN / Challan No)" />
                        <TextInput v-model="form.transaction_ref" class="mt-1 block w-full" placeholder="e.g. 0524083765123" />
                    </div>
                     <div class="col-span-2">
                        <InputLabel value="Payment Date" />
                        <TextInput type="date" v-model="form.payment_date" class="mt-1 block w-full" />
                    </div>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                  <SecondaryButton @click="showPaymentModal = false">Cancel</SecondaryButton>
                  <PrimaryButton @click="submitPayment" :disabled="form.processing">Record Payment</PrimaryButton>
              </div>
          </div>
      </Modal>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    challans: Array,
    insights: Array
});

// Mock Logic for Pending
const pendingPayments = computed(() => {
    // In real app, check if current month payroll done but no challan exists
    return []; 
});

const showModal = ref(false);
const form = useForm({
    month: new Date().getMonth() + 1,
    year: new Date().getFullYear(),
    type: 'pf',
    amount_paid: '',
    transaction_ref: '',
    payment_date: new Date().toISOString().split('T')[0],
    proof: null
});

const openRecordModal = (existing = null) => {
    if(existing) {
        form.month = existing.month;
        form.year = existing.year;
        form.type = existing.type;
        form.amount_paid = existing.amount_paid;
    }
    showModal.value = true;
};

const closeModal = () => showModal.value = false;

const submitPayment = () => {
    form.post(route('hr.compliance.record-payment'), {
        onSuccess: () => closeModal()
    });
};

const getMonthName = (m) => new Date(0, m - 1).toLocaleString('default', { month: 'long' });
const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val);
</script>

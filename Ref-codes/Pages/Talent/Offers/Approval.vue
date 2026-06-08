<template>
    <Head title="Offer Approval" />
    <div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Offer Approval Request</h1>
                    <p class="mt-2 text-sm text-gray-600">Complete summary for candidate <strong>{{ candidate.first_name }} {{ candidate.last_name }}</strong>.</p>
                </div>
                <!-- Status Badge -->
                <span class="px-4 py-1.5 rounded-full text-sm font-bold border shadow-sm"
                    :class="{
                        'bg-yellow-100 text-yellow-800 border-yellow-200': offer.approval_status === 'Pending',
                        'bg-green-100 text-green-800 border-green-200': offer.approval_status === 'Approved',
                        'bg-red-100 text-red-800 border-red-200': offer.approval_status === 'Rejected'
                    }"
                >
                    {{ offer.approval_status || 'Pending Review' }}
                </span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- LEFT COLUMN: Offer & Candidate Details -->
                <div class="lg:col-span-2 space-y-6">
                    
                    <!-- 1. Offer Snapshot -->
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="p-6 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                            <h2 class="text-lg font-bold text-gray-800">💼 Offer Details</h2>
                            <a v-if="offer.document_template_id" :href="pdf_preview_url" target="_blank" class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Preview Offer Letter
                            </a>
                            <span v-else class="text-xs text-gray-400 italic">No Template Assigned</span>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Position</label>
                                <div class="text-gray-900 font-medium text-lg">{{ offer.designation }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Joining Date</label>
                                <div class="text-gray-900 font-medium text-lg">{{ new Date(offer.joining_date).toLocaleDateString() }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">CTC (Annual)</label>
                                <div class="text-indigo-700 font-bold text-xl">{{ offer.salary_currency }} {{ Number(offer.salary_amount).toLocaleString() }}</div>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-1">Expiry Date</label>
                                <div class="text-gray-900">{{ new Date(offer.expiry_date).toLocaleDateString() }}</div>
                            </div>
                        </div>
                        <!-- Salary Breakdown Toggle/Preview -->
                        <div class="px-6 pb-6" v-if="offer.salary_breakdown">
                             <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                                 <h3 class="text-xs font-bold text-gray-500 uppercase mb-3">Compensation Structure</h3>
                                 <div class="space-y-1 text-sm">
                                      <div v-for="(comp, i) in salaryComponents" :key="i" class="flex justify-between border-b border-gray-200 border-dashed pb-1 last:border-0">
                                          <span class="text-gray-700">{{ comp.name }}</span>
                                          <span class="font-mono">{{ formatCurrency(comp.value || comp.annual || 0) }}</span>
                                      </div>
                                      <div class="flex justify-between font-bold pt-2 mt-2 border-t border-gray-300">
                                          <span>Net Salary (Monthly)</span>
                                          <span>{{ formatCurrency(netSalary) }}</span>
                                      </div>
                                 </div>
                             </div>
                        </div>
                    </div>

                    <!-- 2. Interview & Feedback Summary -->
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="p-4 border-b border-gray-200 bg-gray-50">
                            <h2 class="text-lg font-bold text-gray-800">📝 Interview Feedback</h2>
                        </div>
                        <div class="p-0">
                            <div v-if="!interviews || interviews.length === 0" class="p-6 text-gray-500 italic">No interviews recorded.</div>
                            <table v-else class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Round</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Interviewer</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Feedback</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="interview in interviews" :key="interview.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ interview.round || 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ interview.interviewer?.name || 'Unknown' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div v-if="interview.feedback" class="flex flex-col space-y-1">
                                                <div class="flex items-center space-x-2">
                                                    <div class="flex text-yellow-400 text-xs">
                                                        <span v-for="i in 5" :key="i" :class="i <= interview.feedback.rating ? 'text-yellow-400' : 'text-gray-200'">★</span>
                                                    </div>
                                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full" 
                                                        :class="{
                                                            'bg-green-100 text-green-800': ['Strong Hire', 'Hire'].includes(interview.feedback.recommendation),
                                                            'bg-red-100 text-red-800': ['No Hire', 'Strong No'].includes(interview.feedback.recommendation),
                                                            'bg-yellow-100 text-yellow-800': !['Strong Hire', 'Hire', 'No Hire', 'Strong No'].includes(interview.feedback.recommendation)
                                                        }">
                                                        {{ interview.feedback.recommendation }}
                                                    </span>
                                                </div>
                                            </div>
                                            <span v-else class="text-gray-400 text-xs italic">Pending Feedback</span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate" :title="interview.feedback?.summary">
                                            {{ interview.feedback?.summary || '-' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- RIGHT COLUMN: Documents & Actions -->
                <div class="space-y-6">
                    
                    <!-- 3. Documents -->
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="p-4 border-b border-gray-200 bg-gray-50">
                            <h2 class="text-lg font-bold text-gray-800">📂 Documents</h2>
                        </div>
                        <ul class="divide-y divide-gray-200">
                            <template v-if="documents && documents.length > 0">
                                <li v-for="doc in documents" :key="doc.id" class="p-4 flex items-center justify-between hover:bg-gray-50 transition">
                                    <div class="flex items-center">
                                        <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ doc.request_name || doc.type }}</p>
                                            <p class="text-xs" :class="{
                                                'text-green-600': doc.status === 'Verified',
                                                'text-blue-600': doc.status === 'Uploaded',
                                                'text-yellow-600': doc.status === 'Pending'
                                            }">{{ doc.status }}</p>
                                        </div>
                                    </div>
                                    <a v-if="doc.file_path" :href="'/storage/' + doc.file_path" target="_blank" class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">View</a>
                                </li>
                            </template>
                            <li v-else class="p-4 text-center text-gray-500 text-sm">No documents requested/uploaded.</li>
                        </ul>
                    </div>

                    <!-- 4. Final Actions -->
                    <div class="bg-white shadow rounded-lg p-6 sticky top-6">
                        <h3 class="text-sm font-bold text-gray-500 uppercase mb-4">Actions</h3>
                        
                        <div v-if="offer.approval_status === 'Pending'" class="space-y-3">
                            <button @click="approve" :disabled="form.processing" class="w-full flex justify-center py-3 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all">
                                ✓ Approve Offer
                            </button>
                            <button @click="reject" :disabled="form.processing" class="w-full flex justify-center py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all">
                                ✗ Reject Offer
                            </button>
                        </div>
                        <div v-else class="text-center py-4">
                             <div v-if="offer.approval_status === 'Approved'" class="text-green-600 font-bold text-lg flex flex-col items-center">
                                 <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                 Offer Approved
                             </div>
                             <div v-else-if="offer.approval_status === 'Rejected'" class="text-red-600 font-bold text-lg flex flex-col items-center">
                                 <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                 Offer Rejected
                             </div>
                             <div v-else class="text-gray-500 font-bold text-lg flex flex-col items-center">
                                  <!-- Fallback for other states like Sent/Accepted -->
                                  {{ offer.approval_status || 'Under Review' }}
                             </div>
                        </div>

                        <!-- Onboarding Action (Only for Accepted Offers) -->
                        <div v-if="offer.status === 'Accepted'" class="mt-4 pt-4 border-t border-gray-100">
                            <a :href="route('talent.candidates.onboard.create', candidate.id)" class="w-full flex justify-center py-3 border border-transparent rounded-md shadow-sm text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                                🚀 Onboard Candidate
                            </a>
                            <p class="text-xs text-center text-gray-500 mt-2">Convert to Employee & Create Account</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    offer: Object,
    candidate: Object,
    interviews: Array,
    documents: Array,
    approval_data: Object,
    pdf_preview_url: String,
    is_approver: Boolean
});

const form = useForm({
    reason: ''
});

// Helper for Salary
const salaryComponents = computed(() => {
    if (!props.offer.salary_breakdown) return [];
    if (Array.isArray(props.offer.salary_breakdown)) return props.offer.salary_breakdown;
    return props.offer.salary_breakdown.components || [];
});

const netSalary = computed(() => {
    const breakdown = props.offer.salary_breakdown;
    if (!breakdown) return 0;
    
    // If net_salary is explicitly stored
    if (!Array.isArray(breakdown) && breakdown.net_salary) return breakdown.net_salary;

    // Calculate from components
    const components = Array.isArray(breakdown) ? breakdown : (breakdown.components || []);
    let net = 0;
    
    components.forEach(comp => {
        // Use monthly if available, else annual/12, else value (legacy)
        let amount = parseFloat(comp.monthly || (comp.annual ? comp.annual / 12 : 0) || comp.value || 0);
        
        if (comp.type === 'earning') net += amount;
        else if (comp.type === 'deduction') net -= amount;
    });
    
    return net;
});

const formatCurrency = (val) => {
    return Number(val).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

const approve = () => {
    if(!confirm('Are you sure you want to approve this offer? It will be marked as Approved.')) return;
    form.post(route('talent.offers.approve', props.offer.id));
};

const reject = () => {
    const reason = prompt('Please enter a rejection reason:');
    if (reason) {
        form.reason = reason;
        form.post(route('talent.offers.reject', props.offer.id));
    }
};
</script>

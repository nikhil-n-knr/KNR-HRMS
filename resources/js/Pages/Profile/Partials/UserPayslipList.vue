<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">My Payslips & Documents</h2>
            <p class="mt-1 text-sm text-gray-600">
                Access your monthly salary slips and tax documents.
            </p>
        </header>

        <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
            <table class="min-w-full divide-y divide-gray-200" v-if="payslips.length > 0">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Net Pay</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="slip in payslips" :key="slip.id">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ slip.payroll ? slip.payroll.batch_name : 'Unknown Batch' }}
                            <div class="text-xs text-gray-400">#{{ slip.payslip_number }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
                            ₹{{ Number(slip.net_pay).toLocaleString('en-IN') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a :href="route('hr.payslip.download', slip.id)" target="_blank" class="text-indigo-600 hover:text-indigo-900 flex items-center justify-end">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Download
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-else class="p-6 text-center text-gray-500 italic">
                No payslips generated yet.
            </div>
        </div>
    </section>
</template>

<script setup>
defineProps({
    payslips: Array
});
</script>

<template>
    <Head :title="`Expense #${expense.id}`" />
    <MainLayout>
        <template #header>
            <div class="flex items-center">
                 <Link :href="route('employee.expenses.index')" class="text-indigo-600 hover:text-indigo-800 mr-4">
                    &larr; Back
                </Link>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Claim Details #{{ expense.id }}</h2>
            </div>
        </template>

        <div class="py-12 bg-gray-50 min-h-screen">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Use 2/3 for Details -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Main Card -->
                    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                        <div class="px-4 py-5 sm:px-6 flex justify-between items-start">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900">{{ expense.title }}</h3>
                                <p class="mt-1 max-w-2xl text-sm text-gray-500">Submitted on {{ formatDate(expense.created_at) }}</p>
                            </div>
                            <StatusBadge :status="expense.status" :stage="expense.current_stage?.stage_name" class="text-sm px-3 py-1" />
                        </div>
                        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
                            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Amount</dt>
                                    <dd class="mt-1 text-2xl font-bold text-gray-900">{{ formatCurrency(expense.amount) }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Payout Method</dt>
                                    <dd class="mt-1 text-sm text-gray-900 font-medium capitalize">{{ expense.payout_method === 'direct' ? 'Direct Transfer' : 'Payroll Addition' }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Category</dt>
                                    <dd class="mt-1 text-sm text-gray-900 bg-gray-100 px-2 py-1 rounded inline-block">{{ expense.category?.name }}</dd>
                                </div>
                                <div class="sm:col-span-1">
                                    <dt class="text-sm font-medium text-gray-500">Date Incurred</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ formatDate(expense.incurred_date) }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Description</dt>
                                    <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap bg-gray-50 p-3 rounded border border-gray-100">{{ expense.description || 'No description provided.' }}</dd>
                                </div>
                                <div class="sm:col-span-2" v-if="expense.receipt_path">
                                    <dt class="text-sm font-medium text-gray-500">Receipt / Proof</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <a :href="`/storage/${expense.receipt_path}`" target="_blank" class="font-medium text-indigo-600 hover:text-indigo-500 flex items-center">
                                            <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8 4a3 3 0 00-3 3v4a5 5 0 0010 0V7a1 1 0 112 0v4a7 7 0 11-14 0V7a5 5 0 0110 0v4a3 3 0 11-6 0V7a1 1 0 012 0v4a1 1 0 102 0V7a3 3 0 00-3-3z" clip-rule="evenodd" />
                                            </svg>
                                            Download Receipt
                                        </a>
                                    </dd>
                                </div>
                                <div class="sm:col-span-2" v-if="expense.rejection_reason">
                                    <div class="rounded-md bg-red-50 p-4 border border-red-200">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-sm font-medium text-red-800">Rejection Reason</h3>
                                                <div class="mt-2 text-sm text-red-700">
                                                    <p>{{ expense.rejection_reason }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Use 1/3 for Timeline -->
                <div class="md:col-span-1">
                    <div class="bg-white shadow sm:rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Request Timeline</h3>
                        <div class="flow-root">
                            <ul role="list" class="-mb-8">
                                <!-- Create Event -->
                                <li>
                                    <div class="relative pb-8">
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm text-gray-500">Request submitted</p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    <time :datetime="expense.created_at">{{ formatDateShort(expense.created_at) }}</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <!-- Workflow Logic Placeholders (Static for now, dynamic if we had activities) -->
                                <!-- We can infer some events from status -->
                                <li v-if="expense.status === 'Processing' || expense.status === 'Approved'">
                                    <div class="relative pb-8">
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">Processing</p>
                                                    <p class="text-xs text-gray-500">{{ expense.current_stage?.stage_name || 'Workflow' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <li v-if="expense.status === 'Approved' || expense.status === 'Paid'">
                                    <div class="relative pb-8">
                                        <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center ring-8 ring-white">
                                                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">Approved</p>
                                                </div>
                                                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                                                    <time :datetime="expense.updated_at">{{ formatDateShort(expense.updated_at) }}</time>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>

                                <li v-if="expense.status === 'Paid'">
                                    <div class="relative pb-8">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-emerald-500 flex items-center justify-center ring-8 ring-white">
                                                     <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">Paid / Settled</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                                
                                <li v-if="expense.status === 'Rejected'">
                                    <div class="relative pb-8">
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center ring-8 ring-white">
                                                     <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </span>
                                            </div>
                                            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">Rejected</p>
                                                    <p class="text-xs text-gray-500">{{ formatDateShort(expense.updated_at) }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </MainLayout>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import StatusBadge from '@/Components/StatusBadge.vue';

const props = defineProps({
    expense: Object
});

const formatCurrency = (val) => new Intl.NumberFormat('en-IN', { style: 'currency', currency: 'INR' }).format(val || 0);
const formatDate = (date) => new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' });
const formatDateShort = (date) => new Date(date).toLocaleDateString('en-IN', { day: 'numeric', month: 'short' });
</script>

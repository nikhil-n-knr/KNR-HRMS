<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <h2 class="text-xs font-black text-gray-400 uppercase tracking-widest">Partner Ecosystem</h2>
            <button class="px-4 py-2 bg-blue-600 text-white text-sm font-black uppercase tracking-widest rounded-xl hover:bg-blue-700 transition-colors shadow-sm shadow-blue-200">
                Register New Partner
            </button>
        </div>

        <!-- Partners Table -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 border-bottom border-gray-100">
                        <th class="px-6 py-4 text-sm font-black text-gray-400 uppercase tracking-widest">Partner</th>
                        <th class="px-6 py-4 text-sm font-black text-gray-400 uppercase tracking-widest">Referral Code</th>
                        <th class="px-6 py-4 text-sm font-black text-gray-400 uppercase tracking-widest">Performance</th>
                        <th class="px-6 py-4 text-sm font-black text-gray-400 uppercase tracking-widest">Rate</th>
                        <th class="px-6 py-4 text-sm font-black text-gray-400 uppercase tracking-widest">Status</th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="partner in partners" :key="partner.id" class="group hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 font-black text-xs">
                                    {{ partner.name.charAt(0) }}
                                </div>
                                <div>
                                    <div class="text-xs font-black text-gray-900">{{ partner.name }}</div>
                                    <div class="text-sm font-medium text-gray-400">{{ partner.email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <code class="px-2 py-1 bg-gray-100 rounded-md text-sm font-black text-blue-600">{{ partner.referral_code }}</code>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex gap-4">
                                <div>
                                    <div class="text-sm font-black text-gray-900">{{ partner.leads_count || 0 }}</div>
                                    <div class="text-sm font-bold text-gray-400 uppercase tracking-wider">Leads</div>
                                </div>
                                <div>
                                    <div class="text-sm font-black text-gray-900">{{ partner.referrals_count || 0 }}</div>
                                    <div class="text-sm font-bold text-gray-400 uppercase tracking-wider">Clicks</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-xs font-black text-green-600">{{ partner.commission_rate }}%</span>
                        </td>
                        <td class="px-6 py-4">
                            <span :class="[
                                'px-2 py-1 rounded-lg text-sm font-black uppercase tracking-widest',
                                partner.status === 'active' ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-400'
                            ]">
                                {{ partner.status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button class="p-2 text-gray-400 hover:text-blue-600 transition-colors">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="partners.length === 0" class="py-20 text-center">
                <i class="fas fa-handshake text-gray-100 text-5xl mb-4"></i>
                <p class="text-sm font-bold text-gray-300 uppercase tracking-widest">No partners registered yet</p>
                <button class="mt-4 text-blue-600 font-black text-sm uppercase underline">Invite your first partner</button>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    partners: { type: Array, default: () => [] }
});
</script>

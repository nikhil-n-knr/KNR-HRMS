<template>
    <div class="space-y-8 text-left">
        <!-- Header -->
        <div class="flex justify-between items-end pb-6 border-b border-gray-100">
            <div>
                <h2 class="text-3xl font-black text-gray-900 tracking-tight">Marketing Growth</h2>
                <div class="flex items-center mt-2">
                    <span class="w-8 h-1 bg-indigo-500 rounded-full mr-3"></span>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-widest">Campaign Engagement & Audience Reach</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Campaign Stats -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-indigo-600 rounded-[40px] p-8 text-white shadow-2xl relative overflow-hidden">
                    <div class="relative z-10">
                        <h3 class="text-sm font-black uppercase tracking-widest text-indigo-200 mb-6">Channel Status</h3>
                        <div class="space-y-4">
                            <div v-for="stat in campaign_stats" :key="stat.status" 
                                 class="flex items-center justify-between p-4 bg-white/10 rounded-2xl border border-white/10">
                                <span class="text-xs font-black uppercase tracking-widest">{{ stat.status }}</span>
                                <span class="text-2xl font-black">{{ stat.count }}</span>
                            </div>
                        </div>
                    </div>
                    <!-- Decorative background -->
                    <div class="absolute -right-4 -bottom-4 w-32 h-32 bg-indigo-500/20 rounded-full blur-3xl"></div>
                </div>

                <div class="bg-indigo-900 rounded-[40px] p-8 text-white shadow-2xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-8 opacity-10">
                        <i class="fas fa-brain text-8xl"></i>
                    </div>
                    <h3 class="text-sm font-black uppercase tracking-widest text-indigo-300 mb-6">AI Campaign Predictor</h3>
                    <div class="flex items-end gap-3 mb-4">
                        <span class="text-5xl font-black text-white">{{ Math.round(avg_success_score) }}</span>
                        <span class="text-xs font-black text-indigo-400 uppercase tracking-widest mb-2">Avg Probability</span>
                    </div>
                    <p class="text-sm font-bold text-indigo-200 uppercase tracking-widest leading-relaxed">
                        Based on current trends, your active campaigns have a high engagement probability.
                    </p>
                </div>

                <div class="bg-white border border-gray-100 rounded-[40px] p-8 shadow-sm">
                    <h3 class="text-sm font-black tracking-widest uppercase text-gray-400 mb-6">Audience Distribution</h3>
                    <div class="space-y-4">
                        <div v-for="segment in segment_distribution" :key="segment.id">
                            <div class="flex justify-between mb-2">
                                <span class="text-xs font-black text-gray-700">{{ segment.name }}</span>
                                <span class="text-xs font-black text-gray-400">{{ segment.contacts_count }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-indigo-500 h-full rounded-full" 
                                     :style="{ width: getPercentage(segment.contacts_count) + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Campaigns -->
            <div class="lg:col-span-2">
                <div class="bg-white border border-gray-100 rounded-[40px] shadow-sm overflow-hidden h-full">
                    <div class="p-8 border-b border-gray-50 flex items-center justify-between">
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest">Recent Activity</h3>
                        <button class="text-sm font-black text-indigo-600 px-4 py-2 bg-indigo-50 rounded-xl hover:bg-indigo-600 hover:text-white transition-all">VIEW ALL</button>
                    </div>
                    
                    <div class="divide-y divide-gray-50">
                        <div v-for="campaign in recent_campaigns" :key="campaign.id" 
                             class="p-6 flex items-center gap-6 hover:bg-gray-50/50 transition-colors">
                            <div class="w-14 h-14 bg-gray-50 rounded-2xl flex items-center justify-center text-indigo-500 border border-gray-100">
                                <i class="fas fa-paper-plane text-xl"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-black text-gray-900 tracking-tight">{{ campaign.name }}</div>
                                <div class="text-sm font-bold text-gray-400 uppercase tracking-widest mt-1">
                                    {{ campaign.template?.name || 'Manual Broadcast' }}
                                </div>
                            </div>
                            <div class="text-right">
                                <div v-if="campaign.predicted_success_score" class="flex flex-col items-end mb-2">
                                    <span class="text-sm font-black text-emerald-600 uppercase tracking-widest">{{ campaign.predicted_success_score }}% Probability</span>
                                    <div class="w-16 bg-gray-100 h-1 rounded-full mt-1 overflow-hidden">
                                        <div class="bg-emerald-500 h-full" :style="{ width: campaign.predicted_success_score + '%' }"></div>
                                    </div>
                                </div>
                                <div class="text-xs font-black text-indigo-600 uppercase tracking-widest px-3 py-1 bg-indigo-50 rounded-lg inline-block">
                                    {{ campaign.status }}
                                </div>
                                <div class="text-sm font-black text-gray-400 mt-2 tracking-widest">
                                    {{ formatDate(campaign.created_at) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div v-if="recent_campaigns.length === 0" class="p-20 text-center text-gray-300">
                        <div class="text-4xl mb-4 opacity-20"><i class="fas fa-bullhorn"></i></div>
                        <div class="text-base font-black uppercase tracking-widest">No active campaigns detected</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    campaign_stats: Array,
    recent_campaigns: Array,
    segment_distribution: Array,
    avg_success_score: Number
});

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric'
    });
};

const getPercentage = (count) => {
    const total = props.segment_distribution.reduce((acc, curr) => acc + curr.contacts_count, 0);
    return total > 0 ? (count / total) * 100 : 0;
};
</script>

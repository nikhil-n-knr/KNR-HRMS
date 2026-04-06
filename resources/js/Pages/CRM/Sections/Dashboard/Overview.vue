<template>
    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Total Leads</p>
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight">{{ metrics.total_leads }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center text-blue-500">
                    <i class="fas fa-filter text-lg"></i>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Weighted PIPELINE</p>
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight">{{ formatCurrency(metrics.weighted_pipeline || 0) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center text-amber-500">
                    <i class="fas fa-magic text-lg"></i>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Revenue (MTD)</p>
                    <h3 class="text-2xl font-black text-gray-900 tracking-tight">{{ formatCurrency(metrics.revenue_mtd || 0) }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-50 flex items-center justify-center text-green-500">
                    <i class="fas fa-chart-line text-lg"></i>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Avg Health</p>
                    <div class="flex items-center gap-2">
                        <span :class="['w-3 h-3 rounded-full shadow-sm', getHealthBulletColor(metrics.avg_health)]"></span>
                        <h3 class="text-2xl font-black text-gray-900 tracking-tight">{{ Math.round(metrics.avg_health) }}</h3>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-50 flex items-center justify-center text-purple-500">
                    <i class="fas fa-heartbeat text-lg"></i>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Activity Feed -->
            <div class="lg:col-span-2 bg-white rounded-3xl p-8 border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h3 class="text-lg font-black text-gray-900 tracking-tight">Recent Activity</h3>
                        <p class="text-xs text-gray-500 font-medium mt-1">Latest updates across your network</p>
                    </div>
                    <button class="text-xs font-bold text-gray-400 hover:text-gray-600 uppercase tracking-widest">View All</button>
                </div>

                <div v-if="activities.length > 0" class="space-y-6 relative pl-4">
                    <div class="absolute left-[19px] top-2 bottom-2 w-0.5 bg-gray-100"></div>
                    
                    <div v-for="activity in activities" :key="activity.id" class="relative pl-6">
                        <div class="absolute left-[-5px] top-1 h-3 w-3 rounded-full border-2 border-white shadow-sm z-10"
                             :class="getTypeColor(activity.type)">
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-bold text-gray-900">{{ activity.creator?.name || 'System' }}</span>
                                <span class="text-xs text-gray-400 font-medium">logged a {{ activity.type }}</span>
                                <span class="text-sm text-gray-300 font-bold uppercase tracking-wider ml-auto">{{ formatDate(activity.created_at) }}</span>
                            </div>
                            <p class="text-sm text-gray-600 font-medium">{{ activity.subject }}</p>
                            <p v-if="activity.activityable" class="text-xs text-gray-400 mt-1">
                                regarding <span class="font-bold text-blue-500">{{ activity.activityable.name || (activity.activityable.first_name + ' ' + activity.activityable.last_name) }}</span>
                            </p>
                        </div>
                    </div>
                </div>
                
                <div v-else class="text-center py-12">
                     <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-history text-gray-300 text-xl"></i>
                    </div>
                    <p class="text-sm font-bold text-gray-400">No recent activity found.</p>
                </div>
            </div>

            <!-- Quick Actions / Today's Meetings -->
            <div class="space-y-6">
                <div class="bg-gray-900 text-white rounded-3xl p-8 shadow-xl relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-8 opacity-10">
                        <i class="fas fa-calendar-day text-8xl"></i>
                    </div>
                    <div class="relative z-10">
                        <h3 class="text-xl font-black tracking-tight mb-6">Today's Agenda</h3>
                        
                        <div v-if="metrics.todays_meetings && metrics.todays_meetings.length > 0" class="space-y-4">
                             <div v-for="meeting in metrics.todays_meetings" :key="meeting.id" class="bg-white/10 rounded-xl p-4 border border-white/5 backdrop-blur-sm">
                                 <div class="flex justify-between items-start mb-2">
                                     <span class="text-xs font-black bg-amber-500/20 text-amber-500 px-2 py-1 rounded uppercase tracking-widest">{{ formatTime(meeting.start_time) }}</span>
                                 </div>
                                 <h4 class="font-bold text-sm mb-1">{{ meeting.title }}</h4>
                                 <p class="text-xs text-gray-400 truncate">with {{ meeting.contacts && meeting.contacts.length ? meeting.contacts[0].first_name : 'Team' }}</p>
                             </div>
                        </div>
                        <div v-else class="py-8 text-center bg-white/5 rounded-2xl border border-white/5 border-dashed">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">No meetings scheduled</p>
                        </div>

                        <button class="w-full mt-6 py-3 bg-white text-gray-900 rounded-xl text-xs font-black uppercase tracking-widest hover:bg-gray-100 transition-all">
                            Add Meeting
                        </button>
                    </div>
                </div>

                <!-- Sales Arena (Leaderboard) -->
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm overflow-hidden">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest flex items-center gap-2">
                             <i class="fas fa-trophy text-amber-500"></i>
                             Sales Arena
                        </h3>
                        <span class="text-sm font-bold text-gray-400">LIVE RANKINGS</span>
                    </div>

                    <div class="space-y-5">
                        <div v-for="(stat, index) in metrics.leaderboard" :key="stat.id" class="flex items-center gap-4 group">
                            <div class="w-6 text-xs font-black text-gray-300 group-hover:text-blue-500 transition-colors">#{{ index + 1 }}</div>
                            <div class="w-10 h-10 rounded-xl bg-gray-50 overflow-hidden border border-gray-100 ring-2 ring-white">
                                <img v-if="stat.user?.avatar" :src="stat.user.avatar" class="w-full h-full object-cover">
                                <div v-else class="w-full h-full flex items-center justify-center text-xs font-black text-gray-400 capitalize">
                                    {{ stat.user?.name.charAt(0) }}
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs font-black text-gray-900 truncate">{{ stat.user?.name }}</div>
                                <div class="text-sm font-bold text-gray-400 uppercase tracking-wider">{{ formatCurrency(stat.total_revenue) }} Revenue</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-black text-blue-600">{{ stat.total_points }}</div>
                                <div class="text-xs font-bold text-gray-300 uppercase">PTS</div>
                            </div>
                        </div>
                    </div>

                    <div v-if="!metrics.leaderboard || metrics.leaderboard.length === 0" class="py-10 text-center">
                        <p class="text-sm font-bold text-gray-300 uppercase tracking-widest">No active competitors</p>
                    </div>
                </div>

                <!-- My Achievements -->
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm relative overflow-hidden group">
                    <div class="absolute -right-6 -bottom-6 opacity-5 group-hover:opacity-10 transition-opacity">
                         <i class="fas fa-medal text-9xl"></i>
                    </div>
                    <div class="relative z-10">
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">Latest Achievement</h3>
                        <div v-if="metrics.user_achievements && metrics.user_achievements.length > 0">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-14 h-14 bg-amber-50 rounded-2xl flex items-center justify-center text-amber-500 border border-amber-100 shadow-sm shadow-amber-200/50">
                                    <i :class="metrics.user_achievements[0].achievement.icon + ' text-2xl'"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-black text-gray-900 tracking-tight">{{ metrics.user_achievements[0].achievement.name }}</h4>
                                    <p class="text-sm font-bold text-amber-600 uppercase tracking-widest">+{{ metrics.user_achievements[0].achievement.points_reward }} Points</p>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                 <div v-for="ua in metrics.user_achievements.slice(1)" :key="ua.id" class="w-8 h-8 rounded-lg bg-gray-50 flex items-center justify-center text-gray-300 hover:text-amber-400 transition-colors" :title="ua.achievement.name">
                                     <i :class="ua.achievement.icon"></i>
                                 </div>
                            </div>
                        </div>
                        <div v-else class="text-center py-6">
                            <i class="fas fa-lock text-gray-100 text-3xl mb-2"></i>
                            <p class="text-sm font-bold text-gray-300 uppercase tracking-widest">No badges earned yet</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { formatDistanceToNow, format } from 'date-fns';

const props = defineProps({
    metrics: { type: Object, default: () => ({}) },
    activities: { type: Array, default: () => [] }
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(value);
};

const formatDate = (date) => {
    try {
        return formatDistanceToNow(new Date(date), { addSuffix: true });
    } catch {
        return date;
    }
};

const formatTime = (date) => {
    try {
        return format(new Date(date), 'h:mm a');
    } catch {
        return '';
    }
};

const getHealthBulletColor = (score) => {
    if (score >= 80) return 'bg-emerald-500';
    if (score >= 50) return 'bg-amber-500';
    return 'bg-rose-500';
};

const getTypeColor = (type) => {
    const colors = {
        note: 'bg-yellow-400',
        call: 'bg-green-500',
        email: 'bg-blue-500',
        meeting: 'bg-purple-500',
        task: 'bg-red-500'
    };
    return colors[type] || 'bg-gray-400';
};
</script>

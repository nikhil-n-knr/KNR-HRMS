<template>
    <div class="space-y-12 animate-fade-in font-sans pb-24">
        <!-- Strategic Header: Institutional Oversight -->
        <div class="bg-white p-12 rounded-[4rem] border border-gray-100 shadow-sm flex flex-col md:flex-row items-center justify-between group overflow-hidden relative">
            <div class="relative z-10 space-y-4 text-center md:text-left">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 rounded-full border border-blue-100">
                    <div class="w-1.5 h-1.5 rounded-full bg-blue-500 animate-pulse"></div>
                    <span class="text-[9px] font-black text-blue-600 uppercase tracking-widest leading-none font-sans">Corporate Hub Infrastructure Active</span>
                </div>
                <h3 class="text-3xl md:text-4xl font-black text-gray-900 tracking-tighter mb-1 uppercase italic leading-none">INSTITUTIONAL ARCHIVE</h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.3em] font-sans">Orchestrating multi-tenant hierarchies for {{ institutions.length }} global partners</p>
            </div>
            <button class="px-10 py-5 bg-gray-900 text-white rounded-3xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all shadow-2xl shadow-gray-200 relative z-10 transform active:scale-95 flex items-center gap-3">
                <i class="fas fa-university-slash font-light"></i> Authorize New Partner
            </button>
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-blue-50 rounded-full opacity-50 group-hover:scale-125 transition-transform duration-1000"></div>
        </div>

        <!-- Partnership Matrix -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
            <div v-for="inst in institutions" :key="inst.id" class="group bg-white rounded-[3.5rem] border border-gray-100 shadow-sm hover:shadow-2xl hover:border-blue-200 transition-all duration-700 p-10 space-y-10 relative overflow-hidden">
                <div class="flex items-start justify-between relative z-10">
                    <div class="w-16 h-16 rounded-[2rem] bg-gray-50 border border-gray-100 flex items-center justify-center text-blue-600 shadow-sm group-hover:bg-blue-600 group-hover:text-white transition-all duration-500 transform group-hover:rotate-12">
                         <i class="fas fa-landmark text-2xl"></i>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-[9px] font-black text-emerald-500 uppercase tracking-widest bg-emerald-50 px-3 py-1 rounded-xl mb-3 border border-emerald-100">Verified Hub</span>
                        <div class="flex -space-x-3">
                            <div v-for="i in 3" :key="i" class="w-8 h-8 rounded-full border-2 border-white bg-gray-100 shadow-sm"></div>
                            <div class="w-8 h-8 rounded-full border-2 border-white bg-white flex items-center justify-center text-[8px] font-black text-gray-400 shadow-sm">+99</div>
                        </div>
                    </div>
                </div>

                <div class="relative z-10 space-y-2">
                    <h4 class="text-xl font-black text-gray-900 tracking-tight uppercase italic mb-1 line-clamp-1 group-hover:text-blue-600 transition-colors">
                        {{ inst.name }}
                    </h4>
                    <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest font-sans italic">Program: {{ inst.type || 'Enterprise Hub' }} • Logic Layer v2.1</p>
                </div>

                <!-- Strategic KPIs for Institution -->
                <div class="grid grid-cols-2 gap-6 border-t border-gray-50 pt-8 relative z-10">
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-gray-300 uppercase tracking-widest font-sans">Catalog Assets</p>
                        <p class="text-xl font-black text-gray-900 tabular-nums italic">{{ inst.courses_count || '120+' }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-gray-300 uppercase tracking-widest font-sans">Active Minds</p>
                        <p class="text-xl font-black text-gray-900 tabular-nums italic">{{ inst.enrollments_count || '4,2k' }}</p>
                    </div>
                </div>

                <div class="relative z-10 pt-4 flex gap-4">
                    <button class="flex-1 py-4 bg-gray-50 text-gray-400 border border-gray-100 rounded-2xl text-[9px] font-black uppercase tracking-widest hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all shadow-sm transform active:scale-95 group/btn">
                         <i class="fas fa-shield-halved mr-2 group-hover/btn:animate-pulse"></i> Deep Governance
                    </button>
                    <button class="w-14 h-14 bg-gray-50 text-gray-400 rounded-2xl flex items-center justify-center hover:bg-gray-900 hover:text-white transition-all transform active:scale-95">
                         <i class="fas fa-external-link-alt text-xs"></i>
                    </button>
                </div>
                <!-- Abstract BG Ornament -->
                <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-blue-50 rounded-full blur-3xl opacity-0 group-hover:opacity-60 transition-opacity duration-1000 -z-1"></div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    institutions: {
        type: Array,
        default: () => [
            { id: 1, name: 'Quantum Logic Faculty', courses_count: 54, enrollments_count: '2.1k', type: 'Affiliate' },
            { id: 2, name: 'Spectral Systems Inc.', courses_count: 32, enrollments_count: '1.4k', type: 'Corporate' },
            { id: 4, name: 'Neural Design Hub', courses_count: 12, enrollments_count: '840', type: 'Partner' },
            { id: 5, name: 'DeepMind Academy', courses_count: 88, enrollments_count: '5.2k', type: 'Enterprise' },
            { id: 6, name: 'Titan Edge Learning', courses_count: 21, enrollments_count: '1.1k', type: 'Strategic' },
        ]
    }
});
</script>


<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }
</style>

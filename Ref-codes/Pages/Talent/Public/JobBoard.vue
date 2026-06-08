<template>
    <div class="min-h-screen bg-gray-50 flex flex-col font-sans selection:bg-indigo-100 selection:text-indigo-700">
        <!-- Floating Header -->
        <header class="fixed top-0 w-full z-50 transition-all duration-300 bg-white/70 backdrop-blur-lg border-b border-indigo-50/50 supports-[backdrop-filter]:bg-white/60">
             <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
                <div class="flex items-center gap-3">
                     <div class="p-2 bg-indigo-600 rounded-lg shadow-lg shadow-indigo-600/20">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                     </div>
                     <span class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-900 to-indigo-600 tracking-tight">Careers</span>
                </div>
                <div>
                     <a href="/login" class="px-5 py-2.5 text-sm font-medium text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 rounded-full transition-all duration-200">
                        Employee Login
                     </a>
                </div>
             </div>
        </header>

        <main class="flex-grow pt-28 pb-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full relative">
            <!-- Hero Section -->
            <div class="text-center mb-16 relative">
                 <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[400px] bg-indigo-300/20 rounded-full blur-3xl -z-10"></div>
                 <span class="inline-block py-1 px-3 rounded-full bg-indigo-50 text-indigo-600 text-xs font-bold tracking-wide uppercase mb-4 shadow-sm border border-indigo-100">We are hiring</span>
                 <h1 class="text-5xl md:text-6xl font-black text-gray-900 tracking-tight mb-6">
                    Build the <span class="text-transparent bg-clip-text bg-gradient-to-br from-indigo-600 to-purple-600">future</span> with us.
                 </h1>
                 <p class="mt-4 text-xl text-gray-500 max-w-2xl mx-auto leading-relaxed">Join a team of visionaries, creators, and problem solvers. Find your next role and make an impact.</p>
            </div>

            <!-- Glass Filter Bar -->
            <div class="sticky top-24 z-40 bg-white/80 backdrop-blur-xl rounded-2xl shadow-xl shadow-indigo-100/50 p-2 mb-12 border border-white/60 ring-1 ring-gray-100 mx-auto max-w-4xl transition-all duration-300">
                 <div class="flex flex-col md:flex-row gap-2">
                     <div class="flex-grow relative group">
                        <svg class="w-5 h-5 absolute left-3.5 top-3.5 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        <input v-model="filters.search" type="text" placeholder="Search by role or keyword..." class="w-full pl-11 pr-4 py-3 bg-transparent border-none rounded-xl focus:ring-2 focus:ring-indigo-500/20 text-gray-900 placeholder-gray-400 font-medium transition-all">
                     </div>
                     <div class="flex gap-2">
                         <div class="w-full md:w-48 relative">
                             <select v-model="filters.dept" class="w-full appearance-none bg-gray-50/50 border-none rounded-xl py-3 pl-4 pr-10 focus:ring-2 focus:ring-indigo-500/20 text-gray-700 font-medium cursor-pointer hover:bg-gray-100 transition-colors">
                                <option value="">All Depts</option>
                                <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                            </select>
                            <svg class="w-4 h-4 absolute right-3.5 top-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                         </div>
                         <div class="w-full md:w-48 relative">
                             <select v-model="filters.loc" class="w-full appearance-none bg-gray-50/50 border-none rounded-xl py-3 pl-4 pr-10 focus:ring-2 focus:ring-indigo-500/20 text-gray-700 font-medium cursor-pointer hover:bg-gray-100 transition-colors">
                                <option value="">All Locations</option>
                                <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                            </select>
                             <svg class="w-4 h-4 absolute right-3.5 top-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                         </div>
                     </div>
                     <button v-if="hasFilters" @click="resetFilters" class="px-4 py-3 text-red-500 hover:bg-red-50 rounded-xl transition-colors" title="Clear Filters">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                     </button>
                 </div>
            </div>

            <!-- Job Grid -->
            <transition-group name="list" tag="div" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div v-for="job in filteredJobs" :key="job.id" class="group bg-white rounded-3xl p-6 shadow-sm hover:shadow-xl hover:shadow-indigo-100/50 border border-gray-100 hover:border-indigo-100 transition-all duration-300 flex flex-col h-full hover:-translate-y-1 relative overflow-hidden">
                    <!-- Decorator -->
                    <div class="absolute top-0 right-0 p-6 opacity-0 group-hover:opacity-10 transition-opacity duration-300">
                         <svg class="w-24 h-24 text-indigo-600 transform group-hover:scale-110 transition-transform duration-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0L24 12L12 24L0 12L12 0Z"/></svg>
                    </div>

                    <div class="flex justify-between items-start mb-6 z-10">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                            {{ job.type }}
                        </span>
                        <div class="text-right">
                             <div class="text-sm font-bold text-gray-900">{{ job.salary_min ? job.salary_currency + ' ' + (job.salary_min/1000) + 'k+' : '' }}</div>
                             <div class="text-sm text-gray-400 font-medium uppercase tracking-wider">{{ job.job_code }}</div>
                        </div>
                    </div>

                    <h3 class="text-xl font-bold text-gray-900 group-hover:text-indigo-600 transition-colors mb-2 z-10">{{ job.title }}</h3>
                    
                    <div class="flex flex-wrap gap-y-2 gap-x-4 text-sm text-gray-500 mb-6 z-10">
                         <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            {{ job.department?.name }}
                         </div>
                         <div class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            {{ job.location?.city }}
                         </div>
                    </div>

                    <p class="text-sm text-gray-600 line-clamp-3 mb-6 leading-relaxed z-10 flex-grow">
                        {{ job.description }}
                    </p>
                    
                    <!-- Skills -->
                    <div v-if="job.skills && job.skills.length" class="flex flex-wrap gap-1.5 mb-6 z-10">
                        <span v-for="(skill, idx) in job.skills.slice(0, 3)" :key="idx" class="px-2.5 py-1 bg-gray-50 text-gray-600 text-base font-semibold rounded border border-gray-100">
                            {{ skill }}
                        </span>
                         <span v-if="job.skills.length > 3" class="px-2 py-1 bg-gray-50 text-gray-400 text-sm font-medium rounded">+{{ job.skills.length - 3 }}</span>
                    </div>

                    <div class="pt-6 border-t border-gray-50 mt-auto flex justify-between items-center z-10">
                        <div class="text-xs text-gray-400 font-medium">
                             Posted {{ new Date(job.created_at).toLocaleDateString([], { month: 'short', day: 'numeric' }) }}
                        </div>
                        <Link :href="route('careers.show', job.id)" class="group/btn relative inline-flex items-center justify-center px-6 py-2 border border-transparent text-sm font-bold rounded-full text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-lg shadow-indigo-600/30 hover:shadow-indigo-600/50 transition-all duration-200 overflow-hidden">
                           <span class="relative z-10 flex items-center">Apply Now <svg class="ml-1 w-4 h-4 transition-transform group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg></span>
                        </Link>
                    </div>
                </div>
            </transition-group>
             
             <!-- Empty State -->
             <div v-if="filteredJobs.length === 0" class="text-center py-24">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <h3 class="mt-2 text-lg font-bold text-gray-900">No open positions found</h3>
                <p class="mt-1 text-gray-500 max-w-sm mx-auto">We couldn't find any jobs matching your criteria. Try different keywords or browse all jobs.</p>
                <div class="mt-8">
                    <button @click="resetFilters" class="text-indigo-600 hover:text-indigo-800 font-semibold text-sm bg-indigo-50 px-6 py-2 rounded-full transition-colors">Clear all filters</button>
                </div>
            </div>

        </main>
        
        <footer class="bg-white border-t border-gray-100 mt-auto py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col items-center">
                 <div class="flex items-center gap-2 mb-4 opacity-50 grayscale">
                     <span class="text-xl font-bold text-gray-900 tracking-tight">OPSCORE</span>
                 </div>
                 <p class="text-center text-sm text-gray-400">&copy; {{ new Date().getFullYear() }} OPSCORE Inc. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    jobs: Array,
    departments: Array,
    locations: Array
});

const filters = ref({
    search: '',
    dept: '',
    loc: ''
});

const resetFilters = () => {
    filters.value = { search: '', dept: '', loc: '' };
};

const hasFilters = computed(() => {
    return filters.value.search !== '' || filters.value.dept !== '' || filters.value.loc !== '';
});

const filteredJobs = computed(() => {
    return props.jobs.filter(job => {
        const matchesSearch = job.title.toLowerCase().includes(filters.value.search.toLowerCase()) || 
                              job.description.toLowerCase().includes(filters.value.search.toLowerCase()) ||
                              (job.skills && job.skills.some(s => s.toLowerCase().includes(filters.value.search.toLowerCase())));
                              
        const matchesDept = !filters.value.dept || job.department_id == filters.value.dept;
        const matchesLoc = !filters.value.loc || job.location_id == filters.value.loc;
        
        return matchesSearch && matchesDept && matchesLoc;
    });
});
</script>
<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.5s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateY(30px);
}
</style>

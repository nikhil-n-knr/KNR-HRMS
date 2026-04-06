<template>
    <div class="min-h-screen bg-gray-50 flex flex-col font-inter">
        <!-- Client Navbar -->
        <nav class="bg-white border-b border-gray-200 px-8 py-4 flex justify-between items-center sticky top-0 z-50 shadow-sm">
            <div class="flex items-center gap-4">
                <div class="bg-emerald-600 text-white p-2 rounded-xl shadow-lg shadow-emerald-100">
                    <RocketLaunchIcon class="w-6 h-6" />
                </div>
                <div>
                    <h2 class="text-lg font-black text-gray-900 tracking-tight">Client Hub</h2>
                    <p class="text-sm text-gray-400 font-bold uppercase tracking-widest">{{ $page.props.auth.user.name }}</p>
                </div>
            </div>
            <div class="flex items-center gap-6">
                <button @click="showCreateModal = true" class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-xl text-sm font-bold transition-all flex items-center gap-2">
                    <PlusIcon class="w-4 h-4" />
                    New Issue
                </button>
                <Link :href="route('client.logout')" method="post" as="button" class="text-gray-400 hover:text-red-600 transition-colors">
                    <PowerIcon class="w-5 h-5" />
                </Link>
            </div>
        </nav>

        <main class="flex-1 p-8 overflow-y-auto">
            <div class="max-w-7xl mx-auto space-y-8">
                
                <!-- Welcome Section -->
                <div>
                    <h1 class="text-3xl font-black text-gray-900 tracking-tighter">Project Quality Overview</h1>
                    <p class="text-gray-500 mt-1">Real-time status of your active software projects.</p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all group overflow-hidden relative">
                        <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-50 rounded-full opacity-50 group-hover:scale-125 transition-transform"></div>
                        <div class="relative">
                            <p class="text-xs font-bold text-gray-400 uppercase mb-1">Active Tickets</p>
                            <h3 class="text-3xl font-black text-gray-900">{{ stats.active }}</h3>
                            <div class="mt-2 text-sm text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full inline-block">Need Attention</div>
                        </div>
                    </div>
                    
                    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all group overflow-hidden relative">
                         <div class="absolute -right-4 -top-4 w-24 h-24 bg-green-50 rounded-full opacity-50 group-hover:scale-125 transition-transform"></div>
                         <div class="relative">
                            <p class="text-xs font-bold text-gray-400 uppercase mb-1">Resolved (Total)</p>
                            <h3 class="text-3xl font-black text-gray-900">{{ stats.resolved }}</h3>
                            <div class="mt-2 text-sm text-green-600 font-bold bg-green-50 px-2 py-0.5 rounded-full inline-block">Cycle Complete</div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-all group overflow-hidden relative">
                         <div class="absolute -right-4 -top-4 w-24 h-24 bg-red-50 rounded-full opacity-50 group-hover:scale-125 transition-transform"></div>
                         <div class="relative">
                            <p class="text-xs font-bold text-gray-700 uppercase mb-1">Critical Open</p>
                            <h3 class="text-3xl font-black text-red-600">{{ stats.critical }}</h3>
                            <div class="mt-2 text-sm text-red-600 font-bold bg-red-50 px-2 py-0.5 rounded-full inline-block">High Priority</div>
                        </div>
                    </div>

                    <div class="bg-emerald-600 p-6 rounded-2xl shadow-xl shadow-emerald-100 text-white relative transition-transform hover:-translate-y-1">
                        <div class="relative">
                            <p class="text-xs font-bold text-emerald-100 uppercase mb-1">Project Health</p>
                            <h3 class="text-3xl font-black">{{ stats.critical > 0 ? 'Action Reqd' : 'Healthy' }}</h3>
                            <div class="mt-2 text-sm bg-white/20 text-white font-bold px-2 py-0.5 rounded-full inline-block">Automated Audit</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Action Required (High Control) -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="flex justify-between items-center">
                            <h2 class="text-xl font-black text-gray-900 flex items-center gap-2">
                                <ShieldCheckIcon class="w-6 h-6 text-emerald-600" />
                                Action Required: Your Verification
                            </h2>
                            <span class="text-xs font-bold text-emerald-600 px-3 py-1 bg-emerald-50 rounded-full border border-emerald-100">
                                {{ actionRequired.length }} Pending
                            </span>
                        </div>

                        <div v-if="actionRequired.length === 0" class="bg-white p-12 rounded-2xl border-2 border-dashed border-gray-200 text-center">
                            <CheckBadgeIcon class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                            <h4 class="font-bold text-gray-900">All caught up!</h4>
                            <p class="text-sm text-gray-500">There are no tickets waiting for your verification right now.</p>
                        </div>

                        <div v-else class="grid grid-cols-1 gap-4">
                            <div v-for="bug in actionRequired" :key="bug.id" 
                                @click="router.visit(route('client.bugs.show', bug.id))"
                                class="bg-white p-5 rounded-2xl border border-gray-100 shadow-sm flex items-center justify-between hover:border-emerald-300 transition-all border-l-4 border-l-orange-500 cursor-pointer group"
                            >
                                <div class="flex items-center gap-4">
                                     <div class="p-3 bg-orange-50 rounded-xl text-orange-600 group-hover:bg-orange-100 transition-colors">
                                        <BugAntIcon class="w-6 h-6" />
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 text-sm">#{{ bug.id }} - {{ bug.subject }}</h4>
                                        <p class="text-xs text-gray-500">{{ bug.project.name }} • {{ bug.module?.name || 'Main' }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click.stop="openVerification(bug)" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold shadow-md shadow-emerald-100 transition-all">
                                        Verify Now
                                    </button>
                                </div>
                            </div>
                        </div>

                         <!-- Issues Feed -->
                        <div class="pt-4 space-y-4">
                            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                                <h2 class="text-xl font-black text-gray-900">Your Issues Feed</h2>
                                <div class="flex items-center gap-3 w-full md:w-auto">
                                    <div class="relative flex-1 md:w-64">
                                        <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                        <input 
                                            v-model="searchQuery" 
                                            type="text" 
                                            placeholder="Search by ID or Subject..."
                                            class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-xl text-xs focus:ring-emerald-500 transition-all font-medium"
                                        />
                                    </div>
                                    <select v-model="filterProject" class="bg-white border border-gray-200 rounded-xl text-xs px-4 py-2 focus:ring-emerald-500 font-bold text-gray-700">
                                        <option value="">All Projects</option>
                                        <option v-for="p in projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden min-h-[400px]">
                                <table class="min-w-full divide-y divide-gray-100">
                                    <thead class="bg-gray-50/50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-sm font-black text-gray-400 uppercase tracking-widest">ID</th>
                                            <th class="px-6 py-3 text-left text-sm font-black text-gray-400 uppercase tracking-widest">Issue</th>
                                            <th class="px-6 py-3 text-left text-sm font-black text-gray-400 uppercase tracking-widest">Status</th>
                                            <th class="px-6 py-3 text-right text-sm font-black text-gray-400 uppercase tracking-widest">Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50">
                                        <tr v-for="bug in filteredBugs" :key="bug.id" 
                                            @click="router.visit(route('client.bugs.show', bug.id))"
                                            class="hover:bg-gray-50/50 transition-colors group cursor-pointer"
                                        >
                                            <td class="px-6 py-4 text-xs font-mono text-gray-400 font-bold">#{{ bug.id }}</td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-bold text-gray-900 leading-tight group-hover:text-emerald-600 transition-colors">{{ bug.subject }}</div>
                                                <div class="text-sm text-gray-400 font-medium">{{ bug.project.name }}</div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <span :class="[
                                                    'px-2 py-0.5 rounded-full text-sm font-black uppercase tracking-wider border',
                                                    bug.stage.is_final ? 'bg-green-50 text-green-700 border-green-100' : 'bg-blue-50 text-blue-700 border-blue-100'
                                                ]">
                                                    {{ bug.stage.name }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 text-right text-xs text-gray-500 font-medium">
                                                {{ new Date(bug.updated_at).toLocaleDateString() }}
                                            </td>
                                        </tr>
                                        <tr v-if="filteredBugs.length === 0">
                                            <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic text-sm">
                                                No matches found for your criteria.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Visual Metrics -->
                    <div class="space-y-6">
                        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-6">Bugs by Module</h3>
                            <div class="space-y-4">
                                <div v-for="module in bugsByModule" :key="module.label" class="space-y-1">
                                    <div class="flex justify-between text-xs font-bold">
                                        <span class="text-gray-600">{{ module.label }}</span>
                                        <span class="text-emerald-600">{{ module.count }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                                        <div class="bg-emerald-500 h-full rounded-full transition-all duration-1000" :style="{ width: (module.count / stats.active * 100) + '%' }"></div>
                                    </div>
                                </div>
                                <div v-if="bugsByModule.length === 0" class="text-center py-8 text-gray-400 text-xs italic">
                                    No module data available.
                                </div>
                            </div>
                        </div>

                        <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-widest mb-6">Resolution Velocity</h3>
                            <div class="flex items-end justify-between gap-1 h-32">
                                <div v-for="day in velocity" :key="day.date" class="flex-1 group relative">
                                    <div 
                                        class="bg-emerald-100 group-hover:bg-emerald-500 transition-all rounded-t-lg mx-auto"
                                        :style="{ height: (day.count * 15 + 10) + 'px', width: '80%' }"
                                    ></div>
                                    <div class="absolute -top-6 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 bg-gray-900 text-white px-2 py-0.5 rounded text-xs font-bold whitespace-nowrap z-10">
                                        {{ day.count }} fixed
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-between mt-4 border-t border-gray-50 pt-2 text-xs font-black text-gray-400 uppercase tracking-widest">
                                <span>{{ velocity[0].date.split('-').slice(1).join('/') }}</span>
                                <span>Recent 7 Days</span>
                                <span>{{ velocity[6].date.split('-').slice(1).join('/') }}</span>
                            </div>
                        </div>
                        
                        <!-- Secure Link Placeholder -->
                        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 p-6 rounded-2xl text-white shadow-xl shadow-emerald-100">
                             <h4 class="font-black text-lg">Secure Access</h4>
                             <p class="text-white/70 text-xs mt-1">Your session is secured with end-to-end encryption. Last login: {{ $page.props.auth.user.last_login_at || 'Just now' }}</p>
                             <div class="mt-6">
                                <button class="w-full bg-white/10 hover:bg-white/20 py-2 rounded-xl text-xs font-bold transition-all">Download Security Certificate</button>
                             </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <!-- Verification Modal (High Control) -->
         <Modal :show="!!verifyingBug" @close="verifyingBug = null">
            <div class="p-6">
                <div class="flex items-center gap-4 mb-6">
                    <div class="h-12 w-12 rounded-2xl bg-emerald-50 flex items-center justify-center text-emerald-600">
                        <CheckBadgeIcon class="w-8 h-8" />
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-900 tracking-tight">Verify Resolution</h3>
                        <p class="text-xs text-gray-500 font-medium">Bug #{{ verifyingBug?.id }} • {{ verifyingBug?.subject }}</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-black text-gray-400 uppercase tracking-widest mb-1">Feedback / Notes</label>
                        <textarea 
                            v-model="verificationNote" 
                            rows="4" 
                            class="w-full rounded-2xl border-gray-200 text-sm focus:ring-emerald-500 focus:border-emerald-500 shadow-inner bg-gray-50/50"
                            placeholder="Add your comments here..."
                        ></textarea>
                    </div>
                </div>

                <div class="mt-8 grid grid-cols-2 gap-4">
                    <button 
                        @click="submitVerification('reject')"
                        class="bg-white border-2 border-red-500/20 text-red-600 font-bold py-3 rounded-2xl hover:bg-red-50 transition-all flex items-center justify-center gap-2"
                    >
                        <XMarkIcon class="w-5 h-5" />
                        Reject & Re-open
                    </button>
                    <button 
                        @click="submitVerification('verify')"
                        class="bg-green-600 shadow-lg shadow-green-100 text-white font-bold py-3 rounded-2xl hover:bg-green-700 transition-all flex items-center justify-center gap-2"
                    >
                        <CheckIcon class="w-5 h-5" />
                        Verify & Close
                    </button>
                </div>
            </div>
         </Modal>

        <!-- Create Modal -->
        <Modal :show="showCreateModal" @close="showCreateModal = false" maxWidth="2xl">
            <ClientTicketWizard :projects="projects" @close="showCreateModal = false" />
        </Modal>

    </div>
</template>

<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { 
    PlusIcon, 
    RocketLaunchIcon, 
    ShieldCheckIcon, 
    BugAntIcon, 
    CheckBadgeIcon, 
    CheckIcon, 
    XMarkIcon,
    PowerIcon,
    MagnifyingGlassIcon
} from '@heroicons/vue/24/outline';
import Modal from '@/Components/Modal.vue';
import ClientTicketWizard from './Components/ClientTicketWizard.vue';

const props = defineProps({
    stats: Object,
    actionRequired: Array,
    bugsByModule: Array,
    velocity: Array,
    projects: Array,
    recentBugs: Array
});

const showCreateModal = ref(false);
const verifyingBug = ref(null);
const verificationNote = ref('');
const searchQuery = ref('');
const filterProject = ref('');

const filteredBugs = computed(() => {
    return props.recentBugs.filter(bug => {
        const matchesSearch = !searchQuery.value || 
            bug.subject.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            bug.id.toString().includes(searchQuery.value);
            
        const matchesProject = !filterProject.value || bug.project_id == filterProject.value;
        
        return matchesSearch && matchesProject;
    });
});

const openVerification = (bug) => {
    router.visit(route('client.bugs.show', bug.id));
};

const submitVerification = (status) => {
    router.post(route('client.bugs.verify', verifyingBug.value.id), {
        status: status,
        note: verificationNote.value
    }, {
        onSuccess: () => {
            verifyingBug.value = null;
            verificationNote.value = '';
        }
    });
};
</script>

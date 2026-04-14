<template>
    <MainLayout>
        <template #header>
            <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 py-2">
                <div class="flex flex-col">
                    <h2 class="font-black text-2xl text-slate-800 tracking-tight leading-none group">
                        Control <span class="text-emerald-600 group-hover:text-teal-500 transition-colors">Portal</span>
                    </h2>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mt-2 flex items-center gap-2">
                        <i class="fas fa-shield-halved text-emerald-500"></i>
                         {{ client?.name || 'External Workspace' }} • Governance 2.0
                    </span>
                </div>
                
                <!-- Tab Navigation (Glassmorphic) -->
                <div class="flex items-center gap-1 bg-white/50 backdrop-blur-md p-1 rounded-2xl border border-white/60 shadow-sm self-start md:self-center">
                    <button v-for="tab in tabs" :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="activeTab === tab.id ? 'bg-white text-emerald-600 shadow-sm' : 'text-slate-500 hover:text-emerald-500 hover:bg-white/30'"
                        class="px-5 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all duration-200 flex items-center gap-2">
                        {{ tab.label }}
                        <span v-if="tab.badge" class="flex items-center justify-center min-w-[16px] h-4 px-1 rounded-md bg-rose-500 text-white text-[8px] animate-pulse">
                            {{ tab.badge }}
                        </span>
                    </button>
                </div>

                <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-xl bg-white border border-slate-100 shadow-sm flex items-center justify-center text-slate-400 relative">
                        <i class="fas fa-bell text-sm"></i>
                        <span class="absolute top-2 right-2 w-2 h-2 bg-rose-500 rounded-full border-2 border-white animate-pulse"></span>
                    </div>
                </div>
            </div>
        </template>

        <div class="bg-slate-50/50 min-h-screen py-8 pb-32">
            <div class="max-w-7xl mx-auto px-4 lg:px-8">
                <!-- Transition Wrapper -->
                <transition name="fade-slide" mode="out-in">
                    <div :key="activeTab">
                        <!-- Dashboard -->
                        <ClientDashboard 
                            v-if="activeTab === 'dashboard'" 
                            :stats="stats" 
                            :projects="projects" 
                        />

                        <!-- Task Matrix (Existing Partial) -->
                        <div v-else-if="activeTab === 'tasks'" class="bg-white/80 backdrop-blur-xl rounded-[40px] border border-white/50 shadow-sm overflow-hidden">
                             <ExternalPortalPartial 
                                :bugs="bugs" 
                                :projects="projects" 
                                :open_critical_count="open_critical_count" 
                                :stages="stages"
                            />
                        </div>

                        <!-- Document Vault -->
                        <DocumentVault 
                            v-else-if="activeTab === 'documents'" 
                            :documents="documents || []"
                        />

                        <!-- Organisation Profile -->
                        <ClientProfile 
                            v-else-if="activeTab === 'profile'" 
                            :client="client" 
                            :projects="projects"
                        />
                    </div>
                </transition>
            </div>
        </div>

    </MainLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import ExternalPortalPartial from './Components/ExternalPortalPartial.vue';
import ClientDashboard from './Components/ClientDashboard.vue';
import DocumentVault from './Components/DocumentVault.vue';
import ClientProfile from './Components/ClientProfile.vue';

const props = defineProps([
    'bugs', 
    'projects', 
    'open_critical_count', 
    'stages', 
    'client', 
    'stats',
    'documents'
]);

const activeTab = ref('dashboard');

const pendingSignoffCount = computed(() => {
    return props.documents?.filter(d => d.category === 'requirement' && !d.is_signed).length || 0;
});

const tabs = computed(() => [
    { id: 'dashboard', label: 'Dashboard' },
    { id: 'tasks', label: 'Ticket Matrix' },
    { id: 'documents', label: 'Governance', badge: pendingSignoffCount.value },
    { id: 'profile', label: 'Corporate Profile' }
]);

onMounted(() => {
    // Listen for Real-time Progress Updates
    props.projects.forEach(project => {
        window.Echo.private(`projects.${project.id}`)
            .listen('.progress.updated', (e) => {
                // Update project data in place
                const p = props.projects.find(x => x.id === e.project.id);
                if (p) {
                    p.manual_progress_percentage = e.project.manual_progress_percentage;
                    p.manual_status_label = e.project.manual_status_label;
                    p.project_health_index = e.project.project_health_index;
                }
            })
            .listen('.document.action', (e) => {
                // We reload to get fresh document state or update list manually
                // For now, a toast or pulse is better
                console.log('Document action received:', e.actionType);
            });
    });
});
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.fade-slide-enter-from {
  opacity: 0;
  transform: translateY(10px);
}

.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>

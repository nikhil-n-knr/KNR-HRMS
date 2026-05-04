<template>
  <Head title="Access Review" />
  <div class="bg-[#f4f5fa]">
    <GradientHeroHeader
      kicker="Administration"
      title="Access Review"
      subtitle="Audit effective permissions, roles, and scopes for any user."
      :allow-overflow="true"
    >
      <template #right>
        <div class="w-full sm:w-[380px]">
          <Combobox
            v-model="selectedUserId"
            :items="users.data || []"
            label-key="first_name"
            value-key="id"
            :display-format="(u) => `${u?.first_name || ''}${u?.email ? ` (${u.email})` : ''}`.trim()"
            placeholder="Search user…"
          />
        </div>
      </template>
    </GradientHeroHeader>

    <div class="mx-0 sm:mx-6 mt-5 pb-12">
      <div class="space-y-6 pb-20">

    <!-- Report Area -->
    <div v-if="accessReport" class="space-y-6 animate-fade-in-up">
        
        <!-- User Summary Card -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
             <!-- Profile Card -->
             <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-3xl p-6 text-white shadow-lg shadow-emerald-500/30 flex flex-col justify-between relative overflow-hidden group">
                 <div class="absolute top-0 right-0 p-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl group-hover:bg-white/20 transition duration-700"></div>
                 
                 <div>
                     <div class="flex items-center gap-4 mb-4 relative z-10">
                        <div class="h-16 w-16 rounded-2xl bg-white/20 backdrop-blur-sm shadow-inner flex items-center justify-center text-2xl font-bold">
                            {{ accessReport.user.name.charAt(0) }}
                        </div>
                        <div>
                             <h3 class="text-emerald-100 uppercase text-sm font-bold tracking-widest">Employee Profile</h3>
                             <p class="text-2xl font-bold tracking-tight">{{ accessReport.user.name }}</p>
                             <div class="flex items-center gap-2 mt-1">
                                 <span class="w-2 h-2 rounded-full bg-emerald-300 animate-pulse"></span>
                                 <p class="text-xs text-emerald-100 font-medium opacity-90">{{ accessReport.user.status || 'Active' }}</p>
                             </div>
                        </div>
                     </div>
                 </div>
                 
                 <div class="relative z-10">
                    <p class="text-sm text-emerald-100/80 font-mono bg-black/10 px-3 py-1.5 rounded-lg inline-block">{{ accessReport.user.email }}</p>
                 </div>
             </div>

             <!-- Roles Card -->
             <div class="col-span-1 md:col-span-2 bg-white/60 backdrop-blur-xl rounded-3xl p-8 border border-white/60 shadow-sm flex flex-col justify-center">
                 <h3 class="text-gray-400 uppercase text-xs font-bold tracking-wider mb-4 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    Assigned Roles & Security Groups
                 </h3>
                 <div class="flex flex-wrap gap-3">
                     <span 
                        v-for="role in accessReport.roles" 
                        :key="role" 
                        class="px-4 py-2 bg-gradient-to-b from-white to-emerald-50 text-emerald-800 rounded-xl text-sm font-bold border border-emerald-100 shadow-sm flex items-center gap-2"
                     >
                         <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path></svg>
                         {{ role }}
                     </span>
                     <span v-if="!accessReport.roles.length" class="px-4 py-2 bg-gray-50 text-gray-400 rounded-xl text-sm italic border border-gray-100">
                        No explicit roles assigned
                     </span>
                 </div>
             </div>
        </div>

        <!-- Detailed Matrix -->
        <div class="space-y-4">
             <h3 class="text-lg font-bold text-gray-700 px-2 mt-4">Effective Permissions</h3>
             
             <div class="bg-white/70 backdrop-blur-xl border border-white/60 rounded-3xl shadow-sm overflow-hidden">
                <div v-for="mod in accessReport.access_matrix" :key="mod.name" class="border-b border-gray-100 last:border-0 group">
                    <!-- Module Header -->
                    <div class="bg-gray-50/50 px-8 py-4 border-b border-gray-100 group-hover:bg-emerald-50/10 transition-colors">
                         <h3 class="font-bold text-gray-800 flex items-center gap-2">
                             <span class="w-1.5 h-1.5 rounded-full bg-gray-300 group-hover:bg-emerald-500 transition-colors"></span>
                             {{ mod.name }}
                         </h3>
                    </div>
                    
                    <!-- Submodules -->
                    <div class="divide-y divide-gray-50">
                        <div v-for="sub in mod.sub_modules" :key="sub.name" class="px-8 py-5 grid grid-cols-1 lg:grid-cols-4 gap-6 items-start hover:bg-white/80 transition-all">
                             <div class="font-semibold text-gray-700 pt-1">{{ sub.name }}</div>
                             
                             <div class="lg:col-span-3 flex flex-wrap gap-3">
                                 <div 
                                    v-for="perm in sub.permissions" 
                                    :key="perm.action"
                                    class="relative group/perm"
                                 >
                                    <div 
                                        class="px-3 py-1.5 rounded-lg text-xs font-bold border flex items-center gap-2 transition-all duration-300"
                                        :class="perm.allowed 
                                            ? 'bg-emerald-50 border-emerald-200 text-emerald-800 shadow-sm scale-100' 
                                            : 'bg-gray-50 border-gray-100 text-gray-300 scale-95 grayscale opacity-60'"
                                    >
                                        <span>{{ formatAction(perm.action) }}</span>
                                        <span 
                                            v-if="perm.allowed" 
                                            class="w-5 h-5 rounded flex items-center justify-center text-sm font-extrabold shadow-inner" 
                                            :class="getScopeClass(perm.scope)"
                                            :title="'Scope: ' + perm.scope"
                                        >
                                            {{ getScopeLabel(perm.scope) }}
                                        </span>
                                    </div>
                                    
                                    <!-- Tooltip -->
                                    <div v-if="perm.allowed" class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 bg-gray-800 text-white text-sm rounded opacity-0 group-hover/perm:opacity-100 transition pointer-events-none whitespace-nowrap z-20">
                                        {{ perm.action }} allows <strong>{{ perm.scope }}</strong> access
                                    </div>
                                 </div>
                             </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Empty State -->
    <div v-else class="flex flex-col items-center justify-center py-32 opacity-50">
        <div class="h-24 w-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
             <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </div>
        <p class="text-lg font-medium text-gray-500">Search and select a user to view their effective access.</p>
    </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { router, Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import GradientHeroHeader from '@/Components/UI/GradientHeroHeader.vue';
import Combobox from '@/Components/Combobox.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    users: Object, // Paginator
    selectedUser: Object,
    accessReport: Object,
});

const selectedUserId = ref(props.selectedUser?.id || '');

watch(selectedUserId, (val) => {
    if (val) {
        router.get('/admin/users/access-review', { user_id: val }, { 
            preserveState: true, 
            preserveScroll: true,
            only: ['selectedUser', 'accessReport'] 
        });
    }
});

const formatAction = (slug) => slug.charAt(0).toUpperCase() + slug.slice(1);

const getScopeClass = (scope) => {
    const map = {
        self: 'bg-white text-gray-600 ring-1 ring-gray-200',
        team: 'bg-blue-100 text-blue-700 ring-1 ring-blue-200',
        department: 'bg-orange-100 text-orange-700 ring-1 ring-orange-200',
        tenant: 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
        global: 'bg-purple-100 text-purple-700 ring-1 ring-purple-200'
    };
    return map[scope] || 'bg-gray-100';
};

const getScopeLabel = (scope) => {
    const map = { self: 'S', team: 'T', department: 'D', tenant: 'O', global: 'G' };
    return map[scope] || '?';
};
</script>

<style scoped>
.animate-fade-in-up {
  animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px) scale(0.98); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>

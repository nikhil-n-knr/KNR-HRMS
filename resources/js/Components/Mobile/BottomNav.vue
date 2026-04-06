<template>
  <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-50 px-4 pb-6 pt-2">
    <div class="max-w-md mx-auto relative group">
      <!-- Glass Background -->
      <div class="absolute inset-0 bg-white/70 backdrop-blur-xl border border-white/40 shadow-[0_-8px_32px_rgba(0,0,0,0.08)] rounded-2xl"></div>
      
      <!-- Nav Items -->
      <div class="relative flex items-center justify-around h-16">
        <Link 
          v-for="item in navItems" 
          :key="item.key"
          :href="item.href"
          class="relative flex flex-col items-center justify-center w-12 h-12 transition-all duration-300 group/item"
          :class="isActive(item.href) ? 'text-emerald-600 scale-110' : 'text-slate-400'"
        >
          <!-- Active Indicator Backdrop -->
          <div 
            v-if="isActive(item.href)"
            class="absolute inset-0 bg-emerald-100/50 rounded-xl animate-pulse-slow"
          ></div>
          
          <!-- Icon -->
          <component 
            :is="item.icon" 
            class="h-6 w-6 relative z-10 transition-transform duration-300"
            :class="isActive(item.href) ? '-translate-y-1' : 'group-hover/item:-translate-y-0.5'"
          />
          
          <!-- Shared Label (Optional/Adaptive) -->
          <span 
            v-if="isActive(item.href)"
            class="text-[10px] font-bold uppercase tracking-tighter mt-0.5 relative z-10 animate-fade-in"
          >
            {{ item.label }}
          </span>
        </Link>

        <!-- Dynamic Menu Trigger -->
        <button 
          @click="$emit('toggle-sidebar')"
          class="flex flex-col items-center justify-center w-12 h-12 text-slate-400 hover:text-emerald-600 transition-colors"
        >
          <Bars3Icon class="h-6 w-6" />
        </button>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { 
  HomeIcon, 
  ClockIcon, 
  BriefcaseIcon, 
  CheckCircleIcon,
  Bars3Icon,
  UserIcon
} from '@heroicons/vue/24/outline';

const props = defineProps({
  currentContext: String
});

defineEmits(['toggle-sidebar']);

const page = usePage();

const navItems = computed(() => [
  { 
    key: 'home', 
    label: 'Home', 
    icon: HomeIcon, 
    href: '/dashboard' 
  },
  { 
    key: 'attendance', 
    label: 'Times', 
    icon: ClockIcon, 
    href: props.currentContext === 'admin' ? '/admin/attendance/hub' : '/attendance' 
  },
  { 
    key: 'projects', 
    label: 'Work', 
    icon: BriefcaseIcon, 
    href: '/projects/dashboard' 
  },
  { 
    key: 'approvals', 
    label: 'Done', 
    icon: CheckCircleIcon, 
    href: props.currentContext === 'admin' ? '/manager/approvals' : '/leave-management' 
  },
  { 
    key: 'personal', 
    label: 'Me', 
    icon: UserIcon, 
    href: route('employee.hub') 
  }
]);

const isActive = (href) => {
  return page.url.startsWith(href);
};
</script>

<style scoped>
@keyframes pulse-slow {
  0%, 100% { opacity: 0.5; transform: scale(1); }
  50% { opacity: 0.8; transform: scale(1.05); }
}

.animate-pulse-slow {
  animation: pulse-slow 3s infinite ease-in-out;
}

.animate-fade-in {
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(4px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>

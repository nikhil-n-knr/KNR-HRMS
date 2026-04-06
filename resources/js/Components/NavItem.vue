<template>
  <component 
    :is="componentType"
    v-bind="linkProps"
    class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 group relative overflow-hidden"
    :class="[
      isActive ? 'bg-gradient-to-r from-emerald-500 to-teal-500 text-white shadow-md shadow-emerald-500/20' : 'text-slate-600 hover:bg-white/50 hover:text-emerald-700 hover:shadow-sm'
    ]"
  >
    <component :is="iconComponent" class="w-5 h-5 transition-transform group-hover:scale-110 duration-200" />
    
    <span v-if="isOpen" class="font-medium tracking-wide text-sm">{{ label }}</span>
    
    <!-- Active Indicator Dot -->
    <span v-if="isActive && isOpen" class="absolute right-3 w-1.5 h-1.5 rounded-full bg-white/80 shadow-sm"></span>
  </component>
</template>

<script setup>
import { computed, getCurrentInstance } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { useRoute } from 'vue-router';
import * as Icons from '@heroicons/vue/24/outline';

const props = defineProps({
  to: { type: String, required: true },
  icon: { type: String, default: '' },
  label: { type: String, required: true },
  isOpen: { type: Boolean, default: true },
  mode: { type: String, default: 'inertia' } // 'spa' or 'inertia'
});

// Environment Detection
const app = getCurrentInstance();
const hasInertia = !!app?.appContext.config.globalProperties.$inertia; 
const hasRouter = !!app?.appContext.components['RouterLink'] || !!app?.appContext.config.globalProperties.$router;

const componentType = computed(() => {
    if (props.mode === 'inertia') return Link;
    if (props.mode === 'spa') return 'router-link'; 
    return 'a';
});

const linkProps = computed(() => {
    if (props.mode === 'inertia') return { href: props.to };
    if (props.mode === 'spa') return { to: props.to };
    return { href: props.to };
});

const isActive = computed(() => {
    // 1. Inertia Active Check
    if (hasInertia) {
        const page = usePage();
        if (page?.url && props.to) {
             return page.url.startsWith(props.to) || page.url === props.to;
        }
    }
    
    // 2. Router Active Check
    if (hasRouter) {
        try {
            const route = useRoute();
            if (route?.path) {
                return route.path === props.to || (props.to !== '/' && route.path.startsWith(props.to));
            }
        } catch (e) {}
    }

    // 3. Fallback URL check
    if (typeof window !== 'undefined') {
        return window.location.pathname === props.to || (props.to !== '/' && window.location.pathname.startsWith(props.to));
    }
    
    return false;
});

const iconComponent = computed(() => {
    return Icons[props.icon] || Icons.MinusIcon;
});
</script>

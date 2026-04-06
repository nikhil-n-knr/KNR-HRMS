<template>
  <div class="relative min-h-screen bg-[#F0F7F4] font-sans text-slate-800">
    <!-- Top Nav -->
    <header class="h-20 border-b border-emerald-100 bg-white/70 backdrop-blur-xl sticky top-0 z-50 flex items-center justify-between px-6 lg:px-12">
      <div class="flex items-center gap-4">
        <Link href="/" class="flex items-center gap-2 group">
          <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center text-white shadow-lg shadow-emerald-200 group-hover:rotate-6 transition-transform">
            <i class="fas fa-rocket text-lg"></i>
          </div>
          <div class="leading-none">
            <h1 class="text-lg font-black tracking-tight text-slate-900 uppercase">Deep<span class="text-emerald-500">LMS</span></h1>
            <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Intelligence Engine</p>
          </div>
        </Link>
      </div>

      <nav class="hidden md:flex items-center gap-8">
        <Link 
            v-for="item in navItems" 
            :key="item.id" 
            :href="item.route"
            class="text-[10px] font-black uppercase tracking-[0.2em] transition-all"
            :class="route().current(item.active) ? 'text-emerald-600 border-b-2 border-emerald-600 pb-1' : 'text-slate-400 hover:text-emerald-500'"
        >
          {{ item.label }}
        </Link>
      </nav>

      <div class="flex items-center gap-4">
        <!-- User Dropdown -->
        <div class="relative group">
            <button class="flex items-center gap-3 p-1 pr-3 bg-emerald-50 rounded-full border border-emerald-100 hover:shadow-md transition-all">
                <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center text-white font-black text-xs border-2 border-white shadow-sm overflow-hidden">
                    <img v-if="$page.props.auth.user.avatar" :src="$page.props.auth.user.avatar" class="w-full h-full object-cover">
                    <span v-else>{{ $page.props.auth.user.name.charAt(0) }}</span>
                </div>
                <span class="text-[10px] font-black text-emerald-800 uppercase tracking-widest hidden md:block">{{ $page.props.auth.user.name.split(' ')[0] }}</span>
            </button>
            <!-- Basic Dropdown (can be expanded) -->
            <div class="absolute right-0 mt-2 w-48 bg-white rounded-2xl shadow-2xl border border-emerald-50 opacity-0 invisible group-hover:visible group-hover:opacity-100 transition-all z-[60] overflow-hidden">
                <Link :href="route('lms.store.profile', { user: $page.props.auth.user.id })" class="flex items-center gap-3 px-5 py-3 hover:bg-emerald-50 text-slate-600 transition-colors">
                    <i class="fas fa-user-circle text-emerald-500"></i>
                    <span class="text-[10px] font-bold uppercase tracking-widest">Public Profile</span>
                </Link>
                <Link :href="route('lms.store.logout')" method="post" as="button" class="w-full text-left flex items-center gap-3 px-5 py-3 hover:bg-rose-50 text-rose-500 transition-colors border-t border-emerald-50">
                    <i class="fas fa-power-off text-rose-400"></i>
                    <span class="text-[10px] font-bold uppercase tracking-widest">Terminate Session</span>
                </Link>
            </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-6 lg:px-12 py-10">
      <slot />
    </main>

    <!-- Global Toast (Reuse if possible, or simple local one) -->
    <div id="student-toast"></div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const navItems = [
    { id: 'hub', label: 'Learning Hub', route: route('lms.learn.hub'), active: 'lms.learn.hub' },
    { id: 'catalog', label: 'Catalog', route: route('lms.store.catalog'), active: 'lms.store.catalog' },
    { id: 'pricing', label: 'Memberships', route: route('lms.store.pricing'), active: 'lms.store.pricing' },
];
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap');
.font-sans { font-family: 'Outfit', sans-serif; }
</style>

<template>
  <div class="relative min-h-screen overflow-hidden" :class="roleTheme.pageBg">
    <!-- soft grid background -->
    <div class="absolute inset-0 opacity-[0.18]" :style="roleTheme.gridStyle"></div>

    <!-- glow orbs -->
    <div class="absolute -top-32 -left-24 h-96 w-96 rounded-full blur-3xl" :class="roleTheme.orbA"></div>
    <div class="absolute top-1/2 -right-20 h-[26rem] w-[26rem] rounded-full blur-3xl" :class="roleTheme.orbB"></div>
    <div class="absolute bottom-0 left-1/3 h-72 w-72 rounded-full blur-3xl" :class="roleTheme.orbC"></div>

    <div class="relative mx-auto flex min-h-screen w-full items-center justify-center px-4 lg:grid lg:grid-cols-[1.1fr_0.9fr] lg:gap-12">
      <!-- Left Brand / Role Panel -->
      <div class="hidden lg:flex flex-col justify-center">
        <div class="flex items-center gap-4">
          <img
            src="https://knrint-website.blr1.digitaloceanspaces.com/KNR-WEBSITE/2026/site_logo/KNR-WEBSITE_f817360c-0c15-4992-bc1b-4df24f071612_KNR-Logo.png"
            alt="KNR Logo"
            class="h-14 w-auto object-contain"
          >
          <span class="text-6xl font-black" :class="roleTheme.accentText">CONNECT</span>
        </div>

        <h1 class="mt-8 text-5xl font-black leading-tight text-slate-900">
          Unified login.
          <span class="block" :class="roleTheme.accentText">Every role.</span>
        </h1>
        <p class="mt-4 text-lg text-slate-600 max-w-md">
          A unified access point for KNR Management, KNR Team, and Clients.
        </p>

        <div class="mt-8 space-y-3">
          <button
            v-for="option in roleOptions"
            :key="option.key"
            type="button"
            @click="currentRole = option.key"
            class="w-full text-left rounded-2xl border px-4 py-3 transition-all shadow-sm"
            :class="currentRole === option.key
              ? roleTheme.roleActive
              : 'border-white/70 bg-white/60 hover:bg-white/80'"
          >
            <div class="flex items-center gap-3">
              <div
                class="h-10 w-10 rounded-xl flex items-center justify-center"
                :class="currentRole === option.key ? roleTheme.roleIconActive : roleTheme.roleIconIdle"
              >
                <i :class="option.icon"></i>
              </div>
              <div class="flex-1">
                <p class="text-sm font-black text-slate-900 uppercase tracking-wider">{{ option.label }}</p>
                <p class="text-xs text-slate-500">{{ option.desc }}</p>
              </div>
              <span
                v-if="currentRole === option.key"
                class="text-[10px] font-black uppercase tracking-widest px-2 py-1 rounded-full"
                :class="roleTheme.roleBadge"
              >Active</span>
            </div>
          </button>
        </div>

        <div class="mt-8 flex items-center gap-3 text-xs text-slate-500">
          <span class="px-3 py-1 rounded-full bg-white/70 border border-white/60">4K+ Users</span>
          <span class="px-3 py-1 rounded-full bg-white/70 border border-white/60">100% Uptime</span>
          <span class="px-3 py-1 rounded-full bg-white/70 border border-white/60">SOC2 Ready</span>
        </div>
      </div>

      <!-- Login Card -->
      <div class="w-full max-w-md justify-self-center">
        <div class="rounded-[28px] bg-white/80 backdrop-blur-xl border border-white/70 shadow-[0_20px_60px_rgba(15,23,42,0.15)] p-6 sm:p-8">
          <div class="text-center">
            <img
              src="https://knrint-website.blr1.digitaloceanspaces.com/KNR-WEBSITE/2026/site_logo/KNR-WEBSITE_f817360c-0c15-4992-bc1b-4df24f071612_KNR-Logo.png"
              alt="KNR Logo"
              class="h-12 w-auto mx-auto object-contain"
            >
            <p class="mt-2 text-[10px] font-black uppercase tracking-[0.4em]" :class="roleTheme.accentTextSoft">KNR CONNECT</p>
           <!-- <h2 class="mt-3 text-2xl font-black text-slate-900">Welcome back</h2> -->
            <p class="text-sm text-slate-500">Sign in to your workspace</p>
          </div>

          <!-- Role Pills -->
          <div class="mt-6 flex items-center gap-2 rounded-2xl bg-slate-50/80 border border-slate-100 p-1">
            <button
              v-for="option in roleOptions"
              :key="option.key + '-pill'"
              type="button"
              @click="currentRole = option.key"
              class="flex-1 rounded-xl px-2.5 py-2 text-[10px] font-black uppercase tracking-widest transition-all"
              :class="currentRole === option.key ? roleTheme.pillActive : 'text-slate-500 hover:text-slate-700'"
            >
              {{ option.short }}
            </button>
          </div>

          <form @submit.prevent="handleLogin" class="mt-6 space-y-5">
            <BaseInput
              v-model="form.email"
              class="mb-2"
              label="Work Email"
              type="email"
              placeholder="name@knrint.com"
              required
            />

            <BaseInput
              v-model="form.password"
              class="mb-1"
              label="Password"
              type="password"
              placeholder="********"
              required
            />

            <div class="flex items-center justify-between">
              <label class="flex items-center gap-2 text-xs text-slate-500 cursor-pointer">
                <input 
                  type="checkbox" 
                  v-model="form.remember"
                  class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                >
                Remember me
              </label>
              <Link href="/forgot-password" class="text-xs font-semibold" :class="roleTheme.link">
                Forgot password?
              </Link>
            </div>

            <div v-if="error" class="p-3 bg-red-50 border border-red-100 text-red-600 text-sm rounded-xl flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
              {{ error }}
            </div>

            <!-- reCAPTCHA Widget -->
            <div v-if="$page.props.recaptcha_site_key" class="flex justify-center my-4">
              <div 
                id="recaptcha-container"
                class="g-recaptcha" 
                :data-sitekey="$page.props.recaptcha_site_key"
              ></div>
            </div>

            <button
              type="submit"
              :disabled="loading"
              class="w-full rounded-2xl text-white font-black py-3 shadow-lg transition-all hover:-translate-y-0.5 active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2"
              :class="roleTheme.cta"
            >
              <svg v-if="loading" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span v-else>Sign in as {{ currentRoleLabel }}</span>
            </button>
          </form>

          <div class="mt-6 text-center text-[11px] text-slate-400">
            <p>By continuing, you agree to KNR Terms & Privacy Policy.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useAuthStore } from '@/stores/auth';
import BaseInput from '@/Components/BaseInput.vue';

const authStore = useAuthStore();

const roleOptions = [
  {
    key: 'mgmt',
    label: 'KNR Management',
    short: 'KNR Mgmt',
    desc: 'Leadership, approvals, and executive dashboards',
    icon: 'fas fa-crown'
  },
  {
    key: 'team',
    label: 'KNR Team',
    short: 'KNR Team',
    desc: 'Employees, operations, and internal tools',
    icon: 'fas fa-users'
  },
  {
    key: 'client',
    label: 'Clients',
    short: 'Clients',
    desc: 'Client access, projects, and support portals',
    icon: 'fas fa-handshake'
  }
];

const currentRole = ref('mgmt');
const currentRoleLabel = computed(() => roleOptions.find(r => r.key === currentRole.value)?.label || 'KNR');
const roleThemes = {
  mgmt: {
    pageBg: 'bg-gradient-to-br from-[#EEF1FF] via-[#F2F6FF] to-[#ECF7FF]',
    gridStyle: 'background-image: linear-gradient(to right, #c7d2fe 1px, transparent 1px), linear-gradient(to bottom, #c7d2fe 1px, transparent 1px); background-size: 28px 28px;',
    orbA: 'bg-indigo-400/25',
    orbB: 'bg-cyan-400/25',
    orbC: 'bg-emerald-300/25',
    accentText: 'text-indigo-600',
    accentTextSoft: 'text-indigo-700/70',
    roleActive: 'border-indigo-300 bg-indigo-50/80 shadow-indigo-200/50',
    roleIconActive: 'bg-indigo-200/70 text-indigo-700',
    roleIconIdle: 'bg-indigo-100/70 text-indigo-600',
    roleBadge: 'text-indigo-700 bg-indigo-100',
    pillActive: 'bg-white text-indigo-700 shadow-sm',
    link: 'text-indigo-600 hover:text-indigo-800',
    cta: 'bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 shadow-indigo-500/30 hover:shadow-indigo-500/40'
  },
  team: {
    pageBg: 'bg-gradient-to-br from-[#ECFFF7] via-[#F1FFFB] to-[#E9F7FF]',
    gridStyle: 'background-image: linear-gradient(to right, #a7f3d0 1px, transparent 1px), linear-gradient(to bottom, #a7f3d0 1px, transparent 1px); background-size: 28px 28px;',
    orbA: 'bg-emerald-400/25',
    orbB: 'bg-teal-400/25',
    orbC: 'bg-cyan-300/25',
    accentText: 'text-emerald-600',
    accentTextSoft: 'text-emerald-700/70',
    roleActive: 'border-emerald-300 bg-emerald-50/80 shadow-emerald-200/50',
    roleIconActive: 'bg-emerald-200/70 text-emerald-700',
    roleIconIdle: 'bg-emerald-100/70 text-emerald-600',
    roleBadge: 'text-emerald-700 bg-emerald-100',
    pillActive: 'bg-white text-emerald-700 shadow-sm',
    link: 'text-emerald-600 hover:text-emerald-800',
    cta: 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-emerald-500/30 hover:shadow-emerald-500/40'
  },
  client: {
    pageBg: 'bg-gradient-to-br from-[#FFF6EE] via-[#FFF7F1] to-[#F2F5FF]',
    gridStyle: 'background-image: linear-gradient(to right, #fdba74 1px, transparent 1px), linear-gradient(to bottom, #fdba74 1px, transparent 1px); background-size: 28px 28px;',
    orbA: 'bg-orange-400/25',
    orbB: 'bg-rose-400/25',
    orbC: 'bg-amber-300/25',
    accentText: 'text-orange-600',
    accentTextSoft: 'text-orange-700/70',
    roleActive: 'border-orange-300 bg-orange-50/80 shadow-orange-200/50',
    roleIconActive: 'bg-orange-200/70 text-orange-700',
    roleIconIdle: 'bg-orange-100/70 text-orange-600',
    roleBadge: 'text-orange-700 bg-orange-100',
    pillActive: 'bg-white text-orange-700 shadow-sm',
    link: 'text-orange-600 hover:text-orange-800',
    cta: 'bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 shadow-orange-500/30 hover:shadow-orange-500/40'
  }
};
const roleTheme = computed(() => roleThemes[currentRole.value] || roleThemes.mgmt);

const form = reactive({
  email: '',
  password: '',
  remember: false,
  recaptcha_token: ''
});

const renderRecaptcha = () => {
  if (window.grecaptcha && window.grecaptcha.render) {
    const container = document.getElementById('recaptcha-container');
    if (container) {
      try {
        // Clear container to ensure a fresh render on SPA navigation
        container.innerHTML = '';
        window.grecaptcha.render('recaptcha-container', {
          'sitekey': usePage().props.recaptcha_site_key,
          'callback': (token) => {
            form.recaptcha_token = token;
          },
          'expired-callback': () => {
            form.recaptcha_token = '';
          }
        });
      } catch (e) {
        // Fallback for cases where render might fail if already active
        console.warn("reCAPTCHA already rendered or error:", e);
      }
    }
  } else {
    // If grecaptcha is not yet available, try again in 500ms
    setTimeout(renderRecaptcha, 500);
  }
};

onMounted(() => {
  // Delay slightly to ensure Inertia has fully swapped the DOM
  setTimeout(renderRecaptcha, 500);
});

const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
  if (usePage().props.recaptcha_site_key && !form.recaptcha_token) {
    error.value = 'Please complete the reCAPTCHA';
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    await authStore.login(form);
    router.visit('/dashboard');
  } catch (e) {
    const errorData = e.response?.data;
    
    if (errorData?.errors) {
      // Prioritize the first validation error
      error.value = Object.values(errorData.errors).flat()[0];
    } else if (errorData?.message) {
      // Fallback to top-level message
      error.value = errorData.message;
    } else if (e.message?.includes('422')) {
      // Specifically handle the case where axios message mentions 422 but no JSON was parsed
      error.value = 'Invalid credentials. Please check your email and password.';
    } else {
      error.value = e.message || 'Login failed. Please try again.';
    }
    
    if (window.grecaptcha) window.grecaptcha.reset();
    form.recaptcha_token = '';
  } finally {
    loading.value = false;
  }
};
</script>
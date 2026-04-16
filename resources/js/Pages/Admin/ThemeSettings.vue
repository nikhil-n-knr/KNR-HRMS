<template>
  <Head title="Theme Settings" />

  <MainLayout>
    <div class="space-y-8">
      <div class="relative overflow-hidden rounded-3xl border border-white/60 bg-gradient-to-r from-emerald-50 via-white to-sky-50 p-6 shadow-sm">
        <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-emerald-300/20 blur-3xl"></div>
        <div class="absolute -left-16 -bottom-16 h-40 w-40 rounded-full bg-indigo-300/20 blur-3xl"></div>
        <div class="relative z-10 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <p class="text-[11px] font-black uppercase tracking-[0.4em] text-emerald-600">Global Theme Studio</p>
            <h1 class="mt-2 text-3xl font-black text-slate-900 tracking-tight">Theme Settings</h1>
            <p class="mt-2 text-sm text-slate-500 max-w-2xl">Design the global UI language from one place. Changes can live-preview across modules and become permanent only when saved.</p>
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <button @click="toggleLivePreview" class="px-4 py-2 rounded-xl text-[11px] font-black uppercase tracking-widest border" :class="livePreview ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200'">
              {{ livePreview ? 'Live Preview On' : 'Live Preview Off' }}
            </button>
            <button @click="discardChanges" class="px-4 py-2 rounded-xl bg-white/80 border border-white/70 text-[11px] font-black uppercase tracking-widest text-slate-600 hover:bg-white">Revert</button>
            <button @click="resetTheme" class="px-4 py-2 rounded-xl bg-rose-50 text-rose-600 text-[11px] font-black uppercase tracking-widest hover:bg-rose-100">Reset to Default</button>
            <button @click="saveTheme" class="px-4 py-2 rounded-xl bg-emerald-600 text-white text-[11px] font-black uppercase tracking-widest hover:bg-emerald-700">Save Theme</button>
          </div>
        </div>
        <div class="mt-4 flex items-center gap-3 text-[11px] font-bold uppercase tracking-widest text-slate-500">
          <span class="inline-flex items-center gap-2 rounded-full bg-white/70 px-3 py-1 text-emerald-600">
            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
            {{ hasChanges ? 'Unsaved Changes' : 'All Changes Saved' }}
          </span>
          <span>Applies across all modules after Save</span>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white/80 border border-white/70 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xs font-black uppercase tracking-widest text-slate-500">Mode</h2>
            <div class="mt-4 flex gap-3">
              <button type="button" @click="tempTheme.mode = 'light'" class="flex-1 px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest" :class="tempTheme.mode === 'light' ? 'bg-emerald-600 text-white' : 'bg-white text-slate-600 hover:bg-white/80'">
                Light Mode
              </button>
              <button type="button" @click="tempTheme.mode = 'dark'" class="flex-1 px-4 py-3 rounded-xl text-xs font-black uppercase tracking-widest" :class="tempTheme.mode === 'dark' ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:bg-white/80'">
                Dark Mode
              </button>
            </div>
          </div>

          <div class="bg-white/80 border border-white/70 rounded-2xl p-6 shadow-sm space-y-5">
            <h2 class="text-xs font-black uppercase tracking-widest text-slate-500">Core Colors</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-center justify-between rounded-xl border border-white/70 bg-white/70 px-4 py-3">
                <div>
                  <p class="text-xs font-black uppercase tracking-widest text-slate-400">Primary</p>
                  <p class="text-sm font-semibold text-slate-700">Brand Accent</p>
                </div>
                <div class="flex items-center gap-3">
                  <input v-model="tempTheme.colors.primary" type="color" class="h-8 w-10 rounded border border-slate-200" />
                  <input v-model="tempTheme.colors.primary" type="text" class="w-24 rounded-lg border border-slate-200 px-2 py-1 text-xs font-mono uppercase" />
                </div>
              </div>
              <div class="flex items-center justify-between rounded-xl border border-white/70 bg-white/70 px-4 py-3">
                <div>
                  <p class="text-xs font-black uppercase tracking-widest text-slate-400">Secondary</p>
                  <p class="text-sm font-semibold text-slate-700">Support Accent</p>
                </div>
                <div class="flex items-center gap-3">
                  <input v-model="tempTheme.colors.secondary" type="color" class="h-8 w-10 rounded border border-slate-200" />
                  <input v-model="tempTheme.colors.secondary" type="text" class="w-24 rounded-lg border border-slate-200 px-2 py-1 text-xs font-mono uppercase" />
                </div>
              </div>
              <div class="flex items-center justify-between rounded-xl border border-white/70 bg-white/70 px-4 py-3">
                <div>
                  <p class="text-xs font-black uppercase tracking-widest text-slate-400">Background</p>
                  <p class="text-sm font-semibold text-slate-700">Page Surface</p>
                </div>
                <div class="flex items-center gap-3">
                  <input v-model="tempTheme.colors.background" type="color" class="h-8 w-10 rounded border border-slate-200" />
                  <input v-model="tempTheme.colors.background" type="text" class="w-24 rounded-lg border border-slate-200 px-2 py-1 text-xs font-mono uppercase" />
                </div>
              </div>
              <div class="flex items-center justify-between rounded-xl border border-white/70 bg-white/70 px-4 py-3">
                <div>
                  <p class="text-xs font-black uppercase tracking-widest text-slate-400">Text</p>
                  <p class="text-sm font-semibold text-slate-700">Primary Copy</p>
                </div>
                <div class="flex items-center gap-3">
                  <input v-model="tempTheme.colors.text" type="color" class="h-8 w-10 rounded border border-slate-200" />
                  <input v-model="tempTheme.colors.text" type="text" class="w-24 rounded-lg border border-slate-200 px-2 py-1 text-xs font-mono uppercase" />
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white/80 border border-white/70 rounded-2xl p-6 shadow-sm space-y-5">
            <h2 class="text-xs font-black uppercase tracking-widest text-slate-500">Surfaces</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-center justify-between rounded-xl border border-white/70 bg-white/70 px-4 py-3">
                <div>
                  <p class="text-xs font-black uppercase tracking-widest text-slate-400">Header</p>
                  <p class="text-sm font-semibold text-slate-700">Top Bar Background</p>
                </div>
                <div class="flex items-center gap-3">
                  <input v-model="tempTheme.colors.headerBg" type="color" class="h-8 w-10 rounded border border-slate-200" />
                  <input v-model="tempTheme.colors.headerBg" type="text" class="w-24 rounded-lg border border-slate-200 px-2 py-1 text-xs font-mono uppercase" />
                </div>
              </div>
              <div class="flex items-center justify-between rounded-xl border border-white/70 bg-white/70 px-4 py-3">
                <div>
                  <p class="text-xs font-black uppercase tracking-widest text-slate-400">Header Text</p>
                  <p class="text-sm font-semibold text-slate-700">Top Bar Text</p>
                </div>
                <div class="flex items-center gap-3">
                  <input v-model="tempTheme.colors.headerText" type="color" class="h-8 w-10 rounded border border-slate-200" />
                  <input v-model="tempTheme.colors.headerText" type="text" class="w-24 rounded-lg border border-slate-200 px-2 py-1 text-xs font-mono uppercase" />
                </div>
              </div>
              <div class="flex items-center justify-between rounded-xl border border-white/70 bg-white/70 px-4 py-3">
                <div>
                  <p class="text-xs font-black uppercase tracking-widest text-slate-400">Sidebar</p>
                  <p class="text-sm font-semibold text-slate-700">Navigation Surface</p>
                </div>
                <div class="flex items-center gap-3">
                  <input v-model="tempTheme.colors.sidebarBg" type="color" class="h-8 w-10 rounded border border-slate-200" />
                  <input v-model="tempTheme.colors.sidebarBg" type="text" class="w-24 rounded-lg border border-slate-200 px-2 py-1 text-xs font-mono uppercase" />
                </div>
              </div>
              <div class="flex items-center justify-between rounded-xl border border-white/70 bg-white/70 px-4 py-3">
                <div>
                  <p class="text-xs font-black uppercase tracking-widest text-slate-400">Sidebar Text</p>
                  <p class="text-sm font-semibold text-slate-700">Navigation Text</p>
                </div>
                <div class="flex items-center gap-3">
                  <input v-model="tempTheme.colors.sidebarText" type="color" class="h-8 w-10 rounded border border-slate-200" />
                  <input v-model="tempTheme.colors.sidebarText" type="text" class="w-24 rounded-lg border border-slate-200 px-2 py-1 text-xs font-mono uppercase" />
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white/80 border border-white/70 rounded-2xl p-6 shadow-sm space-y-5">
            <h2 class="text-xs font-black uppercase tracking-widest text-slate-500">Buttons</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex items-center justify-between rounded-xl border border-white/70 bg-white/70 px-4 py-3">
                <div>
                  <p class="text-xs font-black uppercase tracking-widest text-slate-400">Primary</p>
                  <p class="text-sm font-semibold text-slate-700">Primary Button</p>
                </div>
                <div class="flex items-center gap-3">
                  <input v-model="tempTheme.colors.buttonPrimaryBg" type="color" class="h-8 w-10 rounded border border-slate-200" />
                  <input v-model="tempTheme.colors.buttonPrimaryBg" type="text" class="w-24 rounded-lg border border-slate-200 px-2 py-1 text-xs font-mono uppercase" />
                </div>
              </div>
              <div class="flex items-center justify-between rounded-xl border border-white/70 bg-white/70 px-4 py-3">
                <div>
                  <p class="text-xs font-black uppercase tracking-widest text-slate-400">Primary Text</p>
                  <p class="text-sm font-semibold text-slate-700">Button Text</p>
                </div>
                <div class="flex items-center gap-3">
                  <input v-model="tempTheme.colors.buttonPrimaryText" type="color" class="h-8 w-10 rounded border border-slate-200" />
                  <input v-model="tempTheme.colors.buttonPrimaryText" type="text" class="w-24 rounded-lg border border-slate-200 px-2 py-1 text-xs font-mono uppercase" />
                </div>
              </div>
              <div class="flex items-center justify-between rounded-xl border border-white/70 bg-white/70 px-4 py-3">
                <div>
                  <p class="text-xs font-black uppercase tracking-widest text-slate-400">Secondary</p>
                  <p class="text-sm font-semibold text-slate-700">Secondary Button</p>
                </div>
                <div class="flex items-center gap-3">
                  <input v-model="tempTheme.colors.buttonSecondaryBg" type="color" class="h-8 w-10 rounded border border-slate-200" />
                  <input v-model="tempTheme.colors.buttonSecondaryBg" type="text" class="w-24 rounded-lg border border-slate-200 px-2 py-1 text-xs font-mono uppercase" />
                </div>
              </div>
              <div class="flex items-center justify-between rounded-xl border border-white/70 bg-white/70 px-4 py-3">
                <div>
                  <p class="text-xs font-black uppercase tracking-widest text-slate-400">Secondary Text</p>
                  <p class="text-sm font-semibold text-slate-700">Button Text</p>
                </div>
                <div class="flex items-center gap-3">
                  <input v-model="tempTheme.colors.buttonSecondaryText" type="color" class="h-8 w-10 rounded border border-slate-200" />
                  <input v-model="tempTheme.colors.buttonSecondaryText" type="text" class="w-24 rounded-lg border border-slate-200 px-2 py-1 text-xs font-mono uppercase" />
                </div>
              </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
              <button class="btn-primary text-[11px] font-black uppercase tracking-widest">Primary Action</button>
              <button class="btn-secondary text-[11px] font-black uppercase tracking-widest">Secondary Action</button>
            </div>
          </div>

          <div class="bg-white/80 border border-white/70 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xs font-black uppercase tracking-widest text-slate-500">Typography</h2>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
              <label class="block">
                <span class="text-xs font-bold uppercase tracking-widest text-slate-500">Font Family</span>
                <select v-model="tempTheme.typography.fontFamily" class="mt-2 w-full rounded-xl border border-slate-200 bg-white/80 px-3 py-2 text-sm">
                  <option v-for="font in fontFamilies" :key="font" :value="font">{{ font }}</option>
                </select>
              </label>
              <label class="block">
                <span class="text-xs font-bold uppercase tracking-widest text-slate-500">Font Size</span>
                <select v-model="tempTheme.typography.fontSize" class="mt-2 w-full rounded-xl border border-slate-200 bg-white/80 px-3 py-2 text-sm">
                  <option value="small">Small</option>
                  <option value="medium">Medium</option>
                  <option value="large">Large</option>
                </select>
              </label>
            </div>
          </div>

          <div class="bg-white/80 border border-white/70 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xs font-black uppercase tracking-widest text-slate-500">Components</h2>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Button Shape</p>
                <div class="mt-2 flex gap-3">
                  <button type="button" @click="tempTheme.components.buttonStyle = 'rounded'" class="flex-1 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest" :class="tempTheme.components.buttonStyle === 'rounded' ? 'bg-emerald-600 text-white' : 'bg-white/80 text-slate-600 hover:bg-white'">
                    Rounded
                  </button>
                  <button type="button" @click="tempTheme.components.buttonStyle = 'square'" class="flex-1 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest" :class="tempTheme.components.buttonStyle === 'square' ? 'bg-slate-900 text-white' : 'bg-white/80 text-slate-600 hover:bg-white'">
                    Square
                  </button>
                </div>
              </div>
              <div>
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Card Style</p>
                <div class="mt-2 flex gap-3">
                  <button type="button" @click="tempTheme.components.cardStyle = 'shadow'" class="flex-1 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest" :class="tempTheme.components.cardStyle === 'shadow' ? 'bg-emerald-600 text-white' : 'bg-white/80 text-slate-600 hover:bg-white'">
                    Shadow
                  </button>
                  <button type="button" @click="tempTheme.components.cardStyle = 'flat'" class="flex-1 px-4 py-2 rounded-xl text-xs font-black uppercase tracking-widest" :class="tempTheme.components.cardStyle === 'flat' ? 'bg-slate-900 text-white' : 'bg-white/80 text-slate-600 hover:bg-white'">
                    Flat
                  </button>
                </div>
              </div>
              <div class="md:col-span-2">
                <label class="block">
                  <span class="text-xs font-bold uppercase tracking-widest text-slate-500">Border Radius</span>
                  <input v-model.number="tempTheme.components.borderRadius" type="range" min="0" max="24" class="mt-3 w-full" />
                  <p class="text-xs text-slate-500 mt-1">{{ tempTheme.components.borderRadius }}px</p>
                </label>
              </div>
            </div>
          </div>

          <div class="bg-white/80 border border-white/70 rounded-2xl p-6 shadow-sm">
            <h2 class="text-xs font-black uppercase tracking-widest text-slate-500">Layout</h2>
            <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
              <label class="block">
                <span class="text-xs font-bold uppercase tracking-widest text-slate-500">Sidebar Style</span>
                <select v-model="tempTheme.layout.sidebarStyle" class="mt-2 w-full rounded-xl border border-slate-200 bg-white/80 px-3 py-2 text-sm">
                  <option value="expanded">Expanded</option>
                  <option value="collapsed">Collapsed</option>
                </select>
              </label>
              <label class="block">
                <span class="text-xs font-bold uppercase tracking-widest text-slate-500">Layout Width</span>
                <select v-model="tempTheme.layout.layoutWidth" class="mt-2 w-full rounded-xl border border-slate-200 bg-white/80 px-3 py-2 text-sm">
                  <option value="full">Full Width</option>
                  <option value="boxed">Boxed</option>
                </select>
              </label>
              <label class="block">
                <span class="text-xs font-bold uppercase tracking-widest text-slate-500">Navbar Style</span>
                <select v-model="tempTheme.layout.navbarStyle" class="mt-2 w-full rounded-xl border border-slate-200 bg-white/80 px-3 py-2 text-sm">
                  <option value="sticky">Sticky</option>
                  <option value="static">Static</option>
                </select>
              </label>
            </div>
          </div>
        </div>

        <div class="bg-white/80 border border-white/70 rounded-2xl p-6 shadow-sm">
          <h2 class="text-xs font-black uppercase tracking-widest text-slate-500">Live Preview</h2>
          <div class="mt-4 rounded-2xl border border-white/60 overflow-hidden" :class="tempTheme.mode === 'dark' ? 'theme-dark' : ''" :style="previewStyle">
            <div class="p-5" :style="{ background: 'var(--bg-page)', color: 'var(--text-primary)', fontFamily: 'var(--font-sans)', fontSize: 'var(--base-font-size)' }">
              <div class="flex items-center justify-between rounded-2xl px-4 py-3" :style="{ background: 'var(--header-bg)', color: 'var(--header-text)' }">
                <div>
                  <p class="text-[10px] uppercase tracking-widest" :style="{ color: 'var(--text-secondary)' }">Preview</p>
                  <h3 class="text-lg font-black">KNR HRMS</h3>
                </div>
                <button class="px-4 py-2 text-xs font-black uppercase tracking-widest" :style="previewButtonStyle">Primary Action</button>
              </div>

              <div class="mt-4 grid grid-cols-1 gap-3">
                <div class="p-4" :style="previewCardStyle">
                  <p class="text-xs uppercase tracking-widest" :style="{ color: 'var(--text-secondary)' }">Card</p>
                  <p class="mt-2 text-sm">This card previews radius, shadow, and typography.</p>
                </div>
                <div class="p-4" :style="previewCardStyle">
                  <p class="text-xs uppercase tracking-widest" :style="{ color: 'var(--text-secondary)' }">Secondary</p>
                  <p class="mt-2 text-sm">Secondary color sample</p>
                  <div class="mt-3 h-2 rounded-full" :style="{ background: 'var(--secondary-color)' }"></div>
                </div>
                <div class="rounded-2xl border border-white/60 p-3" :style="{ background: 'var(--sidebar-bg)', color: 'var(--sidebar-text)' }">
                  <p class="text-[10px] font-black uppercase tracking-widest">Sidebar Preview</p>
                  <p class="text-xs opacity-80">Navigation labels follow this surface</p>
                </div>
                <div class="flex gap-2">
                  <button class="btn-primary text-[10px] font-black uppercase tracking-widest">Primary</button>
                  <button class="btn-secondary text-[10px] font-black uppercase tracking-widest">Secondary</button>
                </div>
              </div>
            </div>
          </div>
          <p class="text-[11px] text-slate-400 mt-3">Live preview reflects your inputs. Save to apply everywhere permanently.</p>
        </div>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount, watch, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import { useThemeStore } from '@/stores/theme';
import { useToastStore } from '@/stores/toast';

const themeStore = useThemeStore();
const toastStore = useToastStore();
const tempTheme = themeStore.tempTheme;
const livePreview = ref(true);

const fontFamilies = ['Poppins', 'Inter', 'Roboto', 'Montserrat', 'Lato', 'System'];

const fontStack = computed(() => {
  if (tempTheme.typography.fontFamily === 'System') {
    return 'ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif';
  }
  return `${tempTheme.typography.fontFamily}, sans-serif`;
});

const previewStyle = computed(() => {
  const baseFont = tempTheme.typography.fontSize === 'small' ? '14px' : tempTheme.typography.fontSize === 'large' ? '18px' : '16px';
  const radius = Math.max(0, Math.min(24, tempTheme.components.borderRadius || 12));
  const btnRadius = tempTheme.components.buttonStyle === 'square' ? 6 : Math.max(6, radius);
  const cardShadow = tempTheme.components.cardStyle === 'flat' ? 'none' : '0 8px 28px rgba(15, 23, 42, 0.08)';

  return {
    '--bg-page': tempTheme.colors.background,
    '--bg-card': tempTheme.mode === 'dark' ? '#111827' : '#FFFFFF',
    '--text-primary': tempTheme.colors.text,
    '--text-secondary': tempTheme.mode === 'dark' ? '#94A3B8' : '#475569',
    '--border-color': tempTheme.mode === 'dark' ? '#1F2937' : '#E2E8F0',
    '--primary-color': tempTheme.colors.primary,
    '--secondary-color': tempTheme.colors.secondary,
    '--font-sans': fontStack.value,
    '--base-font-size': baseFont,
    '--radius-card': `${radius}px`,
    '--radius-btn': `${btnRadius}px`,
    '--card-shadow': cardShadow,
    '--header-bg': tempTheme.colors.headerBg,
    '--header-text': tempTheme.colors.headerText,
    '--sidebar-bg': tempTheme.colors.sidebarBg,
    '--sidebar-text': tempTheme.colors.sidebarText,
    '--btn-primary-bg': tempTheme.colors.buttonPrimaryBg,
    '--btn-primary-text': tempTheme.colors.buttonPrimaryText,
    '--btn-secondary-bg': tempTheme.colors.buttonSecondaryBg,
    '--btn-secondary-text': tempTheme.colors.buttonSecondaryText
  };
});

const previewCardStyle = computed(() => ({
  background: 'var(--bg-card)',
  border: '1px solid var(--border-color)',
  borderRadius: 'var(--radius-card)',
  boxShadow: 'var(--card-shadow)',
  color: 'var(--text-primary)'
}));

const previewButtonStyle = computed(() => ({
  background: 'var(--btn-primary-bg)',
  color: 'var(--btn-primary-text)',
  borderRadius: 'var(--radius-btn)'
}));

const hasChanges = computed(() => {
  return JSON.stringify(themeStore.currentTheme) !== JSON.stringify(tempTheme);
});

const applyPreview = () => {
  if (livePreview.value) {
    themeStore.applyTheme(tempTheme);
  }
};

const toggleLivePreview = () => {
  livePreview.value = !livePreview.value;
  if (!livePreview.value) {
    themeStore.applyTheme(themeStore.currentTheme);
  } else {
    themeStore.applyTheme(tempTheme);
  }
};

const saveTheme = () => {
  themeStore.saveTheme();
  toastStore.success('Theme saved successfully.');
};

const resetTheme = () => {
  themeStore.resetTheme();
  toastStore.info('Theme reset to default.');
};

const discardChanges = () => {
  themeStore.discardChanges();
  themeStore.applyTheme(themeStore.currentTheme);
  toastStore.info('Changes reverted to last saved theme.');
};

onMounted(() => {
  themeStore.discardChanges();
  themeStore.applyTheme(themeStore.currentTheme);
});

watch(tempTheme, applyPreview, { deep: true });

onBeforeUnmount(() => {
  themeStore.applyTheme(themeStore.currentTheme);
});
</script>

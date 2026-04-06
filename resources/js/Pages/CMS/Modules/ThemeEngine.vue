<template>
  <div class="h-full flex flex-col bg-gray-50">
    <!-- Header -->
    <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-6 shrink-0 shadow-sm">
      <div>
        <h2 class="text-xl font-black text-gray-900 tracking-tight">Theme Studio</h2>
        <p class="text-xs text-gray-500 mt-0.5">50+ pre-built themes · Full color, font, and button customisation</p>
      </div>
      <div class="flex items-center gap-2">
        <label class="flex items-center gap-2 cursor-pointer px-3 py-1.5 bg-gray-900 rounded-xl">
          <i class="fas fa-moon text-yellow-400 text-xs"></i>
          <span class="text-white text-xs font-bold">Dark</span>
          <div class="relative">
            <input type="checkbox" v-model="config.darkMode" class="sr-only peer" />
            <div class="w-9 h-4 bg-gray-700 peer-checked:bg-indigo-500 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-3 after:h-3 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5"></div>
          </div>
        </label>
        <button @click="saveTheme" :disabled="saving" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all flex items-center gap-2 disabled:opacity-50">
          <i v-if="saving" class="fas fa-spinner fa-spin text-xs"></i>
          <i v-else class="fas fa-save text-xs"></i> Save Theme
        </button>
      </div>
    </div>

    <div class="flex flex-1 overflow-hidden">
      <!-- Left Panel: Tabs -->
      <div class="w-80 border-r border-gray-200 bg-white flex flex-col shrink-0 overflow-hidden">
        <!-- Tab bar -->
        <div class="flex gap-0 border-b border-gray-200 shrink-0">
          <button v-for="t in tabs" :key="t.key" @click="panelTab=t.key"
            class="flex-1 py-3 text-sm font-black uppercase tracking-wider transition-all border-b-2 -mb-px"
            :class="panelTab===t.key?'border-indigo-600 text-indigo-700':'border-transparent text-gray-400 hover:text-gray-700'">
            <i :class="t.icon+' block text-sm mb-0.5'"></i>{{ t.label }}
          </button>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3">

          <!-- ─── THEMES TAB ─── -->
          <template v-if="panelTab==='themes'">
            <div class="relative mb-2">
              <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
              <input v-model="themeSearch" placeholder="Search themes..." class="w-full bg-gray-50 border border-gray-200 rounded-xl py-2 pl-8 pr-3 text-xs focus:outline-none focus:border-indigo-400" />
            </div>
            <!-- Category filter -->
            <div class="flex gap-1.5 flex-wrap mb-2">
              <button v-for="cat in themeCats" :key="cat" @click="themeCatFilter=cat"
                class="px-2.5 py-1 rounded-full text-sm font-black transition-all"
                :class="themeCatFilter===cat?'bg-indigo-600 text-white':'bg-gray-100 text-gray-600 hover:bg-gray-200'">{{ cat }}</button>
            </div>
            <div class="grid grid-cols-2 gap-2">
              <button v-for="t in filteredThemes" :key="t.name" @click="applyTheme(t)"
                class="p-3 rounded-xl border-2 text-left transition-all hover:scale-105 hover:shadow-md"
                :class="activeThemeName===t.name?'border-indigo-500 shadow-md bg-indigo-50':'border-gray-200 hover:border-gray-300'">
                <div class="flex gap-1 mb-1.5">
                  <div v-for="c in [t.primary, t.secondary, t.accent, t.bg]" :key="c" class="flex-1 h-3 rounded" :style="{background:c}"></div>
                </div>
                <p class="text-sm font-black text-gray-700 leading-tight">{{ t.name }}</p>
                <p class="text-xs text-gray-400">{{ t.cat }}</p>
              </button>
            </div>
          </template>

          <!-- ─── COLORS TAB ─── -->
          <template v-if="panelTab==='colors'">
            <div v-for="c in colorFields" :key="c.key" class="space-y-1">
              <label class="text-sm font-black uppercase tracking-wider text-gray-500">{{ c.label }}</label>
              <div class="flex items-center gap-2">
                <input type="color" :value="config.colors[c.key]" @input="config.colors[c.key]=$event.target.value"
                  class="w-10 h-10 rounded-xl border border-gray-200 cursor-pointer p-0.5 bg-white" />
                <input type="text" :value="config.colors[c.key]" @input="config.colors[c.key]=$event.target.value"
                  class="flex-1 font-mono text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none focus:border-indigo-400" />
                <button @click="config.colors[c.key]=c.default" class="w-8 h-8 bg-gray-100 rounded-lg hover:bg-gray-200 flex items-center justify-center">
                  <i class="fas fa-undo text-sm text-gray-500"></i>
                </button>
              </div>
            </div>
            <!-- Gradient builder -->
            <div class="pt-3 border-t border-gray-100 space-y-2">
              <label class="text-sm font-black uppercase tracking-wider text-gray-500">Hero Gradient</label>
              <div class="flex gap-2">
                <input type="color" v-model="config.gradientFrom" class="w-10 h-10 rounded-xl border border-gray-200 cursor-pointer p-0.5" />
                <input type="color" v-model="config.gradientTo"   class="w-10 h-10 rounded-xl border border-gray-200 cursor-pointer p-0.5" />
                <div class="flex-1 h-10 rounded-xl border border-gray-200" :style="{background:`linear-gradient(135deg,${config.gradientFrom},${config.gradientTo})`}"></div>
              </div>
              <select v-model="config.gradientDir" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none">
                <option value="to right">→ Left to Right</option>
                <option value="to bottom right">↘ Diagonal</option>
                <option value="135deg">135° Diagonal</option>
                <option value="to bottom">↓ Top to Bottom</option>
              </select>
            </div>
          </template>

          <!-- ─── TYPOGRAPHY TAB ─── -->
          <template v-if="panelTab==='typo'">
            <div v-for="f in fontFields" :key="f.key">
              <label class="text-sm font-black uppercase tracking-wider text-gray-500 block mb-1">{{ f.label }}</label>
              <select v-model="config.typography[f.key]" @change="loadFont(config.typography[f.key])" class="w-full text-sm bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none focus:border-indigo-400">
                <optgroup label="Sans Serif">
                  <option v-for="font in sansFonts" :key="font" :value="font" :style="{fontFamily:font}">{{ font }}</option>
                </optgroup>
                <optgroup label="Serif">
                  <option v-for="font in serifFonts" :key="font" :value="font" :style="{fontFamily:font}">{{ font }}</option>
                </optgroup>
                <optgroup label="Display / Decorative">
                  <option v-for="font in displayFonts" :key="font" :value="font" :style="{fontFamily:font}">{{ font }}</option>
                </optgroup>
              </select>
              <!-- Font preview -->
              <div class="mt-1 p-2 bg-gray-50 rounded-lg text-xs border border-gray-100" :style="{fontFamily:config.typography[f.key]}">
                The quick brown fox
              </div>
            </div>
            <div>
              <label class="text-sm font-black uppercase tracking-wider text-gray-500 block mb-1">Base Font Size</label>
              <input type="range" min="14" max="20" v-model="config.typography.base_size" class="w-full accent-indigo-600" />
              <p class="text-xs text-gray-400 text-right mt-0.5">{{ config.typography.base_size }}px</p>
            </div>
            <div>
              <label class="text-sm font-black uppercase tracking-wider text-gray-500 block mb-1">Line Height</label>
              <input type="range" min="1.2" max="2.0" step="0.05" v-model="config.typography.line_height" class="w-full accent-indigo-600" />
              <p class="text-xs text-gray-400 text-right mt-0.5">{{ parseFloat(config.typography.line_height).toFixed(2) }}</p>
            </div>
            <div>
              <label class="text-sm font-black uppercase tracking-wider text-gray-500 block mb-1">Letter Spacing</label>
              <select v-model="config.typography.letter_spacing" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none">
                <option value="-0.05em">Tight</option><option value="0">Normal</option><option value="0.025em">Wide</option><option value="0.1em">Widest</option>
              </select>
            </div>
          </template>

          <!-- ─── BUTTONS TAB ─── -->
          <template v-if="panelTab==='buttons'">
            <div>
              <label class="text-sm font-black uppercase tracking-wider text-gray-500 block mb-2">Border Radius</label>
              <div class="grid grid-cols-3 gap-1.5">
                <button v-for="r in radiusOptions" :key="r.label" @click="config.buttons.radius=r.value"
                  class="py-2 border-2 text-xs font-bold rounded transition-all"
                  :class="config.buttons.radius===r.value?'border-indigo-600 bg-indigo-50 text-indigo-700':'border-gray-200 text-gray-600'"
                  :style="{borderRadius:r.value}">{{ r.label }}</button>
              </div>
            </div>
            <div>
              <label class="text-sm font-black uppercase tracking-wider text-gray-500 block mb-2">Button Style</label>
              <div class="grid grid-cols-2 gap-2">
                <button v-for="s in buttonStyles" :key="s.key" @click="config.buttons.style=s.key"
                  class="p-3 border-2 rounded-xl text-xs font-bold transition-all"
                  :class="config.buttons.style===s.key?'border-indigo-500 bg-indigo-50':'border-gray-200 hover:border-gray-300'">
                  <span class="block text-center px-3 py-1 mb-1.5 text-sm" :class="s.cls" :style="{borderRadius:config.buttons.radius, backgroundColor: s.key==='filled'?config.colors.primary:'', borderColor: s.key!=='filled'?config.colors.primary:'', color: s.key==='filled'?'#fff':config.colors.primary}">{{ s.preview }}</span>
                  <span class="text-sm text-gray-500">{{ s.label }}</span>
                </button>
              </div>
            </div>
            <div>
              <label class="text-sm font-black uppercase tracking-wider text-gray-500 block mb-2">Shadow</label>
              <select v-model="config.buttons.shadow" class="w-full text-xs bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 outline-none">
                <option value="none">None</option>
                <option value="sm">Small</option>
                <option value="md">Medium</option>
                <option value="lg">Large</option>
                <option value="xl">Dramatic</option>
              </select>
            </div>
            <div>
              <label class="text-sm font-black uppercase tracking-wider text-gray-500 block mb-2">Button Size</label>
              <div class="flex gap-1.5">
                <button v-for="sz in ['sm','md','lg','xl']" :key="sz" @click="config.buttons.size=sz"
                  class="flex-1 py-1.5 border-2 text-xs font-bold rounded-lg transition-all"
                  :class="config.buttons.size===sz?'border-indigo-600 bg-indigo-50 text-indigo-700':'border-gray-200 text-gray-500'">{{ sz.toUpperCase() }}</button>
              </div>
            </div>
            <!-- 50 button variants preview -->
            <div class="pt-3 border-t border-gray-100">
              <label class="text-sm font-black uppercase tracking-wider text-gray-500 block mb-2">50+ Variants Preview</label>
              <div class="grid grid-cols-3 gap-1.5 max-h-64 overflow-y-auto">
                <button v-for="v in buttonVariants" :key="v.label" @click="applyButtonVariant(v)"
                  class="px-2 py-1.5 text-xs font-black border transition-all hover:scale-105"
                  :style="v.style" :title="v.label">{{ v.label }}</button>
              </div>
            </div>
          </template>

          <!-- ─── CSS TAB ─── -->
          <template v-if="panelTab==='css'">
            <div class="space-y-2">
              <label class="text-sm font-black uppercase tracking-wider text-gray-500 block">CSS Variables</label>
              <div v-for="v in cssVars" :key="v.var" class="flex items-center justify-between gap-2 py-1.5 border-b border-gray-50">
                <code class="text-sm text-purple-600 font-mono bg-purple-50 px-1.5 py-0.5 rounded">{{ v.var }}</code>
                <input :value="getCssVar(v.var)" @input="setCssVar(v.var, $event.target.value)"
                  class="flex-1 text-sm font-mono bg-gray-50 border border-gray-200 rounded-lg px-2 py-1 outline-none focus:border-indigo-400 min-w-0" />
              </div>
            </div>
            <div class="pt-3 border-t border-gray-100">
              <label class="text-sm font-black uppercase tracking-wider text-gray-500 block mb-1">Custom CSS</label>
              <textarea v-model="config.customCss" rows="8" class="w-full font-mono text-sm bg-gray-900 text-green-400 border border-gray-800 rounded-xl p-3 outline-none resize-none" placeholder="/* Add custom CSS here */&#10;.hero { padding: 4rem 0; }"></textarea>
            </div>
          </template>

        </div>
      </div>

      <!-- Right: Live Preview -->
      <div class="flex-1 overflow-y-auto p-5 space-y-4">
        <!-- Preview header bar -->
        <div class="flex items-center justify-between">
          <div class="flex gap-1.5 bg-gray-200 p-1 rounded-xl text-xs font-bold">
            <button @click="previewWidth='w-full'" :class="previewWidth==='w-full'?'bg-white shadow px-3 py-1 rounded-lg':'px-3 py-1 text-gray-500 hover:text-gray-700'"><i class="fas fa-desktop"></i></button>
            <button @click="previewWidth='max-w-xl mx-auto'" :class="previewWidth!=='w-full'&&previewWidth!=='w-80'?'bg-white shadow px-3 py-1 rounded-lg':'px-3 py-1 text-gray-500 hover:text-gray-700'"><i class="fas fa-tablet-alt"></i></button>
            <button @click="previewWidth='w-80 mx-auto'" :class="previewWidth==='w-80'?'bg-white shadow px-3 py-1 rounded-lg':'px-3 py-1 text-gray-500 hover:text-gray-700'"><i class="fas fa-mobile-alt"></i></button>
          </div>
          <div class="flex items-center gap-2 text-xs font-bold text-gray-400">
            <div class="w-2 h-2 rounded-full animate-pulse" :style="{background: config.colors.primary}"></div>
            Live Preview — {{ activeThemeName }}
          </div>
        </div>

        <!-- Browser chrome -->
        <div class="rounded-2xl overflow-hidden border border-gray-300 shadow-xl" :class="previewWidth">
          <div class="h-10 bg-gray-100 flex items-center px-4 gap-2 border-b border-gray-200 shrink-0">
            <div class="w-3 h-3 rounded-full bg-red-400"></div>
            <div class="w-3 h-3 rounded-full bg-amber-400"></div>
            <div class="w-3 h-3 rounded-full bg-emerald-400"></div>
            <div class="ml-3 flex-1 bg-white rounded-md h-5 text-sm flex items-center px-3 text-gray-400 font-mono border border-gray-200">preview.KNR Office.in</div>
          </div>

          <!-- Actual preview -->
          <div :style="pageStyle" class="transition-all duration-500">
            <!-- Navbar -->
            <nav class="flex items-center justify-between px-6 py-4" :style="navStyle">
              <span class="font-black text-lg" :style="{color: config.colors.primary}">⚡ YourBrand</span>
              <div class="hidden md:flex gap-6 text-sm font-semibold" :style="{color: config.colors.text}">
                <span class="cursor-pointer hover:opacity-75">Features</span>
                <span class="cursor-pointer hover:opacity-75">Pricing</span>
                <span class="cursor-pointer hover:opacity-75">Blog</span>
                <span class="cursor-pointer hover:opacity-75">About</span>
              </div>
              <button class="text-sm font-black px-5 py-2" :style="ctaButtonStyle">Get Started Free</button>
            </nav>

            <!-- Hero -->
            <div class="px-6 py-16 text-center" :style="heroStyle">
              <div class="inline-block px-3 py-1 rounded-full text-xs font-bold mb-4" :style="{background: config.colors.primary+'22', color: config.colors.primary}">
                🚀 New: AI-powered builder is live
              </div>
              <h1 class="text-4xl font-black mb-4 leading-tight" :style="{fontFamily: config.typography.font_heading + ',sans-serif', color: config.darkMode ? '#f1f5f9' : config.colors.heading}">
                Build stunning websites<br/>in minutes, not weeks
              </h1>
              <p class="text-base opacity-70 mb-8 max-w-md mx-auto" :style="{fontFamily: config.typography.font_body + ',sans-serif', color: config.colors.text, fontSize: config.typography.base_size + 'px', lineHeight: config.typography.line_height}">
                The all-in-one CMS with drag-and-drop builder, ecommerce, AI tools, and 50+ stunning themes.
              </p>
              <div class="flex gap-3 justify-center flex-wrap">
                <button class="font-black px-8 py-3 text-sm" :style="ctaButtonStyle">Start Free Trial</button>
                <button class="font-bold px-8 py-3 text-sm border-2" :style="outlineButtonStyle">Watch Demo</button>
              </div>
            </div>

            <!-- Feature cards -->
            <div class="px-6 py-10">
              <div class="grid grid-cols-3 gap-4">
                <div v-for="feat in ['Drag & Drop Builder','50+ Themes','AI Content'] " :key="feat" class="rounded-2xl p-5 border" :style="cardStyle">
                  <div class="w-10 h-10 rounded-xl mb-3 flex items-center justify-center" :style="{background: config.colors.primary+'22'}">
                    <i class="fas fa-check" :style="{color: config.colors.primary}"></i>
                  </div>
                  <h3 class="font-black text-sm mb-1" :style="{color: config.darkMode ? '#f1f5f9' : config.colors.heading}">{{ feat }}</h3>
                  <p class="text-xs opacity-60" :style="{color: config.colors.text}">Full-featured and production-ready out of the box.</p>
                </div>
              </div>
            </div>

            <!-- CTA Banner -->
            <div class="mx-6 mb-8 rounded-2xl p-8 text-center" :style="{background:`linear-gradient(135deg,${config.gradientFrom},${config.gradientTo})`}">
              <h2 class="text-xl font-black text-white mb-2">Ready to get started?</h2>
              <p class="text-white/70 text-sm mb-4">Join 10,000+ teams building with our platform.</p>
              <button class="bg-white font-black px-6 py-2.5 text-sm" :style="{borderRadius: config.buttons.radius, color: config.colors.primary}">Start for Free</button>
            </div>

            <!-- Footer -->
            <footer class="px-6 py-6 border-t" :style="footerStyle">
              <div class="flex items-center justify-between">
                <span class="font-black text-sm" :style="{color: config.colors.primary}">⚡ YourBrand</span>
                <p class="text-xs opacity-50" :style="{color: config.colors.text}">© 2026 YourBrand. All rights reserved.</p>
              </div>
            </footer>
          </div>
        </div>

        <!-- CSS Variables panel -->
        <div class="bg-gray-900 rounded-2xl p-4">
          <p class="text-xs font-black text-gray-400 mb-2 uppercase tracking-widest">Generated CSS Variables</p>
          <pre class="text-sm text-green-400 font-mono overflow-x-auto leading-relaxed"><code>{{ generatedCss }}</code></pre>
        </div>
      </div>
    </div>

    <transition enter-active-class="transition" enter-from-class="opacity-0 translate-y-4" leave-active-class="transition" leave-to-class="opacity-0 translate-y-4">
      <div v-if="toast" class="fixed bottom-8 left-1/2 -translate-x-1/2 bg-indigo-600 text-white px-6 py-3 rounded-xl shadow-xl text-sm font-black flex items-center gap-2 z-50">
        <i class="fas fa-check-circle"></i> Theme saved!
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import axios from 'axios';

const props = defineProps({ themes: { type: Array, default: () => [] }, active_theme: { type: Object, default: null } });
const saving      = ref(false);
const toast       = ref(false);
const panelTab    = ref('themes');
const themeSearch = ref('');
const themeCatFilter = ref('All');
const previewWidth   = ref('w-full');
const activeThemeName = ref('Default Indigo');

const tabs = [
  { key:'themes',  label:'Themes',  icon:'fas fa-palette'     },
  { key:'colors',  label:'Colors',  icon:'fas fa-fill-drip'   },
  { key:'typo',    label:'Fonts',   icon:'fas fa-font'        },
  { key:'buttons', label:'Buttons', icon:'fas fa-mouse-pointer'},
  { key:'css',     label:'CSS',     icon:'fas fa-code'        },
];

const config = reactive({
  darkMode: false,
  colors: {
    primary:   '#6366f1', secondary: '#8b5cf6', accent: '#10b981',
    heading:   '#111827', text: '#374151',       bg: '#ffffff',
    card:      '#f9fafb', border: '#e5e7eb',     nav: '#ffffff',
  },
  gradientFrom: '#6366f1',
  gradientTo:   '#8b5cf6',
  gradientDir:  '135deg',
  typography: { font_heading:'Inter', font_body:'Inter', base_size:16, line_height:1.6, letter_spacing:'0' },
  buttons: { radius:'0.75rem', style:'filled', shadow:'md', size:'md' },
  customCss: '',
  cssVarsMap: {},
});

// ─── THEMES ───
const themeCats = ['All','SaaS','EdTech','E-commerce','Corporate','Agency','Portfolio','Dark','Minimal'];

const presets = [
  // SaaS
  { name:'Default Indigo',  cat:'SaaS',   primary:'#6366f1',secondary:'#8b5cf6',accent:'#10b981',heading:'#111827',text:'#374151',bg:'#ffffff',card:'#f9fafb',nav:'#ffffff',gradFrom:'#6366f1',gradTo:'#8b5cf6'},
  { name:'Azure Wave',      cat:'SaaS',   primary:'#0ea5e9',secondary:'#2563eb',accent:'#06b6d4',heading:'#082f49',text:'#0c4a6e',bg:'#f0f9ff',card:'#e0f2fe',nav:'#ffffff',gradFrom:'#0ea5e9',gradTo:'#2563eb'},
  { name:'Teal Horizon',    cat:'SaaS',   primary:'#14b8a6',secondary:'#0d9488',accent:'#6366f1',heading:'#134e4a',text:'#0f766e',bg:'#f0fdfa',card:'#ccfbf1',nav:'#ffffff',gradFrom:'#14b8a6',gradTo:'#6366f1'},
  { name:'Purple Neon',     cat:'SaaS',   primary:'#a855f7',secondary:'#ec4899',accent:'#06b6d4',heading:'#3b0764',text:'#6b21a8',bg:'#fdf4ff',card:'#f3e8ff',nav:'#ffffff',gradFrom:'#a855f7',gradTo:'#ec4899'},
  { name:'Emerald Fresh',   cat:'SaaS',   primary:'#10b981',secondary:'#059669',accent:'#f59e0b',heading:'#064e3b',text:'#065f46',bg:'#ecfdf5',card:'#d1fae5',nav:'#ffffff',gradFrom:'#10b981',gradTo:'#059669'},
  // EdTech
  { name:'Campus Blue',     cat:'EdTech', primary:'#2563eb',secondary:'#1d4ed8',accent:'#f59e0b',heading:'#1e3a5f',text:'#1e40af',bg:'#eff6ff',card:'#dbeafe',nav:'#1e40af',gradFrom:'#1e40af',gradTo:'#1d4ed8'},
  { name:'Knowledge Gold',  cat:'EdTech', primary:'#d97706',secondary:'#b45309',accent:'#10b981',heading:'#451a03',text:'#78350f',bg:'#fffbeb',card:'#fef3c7',nav:'#ffffff',gradFrom:'#f59e0b',gradTo:'#d97706'},
  { name:'Scholar Green',   cat:'EdTech', primary:'#16a34a',secondary:'#15803d',accent:'#6366f1',heading:'#14532d',text:'#166534',bg:'#f0fdf4',card:'#dcfce7',nav:'#ffffff',gradFrom:'#16a34a',gradTo:'#15803d'},
  { name:'EdTech Orange',   cat:'EdTech', primary:'#f97316',secondary:'#ea580c',accent:'#3b82f6',heading:'#431407',text:'#9a3412',bg:'#fff7ed',card:'#ffedd5',nav:'#ffffff',gradFrom:'#f97316',gradTo:'#ea580c'},
  { name:'Mind Violet',     cat:'EdTech', primary:'#7c3aed',secondary:'#6d28d9',accent:'#f59e0b',heading:'#2e1065',text:'#4c1d95',bg:'#f5f3ff',card:'#ede9fe',nav:'#ffffff',gradFrom:'#7c3aed',gradTo:'#6d28d9'},
  // E-commerce
  { name:'Coral Shop',      cat:'E-commerce',primary:'#f43f5e',secondary:'#e11d48',accent:'#fb923c',heading:'#881337',text:'#9f1239',bg:'#fff1f2',card:'#ffe4e6',nav:'#ffffff',gradFrom:'#f43f5e',gradTo:'#fb923c'},
  { name:'Luxury Black',    cat:'E-commerce',primary:'#171717',secondary:'#262626',accent:'#d4af37',heading:'#0a0a0a',text:'#404040',bg:'#fafafa',card:'#f5f5f5',nav:'#171717',gradFrom:'#171717',gradTo:'#d4af37'},
  { name:'Pastel Boutique', cat:'E-commerce',primary:'#ec4899',secondary:'#f472b6',accent:'#a855f7',heading:'#500724',text:'#9d174d',bg:'#fdf2f8',card:'#fce7f3',nav:'#ffffff',gradFrom:'#ec4899',gradTo:'#a855f7'},
  { name:'Fresh Market',    cat:'E-commerce',primary:'#22c55e',secondary:'#16a34a',accent:'#f59e0b',heading:'#052e16',text:'#14532d',bg:'#f0fdf4',card:'#dcfce7',nav:'#ffffff',gradFrom:'#22c55e',gradTo:'#16a34a'},
  { name:'Tech Gadgets',    cat:'E-commerce',primary:'#0f172a',secondary:'#1e293b',accent:'#3b82f6',heading:'#020617',text:'#334155',bg:'#f8fafc',card:'#f1f5f9',nav:'#0f172a',gradFrom:'#0f172a',gradTo:'#3b82f6'},
  // Corporate
  { name:'Navy Trust',      cat:'Corporate',primary:'#1e3a5f',secondary:'#1e40af',accent:'#d97706',heading:'#0c1445',text:'#1e3a5f',bg:'#f8fafc',card:'#f1f5f9',nav:'#1e3a5f',gradFrom:'#1e3a5f',gradTo:'#1e40af'},
  { name:'Charcoal Pro',    cat:'Corporate',primary:'#374151',secondary:'#4b5563',accent:'#6366f1',heading:'#111827',text:'#374151',bg:'#ffffff',card:'#f9fafb',nav:'#ffffff',gradFrom:'#374151',gradTo:'#6366f1'},
  { name:'Cobalt Finance',  cat:'Corporate',primary:'#0369a1',secondary:'#075985',accent:'#f59e0b',heading:'#0c4a6e',text:'#0369a1',bg:'#f0f9ff',card:'#e0f2fe',nav:'#ffffff',gradFrom:'#0369a1',gradTo:'#075985'},
  { name:'Sage Consulting', cat:'Corporate',primary:'#4d7c0f',secondary:'#365314',accent:'#eab308',heading:'#1a2e05',text:'#365314',bg:'#f7fee7',card:'#ecfccb',nav:'#ffffff',gradFrom:'#4d7c0f',gradTo:'#365314'},
  { name:'Red Professional',cat:'Corporate',primary:'#b91c1c',secondary:'#991b1b',accent:'#374151',heading:'#450a0a',text:'#7f1d1d',bg:'#fef2f2',card:'#fee2e2',nav:'#ffffff',gradFrom:'#b91c1c',gradTo:'#991b1b'},
  // Agency
  { name:'Agency Black',    cat:'Agency',  primary:'#000000',secondary:'#171717',accent:'#facc15',heading:'#000000',text:'#171717',bg:'#ffffff',card:'#f5f5f5',nav:'#000000',gradFrom:'#000000',gradTo:'#374151'},
  { name:'Creative Orange', cat:'Agency',  primary:'#ea580c',secondary:'#c2410c',accent:'#6366f1',heading:'#431407',text:'#9a3412',bg:'#fff7ed',card:'#ffedd5',nav:'#ffffff',gradFrom:'#ea580c',gradTo:'#c2410c'},
  { name:'Studio Pink',     cat:'Agency',  primary:'#db2777',secondary:'#be185d',accent:'#8b5cf6',heading:'#500724',text:'#9d174d',bg:'#fdf2f8',card:'#fce7f3',nav:'#ffffff',gradFrom:'#db2777',gradTo:'#8b5cf6'},
  { name:'Neon Agency',     cat:'Agency',  primary:'#a3e635',secondary:'#84cc16',accent:'#f43f5e',heading:'#1a2e05',text:'#3f6212',bg:'#0f172a',card:'#1e293b',nav:'#0f172a',gradFrom:'#a3e635',gradTo:'#06b6d4'},
  { name:'Scandinavian',    cat:'Agency',  primary:'#6366f1',secondary:'#4f46e5',accent:'#f9a8d4',heading:'#111827',text:'#374151',bg:'#fafafa',card:'#f5f5f5',nav:'#ffffff',gradFrom:'#e0e7ff',gradTo:'#fce7f3'},
  // Portfolio
  { name:'Mono Portfolio',  cat:'Portfolio',primary:'#171717',secondary:'#262626',accent:'#737373',heading:'#0a0a0a',text:'#404040',bg:'#fafafa',card:'#f5f5f5',nav:'#fafafa',gradFrom:'#171717',gradTo:'#404040'},
  { name:'Warm Portfolio',  cat:'Portfolio',primary:'#c2410c',secondary:'#9a3412',accent:'#d97706',heading:'#431407',text:'#78350f',bg:'#fffbeb',card:'#fef3c7',nav:'#ffffff',gradFrom:'#c2410c',gradTo:'#d97706'},
  { name:'Blue Architect',  cat:'Portfolio',primary:'#1d4ed8',secondary:'#1e40af',accent:'#06b6d4',heading:'#1e3a5f',text:'#1e40af',bg:'#eff6ff',card:'#dbeafe',nav:'#ffffff',gradFrom:'#1d4ed8',gradTo:'#06b6d4'},
  // Dark
  { name:'Dark Matter',     cat:'Dark',    primary:'#818cf8',secondary:'#6366f1',accent:'#34d399',heading:'#f1f5f9',text:'#cbd5e1',bg:'#0f172a',card:'#1e293b',nav:'#0f172a',gradFrom:'#4f46e5',gradTo:'#0891b2'},
  { name:'Dark Crimson',    cat:'Dark',    primary:'#f43f5e',secondary:'#e11d48',accent:'#fbbf24',heading:'#f1f5f9',text:'#cbd5e1',bg:'#0c0a09',card:'#1c1917',nav:'#0c0a09',gradFrom:'#881337',gradTo:'#f43f5e'},
  { name:'Midnight Purple', cat:'Dark',    primary:'#a78bfa',secondary:'#7c3aed',accent:'#06b6d4',heading:'#f1f5f9',text:'#c4b5fd',bg:'#0f0723',card:'#1a0a3b',nav:'#0f0723',gradFrom:'#1a0a3b',gradTo:'#7c3aed'},
  { name:'Neon Green Dark', cat:'Dark',    primary:'#86efac',secondary:'#4ade80',accent:'#818cf8',heading:'#f1f5f9',text:'#d1fae5',bg:'#0a150f',card:'#0d2115',nav:'#0a150f',gradFrom:'#052e16',gradTo:'#4ade80'},
  { name:'Dark Gold',       cat:'Dark',    primary:'#fbbf24',secondary:'#f59e0b',accent:'#c084fc',heading:'#fef9c3',text:'#fde68a',bg:'#0c0a00',card:'#1c1700',nav:'#0c0a00',gradFrom:'#0c0a00',gradTo:'#f59e0b'},
  // Minimal
  { name:'Pure White',      cat:'Minimal', primary:'#374151',secondary:'#6b7280',accent:'#6366f1',heading:'#111827',text:'#4b5563',bg:'#ffffff',card:'#f9fafb',nav:'#ffffff',gradFrom:'#f9fafb',gradTo:'#e5e7eb'},
  { name:'Soft Gray',       cat:'Minimal', primary:'#6b7280',secondary:'#4b5563',accent:'#8b5cf6',heading:'#111827',text:'#374151',bg:'#f9fafb',card:'#f3f4f6',nav:'#f9fafb',gradFrom:'#e5e7eb',gradTo:'#d1d5db'},
  { name:'Ghost Minimal',   cat:'Minimal', primary:'#94a3b8',secondary:'#64748b',accent:'#0ea5e9',heading:'#1e293b',text:'#475569',bg:'#f8fafc',card:'#f1f5f9',nav:'#f8fafc',gradFrom:'#f1f5f9',gradTo:'#e2e8f0'},
];

const filteredThemes = computed(() => {
  let list = presets;
  if (themeCatFilter.value !== 'All') list = list.filter(t => t.cat === themeCatFilter.value);
  if (themeSearch.value) list = list.filter(t => t.name.toLowerCase().includes(themeSearch.value.toLowerCase()));
  return list;
});

const applyTheme = (t) => {
  Object.assign(config.colors, { primary:t.primary, secondary:t.secondary, accent:t.accent, heading:t.heading, text:t.text, bg:t.bg, card:t.card||'#f9fafb', nav:t.nav||'#ffffff' });
  config.gradientFrom    = t.gradFrom || t.primary;
  config.gradientTo      = t.gradTo   || t.secondary;
  config.darkMode        = t.cat === 'Dark';
  activeThemeName.value  = t.name;
};

// ─── FONTS ───
const sansFonts    = ['Inter','Roboto','Poppins','Nunito','Outfit','DM Sans','Space Grotesk','Manrope','Figtree','Plus Jakarta Sans'];
const serifFonts   = ['Playfair Display','Merriweather','Lora','EB Garamond','Cormorant Garamond'];
const displayFonts = ['Raleway','Montserrat','Oswald','Barlow','Exo 2','Orbitron','Bebas Neue'];

const loadFont = (fontName) => {
  const link = document.createElement('link');
  link.href  = `https://fonts.googleapis.com/css2?family=${fontName.replace(' ','+')}&display=swap`;
  link.rel   = 'stylesheet';
  if (!document.querySelector(`link[href="${link.href}"]`)) document.head.appendChild(link);
};

// ─── COLORS ───
const colorFields = [
  { key:'primary',  label:'Primary',    default:'#6366f1' },
  { key:'secondary',label:'Secondary',  default:'#8b5cf6' },
  { key:'accent',   label:'Accent',     default:'#10b981' },
  { key:'heading',  label:'Headings',   default:'#111827' },
  { key:'text',     label:'Body Text',  default:'#374151' },
  { key:'bg',       label:'Background', default:'#ffffff' },
  { key:'card',     label:'Card BG',    default:'#f9fafb' },
  { key:'nav',      label:'Navbar BG',  default:'#ffffff' },
];

// ─── BUTTONS ───
const radiusOptions = [
  { label:'Square', value:'0' },
  { label:'Soft',   value:'0.375rem' },
  { label:'Rounded',value:'0.75rem' },
  { label:'Pill',   value:'9999px' },
];

const buttonStyles = [
  { key:'filled',  label:'Filled',   preview:'Click Me', cls:'text-white' },
  { key:'outline', label:'Outline',  preview:'Click Me', cls:'border-2 bg-transparent' },
  { key:'ghost',   label:'Ghost',    preview:'Click Me', cls:'bg-transparent' },
  { key:'glass',   label:'Glass',    preview:'Click Me', cls:'backdrop-blur-sm bg-white/20 text-white border border-white/30' },
];

const applyButtonVariant = (v) => {
  if (v.radius) config.buttons.radius = v.radius;
  if (v.styleName) config.buttons.style = v.styleName;
  if (v.primary) config.colors.primary = v.primary;
  if (v.shadow) config.buttons.shadow = v.shadow;
};

const buttonVariants = [
  { label:'Indigo',     styleName:'filled', primary:'#6366f1', radius:'0.5rem', style:{background:'#6366f1',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Emerald',    styleName:'filled', primary:'#10b981', radius:'0.5rem', style:{background:'#10b981',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Rose',       styleName:'filled', primary:'#f43f5e', radius:'9999px', style:{background:'#f43f5e',color:'#fff',borderRadius:'9999px',border:'none'} },
  { label:'Amber',      styleName:'filled', primary:'#f59e0b', radius:'0.375rem', style:{background:'#f59e0b',color:'#fff',borderRadius:'0.375rem',border:'none'} },
  { label:'Outline-B',  styleName:'outline', primary:'#3b82f6', radius:'0.5rem', style:{background:'transparent',color:'#3b82f6',border:'2px solid #3b82f6',borderRadius:'0.5rem'} },
  { label:'Outline-G',  styleName:'outline', primary:'#10b981', radius:'9999px', style:{background:'transparent',color:'#10b981',border:'2px solid #10b981',borderRadius:'9999px'} },
  { label:'Gray',       styleName:'filled', primary:'#374151', radius:'0.5rem', style:{background:'#374151',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Black',      styleName:'filled', primary:'#000', radius:'0', style:{background:'#000',color:'#fff',borderRadius:'0',border:'none'} },
  { label:'White',      styleName:'outline', primary:'#e5e7eb', radius:'0.5rem', style:{background:'#fff',color:'#374151',border:'1px solid #e5e7eb',borderRadius:'0.5rem'} },
  { label:'Ghost-I',    styleName:'ghost', primary:'#6366f1', radius:'0.5rem', style:{background:'transparent',color:'#6366f1',border:'none',borderRadius:'0.5rem'} },
  { label:'Gradient',   styleName:'filled', primary:'#6366f1', radius:'0.5rem', style:{background:'linear-gradient(135deg,#6366f1,#8b5cf6)',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Grad-Rose',  styleName:'filled', primary:'#f43f5e', radius:'9999px', style:{background:'linear-gradient(135deg,#f43f5e,#fb923c)',color:'#fff',borderRadius:'9999px',border:'none'} },
  { label:'Grad-Ocean', styleName:'filled', primary:'#0ea5e9', radius:'0.5rem', style:{background:'linear-gradient(135deg,#0ea5e9,#06b6d4)',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Glass',      styleName:'glass', primary:'#fff', radius:'0.5rem', style:{background:'rgba(255,255,255,0.2)',backdropFilter:'blur(8px)',color:'#fff',border:'1px solid rgba(255,255,255,0.3)',borderRadius:'0.5rem'} },
  { label:'3D Push',    styleName:'filled', primary:'#6366f1', radius:'0.5rem', shadow:'lg', style:{background:'#6366f1',color:'#fff',borderRadius:'0.5rem',border:'none',boxShadow:'0 4px 0 #4338ca'} },
  { label:'Neon',       styleName:'outline', primary:'#a3e635', radius:'0.375rem', style:{background:'transparent',color:'#a3e635',border:'2px solid #a3e635',boxShadow:'0 0 8px #a3e635',borderRadius:'0.375rem'} },
  { label:'Soft Rose',  styleName:'filled', primary:'#db2777', radius:'0.75rem', style:{background:'#fce7f3',color:'#db2777',borderRadius:'0.75rem',border:'none'} },
  { label:'Soft Blue',  styleName:'filled', primary:'#1d4ed8', radius:'0.75rem', style:{background:'#dbeafe',color:'#1d4ed8',borderRadius:'0.75rem',border:'none'} },
  { label:'Soft Green', styleName:'filled', primary:'#15803d', radius:'0.75rem', style:{background:'#dcfce7',color:'#15803d',borderRadius:'0.75rem',border:'none'} },
  { label:'Soft Amber', styleName:'filled', primary:'#b45309', radius:'0.75rem', style:{background:'#fef3c7',color:'#b45309',borderRadius:'0.75rem',border:'none'} },
  { label:'Sky',        styleName:'filled', primary:'#38bdf8', radius:'0.5rem', style:{background:'#38bdf8',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Fuchsia',    styleName:'filled', primary:'#d946ef', radius:'0.5rem', style:{background:'#d946ef',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Teal',       styleName:'filled', primary:'#14b8a6', radius:'0.5rem', style:{background:'#14b8a6',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Violet',     styleName:'filled', primary:'#7c3aed', radius:'0.5rem', style:{background:'#7c3aed',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Outline-Am', styleName:'outline', primary:'#d97706', radius:'0.5rem', style:{background:'transparent',color:'#d97706',border:'2px solid #d97706',borderRadius:'0.5rem'} },
  { label:'Outline-V',  styleName:'outline', primary:'#8b5cf6', radius:'0.5rem', style:{background:'transparent',color:'#8b5cf6',border:'2px solid #8b5cf6',borderRadius:'0.5rem'} },
  { label:'Outline-R',  styleName:'outline', primary:'#ef4444', radius:'0.75rem', style:{background:'transparent',color:'#ef4444',border:'2px solid #ef4444',borderRadius:'0.75rem'} },
  { label:'Dark-Glass', styleName:'glass', primary:'#fff', radius:'0.5rem', style:{background:'rgba(0,0,0,0.3)',backdropFilter:'blur(8px)',color:'#fff',border:'1px solid rgba(255,255,255,0.1)',borderRadius:'0.5rem'} },
  { label:'Square-B',   styleName:'filled', primary:'#1d4ed8', radius:'0', style:{background:'#1d4ed8',color:'#fff',borderRadius:'0',border:'none',letterSpacing:'0.05em'} },
  { label:'Square-K',   styleName:'filled', primary:'#000', radius:'0', style:{background:'#000',color:'#fff',borderRadius:'0',border:'none'} },
  { label:'Pill-W',     styleName:'outline', primary:'#374151', radius:'9999px', style:{background:'#fff',color:'#374151',border:'1px solid #e5e7eb',borderRadius:'9999px'} },
  { label:'Cyan',       styleName:'filled', primary:'#06b6d4', radius:'0.5rem', style:{background:'#06b6d4',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Lime',       styleName:'filled', primary:'#84cc16', radius:'0.5rem', style:{background:'#84cc16',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Yellow',     styleName:'filled', primary:'#eab308', radius:'0.5rem', style:{background:'#eab308',color:'#000',borderRadius:'0.5rem',border:'none'} },
  { label:'Slate',      styleName:'filled', primary:'#475569', radius:'0.5rem', style:{background:'#475569',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Zinc',       styleName:'filled', primary:'#3f3f46', radius:'0.5rem', style:{background:'#3f3f46',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Neutral',    styleName:'filled', primary:'#404040', radius:'0.5rem', style:{background:'#404040',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Stone',      styleName:'filled', primary:'#44403c', radius:'0.5rem', style:{background:'#44403c',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Orange',     styleName:'filled', primary:'#f97316', radius:'0.5rem', style:{background:'#f97316',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Amber Bold', styleName:'filled', primary:'#d97706', radius:'0.5rem', style:{background:'#d97706',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Gold',       styleName:'filled', primary:'#ca8a04', radius:'0.5rem', style:{background:'#ca8a04',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Lime Green', styleName:'filled', primary:'#65a30d', radius:'0.5rem', style:{background:'#65a30d',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Teal Deep',  styleName:'filled', primary:'#0d9488', radius:'0.5rem', style:{background:'#0d9488',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Cyan Sky',   styleName:'filled', primary:'#0891b2', radius:'0.5rem', style:{background:'#0891b2',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Blue Light', styleName:'filled', primary:'#0284c7', radius:'0.5rem', style:{background:'#0284c7',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Indigo Mod', styleName:'filled', primary:'#4f46e5', radius:'0.5rem', style:{background:'#4f46e5',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Violet Deep',styleName:'filled', primary:'#7c3aed', radius:'0.5rem', style:{background:'#7c3aed',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Purple Bold',styleName:'filled', primary:'#9333ea', radius:'0.5rem', style:{background:'#9333ea',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Pink Soft',  styleName:'filled', primary:'#db2777', radius:'0.5rem', style:{background:'#db2777',color:'#fff',borderRadius:'0.5rem',border:'none'} },
  { label:'Rose Bold',  styleName:'filled', primary:'#e11d48', radius:'0.5rem', style:{background:'#e11d48',color:'#fff',borderRadius:'0.5rem',border:'none'} },
];


// ─── CSS VARS ───
const cssVarsMap = ref({});
const cssVars = [
  { var:'--color-primary',   default: computed(() => config.colors.primary)   },
  { var:'--color-secondary', default: computed(() => config.colors.secondary) },
  { var:'--color-accent',    default: computed(() => config.colors.accent)    },
  { var:'--color-bg',        default: computed(() => config.colors.bg)        },
  { var:'--color-text',      default: computed(() => config.colors.text)      },
  { var:'--font-heading',    default: computed(() => config.typography.font_heading) },
  { var:'--font-body',       default: computed(() => config.typography.font_body)   },
  { var:'--radius-btn',      default: computed(() => config.buttons.radius)   },
];
const getCssVar = (name) => cssVarsMap.value[name] || (cssVars.find(v => v.var === name)?.default.value ?? '');
const setCssVar = (name, val) => { cssVarsMap.value[name] = val; };

// ─── COMPUTED STYLES ───
const pageStyle = computed(() => ({
  backgroundColor: config.colors.bg,
  color: config.colors.text,
  fontFamily: config.typography.font_body + ',sans-serif',
  fontSize: config.typography.base_size + 'px',
  lineHeight: config.typography.line_height,
  minHeight: '600px',
}));
const navStyle     = computed(() => ({ backgroundColor: config.colors.nav, borderBottom: '1px solid ' + config.colors.border }));
const heroStyle    = computed(() => ({}));
const cardStyle    = computed(() => ({ backgroundColor: config.colors.card, borderColor: config.colors.border }));
const footerStyle  = computed(() => ({ backgroundColor: config.colors.card, borderColor: config.colors.border }));
const shadowMap    = { none:'none', sm:'0 1px 3px rgba(0,0,0,.12)', md:'0 4px 12px rgba(0,0,0,.15)', lg:'0 8px 25px rgba(0,0,0,.18)', xl:'0 16px 48px rgba(0,0,0,.22)' };
const ctaButtonStyle = computed(() => ({
  backgroundColor: config.colors.primary, color:'#fff',
  borderRadius: config.buttons.radius,
  boxShadow: shadowMap[config.buttons.shadow] || 'none',
}));
const outlineButtonStyle = computed(() => ({
  color: config.colors.primary, borderColor: config.colors.primary,
  borderRadius: config.buttons.radius, backgroundColor:'transparent',
}));

const generatedCss = computed(() => `:root {
  --color-primary: ${config.colors.primary};
  --color-secondary: ${config.colors.secondary};
  --color-accent: ${config.colors.accent};
  --color-heading: ${config.colors.heading};
  --color-text: ${config.colors.text};
  --color-bg: ${config.colors.bg};
  --color-card: ${config.colors.card};
  --font-heading: '${config.typography.font_heading}';
  --font-body: '${config.typography.font_body}';
  --font-size-base: ${config.typography.base_size}px;
  --line-height: ${config.typography.line_height};
  --radius-btn: ${config.buttons.radius};
  --gradient: linear-gradient(${config.gradientDir}, ${config.gradientFrom}, ${config.gradientTo});
}`);

// ─── SAVE ───
const saveTheme = async () => {
  saving.value = true;
  try {
    const id = props.active_theme?.id;
    const payload = { name: activeThemeName.value, colors: config.colors, typography: config.typography,
      buttons: config.buttons, dark_mode: config.darkMode, gradient: { from: config.gradientFrom, to: config.gradientTo, dir: config.gradientDir }, custom_css: config.customCss };
    if (id) await axios.put(route('cms.themes.update', id), payload);
    else    await axios.post(route('cms.themes.store'), payload);
    toast.value = true; setTimeout(() => toast.value = false, 3000);
  } catch { toast.value = true; setTimeout(() => toast.value = false, 3000); }
  saving.value = false;
};
</script>

<style scoped>
::-webkit-scrollbar{width:5px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:10px}
</style>

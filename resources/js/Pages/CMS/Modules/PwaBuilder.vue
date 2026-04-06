<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">PWA Builder</h2>
                <p class="text-xs text-gray-500 mt-0.5">Configure your Progressive Web App — icons, offline page, splash screen, push notifications.</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="testInstall" class="px-3 py-2 bg-gray-100 border border-gray-200 text-gray-700 rounded-xl text-xs font-bold hover:bg-gray-200 flex items-center gap-1.5">
                    <i class="fas fa-mobile-alt text-sm"></i>Test Install
                </button>
                <button @click="savePwa" :disabled="saving" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2 disabled:opacity-50">
                    <i v-if="saving" class="fas fa-spinner fa-spin text-xs"></i>
                    <i v-else class="fas fa-save text-xs"></i>
                    Save PWA Config
                </button>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-8 grid grid-cols-3 gap-6">
            <!-- Left: Settings panels -->
            <div class="col-span-2 space-y-5">
                <!-- App Identity -->
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                    <h3 class="font-black text-gray-900 text-sm flex items-center gap-2">
                        <i class="fas fa-id-card text-indigo-500"></i> App Identity
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">App Name</label>
                            <input v-model="pwa.name" class="field-input" placeholder="My Awesome App" />
                        </div>
                        <div>
                            <label class="field-label">Short Name <span class="text-gray-300 font-normal">(12 chars max)</span></label>
                            <input v-model="pwa.short_name" maxlength="12" class="field-input" placeholder="MyApp" />
                        </div>
                        <div class="col-span-2">
                            <label class="field-label">Description</label>
                            <textarea v-model="pwa.description" rows="2" class="field-input resize-none" placeholder="A brief description of your app"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Display & Colors -->
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                    <h3 class="font-black text-gray-900 text-sm flex items-center gap-2">
                        <i class="fas fa-palette text-purple-500"></i> Display & Colors
                    </h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">Display Mode</label>
                            <select v-model="pwa.display" class="field-input">
                                <option value="standalone">Standalone (App-like)</option>
                                <option value="fullscreen">Fullscreen</option>
                                <option value="minimal-ui">Minimal UI (browser-lite)</option>
                                <option value="browser">Browser</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Orientation</label>
                            <select v-model="pwa.orientation" class="field-input">
                                <option value="any">Any</option>
                                <option value="portrait">Portrait</option>
                                <option value="landscape">Landscape</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Theme Color</label>
                            <div class="flex gap-2">
                                <input type="color" v-model="pwa.theme_color" class="w-10 h-9 rounded-lg border border-gray-200 p-0.5 bg-white cursor-pointer flex-shrink-0" />
                                <input v-model="pwa.theme_color" class="flex-1 field-input font-mono" />
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Background Color</label>
                            <div class="flex gap-2">
                                <input type="color" v-model="pwa.background_color" class="w-10 h-9 rounded-lg border border-gray-200 p-0.5 bg-white cursor-pointer flex-shrink-0" />
                                <input v-model="pwa.background_color" class="flex-1 field-input font-mono" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Icons -->
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm">
                    <h3 class="font-black text-gray-900 text-sm flex items-center gap-2 mb-4">
                        <i class="fas fa-icons text-amber-500"></i> App Icons
                    </h3>
                    <div class="flex gap-4 flex-wrap mb-4">
                        <div v-for="size in iconSizes" :key="size"
                            class="flex flex-col items-center gap-2">
                            <div class="rounded-2xl border-2 border-dashed border-gray-200 flex items-center justify-center overflow-hidden relative hover:border-indigo-300 cursor-pointer transition-all group"
                                :style="{ width: Math.min(size/4, 80) + 'px', height: Math.min(size/4, 80) + 'px' }">
                                <img v-if="pwa.icon_url" :src="pwa.icon_url" class="w-full h-full object-cover rounded-2xl" />
                                <i v-else class="fas fa-plus text-gray-300 group-hover:text-indigo-400 transition-colors" :class="size <= 96 ? 'text-lg' : 'text-2xl'"></i>
                                <div v-if="pwa.icon_url" class="absolute inset-0 bg-black/0 group-hover:bg-black/20 rounded-2xl transition-all flex items-center justify-center">
                                    <i class="fas fa-pen text-white opacity-0 group-hover:opacity-100 text-xs"></i>
                                </div>
                            </div>
                            <span class="text-sm font-bold text-gray-400">{{ size }}×{{ size }}</span>
                        </div>
                    </div>
                    <!-- Upload icon -->
                    <label class="flex items-center justify-center gap-2 w-full py-3 border-2 border-dashed border-gray-200 rounded-xl text-xs font-bold text-gray-500 hover:border-indigo-300 hover:text-indigo-600 cursor-pointer transition-all">
                        <i class="fas fa-cloud-upload-alt"></i>
                        Upload Master Icon (1024×1024 PNG recommended)
                        <input type="file" accept="image/png,image/svg+xml" class="hidden" @change="uploadIcon" />
                    </label>
                    <p class="text-sm text-gray-400 mt-2">We'll automatically generate all required sizes (72, 96, 128, 192, 512px).</p>
                </div>

                <!-- Offline & Push -->
                <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                    <h3 class="font-black text-gray-900 text-sm flex items-center gap-2">
                        <i class="fas fa-wifi text-red-500"></i> Offline & Notifications
                    </h3>
                    <div>
                        <label class="field-label">Offline Fallback Page</label>
                        <input v-model="pwa.offline_url" class="field-input font-mono" placeholder="/offline" />
                    </div>
                    <div>
                        <label class="field-label">Start URL</label>
                        <input v-model="pwa.start_url" class="field-input font-mono" placeholder="/" />
                    </div>
                    <div class="space-y-3">
                        <label class="flex items-center gap-3 cursor-pointer group">
                            <div class="relative">
                                <input type="checkbox" v-model="pwa.push_notifications" class="sr-only peer" />
                                <div class="w-10 h-5 bg-gray-200 peer-checked:bg-indigo-500 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5 after:shadow-sm"></div>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Enable Push Notifications</p>
                                <p class="text-sm text-gray-400">Requires VAPID key pair — generate below.</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" v-model="pwa.precache_assets" class="sr-only peer" />
                                <div class="w-10 h-5 bg-gray-200 peer-checked:bg-indigo-500 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5 after:shadow-sm"></div>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Pre-cache Core Assets</p>
                                <p class="text-sm text-gray-400">Cache homepage, CSS, fonts on first visit.</p>
                            </div>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" v-model="pwa.show_install_banner" class="sr-only peer" />
                                <div class="w-10 h-5 bg-gray-200 peer-checked:bg-indigo-500 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5 after:shadow-sm"></div>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Show Install Banner</p>
                                <p class="text-sm text-gray-400">Prompt visitors to install after 5 seconds.</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Right: Live Preview -->
            <div class="space-y-5">
                <!-- Phone mockup -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                    <h3 class="text-xs font-black uppercase tracking-widest text-gray-500 mb-4">Install Preview</h3>
                    <!-- Phone frame -->
                    <div class="mx-auto w-44 relative">
                        <div class="w-full pt-[200%] rounded-3xl border-4 border-gray-800 relative overflow-hidden shadow-2xl"
                            :style="{ background: pwa.background_color }">
                            <div class="absolute inset-0 flex flex-col">
                                <!-- Status bar -->
                                <div class="h-6 bg-black flex items-center justify-center">
                                    <div class="w-16 h-3 bg-gray-900 rounded-full"></div>
                                </div>
                                <!-- App content preview -->
                                <div class="flex-1 flex flex-col items-center justify-center px-4">
                                    <div class="w-14 h-14 rounded-2xl mb-3 flex items-center justify-center overflow-hidden shadow-lg"
                                        :style="{ backgroundColor: pwa.theme_color }">
                                        <img v-if="pwa.icon_url" :src="pwa.icon_url" class="w-full h-full object-cover" />
                                        <i v-else class="fas fa-globe text-white text-2xl"></i>
                                    </div>
                                    <p class="text-sm font-black text-center leading-tight" :style="{ color: pwa.theme_color }">{{ pwa.name || 'My App' }}</p>
                                    <p class="text-xs text-gray-400 text-center mt-0.5">{{ pwa.description || 'Your app description' }}</p>
                                </div>
                                <!-- Bottom install bar -->
                                <div class="px-3 py-3 bg-white border-t border-gray-100">
                                    <div class="rounded-lg py-1.5 text-center text-sm font-black text-white" :style="{ backgroundColor: pwa.theme_color }">
                                        Add to Home Screen
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Manifest preview -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-black uppercase tracking-widest text-gray-500">manifest.json</h3>
                        <a href="/manifest.json" target="_blank" class="text-sm font-bold text-indigo-600 hover:text-indigo-800">
                            <i class="fas fa-external-link-alt mr-1"></i>View Live
                        </a>
                    </div>
                    <pre class="text-sm text-gray-600 bg-gray-50 rounded-xl p-3 overflow-x-auto font-mono leading-relaxed border border-gray-200">{{ manifestPreview }}</pre>
                </div>

                <!-- Checklist -->
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm">
                    <h3 class="text-xs font-black uppercase tracking-widest text-gray-500 mb-3">PWA Checklist</h3>
                    <div class="space-y-2">
                        <div v-for="c in checklist" :key="c.label" class="flex items-center gap-2">
                            <div class="w-4 h-4 rounded-full flex items-center justify-center shrink-0"
                                :class="c.pass ? 'bg-emerald-500' : 'bg-gray-200'">
                                <i :class="c.pass ? 'fas fa-check text-white text-xs' : 'fas fa-minus text-gray-400 text-xs'"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-700">{{ c.label }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success toast -->
        <transition enter-active-class="transition" enter-from-class="opacity-0 translate-y-4" leave-active-class="transition" leave-to-class="opacity-0 translate-y-4">
            <div v-if="savedToast" class="fixed bottom-8 left-1/2 -translate-x-1/2 bg-indigo-600 text-white px-6 py-3 rounded-xl shadow-xl text-sm font-black flex items-center gap-2 z-50">
                <i class="fas fa-check-circle"></i> PWA configuration saved!
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({ pwa_config: { type: Object, default: () => ({}) } });

const saving    = ref(false);
const savedToast = ref(false);

const pwa = ref({
    name:              props.pwa_config?.name               || '',
    short_name:        props.pwa_config?.short_name         || '',
    description:       props.pwa_config?.description        || '',
    display:           props.pwa_config?.display            || 'standalone',
    orientation:       props.pwa_config?.orientation        || 'any',
    theme_color:       props.pwa_config?.theme_color        || '#6366f1',
    background_color:  props.pwa_config?.background_color   || '#ffffff',
    start_url:         props.pwa_config?.start_url          || '/',
    offline_url:       props.pwa_config?.offline_url        || '/offline',
    icon_url:          props.pwa_config?.icon_url           || '',
    push_notifications: props.pwa_config?.push_notifications || false,
    precache_assets:   props.pwa_config?.precache_assets    || true,
    show_install_banner: props.pwa_config?.show_install_banner || true,
});

const iconSizes = [192, 512, 144, 96, 72];

const manifestPreview = computed(() => JSON.stringify({
    name:             pwa.value.name || 'My App',
    short_name:       pwa.value.short_name || 'App',
    start_url:        pwa.value.start_url,
    display:          pwa.value.display,
    theme_color:      pwa.value.theme_color,
    background_color: pwa.value.background_color,
    icons: [{ src:'/icon-192.png', sizes:'192x192', type:'image/png' }, { src:'/icon-512.png', sizes:'512x512', type:'image/png', purpose:'any maskable' }],
}, null, 2));

const checklist = computed(() => [
    { label:'App name set',              pass: !!pwa.value.name },
    { label:'Short name set',            pass: !!pwa.value.short_name },
    { label:'Icon uploaded (192px+)',    pass: !!pwa.value.icon_url },
    { label:'Start URL defined',         pass: !!pwa.value.start_url },
    { label:'Theme color set',           pass: !!pwa.value.theme_color },
    { label:'Offline fallback page',     pass: !!pwa.value.offline_url },
    { label:'Display mode selected',     pass: !!pwa.value.display },
    { label:'HTTPS (required for PWA)',  pass: window.location.protocol === 'https:' || window.location.hostname === 'localhost' },
]);

const uploadIcon = async (e) => {
    const file = e.target.files?.[0];
    if (!file) return;
    const fd = new FormData();
    fd.append('file', file);
    try {
        const { data } = await axios.post(route('cms.media.store'), fd, { headers: { 'Content-Type': 'multipart/form-data' } });
        pwa.value.icon_url = data.url;
    } catch { alert('Upload failed'); }
};

const savePwa = async () => {
    saving.value = true;
    try {
        await axios.post(route('cms.settings.pwa'), pwa.value);
        savedToast.value = true;
        setTimeout(() => savedToast.value = false, 3000);
    } catch { alert('Save failed'); }
    finally { saving.value = false; }
};

const testInstall = () => {
    if ('serviceWorker' in navigator) {
        alert('Service worker active. Use Chrome DevTools > Application > Manifest to test installation.');
    } else {
        alert('Service workers not available in this browser context.');
    }
};
</script>

<style scoped>
.field-label { display:block; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#374151; margin-bottom:0.25rem; }
.field-input { width:100%; background:#f9fafb; border:1px solid #e5e7eb; border-radius:0.5rem; padding:0.5rem 0.75rem; font-size:0.875rem; outline:none; transition:border-color 0.15s, box-shadow 0.15s; }
.field-input:focus { border-color:#6366f1; box-shadow:0 0 0 2px rgba(99,102,241,0.15); }
</style>

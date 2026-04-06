<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">Settings</h2>
                <p class="text-xs text-gray-500 mt-0.5">Configure payments, shipping, PWA, and global site settings.</p>
            </div>
            <button @click="saveAll" :disabled="saving" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2 disabled:opacity-50">
                <i v-if="saving" class="fas fa-spinner fa-spin text-xs"></i>
                <i v-else class="fas fa-save text-xs"></i>
                Save All
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-8 grid grid-cols-4 gap-6">
            <!-- Left nav -->
            <div class="col-span-1">
                <nav class="space-y-1 sticky top-0">
                    <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                        class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-bold transition-all text-left"
                        :class="activeTab === tab.id ? 'bg-indigo-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100'">
                        <i :class="tab.icon" class="w-4 text-center text-sm"></i>
                        {{ tab.label }}
                    </button>
                </nav>
            </div>

            <!-- Right panel -->
            <div class="col-span-3 space-y-5">
                <!-- General -->
                <div v-if="activeTab === 'general'" class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                    <h3 class="font-black text-gray-900">General Settings</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="field-label">Site Name</label><input v-model="settings.site_name" class="field-input" /></div>
                        <div><label class="field-label">Support Email</label><input v-model="settings.support_email" type="email" class="field-input" /></div>
                        <div><label class="field-label">Support Phone</label><input v-model="settings.support_phone" class="field-input" /></div>
                        <div><label class="field-label">WhatsApp Number</label><input v-model="settings.whatsapp" class="field-input" /></div>
                        <div class="col-span-2"><label class="field-label">Address</label><textarea v-model="settings.address" rows="2" class="field-input resize-none"></textarea></div>
                        <div><label class="field-label">Google Analytics ID</label><input v-model="settings.ga_id" placeholder="G-XXXXXXXXXX" class="field-input font-mono" /></div>
                        <div><label class="field-label">Facebook Pixel</label><input v-model="settings.fb_pixel" placeholder="XXXXXXXXXX" class="field-input font-mono" /></div>
                    </div>
                </div>

                <!-- Payments -->
                <div v-if="activeTab === 'payments'" class="space-y-5">
                    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                        <div class="flex items-center gap-2 mb-1">
                            <img src="https://razorpay.com/favicon.ico" class="w-5 h-5 rounded" />
                            <h3 class="font-black text-gray-900">Razorpay</h3>
                            <span class="ml-auto px-2 py-0.5 bg-emerald-50 text-emerald-700 text-sm font-black uppercase tracking-wider rounded-full border border-emerald-200">Live Gateway</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="field-label">Key ID</label><input v-model="settings.razorpay_key_id" class="field-input font-mono" placeholder="rzp_live_..." /></div>
                            <div><label class="field-label">Key Secret</label>
                                <div class="relative">
                                    <input :type="showSecret ? 'text' : 'password'" v-model="settings.razorpay_key_secret" class="field-input font-mono pr-10" placeholder="••••••••••••" />
                                    <button @click="showSecret = !showSecret" class="absolute right-3 top-2 text-gray-400 hover:text-gray-700">
                                        <i :class="showSecret ? 'fas fa-eye-slash' : 'fas fa-eye'" class="text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 bg-amber-50 border border-amber-200 rounded-xl text-xs text-amber-700">
                            <i class="fas fa-exclamation-triangle mr-1.5"></i>
                            Never share your Key Secret. It will be encrypted at rest.
                        </div>
                    </div>
                    <div class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                        <h3 class="font-black text-gray-900">Shipping</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div><label class="field-label">Free Shipping Above (₹)</label><input v-model="settings.free_shipping_threshold" type="number" class="field-input" placeholder="499" /></div>
                            <div><label class="field-label">Flat Shipping Rate (₹)</label><input v-model="settings.flat_shipping_rate" type="number" class="field-input" placeholder="49" /></div>
                        </div>
                    </div>
                </div>

                <!-- PWA -->
                <div v-if="activeTab === 'pwa'" class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                    <h3 class="font-black text-gray-900">Progressive Web App</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="field-label">App Name</label><input v-model="settings.pwa_app_name" class="field-input" /></div>
                        <div><label class="field-label">Short Name</label><input v-model="settings.pwa_short_name" class="field-input" /></div>
                        <div><label class="field-label">Theme Color</label>
                            <div class="flex gap-2">
                                <input type="color" v-model="settings.pwa_theme_color" class="w-10 h-9 rounded-lg border border-gray-200 p-0.5 cursor-pointer" />
                                <input v-model="settings.pwa_theme_color" class="flex-1 field-input font-mono" />
                            </div>
                        </div>
                        <div><label class="field-label">Display Mode</label>
                            <select v-model="settings.pwa_display" class="field-input">
                                <option value="standalone">Standalone</option>
                                <option value="fullscreen">Fullscreen</option>
                                <option value="browser">Browser</option>
                            </select>
                        </div>
                        <div class="col-span-2 flex items-center gap-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" v-model="settings.pwa_push" class="sr-only peer" />
                                <div class="w-9 h-5 bg-gray-200 peer-checked:bg-indigo-500 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-4 after:shadow-sm"></div>
                                <span class="text-sm font-bold text-gray-700">Enable Push Notifications</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- SEO Globals -->
                <div v-if="activeTab === 'seo'" class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                    <h3 class="font-black text-gray-900">Global SEO</h3>
                    <div class="space-y-4">
                        <div><label class="field-label">Default Meta Title</label><input v-model="settings.default_meta_title" class="field-input" /></div>
                        <div><label class="field-label">Default Meta Description</label><textarea v-model="settings.default_meta_desc" rows="2" class="field-input resize-none"></textarea></div>
                        <div><label class="field-label">OG Image URL</label><input v-model="settings.og_image" class="field-input" placeholder="https://..." /></div>
                        <div>
                            <label class="field-label">Robots</label>
                            <select v-model="settings.robots" class="field-input">
                                <option value="index,follow">index, follow (default)</option>
                                <option value="noindex,nofollow">noindex, nofollow (blocks SEO)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save flash -->
        <transition enter-active-class="transition" enter-from-class="opacity-0 translate-y-4" leave-active-class="transition" leave-to-class="opacity-0 translate-y-4">
            <div v-if="saved" class="fixed bottom-8 left-1/2 -translate-x-1/2 bg-indigo-600 text-white px-5 py-2.5 rounded-xl shadow-xl text-sm font-bold flex items-center gap-2 z-50">
                <i class="fas fa-check-circle"></i> Settings saved!
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import axios from 'axios';

const props = defineProps({ settings: { type: Object, default: () => ({}) } });

const activeTab  = ref('general');
const saving     = ref(false);
const saved      = ref(false);
const showSecret = ref(false);

const settings = reactive({ ...{
    site_name: '', support_email: '', support_phone: '', whatsapp: '', address: '',
    ga_id: '', fb_pixel: '',
    razorpay_key_id: '', razorpay_key_secret: '',
    free_shipping_threshold: 499, flat_shipping_rate: 49,
    pwa_app_name: '', pwa_short_name: '', pwa_theme_color: '#10b981', pwa_display: 'standalone', pwa_push: false,
    default_meta_title: '', default_meta_desc: '', og_image: '', robots: 'index,follow',
}, ...(props.settings || {}) });

const tabs = [
    { id:'general',  label:'General',  icon:'fas fa-cog' },
    { id:'payments', label:'Payments', icon:'fas fa-credit-card' },
    { id:'pwa',      label:'PWA',      icon:'fas fa-mobile-alt' },
    { id:'seo',      label:'Global SEO', icon:'fas fa-search' },
];

const saveAll = async () => {
    saving.value = true;
    try {
        await axios.post(route('cms.settings.save'), settings);
        saved.value = true;
        setTimeout(() => { saved.value = false; }, 3000);
    } catch (e) { alert('Failed to save settings'); }
    finally { saving.value = false; }
};
</script>

<style scoped>
.field-label { display:block; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#374151; margin-bottom:0.25rem; }
.field-input { width:100%; background:#f9fafb; border:1px solid #e5e7eb; border-radius:0.5rem; padding:0.5rem 0.75rem; font-size:0.875rem; outline:none; transition:border-color 0.15s, box-shadow 0.15s; }
.field-input:focus { border-color:#6366f1; box-shadow:0 0 0 2px rgba(99,102,241,0.15); }
</style>

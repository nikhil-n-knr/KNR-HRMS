<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">Cart & Checkout</h2>
                <p class="text-xs text-gray-500 mt-0.5">Cart behaviour, checkout flow, abandoned cart recovery, and order confirmation.</p>
            </div>
            <button @click="saveAll" :disabled="saving" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 transition-all flex items-center gap-2 disabled:opacity-50">
                <i v-if="saving" class="fas fa-spinner fa-spin text-xs"></i>
                <i v-else class="fas fa-save text-xs"></i> Save Settings
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-8 space-y-5">
            <!-- KPIs -->
            <div class="grid grid-cols-4 gap-4">
                <div v-for="k in kpis" :key="k.label" class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center" :style="{background:k.bg,color:k.color}"><i :class="k.icon"></i></div>
                    <div><p class="text-xl font-black text-gray-900">{{ k.value }}</p><p class="text-sm font-bold text-gray-400 uppercase tracking-wider">{{ k.label }}</p></div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="flex gap-1 border-b border-gray-200">
                <button v-for="t in tabs" :key="t.key" @click="tab = t.key"
                    class="px-4 py-2.5 text-xs font-bold transition-all border-b-2 -mb-px"
                    :class="tab === t.key ? 'border-indigo-600 text-indigo-700' : 'border-transparent text-gray-500 hover:text-gray-700'">
                    <i :class="t.icon + ' mr-1'"></i>{{ t.label }}
                </button>
            </div>

            <!-- Cart -->
            <div v-show="tab === 'cart'" class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                <h3 class="font-black text-gray-900 text-sm mb-2">Cart Behaviour</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="field-label">Cart Expiry (hrs)</label><input v-model.number="s.cart_expiry" type="number" class="field-input" placeholder="72" /></div>
                    <div><label class="field-label">Max Items</label><input v-model.number="s.max_items" type="number" class="field-input" placeholder="50" /></div>
                </div>
                <label v-for="item in cartToggles" :key="item.key" class="flex items-center gap-3 cursor-pointer py-2 border-b border-gray-50 last:border-0">
                    <div class="relative"><input type="checkbox" v-model="s[item.key]" class="sr-only peer" />
                        <div class="w-10 h-5 bg-gray-200 peer-checked:bg-indigo-500 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5"></div>
                    </div>
                    <div><p class="text-sm font-bold text-gray-800">{{ item.label }}</p><p class="text-sm text-gray-400">{{ item.desc }}</p></div>
                </label>
            </div>

            <!-- Checkout -->
            <div v-show="tab === 'checkout'" class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                <h3 class="font-black text-gray-900 text-sm mb-2">Checkout Flow</h3>
                <div class="flex items-center gap-2 flex-wrap mb-4">
                    <div v-for="(step, i) in checkoutSteps" :key="step" class="flex items-center gap-2">
                        <div class="flex items-center gap-2 px-3 py-2 bg-indigo-50 border border-indigo-200 rounded-xl">
                            <span class="w-5 h-5 rounded-full bg-indigo-600 text-white text-sm font-black flex items-center justify-center">{{ i+1 }}</span>
                            <span class="text-xs font-bold text-indigo-700">{{ step }}</span>
                        </div>
                        <i v-if="i < checkoutSteps.length-1" class="fas fa-chevron-right text-sm text-gray-300"></i>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="field-label">Checkout Mode</label>
                        <select v-model="s.checkout_mode" class="field-input">
                            <option value="guest_and_login">Guest + Login</option>
                            <option value="login_required">Login Required</option>
                            <option value="guest_only">Guest Only</option>
                        </select>
                    </div>
                    <div><label class="field-label">Address Fields</label>
                        <select v-model="s.address_mode" class="field-input">
                            <option value="full">Full Address</option>
                            <option value="minimal">Minimal (Name, Phone, PIN)</option>
                        </select>
                    </div>
                </div>
                <label v-for="item in checkoutToggles" :key="item.key" class="flex items-center gap-3 cursor-pointer py-2 border-b border-gray-50 last:border-0">
                    <div class="relative"><input type="checkbox" v-model="s[item.key]" class="sr-only peer" />
                        <div class="w-10 h-5 bg-gray-200 peer-checked:bg-indigo-500 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5"></div>
                    </div>
                    <div><p class="text-sm font-bold text-gray-800">{{ item.label }}</p><p class="text-sm text-gray-400">{{ item.desc }}</p></div>
                </label>
            </div>

            <!-- Abandoned -->
            <div v-show="tab === 'abandoned'" class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-black text-gray-900 text-sm">Abandoned Cart Recovery</h3>
                    <label class="relative cursor-pointer">
                        <input type="checkbox" v-model="s.abandoned_enabled" class="sr-only peer" />
                        <div class="w-10 h-5 bg-gray-200 peer-checked:bg-amber-500 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5"></div>
                    </label>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="field-label">Email 1 Delay (hrs)</label><input v-model.number="s.abandoned_delay_1" type="number" class="field-input" /></div>
                    <div><label class="field-label">Email 2 Delay (hrs)</label><input v-model.number="s.abandoned_delay_2" type="number" class="field-input" /></div>
                    <div><label class="field-label">Coupon Code</label><input v-model="s.abandoned_coupon" class="field-input font-mono" placeholder="COMEBACK10" /></div>
                    <div><label class="field-label">Coupon Discount (%)</label><input v-model.number="s.abandoned_coupon_pct" type="number" class="field-input" /></div>
                </div>
                <div class="grid grid-cols-3 gap-3 mt-2">
                    <div v-for="stat in abandonedStats" :key="stat.label" class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-center">
                        <p class="text-lg font-black text-amber-700">{{ stat.value }}</p>
                        <p class="text-sm font-bold text-amber-600 uppercase tracking-wider">{{ stat.label }}</p>
                    </div>
                </div>
            </div>

            <!-- Confirmation -->
            <div v-show="tab === 'confirm'" class="bg-white rounded-2xl border border-gray-200 p-6 shadow-sm space-y-4">
                <h3 class="font-black text-gray-900 text-sm">Order Confirmation</h3>
                <div><label class="field-label">Confirmation Message</label>
                    <textarea v-model="s.confirm_msg" rows="3" class="field-input resize-none" placeholder="Thank you for your order!..."></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="field-label">Reply-to Email</label><input v-model="s.reply_email" type="email" class="field-input" /></div>
                    <div><label class="field-label">BCC Email</label><input v-model="s.bcc_email" type="email" class="field-input" /></div>
                </div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <div class="relative"><input type="checkbox" v-model="s.whatsapp_confirm" class="sr-only peer" />
                        <div class="w-10 h-5 bg-gray-200 peer-checked:bg-emerald-500 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5"></div>
                    </div>
                    <div><p class="text-sm font-bold text-gray-800">WhatsApp Confirmation</p><p class="text-sm text-gray-400">Requires WhatsApp Business API key.</p></div>
                </label>
            </div>
        </div>

        <transition enter-active-class="transition" enter-from-class="opacity-0 translate-y-4" leave-active-class="transition" leave-to-class="opacity-0 translate-y-4">
            <div v-if="toast" class="fixed bottom-8 left-1/2 -translate-x-1/2 bg-indigo-600 text-white px-6 py-3 rounded-xl shadow-xl text-sm font-black flex items-center gap-2 z-50">
                <i class="fas fa-check-circle"></i> Cart settings saved!
            </div>
        </transition>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const saving = ref(false);
const toast  = ref(false);
const tab    = ref('cart');
const tabs   = [
    { key:'cart',      label:'Cart',        icon:'fas fa-shopping-cart'  },
    { key:'checkout',  label:'Checkout',    icon:'fas fa-credit-card'    },
    { key:'abandoned', label:'Recovery',    icon:'fas fa-cart-arrow-down'},
    { key:'confirm',   label:'Confirmation',icon:'fas fa-check-circle'   },
];
const kpis = [
    { label:'Active Carts',    value:'34',   icon:'fas fa-shopping-cart',  color:'#6366f1', bg:'#eef2ff' },
    { label:'Abandoned Today', value:'12',   icon:'fas fa-cart-arrow-down',color:'#f59e0b', bg:'#fffbeb' },
    { label:'Recovery Rate',   value:'28%',  icon:'fas fa-undo',           color:'#10b981', bg:'#ecfdf5' },
    { label:'Avg Cart Value',  value:'₹1.8K',icon:'fas fa-rupee-sign',     color:'#ec4899', bg:'#fdf2f8' },
];
const s = ref({
    cart_expiry:72, max_items:50, show_stock:true, qty_edit:true, save_cart:true, coupon_in_cart:true,
    checkout_mode:'guest_and_login', address_mode:'full', order_notes:true, upsells:false, gst:true,
    abandoned_enabled:true, abandoned_delay_1:1, abandoned_delay_2:24, abandoned_coupon:'COMEBACK10', abandoned_coupon_pct:10,
    confirm_msg:"Thank you! We'll ship within 2 business days.", reply_email:'', bcc_email:'', whatsapp_confirm:false,
});
const checkoutSteps = ['Cart','Login/Guest','Address','Payment','Confirmation'];
const cartToggles = [
    { key:'show_stock',    label:'Show Stock Badge',       desc:'Display "Only 3 left!" on product cards' },
    { key:'qty_edit',      label:'Allow Quantity Edit',    desc:'Customers can change quantity in cart'   },
    { key:'save_cart',     label:'Save Cart for Members',  desc:'Persist cart for logged-in users'        },
    { key:'coupon_in_cart',label:'Coupon Field in Cart',   desc:'Show promo code input on cart page'      },
];
const checkoutToggles = [
    { key:'order_notes',label:'Order Notes Field',  desc:'Let customers add delivery notes at checkout' },
    { key:'upsells',    label:'One-Click Upsells',  desc:'Show recommended products before payment'     },
    { key:'gst',        label:'GST Invoice PDF',    desc:'Auto-generate and email GST invoice'          },
];
const abandonedStats = [
    { label:'Emails Sent', value:'124' },
    { label:'Recovered',   value:'35'  },
    { label:'Revenue',     value:'₹82K'},
];
const saveAll = async () => {
    saving.value = true;
    try { await axios.post('/cms/settings/cart', s.value); } catch {}
    toast.value = true; setTimeout(() => toast.value = false, 3000);
    saving.value = false;
};
</script>
<style scoped>
.field-label { display:block; font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#374151; margin-bottom:.25rem; }
.field-input { width:100%; background:#f9fafb; border:1px solid #e5e7eb; border-radius:.5rem; padding:.5rem .75rem; font-size:.875rem; outline:none; }
.field-input:focus { border-color:#6366f1; box-shadow:0 0 0 2px rgba(99,102,241,.15); }
</style>

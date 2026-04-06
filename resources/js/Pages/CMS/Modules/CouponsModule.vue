<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">Coupons & Discounts</h2>
                <p class="text-xs text-gray-500 mt-0.5">Create promo codes, flash sales, and bulk discount rules for your store.</p>
            </div>
            <button @click="openCreate" class="px-4 py-2 bg-indigo-600 text-white rounded-xl text-sm font-bold shadow-md shadow-indigo-500/20 hover:bg-indigo-700 transition-all flex items-center gap-2">
                <i class="fas fa-tag text-xs"></i> New Coupon
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-8 space-y-6">
            <!-- KPIs -->
            <div class="grid grid-cols-4 gap-4">
                <div v-for="k in kpis" :key="k.label" class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" :style="{background:k.bg,color:k.color}">
                        <i :class="k.icon" class="text-sm"></i>
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-900">{{ k.value }}</p>
                        <p class="text-sm font-bold text-gray-400 uppercase tracking-wider">{{ k.label }}</p>
                    </div>
                </div>
            </div>

            <!-- Filter tabs -->
            <div class="flex gap-1.5">
                <button v-for="t in tabs" :key="t" @click="activeTab = t"
                    class="px-3 py-1.5 rounded-xl text-sm font-black transition-all"
                    :class="activeTab === t ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'">
                    {{ t }}
                </button>
            </div>

            <!-- Coupons table -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="text-left py-3 px-5 text-sm font-black uppercase tracking-widest text-gray-400">Code</th>
                                <th class="text-left py-3 px-4 text-sm font-black uppercase tracking-widest text-gray-400">Type</th>
                                <th class="text-left py-3 px-4 text-sm font-black uppercase tracking-widest text-gray-400">Discount</th>
                                <th class="text-left py-3 px-4 text-sm font-black uppercase tracking-widest text-gray-400">Used</th>
                                <th class="text-left py-3 px-4 text-sm font-black uppercase tracking-widest text-gray-400">Limit</th>
                                <th class="text-left py-3 px-4 text-sm font-black uppercase tracking-widest text-gray-400">Expiry</th>
                                <th class="text-left py-3 px-4 text-sm font-black uppercase tracking-widest text-gray-400">Status</th>
                                <th class="py-3 px-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="c in filteredCoupons" :key="c.id" class="hover:bg-gray-50 transition-colors group">
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono font-black text-gray-900 text-sm tracking-widest">{{ c.code }}</span>
                                        <button @click="copy(c.code)" class="opacity-0 group-hover:opacity-100 transition-opacity text-gray-400 hover:text-indigo-600">
                                            <i class="fas fa-copy text-sm"></i>
                                        </button>
                                    </div>
                                    <p class="text-sm text-gray-400 mt-0.5">{{ c.description }}</p>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-sm font-black"
                                        :class="c.type === 'percentage' ? 'bg-purple-100 text-purple-700' : c.type === 'flat' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700'">
                                        {{ c.type }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-black text-gray-900">
                                    {{ c.type === 'percentage' ? c.value + '%' : c.type === 'flat' ? '₹' + c.value : 'Free Ship' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900 text-xs">{{ c.used }}</span>
                                        <div class="w-16 h-1 bg-gray-100 rounded-full mt-1">
                                            <div class="h-full rounded-full" :style="{width: Math.min((c.used / (c.limit || c.used || 1)) * 100, 100) + '%', background: '#6366f1'}"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-xs text-gray-500">{{ c.limit || '∞' }}</td>
                                <td class="py-3.5 px-4 text-xs" :class="isExpired(c.expiry) ? 'text-red-500 font-bold' : 'text-gray-500'">
                                    {{ c.expiry || '—' }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <label class="relative cursor-pointer">
                                        <input type="checkbox" :checked="c.active" @change="updateStatus(c)" class="sr-only peer" />
                                        <div class="w-8 h-4 bg-gray-200 peer-checked:bg-emerald-500 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-3 after:h-3 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-4 after:shadow-sm"></div>
                                    </label>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="editCoupon(c)" class="w-7 h-7 bg-indigo-50 text-indigo-600 rounded-lg flex items-center justify-center hover:bg-indigo-100">
                                            <i class="fas fa-pen text-sm"></i>
                                        </button>
                                        <button @click="deleteCoupon(c)" class="w-7 h-7 bg-red-50 text-red-500 rounded-lg flex items-center justify-center hover:bg-red-100">
                                            <i class="fas fa-trash text-sm"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!filteredCoupons.length">
                                <td colspan="8" class="py-10 text-center text-gray-400 text-xs">
                                    <i class="fas fa-tag text-3xl text-gray-200 mb-3 block"></i>No coupons found
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="modal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm z-50 flex items-center justify-center" @click.self="modal = false">
            <div class="bg-white rounded-2xl shadow-2xl w-[500px] p-7 max-h-[85vh] overflow-y-auto">
                <h3 class="text-base font-black text-gray-900 mb-5">{{ editMode ? 'Edit Coupon' : 'Create Coupon' }}</h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">Coupon Code</label>
                            <div class="flex gap-2">
                                <input v-model="form.code" class="flex-1 field-input font-mono uppercase" placeholder="SAVE20" @input="form.code = form.code.toUpperCase()" />
                                <button @click="form.code = generateCode()" class="px-2.5 py-2 bg-gray-100 rounded-lg text-xs text-gray-600 hover:bg-gray-200 shrink-0">
                                    <i class="fas fa-dice"></i>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Discount Type</label>
                            <select v-model="form.type" class="field-input">
                                <option value="percentage">Percentage Off</option>
                                <option value="flat">Flat Amount (₹)</option>
                                <option value="free_shipping">Free Shipping</option>
                            </select>
                        </div>
                    </div>
                    <div v-if="form.type !== 'free_shipping'" class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">Discount Value {{ form.type === 'percentage' ? '(%)' : '(₹)' }}</label>
                            <input v-model.number="form.value" type="number" class="field-input" :placeholder="form.type === 'percentage' ? '20' : '500'" />
                        </div>
                        <div>
                            <label class="field-label">Min Order Amount (₹)</label>
                            <input v-model.number="form.min_order" type="number" class="field-input" placeholder="0" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">Usage Limit <span class="text-gray-300 font-normal">(leave blank = unlimited)</span></label>
                            <input v-model.number="form.limit" type="number" class="field-input" placeholder="∞" />
                        </div>
                        <div>
                            <label class="field-label">Per User Limit</label>
                            <input v-model.number="form.per_user" type="number" class="field-input" placeholder="1" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="field-label">Start Date</label>
                            <input v-model="form.start_date" type="date" class="field-input" />
                        </div>
                        <div>
                            <label class="field-label">Expiry Date</label>
                            <input v-model="form.expiry" type="date" class="field-input" />
                        </div>
                    </div>
                    <div>
                        <label class="field-label">Description / Internal Note</label>
                        <input v-model="form.description" class="field-input" placeholder="Summer sale 20% off..." />
                    </div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" v-model="form.active" class="sr-only peer" />
                            <div class="w-10 h-5 bg-gray-200 peer-checked:bg-emerald-500 rounded-full transition-colors relative after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:w-4 after:h-4 after:bg-white after:rounded-full after:transition-all peer-checked:after:translate-x-5 after:shadow-sm"></div>
                        </div>
                        <span class="text-sm font-bold text-gray-800">Active (immediately usable)</span>
                    </label>
                </div>
                <div class="flex gap-3 mt-6">
                    <button @click="modal = false" class="flex-1 py-2.5 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50">Cancel</button>
                    <button @click="saveCoupon" :disabled="saving" class="flex-1 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 disabled:opacity-50 flex justify-center items-center gap-2">
                        <i v-if="saving" class="fas fa-spinner fa-spin text-xs"></i>
                        {{ editMode ? 'Save Changes' : 'Create Coupon' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({ coupons: { type: Array, default: () => [] } });

const couponList = ref(props.coupons.length ? [...props.coupons] : [
    { id:1, code:'WELCOME20', type:'percentage', value:20, used:45,  limit:200,  expiry:'2026-12-31', active:true,  description:'New user welcome coupon',    min_order:0   },
    { id:2, code:'FLAT500',   type:'flat',       value:500,used:12,  limit:100,  expiry:'2026-06-30', active:true,  description:'₹500 off on orders > ₹2000', min_order:2000},
    { id:3, code:'FREESHIP',  type:'free_shipping',value:0,used:88,  limit:null, expiry:null,         active:true,  description:'Free shipping always',       min_order:0   },
    { id:4, code:'SUMMER30',  type:'percentage', value:30, used:302, limit:300,  expiry:'2026-03-31', active:false, description:'Summer 30% off — expired',    min_order:0   },
]);

const tabs      = ['All', 'Active', 'Expired', 'Disabled'];
const activeTab = ref('All');
const modal     = ref(false);
const editMode  = ref(false);
const saving    = ref(false);
const form      = ref({});
const editId    = ref(null);

const kpis = computed(() => [
    { label:'Total Coupons',  value: couponList.value.length,                              icon:'fas fa-tags',       color:'#6366f1', bg:'#eef2ff' },
    { label:'Active',         value: couponList.value.filter(c=>c.active).length,          icon:'fas fa-check-circle',color:'#10b981', bg:'#ecfdf5' },
    { label:'Total Redeemed', value: couponList.value.reduce((s,c)=>s+(c.used||0),0),      icon:'fas fa-receipt',    color:'#f59e0b', bg:'#fffbeb' },
    { label:'Expired',        value: couponList.value.filter(c=>isExpired(c.expiry)).length,icon:'fas fa-clock',     color:'#ef4444', bg:'#fef2f2' },
]);

const filteredCoupons = computed(() => {
    let list = couponList.value;
    if (activeTab.value === 'Active')   list = list.filter(c => c.active && !isExpired(c.expiry));
    if (activeTab.value === 'Expired')  list = list.filter(c => isExpired(c.expiry));
    if (activeTab.value === 'Disabled') list = list.filter(c => !c.active);
    return list;
});

const isExpired = (d) => d && new Date(d) < new Date();

const generateCode = () => {
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return Array.from({ length: 8 }, () => chars[Math.floor(Math.random() * chars.length)]).join('');
};

const copy = (code) => navigator.clipboard?.writeText(code).then(() => alert('Copied: ' + code));

const openCreate = () => {
    editMode.value = false;
    editId.value   = null;
    form.value = { code:'', type:'percentage', value:10, min_order:0, limit:null, per_user:1, expiry:'', start_date:'', description:'', active:true };
    modal.value = true;
};

const editCoupon = (c) => {
    editMode.value = true;
    editId.value   = c.id;
    form.value = { ...c };
    modal.value = true;
};

const deleteCoupon = (c) => {
    if (!confirm(`Delete coupon "${c.code}"?`)) return;
    try { axios.delete(route('cms.coupons.destroy', c.id)); } catch {}
    couponList.value = couponList.value.filter(x => x.id !== c.id);
};

const updateStatus = async (c) => {
    c.active = !c.active;
    try { await axios.put(route('cms.coupons.update', c.id), { active: c.active }); } catch {}
};

const saveCoupon = async () => {
    if (!form.value.code) return;
    saving.value = true;
    try {
        if (editMode.value) {
            await axios.put(route('cms.coupons.update', editId.value), form.value);
            const idx = couponList.value.findIndex(c => c.id === editId.value);
            if (idx !== -1) couponList.value[idx] = { ...couponList.value[idx], ...form.value };
        } else {
            const { data } = await axios.post(route('cms.coupons.store'), { ...form.value, used: 0 });
            couponList.value.unshift(data);
        }
    } catch {
        if (!editMode.value) couponList.value.unshift({ id: Date.now(), ...form.value, used: 0 });
        else { const idx = couponList.value.findIndex(c => c.id === editId.value); if (idx !== -1) couponList.value[idx] = { ...couponList.value[idx], ...form.value }; }
    }
    modal.value  = false;
    saving.value = false;
};
</script>

<style scoped>
.field-label { display:block; font-size:0.7rem; font-weight:700; text-transform:uppercase; letter-spacing:0.05em; color:#374151; margin-bottom:0.25rem; }
.field-input { width:100%; background:#f9fafb; border:1px solid #e5e7eb; border-radius:0.5rem; padding:0.5rem 0.75rem; font-size:0.875rem; outline:none; transition:border-color 0.15s; }
.field-input:focus { border-color:#6366f1; box-shadow:0 0 0 2px rgba(99,102,241,0.15); }
</style>

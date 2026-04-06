<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    orders: { type: Array, default: () => [] },
    activeSite: { type: Object, default: null }
});

const list = ref([...(props.orders || [])]);
const loading = ref(false);

const fetchData = async () => {
    loading.value = true;
    try {
        const res = await axios.get(route('cms.orders.index'), { params: { site_id: props.activeSite?.id } });
        list.value = Array.isArray(res.data) ? res.data : (res.data.data || []);
    } catch (e) {
        console.error('Failed to fetch orders:', e);
    } finally {
        loading.value = false;
    }
};

import { onMounted, watch } from 'vue';
onMounted(() => {
    if (!list.value.length) fetchData();
});

watch(() => props.orders, (newOrders) => { list.value = [...(newOrders || [])]; }, { deep: true });
watch(() => props.activeSite, () => { fetchData(); }, { deep: true });

const statusFilter = ref('all');
const orderStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];

const kpis = computed(() => [
    { label: 'Total Orders', value: list.value.length, color: '#6366f1' },
    { label: 'Revenue', value: '₹' + list.value.reduce((s, o) => s + Number(o.total || 0), 0).toLocaleString('en-IN'), color: '#10b981' },
    { label: 'Pending', value: list.value.filter(o => o.status === 'pending').length, color: '#f59e0b' },
    { label: 'Delivered', value: list.value.filter(o => o.status === 'delivered').length, color: '#06b6d4' },
]);

const statusFilters = computed(() => [
    { val: 'all', label: 'All', count: list.value.length },
    { val: 'pending', label: 'Pending', count: list.value.filter(o => o.status === 'pending').length },
    { val: 'shipped', label: 'Shipped', count: list.value.filter(o => o.status === 'shipped').length },
    { val: 'delivered', label: 'Delivered', count: null },
    { val: 'cancelled', label: 'Cancelled', count: null },
]);

const filtered = computed(() => statusFilter.value === 'all' ? list.value : list.value.filter(o => o.status === statusFilter.value));

const statusClass = (s) => ({
    pending: 'bg-amber-50 text-amber-700 border-amber-200',
    confirmed: 'bg-blue-50 text-blue-700 border-blue-200',
    processing: 'bg-purple-50 text-purple-700 border-purple-200',
    shipped: 'bg-sky-50 text-sky-700 border-sky-200',
    delivered: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    cancelled: 'bg-red-50 text-red-700 border-red-200',
    refunded: 'bg-gray-100 text-gray-700 border-gray-300',
}[s] || 'bg-gray-100 text-gray-600 border-gray-200');

const relativeDate = (d) => {
    if (!d) return '';
    const diff = Math.floor((Date.now() - new Date(d)) / 1000);
    if (diff < 60) return 'Just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    return new Date(d).toLocaleDateString('en-IN');
};

const updateStatus = async (order, newStatus) => {
    try {
        await axios.post(route('cms.orders.update-status', order.id), { status: newStatus });
        order.status = newStatus;
    } catch {
        alert('Status update failed');
    }
};

const activeOrder = ref(null);
const orderDetails = ref(null);
const loadingOrder = ref(false);

const viewOrder = async (o) => {
    activeOrder.value = o;
    loadingOrder.value = true;
    orderDetails.value = null;
    try {
        const res = await axios.get(route('cms.orders.invoice', o.id));
        orderDetails.value = res.data;
    } catch {
        alert('Failed to load order details');
        activeOrder.value = null;
    } finally {
        loadingOrder.value = false;
    }
};

const printInvoice = (o) => {
    window.open(route('cms.orders.invoice', o.id), '_blank');
};
</script>

<template>
    <div class="h-full flex flex-col bg-gray-50">
        <div class="h-16 border-b border-gray-200 bg-white flex items-center justify-between px-8 shrink-0 shadow-sm">
            <div>
                <h2 class="text-xl font-black text-gray-900 tracking-tight">Orders</h2>
                <p class="text-xs text-gray-500 mt-0.5">Track orders, update status, refund payments.</p>
            </div>
            <div class="flex items-center gap-2">
                <button v-for="f in statusFilters" :key="f.val" @click="statusFilter = f.val"
                    class="px-2.5 py-1 rounded-full text-sm font-black transition-all"
                    :class="statusFilter === f.val ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                    {{ f.label }} <span v-if="f.count" class="ml-1 opacity-70">({{ f.count }})</span>
                </button>
            </div>
        </div>

        <!-- KPIs -->
        <div class="px-8 py-4 grid grid-cols-4 gap-4 shrink-0">
            <div v-for="k in kpis" :key="k.label" class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
                <p class="text-xl font-black text-gray-900">{{ k.value }}</p>
                <p class="text-sm font-bold text-gray-400 uppercase tracking-wider mt-0.5">{{ k.label }}</p>
                <div class="h-0.5 mt-2 rounded-full" :style="{ background: k.color, width: '40%' }"></div>
            </div>
        </div>

        <!-- Table -->
        <div class="flex-1 overflow-y-auto px-8 pb-8">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <table class="w-full text-left font-sans">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200">
                            <th class="py-3 px-5 text-sm font-black uppercase tracking-widest text-gray-500">Order #</th>
                            <th class="py-3 px-5 text-sm font-black uppercase tracking-widest text-gray-500">Customer</th>
                            <th class="py-3 px-5 text-sm font-black uppercase tracking-widest text-gray-500">Amount</th>
                            <th class="py-3 px-5 text-sm font-black uppercase tracking-widest text-gray-500">Status</th>
                            <th class="py-3 px-5 text-sm font-black uppercase tracking-widest text-gray-500">Date</th>
                            <th class="py-3 px-5 text-sm font-black uppercase tracking-widest text-gray-500 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="o in filtered" :key="o.id" class="hover:bg-gray-50 transition-colors group">
                            <td class="py-3 px-5">
                                <span class="text-sm font-black text-indigo-600 font-mono">#{{ o.order_number }}</span>
                            </td>
                            <td class="py-3 px-5">
                                <p class="text-sm font-bold text-gray-900 truncate max-w-[140px]">{{ o.customer_name }}</p>
                                <p class="text-sm text-gray-400 font-medium">{{ o.customer_email }}</p>
                            </td>
                            <td class="py-3 px-5">
                                <span class="font-black text-gray-900 text-sm">₹{{ Number(o.total).toLocaleString('en-IN') }}</span>
                            </td>
                            <td class="py-3 px-5">
                                <select :value="o.status" @change="updateStatus(o, $event.target.value)"
                                    class="text-sm font-black px-2 py-1 rounded-full border cursor-pointer outline-none transition-all"
                                    :class="statusClass(o.status)">
                                    <option v-for="s in orderStatuses" :key="s" :value="s">{{ s.toUpperCase() }}</option>
                                </select>
                            </td>
                            <td class="py-3 px-5 text-xs text-gray-500 font-medium">{{ relativeDate(o.created_at) }}</td>
                            <td class="py-3 px-5">
                                <div class="flex justify-end gap-1.5">
                                    <button @click="viewOrder(o)" class="action-btn hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50"><i class="fas fa-eye text-sm"></i></button>
                                    <button @click="printInvoice(o)" class="action-btn hover:text-blue-600 hover:border-blue-200 hover:bg-blue-50"><i class="fas fa-print text-sm"></i></button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!filtered.length">
                            <td colspan="6" class="py-20 text-center text-gray-400">
                                <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100">
                                    <i class="fas fa-receipt text-2xl text-gray-200"></i>
                                </div>
                                <p class="font-black uppercase tracking-widest text-sm">No orders yet</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Order Detail Modal -->
        <div v-if="activeOrder" class="fixed inset-0 bg-gray-900/60 backdrop-blur-md z-50 flex items-center justify-center p-4 animate-in fade-in duration-300" @click.self="activeOrder = null">
            <div class="bg-white rounded-3xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden transition-all scale-in duration-300">
                <div class="p-6 border-b border-gray-100 flex items-center justify-between shrink-0 bg-gray-50/50">
                    <div class="flex items-center gap-5">
                        <div class="w-14 h-14 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-2xl shadow-lg shadow-indigo-200"><i class="fas fa-receipt"></i></div>
                        <div>
                            <h3 class="text-xl font-black text-gray-900 leading-none">Order #{{ activeOrder.order_number }}</h3>
                            <p class="text-sm text-gray-400 font-black uppercase tracking-wider mt-1.5 flex items-center gap-2">
                                <i class="far fa-calendar-alt"></i> {{ new Date(activeOrder.created_at).toLocaleString() }}
                                <span class="text-gray-200">|</span>
                                <i class="far fa-user"></i> {{ activeOrder.customer_name }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <select :value="activeOrder.status" @change="updateStatus(activeOrder, $event.target.value)"
                            class="px-4 py-1.5 rounded-full text-sm font-black border uppercase tracking-widest outline-none shadow-sm"
                            :class="statusClass(activeOrder.status)">
                            <option v-for="s in orderStatuses" :key="s" :value="s">{{ s.toUpperCase() }}</option>
                        </select>
                        <button @click="activeOrder = null" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 transition-colors"><i class="fas fa-times"></i></button>
                    </div>
                </div>

                <div v-if="loadingOrder" class="flex-1 flex flex-col items-center justify-center p-20 gap-4">
                    <i class="fas fa-circle-notch fa-spin text-4xl text-indigo-600"></i>
                    <p class="text-xs font-black uppercase tracking-widest text-gray-400">Fetching order intel...</p>
                </div>
                <div v-else-if="orderDetails" class="flex-1 overflow-y-auto p-8 space-y-10">
                    <!-- Progress Bar -->
                    <div class="flex items-center justify-between px-16 relative">
                        <div class="absolute h-1 bg-gray-100 left-20 right-20 top-1/2 -translate-y-1/2 z-0 rounded-full">
                            <div class="h-full bg-indigo-600 rounded-full transition-all duration-1000" 
                                :style="{ width: (orderStatuses.indexOf(activeOrder.status) / (orderStatuses.length - 1) * 100) + '%' }"></div>
                        </div>
                        <div v-for="s in ['pending','processing','shipped','delivered']" :key="s" class="relative z-10 flex flex-col items-center gap-3">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-700 border-4 border-white shadow-md"
                                :class="activeOrder.status === s || orderStatuses.indexOf(activeOrder.status) > orderStatuses.indexOf(s) ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-300'">
                                <i :class="activeOrder.status === s || orderStatuses.indexOf(activeOrder.status) > orderStatuses.indexOf(s) ? 'fas fa-check' : 'fas fa-circle'" class="text-sm"></i>
                            </div>
                            <span class="text-sm font-black uppercase tracking-widest" :class="activeOrder.status === s || orderStatuses.indexOf(activeOrder.status) > orderStatuses.indexOf(s) ? 'text-indigo-600' : 'text-gray-400'">{{ s }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-12 gap-10">
                        <!-- Items -->
                        <div class="col-span-8">
                            <h4 class="text-xs font-black uppercase text-gray-400 tracking-widest mb-6 flex items-center gap-3">
                                <span class="w-6 h-6 rounded bg-gray-100 flex items-center justify-center text-sm"><i class="fas fa-shopping-basket"></i></span>
                                Order Items
                            </h4>
                            <div class="space-y-3">
                                <div v-for="item in orderDetails.items" :key="item.id" class="flex items-center gap-5 p-4 bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow group">
                                    <div class="w-20 h-20 rounded-xl bg-gray-50 border border-gray-100 overflow-hidden shrink-0">
                                        <img v-if="item.image" :src="item.image" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                                        <div v-else class="w-full h-full flex items-center justify-center text-gray-200 text-xl"><i class="fas fa-image"></i></div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-base font-black text-gray-900 truncate">{{ item.name }}</p>
                                        <p class="text-base font-bold text-gray-400 mt-0.5">SKU: {{ item.sku }} • Qty: <span class="text-indigo-600">{{ item.quantity }}</span></p>
                                        <div v-if="item.variation" class="mt-2 flex">
                                            <span class="text-sm bg-indigo-50 text-indigo-600 font-black px-2 py-0.5 rounded-lg border border-indigo-100 uppercase tracking-tighter">{{ item.variation }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-base font-black text-gray-900 italic">₹{{ Number(item.price * item.quantity).toLocaleString('en-IN') }}</p>
                                        <p class="text-sm text-gray-400 font-bold uppercase tracking-tighter">₹{{ Number(item.price).toLocaleString('en-IN') }} / unit</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Totals -->
                            <div class="mt-8 bg-gray-50 rounded-2xl p-6 border border-gray-100 space-y-3 shadow-inner">
                                <div class="flex justify-between text-xs"><span class="text-gray-500 font-bold">Subtotal</span><span class="font-black text-gray-800">₹{{ Number(activeOrder.subtotal).toLocaleString('en-IN') }}</span></div>
                                <div class="flex justify-between text-xs"><span class="text-gray-500 font-bold">GST (18%)</span><span class="font-black text-gray-800">₹{{ Number(activeOrder.tax_amount).toLocaleString('en-IN') }}</span></div>
                                <div class="flex justify-between text-xs"><span class="text-gray-500 font-bold">Shipping</span><span class="font-black text-emerald-600 uppercase tracking-widest text-sm">Calculated as Free</span></div>
                                <div class="pt-3 mt-3 border-t border-gray-200 flex justify-between items-center">
                                    <span class="text-sm font-black text-gray-500 uppercase tracking-widest">Grand Total</span>
                                    <span class="text-2xl font-black text-indigo-600 tracking-tight italic">₹{{ Number(activeOrder.total).toLocaleString('en-IN') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Side Info -->
                        <div class="col-span-4 space-y-8">
                            <!-- Shipping -->
                            <div class="bg-indigo-600 text-white p-6 rounded-3xl shadow-xl shadow-indigo-100 relative overflow-hidden group">
                                <div class="absolute -right-4 -bottom-4 text-white/5 text-6xl rotate-12 group-hover:scale-125 transition-transform duration-700"><i class="fas fa-truck"></i></div>
                                <h5 class="text-sm font-black uppercase text-white/60 mb-4 tracking-widest">Shipping Destination</h5>
                                <div class="text-xs font-bold leading-relaxed space-y-1">
                                    <p class="text-lg mb-2">{{ orderDetails.shipping?.name }}</p>
                                    <p class="opacity-80">{{ orderDetails.shipping?.address }}</p>
                                    <p class="opacity-80">{{ orderDetails.shipping?.city }}, {{ orderDetails.shipping?.state }}</p>
                                    <p class="opacity-80">{{ orderDetails.shipping?.pincode }}</p>
                                    <div class="pt-4 flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-lg bg-white/20 flex items-center justify-center text-sm"><i class="fas fa-phone"></i></div>
                                        <span class="text-sm">{{ orderDetails.shipping?.phone }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Info -->
                            <div class="space-y-4">
                                <h5 class="text-sm font-black uppercase text-gray-400 tracking-widest pl-2">Payment Intel</h5>
                                <div class="flex items-center gap-4 p-4 bg-white border border-gray-100 rounded-2xl shadow-sm">
                                    <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-indigo-600 shadow-inner"><i class="fas fa-credit-card"></i></div>
                                    <div class="flex-1">
                                        <p class="text-xs font-black text-gray-900">{{ activeOrder.payment_method || 'Online Transaction' }}</p>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <span class="w-1.5 h-1.5 rounded-full" :class="activeOrder.payment_status === 'paid' ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                                            <p class="text-sm font-black uppercase" :class="activeOrder.payment_status === 'paid' ? 'text-emerald-500' : 'text-amber-500'">{{ activeOrder.payment_status }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tracking -->
                            <div class="space-y-4">
                                <h5 class="text-sm font-black uppercase text-gray-400 tracking-widest pl-2">Delivery Tracking</h5>
                                <div class="bg-gray-50 p-4 rounded-2xl border border-gray-100 space-y-4 shadow-inner">
                                    <input v-model="activeOrder.tracking_number" placeholder="Paste AWB Number..." class="w-full text-xs font-black p-3 bg-white border border-gray-200 rounded-xl outline-none focus:border-indigo-400 shadow-sm transition-all" />
                                    <button @click="updateStatus(activeOrder, activeOrder.status)" class="w-full py-3 bg-gray-900 text-white text-sm font-black uppercase tracking-widest rounded-xl hover:bg-black transition-all shadow-lg hover:shadow-black/20">Update Tracking</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-6 bg-white border-t border-gray-100 flex gap-4 shrink-0 shadow-[0_-10px_20px_rgba(0,0,0,0.02)]">
                    <button class="flex-1 py-4 bg-gray-50 border border-gray-200 rounded-2xl text-sm font-black uppercase tracking-widest text-gray-600 hover:bg-gray-100 flex items-center justify-center gap-3 transition-all">
                        <i class="fas fa-envelope text-xs opacity-50"></i> Notify Customer
                    </button>
                    <button @click="printInvoice(activeOrder)" class="flex-1 py-4 bg-indigo-600 text-white rounded-2xl text-sm font-black uppercase tracking-widest hover:bg-indigo-700 shadow-xl shadow-indigo-200 flex items-center justify-center gap-3 transition-all group">
                        <i class="fas fa-file-invoice text-xs group-hover:scale-110 transition-transform"></i> Download Invoice
                    </button>
                    <button @click="activeOrder = null" class="w-14 py-4 bg-red-50 text-red-500 rounded-2xl text-xs font-black flex items-center justify-center hover:bg-red-500 hover:text-white transition-all">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.action-btn { width:2.25rem; height:2.25rem; display:flex; align-items:center; justify-content:center; border-radius:10px; border:1px solid #f3f4f6; background:#fff; color:#9ca3af; transition:all 0.2s cubic-bezier(0.4, 0, 0.2, 1); cursor:pointer; }
.action-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
</style>

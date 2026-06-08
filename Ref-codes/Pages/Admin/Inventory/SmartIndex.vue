<script setup>
import { Head, Link, router, useForm, usePage } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
import { ref } from "vue";
import { Html5QrcodeScanner } from "html5-qrcode";
import Modal from "@/Components/Modal.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import PremiumModal from "@/Components/PremiumModal.vue";
import Pagination from "@/Components/Pagination.vue";
import { useToastStore } from "@/stores/toast";
import GradientHeroHeader from "@/Components/UI/GradientHeroHeader.vue";
import {
  ChartBarIcon,
  TableCellsIcon as TableIcon,
  ShoppingCartIcon,
  QrCodeIcon,
  ArchiveBoxIcon,
  PlusIcon,
  ArrowTrendingUpIcon as ChartBarIconAlt,
  ExclamationTriangleIcon,
  ArrowPathIcon,
  RectangleGroupIcon,
  InboxArrowDownIcon,
  ShieldCheckIcon,
  CubeIcon,
  InformationCircleIcon,
  ClockIcon,
  PencilIcon,
  XMarkIcon,
  TrashIcon,
  TagIcon,
  BanknotesIcon,
  ScaleIcon,
  CheckCircleIcon,
} from "@heroicons/vue/24/solid";

defineOptions({ layout: MainLayout });

const props = defineProps({
  tab: { type: String, default: "stats" },
  stats: Object,
  items: Object,
  procurement: Array,
});

const tabs = [
  { id: "stats", label: "Overview", icon: ChartBarIcon },
  { id: "list", label: "Items", icon: TableIcon },
  { id: "procurement", label: "Shopping List", icon: ShoppingCartIcon },
  { id: "scanner", label: "Scanner", icon: QrCodeIcon },
];

const searchQuery = ref(new URLSearchParams(window.location.search).get("search") || "");

const handleSearch = () => {
  router.get(
    route("admin.inventory.dashboard"),
    { view: "list", search: searchQuery.value },
    { preserveState: true, replace: true }
  );
};

const switchTab = (id) => {
  if (id === "scanner") {
    router.visit(route("admin.inventory.scanner"));
  } else {
    const params = { view: id };
    if (id === 'list' && searchQuery.value) {
      params.search = searchQuery.value;
    }
    router.get(
      route("admin.inventory.dashboard"),
      params,
      {
        preserveState: true,
        replace: true,
        only: ["items", "stats", "procurement", "tab"],
      },
    );
  }
};

// Restock Logic
const recordToDelete = ref(null);
const toast = useToastStore();
const showDeleteModal = ref(false);
const showRestockModal = ref(false);
const selectedItem = ref({});
const restockForm = useForm({
  quantity: 10,
  unit_cost: 0,
});

const openRestock = (item) => {
  closeDeleteConfirm();
  closeEdit();
  selectedItem.value = item;
  restockForm.reset();
  showRestockModal.value = true;
};

const closeRestock = () => {
  showRestockModal.value = false;
  selectedItem.value = {};
};

const submitRestock = () => {
  restockForm.post(route("admin.inventory.add-stock", selectedItem.value.id), {
    onSuccess: () => closeRestock(),
  });
};

// Edit Logic
const showEditModal = ref(false);
const editForm = useForm({
  id: null,
  name: "",
  sku: "",
  category: "",
  current_stock: 0,
  min_stock_level: 0,
  unit_cost: 0,
  unit: "",
});

const openEdit = (item) => {
  closeDeleteConfirm();
  closeRestock();
  editForm.id = item.id;
  editForm.name = item.name;
  editForm.sku = item.sku;
  editForm.category = item.category;
  editForm.current_stock = item.current_stock;
  editForm.min_stock_level = item.min_stock_level;
  editForm.unit_cost = item.unit_cost;
  editForm.unit = item.unit;
  showEditModal.value = true;
};

const closeEdit = () => {
  showEditModal.value = false;
  editForm.reset();
};

const submitEdit = () => {
  editForm.put(route("admin.inventory.update", editForm.id), {
    onSuccess: () => closeEdit(),
  });
};

const openDeleteConfirm = (row) => {
  closeRestock();
  closeEdit();
  recordToDelete.value = row;
  showDeleteModal.value = true;
};

const closeDeleteConfirm = () => {
  showDeleteModal.value = false;
  recordToDelete.value = null;
};

// Delete Logic
const deleteItem = () => {
  if (!recordToDelete.value) return;

  router.delete(route('admin.inventory.destroy', recordToDelete.value.id), {
    onSuccess: () => {
      toast.success("Item deleted successfully");
      closeDeleteConfirm();
      // Success message handled by flash
    },
    onError: () => {
      toast.error("Failed to delete item");
    },
  });
};

const showScannerModal = ref(false);
const scanSuccessMsg = ref('');
const scanErrorMsg = ref('');
const scannedItem = ref(null);
const scanForm = useForm({
  barcode: ''
});
const page = usePage();
let dashboardScanner = null;

const openScannerModal = () => {
  showScannerModal.value = true;
  scanSuccessMsg.value = '';
  scanErrorMsg.value = '';
  scannedItem.value = null;
  scanForm.reset();
  
  setTimeout(() => {
    const scannerElement = document.getElementById('modal-reader');
    if (scannerElement) {
      dashboardScanner = new Html5QrcodeScanner(
        "modal-reader",
        {
          fps: 20,
          qrbox: { width: 250, height: 250 },
          aspectRatio: 1.0
        },
        false
      );
      dashboardScanner.render((decodedText) => {
        scanForm.barcode = decodedText;
        if (dashboardScanner) {
          dashboardScanner.pause();
        }
        submitModalScan();
      }, () => {});
    }
  }, 300);
};

const closeScannerModal = () => {
  showScannerModal.value = false;
  if (dashboardScanner) {
    dashboardScanner.clear().catch(err => console.error(err));
    dashboardScanner = null;
  }
};

const submitModalScan = () => {
  scanForm.post(route('admin.inventory.scan'), {
    preserveScroll: true,
    onSuccess: () => {
      const flash = page.props.flash || {};
      if (flash.scan_success) {
        scanSuccessMsg.value = flash.message || 'Verified successfully';
        scannedItem.value = flash.item;
        scanErrorMsg.value = '';
      } else {
        scanErrorMsg.value = flash.message || 'Resource not found';
        scanSuccessMsg.value = '';
        scannedItem.value = null;
      }
    },
    onError: () => {
      scanErrorMsg.value = 'Failed to process scan';
    }
  });
};

const submitManualScan = () => {
  if (!scanForm.barcode) return;
  if (dashboardScanner) {
    dashboardScanner.pause();
  }
  submitModalScan();
};

const resumeModalScan = () => {
  scanSuccessMsg.value = '';
  scanErrorMsg.value = '';
  scannedItem.value = null;
  scanForm.reset();
  if (dashboardScanner) {
    dashboardScanner.resume();
  }
};


</script>

<template>

  <Head title="Store Management" />

  <GradientHeroHeader kicker="Administration" title="Store Management"
    subtitle="Manage small items like pens, paper, and water. Track if you're running low and create lists of what to buy next.">
    <template #right>

      <div
        class="relative z-20 flex flex-wrap items-center justify-end gap-3 rounded-[1.5rem]  p-2 shadow-[0_10px_30px_-15px_rgba(15,23,42,0.18)]">
        <button v-for="t in tabs" :key="t.id" @click="switchTab(t.id)"
          class="group relative flex h-11 items-center gap-3 rounded-2xl px-5 text-[10px] font-black uppercase tracking-[0.22em] transition-all duration-300 cursor-pointer"
          :class="tab === t.id
            ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-500/20 scale-[1.02]'
            : 'bg-white/70 text-slate-500 border border-transparent hover:border-slate-200 hover:bg-white hover:text-slate-900 hover:shadow-md'
            ">
          <!-- Active Glow -->
          <div v-if="tab === t.id" class="absolute inset-0 rounded-2xl bg-white/10"></div>

          <!-- Icon -->
          <component :is="t.icon"
            class="relative z-10 w-4 h-4 transition-transform duration-300 group-hover:scale-110" />

          <!-- Label -->
          <span class="relative z-10 whitespace-nowrap">
            {{ t.label }}
          </span>
        </button>
      </div>

    </template>
  </GradientHeroHeader>

  <div class="p-6">
    <!-- <div
      class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-500/5 via-transparent to-transparent pointer-events-none">
  </div> -->

    <section class="bg-white rounded-3xl border border-slate-200 p-4 mb-5 relative z-10">
      <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="text-left">
          <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">
            Store Lifecycle
          </p>
          <div class="mt-3 flex flex-wrap items-center gap-2 text-[10px] font-bold uppercase tracking-widest">
            <span class="px-3 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200">Item Created</span>
            <span class="text-slate-300">-></span>
            <span class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100">Stock
              Added</span>
            <span class="text-slate-300">-></span>
            <span class="px-3 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-100">Consumed</span>
            <span class="text-slate-300">-></span>
            <span class="px-3 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-100">Low Stock</span>
            <span class="text-slate-300">-></span>
            <span class="px-3 py-1 rounded-lg bg-amber-50 text-amber-700 border border-amber-100">Restock Queue</span>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button @click="openScannerModal"
            class="w-fit h-12 px-4 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-lg transition-all inline-flex items-center gap-2 active:scale-95 shrink-0 cursor-pointer">
            <QrCodeIcon class="w-4 h-4 text-white" />
            Scan Barcode
          </button>
          <Link :href="route('admin.inventory.create')"
            class="w-fit h-12 px-3 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest shadow-lg hover:bg-indigo-700 transition-all inline-flex items-center gap-2 active:scale-95 shrink-0">
            <PlusIcon class="w-4 h-4 text-white" />
            New Item
          </Link>
        </div>
      </div>

      <div class="mt-auto flex flex-wrap gap-3 justify-end pt-5">
        <Link :href="route('admin.inventory.scanner')"
          class="h-10 px-4 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600 flex items-center justify-center">
          Scanner Mode</Link>
        <Link :href="route('admin.inventory.procurement.restock')"
          class="h-10 px-4 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600 flex items-center justify-center">
          Restock Queue</Link>
        <Link :href="route('admin.assets.dashboard', { view: 'list' })"
          class="h-10 px-4 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600 flex items-center justify-center">
          All Assets</Link>
        <Link :href="route('admin.physical-documents.index')"
          class="h-10 px-4 bg-slate-50 border border-slate-200 rounded-lg text-[10px] font-bold uppercase tracking-widest text-slate-600 flex items-center justify-center">
          Physical Docs</Link>
      </div>
    </section>

    <!-- Content -->
    <div class="flex-1 overflow-visible pb-12 relative z-10">
      <!-- Tab 1: Stats Pulse -->
      <div v-if="tab === 'stats'" class="space-y-10 animate-in fade-in duration-500">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div v-for="stat in [
            {
              label: 'Different Items',
              value: stats.total_items,
              icon: RectangleGroupIcon,
              color: 'text-indigo-600',
              bg: 'bg-indigo-50',
            },
            {
              label: 'Weekly Usage',
              value: stats.velocity + ' Units',
              icon: ChartBarIconAlt,
              color: 'text-emerald-600',
              bg: 'bg-emerald-50',
            },
            {
              label: 'Low Stock',
              value: stats.low_stock,
              icon: ExclamationTriangleIcon,
              color: 'text-rose-600',
              bg: 'bg-rose-50',
              pulse: stats.low_stock > 0,
            },
          ]" :key="stat.label"
            class="bg-white p-3 rounded-3xl border border-slate-200 shadow-sm group relative overflow-hidden text-left">
            <div v-if="stat.pulse" class="absolute inset-0 bg-rose-500/[0.02] animate-pulse"></div>
            <div class="flex items-center justify-between relative z-10">
              <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest block mb-3">{{ stat.label
                }}</span>
                <span class="text-2xl font-black text-slate-900 tabular-nums leading-none">{{ stat.value }}</span>
              </div>
              <div :class="[stat.bg, stat.color]"
                class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-sm group-hover:scale-105 transition-all">
                <component :is="stat.icon" class="w-7 h-7" />
              </div>
            </div>
          </div>
        </div>

        <!-- Strategic Reorder Deck -->
        <div
          class="bg-white rounded-3xl p-3 shadow-sm border border-slate-200 relative overflow-hidden group/deck text-left">
          <div class="absolute -right-32 -top-32 w-80 h-80 bg-indigo-50 rounded-full blur-[100px]"></div>

          <div class="flex items-center justify-between mb-8 relative z-10 border-b border-slate-100">
            <div class="flex items-center gap-6">
              <div
                class="w-12 h-12 bg-rose-50 border border-rose-100 rounded-xl flex items-center justify-center text-rose-500">
                <ExclamationTriangleIcon class="w-6 h-6" />
              </div>
              <div>
                <h3 class="text-1xl font-black text-slate-900 uppercase tracking-tight">
                  Stock Alerts
                </h3>
                <p class="text-[10px]  font-bold text-slate-500 uppercase tracking-widest mt-2">
                  Items that need immediate restock attention
                </p>
              </div>
            </div>
            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.3em]">Priority Alert View</span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 relative z-10">
            <div v-for="item in stats.alerts" :key="item.id"
              class="bg-slate-50 border border-slate-200 rounded-2xl p-4 group/item hover:bg-white hover:border-indigo-200 transition-all">
              <div class="flex flex-col h-full justify-between">
                <div class="flex items-start justify-between mb-8">
                  <div
                    class="w-10 h-10 bg-white rounded-xl border border-slate-200 flex items-center justify-center text-slate-500">
                    <CubeIcon class="w-5 h-5" />
                  </div>
                  <div class="text-right flex flex-col items-end gap-2">
                    <div class="text-[8px] font-bold text-rose-400 tracking-widest uppercase mb-1">
                      Low Stock
                    </div>
                    <div class="text-sm font-black text-slate-900 tabular-nums">
                      {{ item.current_stock }} /
                      <span class="opacity-40">{{ item.min_stock_level }}</span>
                    </div>
                    <div class="flex gap-2 relative t-5">
                      <button @click="openEdit(item)"
                        class="text-slate-500 hover:text-indigo-600 transition-colors cursor-pointer">
                        <PencilIcon class="w-3.5 h-3.5" />
                      </button>
                      <button @click="openDeleteConfirm(item)"
                        class="text-slate-500 hover:text-rose-500 transition-colors cursor-pointer">
                        <TrashIcon class="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>
                </div>
                <div class="min-w-0">
                  <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight mb-6 line-clamp-1 truncate">
                    {{ item.name }}
                  </h4>
                  <button @click="openRestock(item)"
                    class="w-full h-10 bg-indigo-600 text-white rounded-xl text-[10px] font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all active:scale-95 shadow-sm cursor-pointer">
                    Quick Restock
                  </button>
                </div>
              </div>
            </div>
            <div v-if="!stats.alerts.length" class="col-span-full py-20 flex flex-col items-center gap-6 opacity-20">
              <ShieldCheckIcon class="w-16 h-16 text-emerald-400 animate-pulse" />
              <p class="text-[10px] font-bold text-slate-700 uppercase tracking-widest">
                All stock levels are healthy
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Tab 2: Master List -->
      <div v-if="tab === 'list'" class="space-y-4">
        <!-- Search Filtering Bar -->
        <div class="flex flex-col sm:flex-row gap-4 items-center justify-between bg-white p-4 rounded-3xl border border-slate-200 shadow-sm">
          <div class="relative w-full sm:max-w-md">
            <input type="text" v-model="searchQuery" @keyup.enter="handleSearch" placeholder="Search by name, SKU, category..."
              class="w-full h-11 pl-10 pr-4 rounded-xl border border-slate-200 bg-slate-50 text-xs font-bold text-slate-800 placeholder-slate-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all" />
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.604 10.604z" />
              </svg>
            </div>
          </div>
          <div class="flex gap-2 w-full sm:w-auto justify-end">
            <button @click="handleSearch" class="h-11 px-5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition-all cursor-pointer">
              Search
            </button>
            <button v-if="searchQuery" @click="searchQuery = ''; handleSearch();" class="h-11 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all cursor-pointer">
              Clear
            </button>
          </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden animate-in fade-in duration-500">
          <div class="overflow-x-auto">
            <table class="w-full min-w-[1200px] text-center border-collapse">
              <thead class="bg-slate-50 text-slate-400 uppercase border-b border-slate-100">
                <tr>
                  <th class="px-6 py-4 text-[12px] font-bold tracking-widest">
                    Item
                  </th>
                  <th class="px-6 py-4 text-[12px] font-bold tracking-widest">
                    Stock Level
                  </th>
                  <th class="px-6 py-4 text-[12px] font-bold tracking-widest">
                    Utilization Velocity
                  </th>
                  <th class="px-6 py-4 text-[12px] font-bold tracking-widest">
                    Unit Specification
                  </th>
                  <th class="px-6 py-4 text-[12px] font-bold tracking-widest">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50">
                <tr v-for="item in items.data" :key="item.id"
                  class="group/row hover:bg-slate-50/50 transition-all duration-300">
                  <td class="px-6 py-5">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-10 h-10 bg-slate-50 rounded-2xl flex items-center justify-center text-slate-300 group-hover/row:bg-indigo-50 group-hover/row:text-indigo-600 transition-all border border-slate-100 shadow-sm">
                        <CubeIcon class="w-5 h-5" />
                      </div>
                      <div>
                        <div
                          class="text-sm font-black text-slate-900 uppercase tracking-tight text-left group-hover/row:text-indigo-600 transition-colors leading-none mb-2">
                          {{ item.name }}
                        </div>
                        <div class="text-[9px] font-bold text-slate-400 text-left uppercase tracking-widest font-mono">
                          SKU-{{ String(item.id).padStart(5, "0") }}
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-5">
                    <div class="flex flex-col gap-0">
                      <div
                        class="flex items-center justify-between text-[10px] font-bold uppercase tracking-widest p-3 leading-none mb-1">
                        <span :class="item.current_stock <= item.min_stock_level
                          ? 'text-rose-600'
                          : 'text-slate-500'
                          ">{{ item.current_stock }} Units</span>
                        <span class="text-slate-400">Min: {{ item.min_stock_level }}</span>
                      </div>
                      <div class="w-40 h-1.5 bg-slate-100 rounded-full overflow-hidden border border-slate-200/50">
                        <div class="h-full rounded-full transition-all duration-1000"
                          :style="`width: ${Math.min((item.current_stock / (item.min_stock_level * 2)) * 100, 100)}%`"
                          :class="item.current_stock <= item.min_stock_level
                            ? 'bg-rose-500'
                            : 'bg-emerald-500'
                            "></div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-5">
                    <div v-if="item.days_remaining < 999">
                      <div
                        class="px-3 py-1 bg-slate-50 border border-slate-100 rounded-lg text-[9px] font-bold tracking-widest uppercase inline-flex items-center gap-2"
                        :class="{
                          'text-rose-600 bg-rose-50 border-rose-100':
                            item.days_remaining <= 7,
                          'text-amber-600 bg-amber-50 border-amber-100':
                            item.days_remaining > 7 && item.days_remaining <= 30,
                          'text-indigo-600': item.days_remaining > 30,
                        }">
                        <ClockIcon class="w-3 h-3" />
                        {{ item.days_remaining }} Days Remaining
                      </div>
                    </div>
                    <span v-else class="text-[9px] font-bold text-slate-500 uppercase tracking-widest opacity-40">Infinite
                      Supply</span>
                  </td>
                  <td class="px-6 py-5 uppercase text-[10px] font-bold text-slate-500 tracking-widest">
                    {{ item.unit }}
                  </td>
                  <td class="px-6 py-5 text-right">
                    <div class="flex justify-end gap-3 transition-all">
                      <button @click="openRestock(item)"
                        class="h-8 px-3 bg-slate-50 text-slate-500 hover:text-emerald-600 cursor-pointer rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all border border-slate-300">
                        Restock
                      </button>
                      <button @click="openEdit(item)"
                        class="h-8 px-3 bg-slate-50 text-slate-500 hover:text-indigo-600 cursor-pointer rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all border border-slate-300">
                        Edit
                      </button>
                      <button @click="openDeleteConfirm(item)"
                        class="h-8 px-3 bg-rose-600 text-white cursor-pointer rounded-lg text-[10px] font-bold uppercase tracking-widest transition-all border border-slate-300">
                        Delete
                      </button>
                      <Link :href="route('admin.inventory.show', item.id)"
                        class="h-8 px-3 bg-indigo-600 text-white rounded-lg text-[10px] cursor-pointer font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-sm flex items-center">
                        Logs</Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Pagination Controls -->
        <div v-if="items.links && items.links.length > 3" class="mt-6 flex justify-center">
          <Pagination :links="items.links" />
        </div>
      </div>

      <!-- Tab 3: Restock Queue -->
      <div v-if="tab === 'procurement'" class="animate-in fade-in duration-500">
        <div v-if="procurement && procurement.length > 0"
          class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">
          <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 text-slate-400 uppercase border-b border-slate-100">
              <tr>
                <th class="px-8 py-5 text-[10px] font-bold tracking-widest">
                  Requirement
                </th>
                <th class="px-8 py-5 text-[10px] font-bold tracking-widest">
                  Spec
                </th>
                <th class="px-8 py-5 text-[10px] font-bold tracking-widest">
                  Available
                </th>
                <th class="px-8 py-5 text-[10px] font-bold tracking-widest">
                  Acquisition Delta
                </th>
                <th class="px-8 py-5 text-right text-[10px] font-bold tracking-widest">
                  Action
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
              <tr v-for="item in procurement" :key="item.id"
                class="group/prow hover:bg-slate-50/50 transition-all duration-300">
                <td class="px-8 py-7">
                  <div class="flex items-center gap-3">
                    <div
                      class="w-12 h-12 bg-rose-50 rounded-xl flex items-center justify-center text-rose-500 border border-rose-100">
                      <ArchiveBoxIcon class="w-6 h-6" />
                    </div>
                    <div>
                      <div class="text-sm font-black text-slate-900 uppercase tracking-tight leading-none mb-2">
                        {{ item.name }}
                      </div>
                      <div class="text-[9px] font-bold text-rose-600 uppercase tracking-widest opacity-60">
                        {{ item.category ?? "ESSENTIAL" }}
                      </div>
                    </div>
                  </div>
                </td>
                <td class="px-8 py-7 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                  {{ item.unit }}
                </td>
                <td class="px-8 py-7">
                  <div
                    class="px-3 py-1.5 bg-rose-50 border border-rose-100 text-rose-600 rounded-lg inline-flex items-center gap-2">
                    <div class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></div>
                    <span class="text-xs font-black">{{
                      item.current_stock
                    }}</span>
                  </div>
                </td>
                <td class="px-8 py-7">
                  <div class="flex flex-col gap-1 pr-8">
                    <span class="text-sm font-black text-rose-600 leading-none mb-1">+{{
                      (item.min_stock_level - item.current_stock).toFixed(1)
                    }}
                      Units</span>
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Min Threshold: {{
                      item.min_stock_level }}</span>
                  </div>
                </td>
                <td class="px-8 py-7 text-right">
                  <div class="flex items-center justify-end gap-3 mt-1">
                    <button @click="openEdit(item)"
                      class="w-9 h-9 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-indigo-600 hover:border-indigo-200 hover:bg-indigo-50 transition-all shadow-sm active:scale-95">
                      <PencilIcon class="w-4 h-4" />
                    </button>
                    <button @click="openDeleteConfirm(item)"
                      class="w-9 h-9 flex items-center justify-center rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition-all shadow-sm active:scale-95">
                      <TrashIcon class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else
          class="bg-white rounded-[2.5rem] border border-slate-200 p-24 text-center group hover:bg-indigo-50/30 transition-all duration-700 shadow-sm relative overflow-hidden">
          <div
            class="absolute inset-0 bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-indigo-500/5 via-transparent to-transparent pointer-events-none">
          </div>
          <div class="relative z-10">
            <div
              class="w-20 h-20 bg-emerald-50 border border-emerald-100 rounded-3xl flex items-center justify-center shadow-inner mx-auto mb-10 group-hover:scale-110 transition-transform">
              <ShieldCheckIcon class="w-10 h-10 text-emerald-500" />
            </div>
            <h3 class="text-3xl font-black text-slate-900 uppercase tracking-tight mb-4 px-1">
              Stock Levels Healthy
            </h3>
            <p
              class="text-[11px] font-bold text-slate-400 uppercase tracking-[0.4em] max-w-sm mx-auto leading-loose mb-12">
              All active units are compliant with minimum safety levels.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-6">
              <Link :href="route('admin.inventory.create')"
                class="h-16 px-10 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] shadow-xl hover:bg-indigo-700 transition-all flex items-center gap-4 active:scale-95 group/btn">
                <PlusIcon class="w-5 h-5 text-white group-hover/btn:rotate-90 transition-transform duration-500" />
                Add Item
              </Link>
              <button @click="switchTab('list')"
                class="text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-indigo-600 transition-colors">
                Open Master List
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Unit Modal -->
    <Modal :show="showEditModal" @close="closeEdit" title="" flat max-width="3xl"
      body-class="max-h-[85vh] overflow-y-auto p-0">
      <div
        class="relative overflow-hidden rounded-[1rem] border border-slate-200/80 bg-white/95 backdrop-blur-xl shadow-[0_25px_80px_-25px_rgba(15,23,42,0.28)] text-left">

        <!-- Ambient Effects -->
        <div
          class="absolute -top-32 -right-24 w-80 h-80 bg-indigo-500/10 rounded-full blur-[120px] pointer-events-none">
        </div>

        <div class="absolute -bottom-32 -left-24 w-80 h-80 bg-sky-400/10 rounded-full blur-[120px] pointer-events-none">
        </div>

        <div class="absolute inset-x-12 top-0 h-px bg-gradient-to-r from-transparent via-indigo-300/70 to-transparent">
        </div>

        <!-- Header -->
        <div class="relative z-10 px-6 md:px-8 py-5 border-b border-slate-100 flex items-start justify-between gap-6">

          <div class="space-y-2">
            <div
              class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-indigo-100 bg-indigo-50 text-indigo-600 text-[9px] font-black uppercase tracking-[0.25em]">
              Inventory Registry
            </div>

            <div>
              <h3 class="text-2xl font-black text-slate-950 uppercase tracking-tight leading-none">
                Edit Resource
              </h3>

              <p class="text-[11px] font-semibold text-slate-400 mt-2">
                Update inventory resource configuration and stock metadata.
              </p>
            </div>
          </div>

          <button @click="closeEdit"
            class="w-11 h-11 rounded-2xl border border-slate-200 bg-white flex items-center justify-center text-slate-400 hover:text-rose-500 hover:border-rose-200 hover:bg-rose-50 transition-all duration-300 shrink-0">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitEdit" class="relative z-10 px-6 md:px-8 py-6 space-y-8">

          <!-- Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 mb-0 gap-x-8 gap-y-7">

            <!-- Name -->
            <div class="md:col-span-2 space-y-3">
              <InputLabel value="Resource Identity (Name)"
                class="px-1 text-[12px] font-black uppercase tracking-[0.1em] text-slate-500" />

              <TextInput v-model="editForm.name" type="text" required
                class="w-full h-12 px-3 rounded-2xl border-slate-200 bg-slate-50/80 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 text-[14px] font-bold tracking-[0.1em]" />

              <InputError :message="editForm.errors.name" />
            </div>

            <!-- SKU -->
            <div class="space-y-3">
              <div class="flex items-center gap-3 px-1">
                <div
                  class="w-10 h-10 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 shadow-sm shrink-0">
                  <QrCodeIcon class="w-5 h-5" />
                </div>

                <div>
                  <InputLabel value="SKU / ID" class="text-[12px] font-black uppercase tracking-[0.1em]" />

                  <p class="text-[10px] text-slate-400 font-semibold mt-1">
                    Resource tracking identifier
                  </p>
                </div>
              </div>

              <TextInput v-model="editForm.sku" type="text"
                class="w-full h-12 px-3 rounded-2xl border-slate-200 bg-slate-50/80 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 text-[14px] font-bold" />

              <InputError :message="editForm.errors.sku" />
            </div>

            <!-- Category -->
            <div class="space-y-3">
              <div class="flex items-center gap-3 px-1">
                <div
                  class="w-10 h-10 rounded-xl bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-600 shadow-sm shrink-0">
                  <TagIcon class="w-5 h-5" />
                </div>

                <div>
                  <InputLabel value="Category" class="text-[12px] font-black uppercase tracking-[0.1em]" />

                  <p class="text-[10px] text-slate-400 font-semibold mt-1">
                    Resource grouping category
                  </p>
                </div>
              </div>

              <TextInput v-model="editForm.category" type="text"
                class="w-full h-12 px-3 rounded-2xl border-slate-200 bg-slate-50/80 focus:bg-white focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 text-[14px] font-bold" />

              <InputError :message="editForm.errors.category" />
            </div>

            <!-- Stock -->
            <div class="space-y-3">
              <div class="flex items-center gap-3 px-1">
                <div
                  class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm shrink-0">
                  <ArchiveBoxIcon class="w-5 h-5" />
                </div>

                <div>
                  <InputLabel value="Initial Presence (Stock)"
                    class="text-[12px] font-black uppercase tracking-[0.1em]" />

                  <p class="text-[10px] text-slate-400 font-semibold mt-1">
                    Current stock availability
                  </p>
                </div>
              </div>

              <TextInput v-model="editForm.current_stock" type="number"
                class="w-full h-12 px-3 rounded-2xl border-slate-200 bg-slate-50/80 focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 text-[14px] font-bold" />

              <InputError :message="editForm.errors.current_stock" />
            </div>

            <!-- Min Stock -->
            <div class="space-y-3">
              <div class="flex items-center gap-3 px-1">
                <div
                  class="w-10 h-10 rounded-xl bg-amber-50 border border-amber-100 flex items-center justify-center text-amber-600 shadow-sm shrink-0">
                  <ExclamationTriangleIcon class="w-5 h-5" />
                </div>

                <div>
                  <InputLabel value="Safety Threshold (Min)"
                    class="text-[12px] font-black uppercase tracking-[0.1em]" />

                  <p class="text-[10px] text-slate-400 font-semibold mt-1">
                    Minimum inventory warning level
                  </p>
                </div>
              </div>

              <TextInput v-model="editForm.min_stock_level" type="number"
                class="w-full h-12 px-3 rounded-2xl border-slate-200 bg-slate-50/80 focus:bg-white tracking-[0.1em] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 text-[14px] font-bold" />

              <InputError :message="editForm.errors.min_stock_level" />
            </div>

            <!-- Unit Cost -->
            <div class="space-y-3">
              <div class="flex items-center gap-3 px-1">
                <div
                  class="w-10 h-10 rounded-xl bg-violet-50 border border-violet-100 flex items-center justify-center text-violet-600 shadow-sm shrink-0">
                  <BanknotesIcon class="w-5 h-5" />
                </div>

                <div>
                  <InputLabel value="Unit Cost (INR)" class="text-[12px] font-black uppercase tracking-[0.1em]" />

                  <p class="text-[10px] text-slate-400 font-semibold mt-1">
                    Cost per inventory unit
                  </p>
                </div>
              </div>

              <TextInput v-model="editForm.unit_cost" type="number" step="0.01"
                class="w-full h-12 px-3 rounded-2xl border-slate-200 bg-slate-50/80 focus:bg-white tracking-[0.1em] focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 text-[14px] font-bold" />

              <InputError :message="editForm.errors.unit_cost" />
            </div>

            <!-- Unit -->
            <div class="space-y-3">
              <div class="flex items-center gap-3 px-1">
                <div
                  class="w-10 h-10 rounded-xl bg-cyan-50 border border-cyan-100 flex items-center justify-center text-cyan-600 shadow-sm shrink-0">
                  <ScaleIcon class="w-5 h-5" />
                </div>

                <div>
                  <InputLabel value="Unit Specification" class="text-[12px] font-black uppercase tracking-[0.1em]" />

                  <p class="text-[10px] text-slate-400 font-semibold mt-1">
                    PCS, BOX, BUNDLE etc.
                  </p>
                </div>
              </div>

              <TextInput v-model="editForm.unit" type="text"
                class="w-full h-12 px-3 rounded-2xl border-slate-200 bg-slate-50/80 focus:bg-white tracking-[0.1em] focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 text-[14px] font-bold" />

              <InputError :message="editForm.errors.unit" />
            </div>
          </div>

          <!-- Footer -->
          <div
            class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-4 pt-7 border-t border-slate-100">

            <button type="button" @click="closeEdit"
              class="h-11 px-4 rounded-2xl border border-slate-200 bg-white text-[10px] font-black uppercase tracking-[0.1em] text-slate-500 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition-all duration-300 cursor-pointer">
              Discard
            </button>

            <PrimaryButton type="submit" :disabled="editForm.processing"
              class="h-11 px-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-[10px] font-black uppercase tracking-[0.1em] shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 hover:scale-[1.01] transition-all duration-300 cursor-pointer">
              <ArrowPathIcon v-if="editForm.processing" class="w-4 h-4 animate-spin mr-2" />

              <CheckCircleIcon v-else class="w-4 h-4 mr-2" />

              {{
                editForm.processing
                  ? "Syncing..."
                  : "Update Resource"
              }}
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <PremiumModal :show="showDeleteModal" @close="closeDeleteConfirm" title="Delete Store item"
      subtitle="This action cannot be undone" maxWidth="sm">
      <div class="space-y-1 text-left">
        <div class="flex items-start gap-2">
          <div
            class="w-10 h-10 rounded-2xl bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shrink-0">
            <TrashIcon class="w-5 h-5" />
          </div>
          <div class="space-y-1 p-0">
            <p class="text-xs font-bold text-slate-900 uppercase tracking-wide">
              Confirm deletion
            </p>
            <p class="text-sm text-slate-500 leading-snug">
              Are you sure you want to delete this item? All associated data will be
              permanently removed.
              ?
            </p>
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
          <button type="button" @click="closeDeleteConfirm"
            class="h-10 px-4 rounded-xl border border-slate-200 bg-white text-[10px] font-black uppercase tracking-widest text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-all cursor-pointer">
            Cancel
          </button>
          <button type="button" @click="deleteItem" :disabled="!recordToDelete"
            class="h-10 px-4 rounded-xl bg-rose-600 text-white text-[10px] font-black uppercase tracking-widest hover:bg-rose-700 transition-all disabled:opacity-50 cursor-pointer">
            Delete
          </button>
        </div>
      </div>
    </PremiumModal>

    <!-- Restoration Authorization Modal (Restock) -->
    <Modal :show="showRestockModal" @close="closeRestock" title="" flat max-width="2xl">
      <div
        class="relative overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/95 backdrop-blur-xl shadow-[0_25px_80px_-25px_rgba(15,23,42,0.28)] text-left">

        <!-- Ambient Effects -->
        <div
          class="absolute -top-28 -right-24 w-80 h-80 bg-indigo-500/10 rounded-full blur-[120px] pointer-events-none">
        </div>

        <div class="absolute -bottom-32 -left-24 w-80 h-80 bg-sky-400/10 rounded-full blur-[120px] pointer-events-none">
        </div>

        <div class="absolute inset-x-10 top-0 h-px bg-gradient-to-r from-transparent via-indigo-300/70 to-transparent">
        </div>

        <!-- Header -->
        <div class="relative z-10 px-8 py-5 border-b border-slate-100 flex items-start justify-between gap-6">

          <div class="space-y-2">
            <div
              class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-indigo-100 bg-indigo-50 text-indigo-600 text-[9px] font-black uppercase tracking-[0.1em]">
              Inventory Matrix
            </div>

            <div>
              <h3 class="text-xl font-black text-slate-950 uppercase tracking-[0.1em] leading-none">
                Add Stock
              </h3>

              <p class="text-[11px] font-semibold text-slate-400 tracking-[0.1em] mt-2">
                Increase inventory quantity for this resource.
              </p>
            </div>
          </div>

          <button @click="closeRestock"
            class="w-11 h-11 rounded-2xl border border-slate-200 bg-white flex items-center justify-center text-slate-400 hover:text-rose-500 hover:border-rose-200 hover:bg-rose-50 transition-all duration-300 shrink-0">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitRestock" class="relative z-10 px-7 py-5 space-y-7">

          <!-- Inventory Card -->
          <div
            class="relative overflow-hidden rounded-[1.75rem] border border-slate-200 bg-gradient-to-br from-slate-50 to-white p-4 shadow-sm">

            <div class="absolute -right-24 -top-24 w-64 h-64 bg-indigo-100/50 rounded-full blur-[90px]">
            </div>

            <div class="relative z-10 flex items-center gap-4">
              <div
                class="w-10 h-10 rounded-2xl border border-indigo-100 bg-white flex items-center justify-center text-indigo-600 shadow-sm shrink-0">
                <CubeIcon class="w-7 h-7" />
              </div>

              <div class="min-w-0">
                <span class="text-[9px] font-black uppercase tracking-[0.1em] text-indigo-400 block mb-2">
                  Resource Identity
                </span>

                <h4 class="text-lg font-black text-slate-950 uppercase tracking-[0.1em] leading-tight truncate">
                  {{ selectedItem.name }}
                </h4>

                <div class="flex items-center gap-3 mt-3 flex-wrap">
                  <span class="text-[10px] font-black uppercase tracking-[0.1em] text-slate-400">
                    Current Presence
                  </span>

                  <div
                    class="px-3 py-1 rounded-full bg-emerald-50 border border-emerald-100 text-emerald-600 text-[11px] font-black tracking-wide tabular-nums">
                    {{ selectedItem.current_stock }} Units
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Inputs -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Quantity -->
            <div class="space-y-3">
              <div class="flex items-center gap-3 px-1">
                <div
                  class="w-10 h-10 rounded-xl border border-emerald-100 bg-emerald-50 flex items-center justify-center text-emerald-600 shadow-sm shrink-0">
                  <ArchiveBoxIcon class="w-5 h-5" />
                </div>

                <div>
                  <InputLabel value="Quantity to Add" class="text-[12px] font-black uppercase tracking-[0.1em]" />

                  <p class="text-[10px] text-slate-400 font-semibold mt-1">
                    Increment stock quantity
                  </p>
                </div>
              </div>

              <TextInput v-model="restockForm.quantity" type="number" min="1" required
                class="w-full h-12 rounded-2xl px-3 border-slate-200 bg-slate-50/80 focus:bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 text-[14px] font-bold" />
            </div>

            <!-- Unit Cost -->
            <div class="space-y-3">
              <div class="flex items-center gap-3 px-1">
                <div
                  class="w-10 h-10 rounded-xl border border-violet-100 bg-violet-50 flex items-center justify-center text-violet-600 shadow-sm shrink-0">
                  <BanknotesIcon class="w-5 h-5" />
                </div>

                <div>
                  <InputLabel value="Unit Cost (Net)" class="text-[12px] font-black uppercase tracking-[0.1em]" />

                  <p class="text-[10px] text-slate-400 font-semibold mt-1">
                    Cost per additional unit
                  </p>
                </div>
              </div>

              <TextInput v-model="restockForm.unit_cost" type="number" step="0.01" placeholder="0.00"
                class="w-full h-12 px-3 rounded-2xl border-slate-200 bg-slate-50/80 focus:bg-white focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10 text-[14px] font-bold" />
            </div>
          </div>

          <!-- Footer -->
          <div
            class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-4 pt-6 border-t border-slate-100">

            <button type="button" @click="closeRestock"
              class="h-11 px-6 rounded-2xl border border-slate-200 bg-white text-[10px] font-black uppercase tracking-[0.25em] text-slate-500 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 cursor-pointer transition-all duration-300">
              Discard
            </button>

            <PrimaryButton type="submit" :disabled="restockForm.processing"
              class="h-11 px-3 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white rounded-2xl text-[11px] font-black uppercase tracking-[0.1em] shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 hover:scale-[1.01] cursor-pointer transition-all duration-300">
              <ArrowPathIcon v-if="restockForm.processing" class="w-4 h-4 animate-spin mr-2" />

              <ShieldCheckIcon v-else class="w-4 h-4 mr-2" />

              {{
                restockForm.processing
                  ? "Updating..."
                  : "Save Stock"
              }}
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Barcode Scanner Modal -->
    <Modal :show="showScannerModal" @close="closeScannerModal" title="" flat max-width="lg">
      <div class="relative overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/95 backdrop-blur-xl shadow-[0_25px_80px_-25px_rgba(15,23,42,0.28)] text-left p-6">
        <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-100">
          <div>
            <h3 class="text-lg font-black text-slate-900 uppercase tracking-tight">Barcode Scanner</h3>
            <p class="text-[10px] font-semibold text-slate-400 mt-1">Scan item barcodes and open actions instantly.</p>
          </div>
          <button @click="closeScannerModal" class="w-8 h-8 rounded-lg border border-slate-200 bg-white flex items-center justify-center text-slate-400 hover:text-rose-500 transition-all duration-300 cursor-pointer">
            <XMarkIcon class="w-4 h-4" />
          </button>
        </div>

        <div class="relative aspect-square bg-slate-900 rounded-2xl overflow-hidden mb-4">
          <div id="modal-reader" class="w-full h-full"></div>
          
          <!-- Scan animation line -->
          <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-transparent via-indigo-500 to-transparent shadow-[0_0_15px_rgba(79,70,229,0.4)] z-20 animate-[scan_3s_linear_infinite] opacity-50 pointer-events-none"></div>

          <!-- Result Overlay -->
          <div v-if="scanSuccessMsg || scanErrorMsg" class="absolute inset-0 bg-white/95 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center z-30">
            <div class="mb-4">
              <div v-if="scanSuccessMsg" class="h-16 w-16 bg-emerald-50 border border-emerald-100 rounded-2xl flex items-center justify-center shadow-inner">
                <CheckCircleIcon class="h-8 w-8 text-emerald-500" />
              </div>
              <div v-else class="h-16 w-16 bg-rose-50 border border-rose-100 rounded-2xl flex items-center justify-center shadow-inner">
                <XMarkIcon class="h-8 w-8 text-rose-500" />
              </div>
            </div>

            <h4 class="text-sm font-black text-slate-900 uppercase tracking-tight mb-2">
              {{ scanSuccessMsg ? 'Item Found' : 'Scan Failed' }}
            </h4>
            <p class="text-[10px] font-semibold text-slate-500 mb-6">{{ scanSuccessMsg || scanErrorMsg }}</p>

            <div v-if="scannedItem" class="w-full bg-slate-50 border border-slate-200 rounded-xl p-4 text-left mb-6">
              <div class="flex items-center justify-between mb-2">
                <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Resource</span>
                <span class="px-2 py-0.5 bg-indigo-50 text-indigo-600 text-[8px] font-bold rounded uppercase tracking-widest border border-indigo-100">
                  {{ scannedItem.sku ? 'Store Item' : 'Asset' }}
                </span>
              </div>
              <p class="text-xs font-black text-slate-900 uppercase tracking-tight mb-1 truncate">{{ scannedItem.name }}</p>
              <p class="text-[10px] font-mono text-indigo-500 font-bold tracking-tight">
                {{ scannedItem.sku || scannedItem.serial_number }}
              </p>

              <!-- Actions in Modal -->
              <div class="mt-4 pt-3 border-t border-slate-200/60 flex gap-2">
                <Link v-if="scannedItem.sku" :href="route('admin.inventory.dashboard', { view: 'list' })" class="flex-1 h-8 bg-white border border-slate-200 text-slate-500 hover:text-indigo-600 rounded-lg text-[9px] font-bold uppercase tracking-widest flex items-center justify-center gap-1 transition-all">
                  Open
                </Link>
                <Link v-else :href="route('admin.assets.show', scannedItem.id)" class="flex-1 h-8 bg-white border border-slate-200 text-slate-500 hover:text-indigo-600 rounded-lg text-[9px] font-bold uppercase tracking-widest flex items-center justify-center gap-1 transition-all">
                  Open
                </Link>
                <button v-if="scannedItem.sku" @click="closeScannerModal(); openRestock(scannedItem);" class="h-8 px-4 bg-indigo-600 text-white rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all cursor-pointer">
                  Restock
                </button>
              </div>
            </div>

            <button @click="resumeModalScan" class="w-full h-11 bg-slate-100 text-slate-600 text-[10px] font-bold uppercase tracking-widest rounded-xl hover:bg-slate-200 transition-all flex items-center justify-center gap-2 cursor-pointer">
              <ArrowPathIcon class="w-4 h-4" />
              Resume Scan
            </button>
          </div>
        </div>

        <!-- Manual Input inside scanner modal -->
        <form @submit.prevent="submitManualScan" class="relative">
          <input v-model="scanForm.barcode" type="text" placeholder="Enter barcode manually..."
            class="w-full h-12 bg-slate-50 border border-slate-200 rounded-xl pl-4 pr-20 text-xs font-bold text-slate-900 placeholder-slate-400 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all outline-none uppercase tracking-widest" />
          <button type="submit" class="absolute right-2 top-2 bottom-2 px-4 bg-indigo-600 text-white rounded-lg text-[9px] font-bold uppercase tracking-widest hover:bg-indigo-700 transition-all cursor-pointer">
            Verify
          </button>
        </form>
      </div>
    </Modal>
  </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>

<style scoped>
/* No scroll bars on numeric inputs */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

.font-mono {
  font-family: "JetBrains Mono", monospace;
}
</style>

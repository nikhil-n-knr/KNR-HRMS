<script setup>
import { Head, useForm, Link } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
import GradientHeroHeader from "@/Components/UI/GradientHeroHeader.vue";

import {
  CubeIcon,
  ArrowLeftIcon,
  IdentificationIcon,
  CurrencyRupeeIcon as CashIcon,
  CalendarDaysIcon,
  MapPinIcon,
  CpuChipIcon,
  InformationCircleIcon,
  CheckCircleIcon,
  ArrowPathIcon,
  ArchiveBoxIcon,
  DocumentTextIcon,
  TagIcon,
  BoltIcon,
  SparklesIcon,
  BuildingStorefrontIcon,
  ArchiveBoxArrowDownIcon,
  PlusIcon,
} from "@heroicons/vue/24/solid";

defineOptions({ layout: MainLayout });

const props = defineProps({
  categories: Array,
  locations: Array,
  vendors: Array,
});

const form = useForm({
  name: "",
  category_id: "",
  vendor_id: "",
  location_id: "",
  serial_number: "",
  make: "",
  model: "",
  purchase_cost: "",
  purchase_date: new Date().toISOString().split("T")[0],
  is_serialized: true,
});

const submit = () => {
  form.post(route("admin.assets.store"));
};
</script>

<template>

  <Head title="Add New Item" />

  <GradientHeroHeader kicker="" title="Add New Item" subtitle="New Resource Entry & Registration Terminal.">
    <template #right>
      <div class="px-5 py-2 bg-emerald-50 border border-emerald-100 rounded-xl flex items-center gap-3 shadow-sm">
        <BoltIcon class="w-4 h-4 text-emerald-500" />
        <span class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest">Swift Entry Active</span>
      </div>
    </template>
  </GradientHeroHeader>

  <div class="p-6">
  
    <!-- Registration Portal Terminal -->
    <form @submit.prevent="submit"
      class="w-full h-auto overflow-visible bg-white rounded-2xl shadow-sm p-5 relative z-10 animate-in zoom-in-95 duration-700 border border-slate-200 text-left">
      <div
        class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_right,_var(--tw-gradient-stops))] from-indigo-50/10 via-transparent to-transparent pointer-events-none">
      </div>

      <div class="space-y-12 relative z-10">
        <!-- Section: Facts -->
        <div class="space-y-10">
          <div class="flex items-center gap-4 border-b mb-5 border-slate-100 pb-3">
            <div
              class="w-11 h-11 bg-slate-50 border border-slate-200 text-slate-400 rounded-2xl flex items-center justify-center shadow-sm group hover:rotate-6 transition-transform shrink-0">
              <ArchiveBoxIcon class="w-8 h-8 text-indigo-500" />
            </div>
            <h3 class="text-[20px] font-black text-slate-900 uppercase tracking-tight leading-none">
              Basic Identifier
            </h3>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 px-2">
            <div class="space-y-3">
              <label class="px-1 text-[13px] font-bold text-slate-400 uppercase tracking-widest leading-none">Resource
                Name</label>
              <input v-model="form.name" type="text"
                class="w-full h-14 bg-slate-50 border border-slate-200 rounded-2xl px-8 text-xl font-black text-slate-900 uppercase tracking-tight focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:shadow-md outline-none shadow-sm transition-all placeholder:text-slate-200"
                placeholder="E.G. MACBOOK PRO M3" required />
            </div>

            <div class="space-y-3">
              <label
                class="px-1 text-[13px] font-bold text-slate-400 uppercase tracking-widest leading-none">Classification
                (Type)</label>
              <select v-model="form.category_id"
                class="w-full h-14 bg-slate-50 border border-slate-200 rounded-2xl px-8 text-sm font-bold text-slate-400 focus:text-indigo-700 uppercase tracking-widest focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:shadow-md outline-none transition-all appearance-none cursor-pointer shadow-sm">
                <option value="" disabled selected>CHOOSE_TYPE...</option>
                <option v-for="c in categories" :key="c.id" :value="c.id">
                  {{ c.name.toUpperCase() }}
                </option>
              </select>
            </div>

            <div class="space-y-3">
              <label class="px-1 text-[13px] font-bold text-slate-400 uppercase tracking-widest leading-none">Location</label>
              <select v-model="form.location_id"
                class="w-full h-14 bg-slate-50 border border-slate-200 rounded-2xl px-8 text-sm font-bold text-slate-400 focus:text-indigo-700 uppercase tracking-widest focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:shadow-md outline-none transition-all appearance-none cursor-pointer shadow-sm">
                <option value="" disabled selected>CHOOSE LOCATION...</option>
                <option v-for="l in locations" :key="l.id" :value="l.id">
                  {{ l.name.toUpperCase() }}
                </option>
              </select>
            </div>

            <div class="space-y-3">
              <label class="px-1 text-[13px] font-bold text-slate-400 uppercase tracking-widest leading-none">Serial
                Matrix / Tag</label>
              <input v-model="form.serial_number" type="text"
                class="w-full h-14 bg-slate-50 border border-slate-200 rounded-2xl px-8 text-xl font-black text-slate-900 uppercase tracking-widest focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:shadow-md outline-none transition-all font-mono shadow-sm placeholder:text-slate-200"
                placeholder="E.G. S/N: 123-ABC" />
            </div>

            <div class="space-y-3">
              <label
                class="px-1 text-[13px] font-bold text-slate-400 uppercase tracking-widest leading-none">Vendor</label>
              <select v-model="form.vendor_id"
                class="w-full h-14 bg-slate-50 border border-slate-200 rounded-2xl px-8 text-sm font-bold text-slate-400 focus:text-indigo-700 uppercase tracking-widest focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:shadow-md outline-none transition-all appearance-none cursor-pointer shadow-sm">
                <option value="" disabled selected>CHOOSE VENDOR...</option>
                <option v-for="v in vendors" :key="v.id" :value="v.id">
                  {{ v.name.toUpperCase() }}
                </option>
              </select>
            </div>
          </div>
        </div>

        <!-- Section: Secondary Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 px-2">
          <div class="space-y-10 bg-slate-50 p-4 rounded-2xl border border-slate-100 shadow-sm">
            <div class="flex items-center gap-6 border-b border-white">
              <TagIcon class="w-8 h-8 text-indigo-400 shadow-indigo-500/10" />
              <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight leading-none">
                Details
              </h3>
            </div>
            <div class="grid grid-cols-2 gap-6">
              <div class="space-y-3">
                <label class="px-1 text-[13px] font-bold text-slate-400 uppercase tracking-widest leading-none">Brand
                  Name</label>
                <input v-model="form.make" type="text"
                  class="w-full h-14 bg-white border border-slate-200 rounded-xl px-6 text-sm font-bold text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:shadow-md outline-none transition-all shadow-sm"
                  placeholder="SHIPPING BRAND..." />
              </div>
              <div class="space-y-3">
                <label class="px-1 text-[13px] font-bold text-slate-400 uppercase tracking-widest leading-none">Model
                  Code</label>
                <input v-model="form.model" type="text"
                  class="w-full h-14 bg-white border border-slate-200 rounded-xl px-6 text-sm font-bold text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:shadow-md outline-none transition-all shadow-sm"
                  placeholder="VERSION X.0..." />
              </div>
            </div>
          </div>

          <div class="space-y-10 bg-emerald-50/20 p-4 rounded-2xl border border-emerald-50 shadow-sm">
            <div class="flex items-center gap-3 border-b border-white">
              <CashIcon class="w-8 h-8 text-emerald-500 shadow-emerald-500/10" />
              <h3 class="text-xl font-black text-slate-900 uppercase tracking-tight leading-none">
                Financials
              </h3>
            </div>
            <div class="grid grid-cols-2 gap-6">
              <div class="space-y-3">
                <label class="px-1 text-[13px] font-bold text-emerald-600 uppercase tracking-widest leading-none">Buy
                  Cost
                  (₹)</label>
                <input v-model="form.purchase_cost" type="number"
                  class="w-full h-14 bg-white border border-slate-200 rounded-xl px-4 text-base font-black focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:shadow-md outline-none transition-all shadow-sm tabular-nums"
                  placeholder="0.00" />
              </div>
              <div class="space-y-3">
                <label class="px-1 text-[13px] font-bold text-emerald-600 uppercase tracking-widest leading-none">Entry
                  Date</label>
                <input v-model="form.purchase_date" type="date"
                  class="w-full h-14 bg-white border border-slate-200 rounded-xl px-4 text-xs font-black text-slate-900 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:shadow-md outline-none transition-all shadow-sm" />
              </div>
            </div>
          </div>
        </div>

        <!-- Footer Execution -->
        <div class="w-full flex items-center justify-between pt-4 border-t border-slate-100 pb-2">
          <button type="reset"
            class="text-[13px] font-bold uppercase tracking-widest text-rose-500 transition-all cursor-pointer">
            Discard Matrix
          </button>
          <button type="submit" :disabled="form.processing"
            class="h-14 px-6 text-white text-[11px] font-black uppercase tracking-widest shadow-lg bg-indigo-600 text-white rounded-xl text-[10px] font-bold transition-all flex items-center gap-3 active:scale-95 disabled:opacity-30 group/save border border-slate-800 cursor-pointer">
            <ArrowPathIcon v-if="form.processing" class="w-6 h-6 animate-spin" />
            <CheckCircleIcon v-else class="w-6 h-6 text-indigo-400 group-hover:scale-125 transition-transform" />
            <span>{{ form.processing ? "Syncing..." : "Confirm Entry" }}</span>
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<style scoped>
.shadow-3xl {
  box-shadow: 0 40px 100px -20px rgba(0, 0, 0, 0.05);
}

.no-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>

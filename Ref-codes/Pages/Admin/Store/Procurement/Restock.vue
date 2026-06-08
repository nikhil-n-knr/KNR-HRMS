<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
import { ref, computed } from "vue";
import GradientHeroHeader from "@/Components/UI/GradientHeroHeader.vue";

import {
  ShoppingCartIcon,
  ExclamationTriangleIcon,
  ArrowRightIcon,
  InformationCircleIcon,
} from "@heroicons/vue/24/outline"; // v2

defineOptions({ layout: MainLayout });

const props = defineProps({
  shortage_items: Array,
});

// State
const selectedItems = ref(new Set());
const orderQuantities = ref({}); // { itemId: qty }

// Initialize defaults
props.shortage_items.forEach((item) => {
  // Default Reorder: Bring it up to (Min * 2) or just +10?
  // Let's say Target = Min Level * 2
  const target = item.min_stock_level * 2;
  const needed = Math.max(0, target - item.current_stock);
  orderQuantities.value[item.id] = needed || 10;
});

const toggleSelection = (id) => {
  if (selectedItems.value.has(id)) {
    selectedItems.value.delete(id);
  } else {
    selectedItems.value.add(id);
  }
};

const selectAll = () => {
  if (selectedItems.value.size === props.shortage_items.length) {
    selectedItems.value.clear();
  } else {
    props.shortage_items.forEach((i) => selectedItems.value.add(i.id));
  }
};

// Computed
const totalEstCost = computed(() => {
  let total = 0;
  selectedItems.value.forEach((id) => {
    const item = props.shortage_items.find((i) => i.id === id);
    if (item) {
      total += item.unit_cost * (orderQuantities.value[id] || 0);
    }
  });
  return total;
});

const form = useForm({
  items: [],
});

const generatePO = () => {
  if (selectedItems.value.size === 0) return;

  const payload = Array.from(selectedItems.value).map((id) => ({
    id: id,
    order_qty: orderQuantities.value[id],
  }));

  form.items = payload;
  form.post(route("admin.store.procurement.generate"), {
    onSuccess: () => {
      selectedItems.value.clear();
    },
  });
};
</script>

<template>

  <Head title="Procurement Queue" />

  <GradientHeroHeader kicker="" title="Procurement Queue" subtitle="Track and manage all small items in the store">
    <template #right>
      <div class="flex flex-wrap items-center gap-3 shrink-0">
        <div class="px-4 py-3 bg-slate-40 border border-slate-200 rounded-2xl flex flex-col items-end shadow-sm">
          <span class="block text-xs font-bold text-white uppercase">Selected Items</span>
          <span class="block text-xl font-bold text-white/60">{{
            selectedItems.size
          }}</span>
        </div>
        <div class="px-4 py-3 bg-slate-40 border border-slate-200 rounded-2xl flex flex-col items-end shadow-sm">
          <span class="block text-xs font-bold text-white uppercase">Est. Cost</span>
          <span class="block text-xl font-bold text-emerald-600">${{ totalEstCost.toLocaleString() }}</span>
        </div>
        <div class="px-4 py-3 bg-slate-40 border border-slate-200 rounded-2xl flex flex-col items-end shadow-sm">
          <span class="block text-xs font-bold text-white uppercase">Est. Cost</span>
          <span class="block text-xl font-bold text-emerald-600">${{ totalEstCost.toLocaleString() }}</span>
        </div>
      </div>
    </template>
  </GradientHeroHeader>

  <div class="p-6">
  
    <!-- Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-4 w-12">
              <input type="checkbox" @click="selectAll" :checked="selectedItems.size === shortage_items.length &&
                shortage_items.length > 0
                " class="rounded border-gray-300 text-rose-600 focus:ring-rose-500" />
            </th>
            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">
              Item Details
            </th>
            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">
              Stock Status
            </th>
            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">
              Action Needed
            </th>
            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">
              Est Cost
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="item in shortage_items" :key="item.id" class="hover:bg-gray-50 transition-colors"
            :class="{ 'bg-rose-50/30': selectedItems.has(item.id) }">
            <td class="px-6 py-4">
              <input type="checkbox" :checked="selectedItems.has(item.id)" @change="toggleSelection(item.id)"
                class="rounded border-gray-300 text-rose-600 focus:ring-rose-500" />
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center">
                <div>
                  <div class="text-sm font-bold text-gray-900">
                    {{ item.name }}
                  </div>
                  <div class="text-xs text-gray-500">
                    {{ item.sku }} • {{ item.category }}
                  </div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-2">
                <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                  <div class="h-full bg-red-500"
                    :style="`width: ${Math.min(100, (item.current_stock / item.min_stock_level) * 100)}%`"></div>
                </div>
                <span class="text-xs font-bold text-red-600">{{ item.current_stock }} / {{ item.min_stock_level
                  }}</span>
              </div>
            </td>
            <td class="px-6 py-4">
              <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Order:</span>
                <input v-model.number="orderQuantities[item.id]" type="number" min="1"
                  class="w-24 rounded-md border-gray-300 py-1 text-sm focus:border-rose-500 focus:ring-rose-500" />
                <span class="text-xs text-gray-400 font-mono">{{
                  item.unit
                }}</span>
              </div>
            </td>
            <td class="px-6 py-4 text-right font-mono text-sm text-gray-600">
              ${{
                (item.unit_cost * (orderQuantities[item.id] || 0)).toFixed(2)
              }}
            </td>
          </tr>
          <tr v-if="shortage_items.length === 0">
            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
              <ExclamationTriangleIcon class="h-10 w-10 mx-auto text-gray-300 mb-2" />
              No stock shortages detected.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { Head, useForm, Link, router } from "@inertiajs/vue3";
import MainLayout from "@/Layouts/MainLayout.vue";
import TextInput from "@/Components/TextInput.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import SecondaryButton from "@/Components/SecondaryButton.vue";
import GradientHeroHeader from "@/Components/UI/GradientHeroHeader.vue";
import {
  ArchiveBoxIcon,
  ArrowPathIcon,
  ChevronLeftIcon,
  CheckCircleIcon,
  PencilSquareIcon,
  CubeTransparentIcon,
  ExclamationTriangleIcon,
  CurrencyRupeeIcon,
  ScaleIcon,
} from "@heroicons/vue/24/solid";
import { useToastStore } from "@/stores/toast";

defineOptions({ layout: MainLayout });

const form = useForm({
  name: "",
  sku: "",
  category: "",
  min_stock_level: 5,
  current_stock: 0,
  unit_cost: 0,
  unit: "pcs",
});

const toast = useToastStore();

const submit = () => {
  form.post(route("admin.inventory.store"), {
    onSuccess: () => {
      form.reset();
      showToast("Resource registered successfully!", "success");
    },
    onError: () => showToast("Failed to register resource. Please check the form for errors.", "error")
  });
};

const showToast = (message, type) => {
  toast.showToast(message, type);
};



</script>

<template>

  <Head title="Register Resource" />

  <GradientHeroHeader kicker="" title="Register Unit" subtitle="Initialize new resource in the supply matrix.">
    <template #right>
      <div class="z-10 flex items-center gap-4">
        <div class="px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center gap-3">
          <ArchiveBoxIcon class="w-4 h-4 text-indigo-600" />
          <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Protocol 4.0</span>
        </div>
      </div>
    </template>
  </GradientHeroHeader>

  <div class="p-6">
    
    <!-- Registration Surface -->
    <div class="flex-1 overflow-y-auto no-scrollbar relative z-10">
      <div class="mx-auto w-full max-w-full">
        <form @submit.prevent="submit"
          class="relative overflow-hidden rounded-[2rem] border border-slate-200/80 bg-white/95 backdrop-blur-xl shadow-[0_20px_70px_-20px_rgba(15,23,42,0.18)]">
          <!-- Ambient Effects -->
          <div class="absolute -right-24 -bottom-24 w-72 h-72 bg-indigo-500/10 rounded-full blur-[120px]"></div>

          <div class="absolute -left-24 -top-24 w-72 h-72 bg-sky-400/10 rounded-full blur-[120px]"></div>

          <div
            class="absolute inset-x-16 top-0 h-px bg-gradient-to-r from-transparent via-indigo-300/70 to-transparent">
          </div>

          <!-- Header -->
          <div
            class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6 px-4 md:px-6 pt-4 pb-4 border-b border-slate-100">
            <div class="space-y-2">
              <div
                class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border border-indigo-100 bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-[0.1em]">
                Inventory Registry
              </div>

              <div>
                <h2 class="text-2xl font-black uppercase tracking-tight text-slate-950 leading-none">
                  Create Resource
                </h2>

                <p class="text-[13px] font-semibold text-slate-400 mt-2">
                  Register inventory resource and stock configuration.
                </p>
              </div>
            </div>

            <div
              class="hidden md:flex w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-600 items-center justify-center shadow-lg shadow-indigo-500/30 shrink-0">
              <CubeTransparentIcon class="w-7 h-7 text-white" />
            </div>
          </div>

          <!-- Form Body -->
          <div class="relative z-10 px-6 py-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-7">
              <!-- Name -->
              <div class="md:col-span-2 space-y-3">
                <InputLabel value="Resource Identity (Name)"
                  class="px-1 text-[12px] font-black uppercase tracking-[0.1em] text-slate-500" />

                <TextInput v-model="form.name" type="text"
                  class="w-full h-13 rounded-2xl border border-slate-200 bg-slate-50/80 px-3 text-[14px] font-sm tracking-wide transition-all duration-200 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none"
                  placeholder="E.G. OFFICE A4 PAPER BUNDLE" required />

                <InputError :message="form.errors.name" />
              </div>

              <!-- SKU -->
              <div class="space-y-3">
                <InputLabel value="Matrix Reference (SKU)"
                  class="px-1 text-[12px] font-black uppercase tracking-[0.1em] text-slate-500" />

                <TextInput v-model="form.sku" type="text"
                  class="w-full h-13 rounded-2xl border border-slate-200 bg-slate-50/80 px-3 text-[14px] font-sm tracking-wide transition-all duration-200 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none"
                  placeholder="AUTO_GEN_IF_EMPTY" />

                <InputError :message="form.errors.sku" />
              </div>

              <!-- Category -->
              <div class="space-y-3">
                <InputLabel value="Resource Category"
                  class="px-1 text-[12px] font-black uppercase tracking-[0.1em] text-slate-500" />

                <TextInput v-model="form.category" type="text"
                  class="w-full h-13 rounded-2xl border border-slate-200 bg-slate-50/80 px-3 text-[14px] font-sm tracking-wide transition-all duration-200 focus:bg-white focus:border-indigo-500 focus:outline-none"
                  placeholder="E.G. STATIONERY" />

                <InputError :message="form.errors.category" />
              </div>

              <!-- Current Stock -->
              <div class="space-y-3">
                <div class="flex items-center gap-3 px-1">
                  <div
                    class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-100 flex items-center justify-center text-emerald-600 shadow-sm shrink-0">
                    <ArchiveBoxIcon class="w-5 h-5" />
                  </div>

                  <div>
                    <InputLabel value="Initial Presence (Stock)"
                      class="text-[12px] font-black uppercase tracking-[0.1em]" />

                    <p class="text-[11px] text-slate-400 font-semibold mt-1">
                      Current stock availability
                    </p>
                  </div>
                </div>

                <TextInput v-model="form.current_stock" type="number" min="0"
                  class="w-full h-13 rounded-2xl border border-slate-200 bg-slate-50/80 px-3 text-[14px] font-sm transition-all duration-200 focus:bg-white focus:border-emerald-500 focus:outline-none" />
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

                    <p class="text-[11px] text-slate-400 font-semibold mt-1">
                      Minimum safe inventory level
                    </p>
                  </div>
                </div>

                <TextInput v-model="form.min_stock_level" type="number" min="0"
                  class="w-full h-13 rounded-2xl border border-slate-200 bg-slate-50/80 px-3 font-sm text-[14px]  transition-all duration-200 focus:bg-white focus:border-amber-500 focus:outline-none" />
              </div>

              <!-- Unit Cost -->
              <div class="space-y-3">
                <div class="flex items-center gap-3 px-1">
                  <div
                    class="w-10 h-10 rounded-xl bg-violet-50 border border-violet-100 flex items-center justify-center text-violet-600 shadow-sm shrink-0">
                    <CurrencyRupeeIcon class="w-5 h-5" />
                  </div>

                  <div>
                    <InputLabel value="Unit Cost (INR)" class="text-[12px] font-black uppercase tracking-[0.2em]" />

                    <p class="text-[11px] text-slate-400 font-semibold mt-1">
                      Cost per inventory unit
                    </p>
                  </div>
                </div>

                <TextInput v-model="form.unit_cost" type="number" step="0.01" min="0"
                  class="w-full h-13 rounded-2xl border border-slate-200 bg-slate-50/80 px-3 text-[14px] font-sm transition-all duration-200 focus:bg-white focus:border-violet-500 focus:outline-none"
                  placeholder="0.00" />
              </div>

              <!-- Unit -->
              <div class="space-y-3">
                <div class="flex items-center gap-3 px-1">
                  <div
                    class="w-10 h-10 rounded-xl bg-sky-50 border border-sky-100 flex items-center justify-center text-sky-600 shadow-sm shrink-0">
                    <ScaleIcon class="w-5 h-5" />
                  </div>

                  <div>
                    <InputLabel value="Unit Specification" class="text-[12px] font-black uppercase tracking-[0.1em]" />

                    <p class="text-[11px] text-slate-400 font-semibold mt-1">
                      PCS, BOX, BUNDLE etc.
                    </p>
                  </div>
                </div>

                <TextInput v-model="form.unit" type="text"
                  class="w-full h-13 rounded-2xl border border-slate-200 bg-slate-50/80 px-3 text-[14px] font-sm transition-all duration-200 focus:bg-white focus:border-sky-500 focus:outline-none"
                  placeholder="E.G. PCS, BOX, BUNDLE" />
              </div>
            </div>

            <!-- Footer -->
            <div
              class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-4 pt-8 border-t border-slate-100 mt-8">
              <SecondaryButton @click="router.visit(route('admin.inventory.index'))" type="button"
                class="h-12 px-3 w-fit-content rounded-2xl border border-slate-200 bg-white text-[11px] font-black uppercase tracking-[0.1em] text-slate-500 hover:text-rose-600 hover:border-rose-200 hover:bg-rose-50 transition-all cursor-pointer">
                Cancel
              </SecondaryButton>

              <PrimaryButton type="submit" :disabled="form.processing"
                class="h-12 px-2 rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 text-white text-[11px] font-black uppercase tracking-[0.1em] shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 hover:scale-[1.01] transition-all cursor-pointer">
                <ArrowPathIcon v-if="form.processing" class="w-4 h-4 animate-spin mr-2" />

                <CheckCircleIcon v-else class="w-4 h-4 mr-2" />

                {{
                  form.processing
                    ? "Syncing Matrix..."
                    : "Register Resource"
                }}
              </PrimaryButton>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
</style>

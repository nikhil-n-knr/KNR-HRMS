<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';
import PremiumModal from '@/Components/PremiumModal.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import { 
    ArchiveBoxIcon, 
    PlusIcon, 
    QrCodeIcon, 
    RectangleGroupIcon, 
    ExclamationTriangleIcon, 
    ArrowPathIcon, 
    ShieldCheckIcon,
    CubeIcon,
    MagnifyingGlassIcon,
    SparklesIcon,
    BoltIcon,
    AdjustmentsHorizontalIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/solid';

const props = defineProps({
    items: Object
});

const columns = [
    { key: 'name', label: 'SKU Identifier', sortable: true },
    { key: 'unit', label: 'Metric', sortable: true },
    { key: 'current_stock', label: 'Quantum Stock', sortable: true },
    { key: 'min_stock_level', label: 'Threshold', sortable: true },
    { key: 'status', label: 'Vital Status', sortable: false },
    { key: 'actions', label: '', sortable: false, align: 'right' }
];

const showCreateModal = ref(false);
const createForm = useForm({
    name: '',
    unit: 'Unit',
    current_stock: 0,
    min_stock_level: 5
});

const submitCreate = () => {
    createForm.post(route('admin.inventory.store'), {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.reset();
        }
    });
};
</script>

<template>
    <Head title="Inventory Matrix" />
    <MainLayout>
        <div class="h-full flex flex-col font-outfit italic -m-8 p-12 bg-slate-50 min-h-screen relative overflow-hidden">
            <!-- Glassy background nodes -->
            <div class="absolute -right-32 -top-32 w-128 h-128 bg-teal-500/5 rounded-full blur-[140px] pointer-events-none"></div>
            <div class="absolute -left-32 bottom-0 w-128 h-128 bg-indigo-500/5 rounded-full blur-[140px] pointer-events-none"></div>

            <!-- Strategic Resource Header -->
            <header class="bg-white rounded-[2.5rem] border border-slate-200 p-10 shadow-sm mb-10 relative overflow-hidden group">
                <div class="absolute -right-24 -top-24 w-64 h-64 bg-slate-50 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-1000"></div>
                
                <div class="flex flex-col xl:flex-row xl:items-center xl:justify-between gap-10 relative z-10">
                    <div class="flex items-center gap-8 text-left">
                        <div class="w-20 h-20 bg-slate-950 rounded-3xl flex items-center justify-center text-white shadow-2xl shrink-0 group-hover:rotate-6 transition-transform">
                            <ArchiveBoxIcon class="w-10 h-10 text-teal-400 group-hover:animate-pulse" />
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase tracking-[0.4em] text-teal-600">Resource Depository</p>
                            <h1 class="text-4xl font-black text-slate-900 tracking-tighter mt-2">Resource Matrix</h1>
                            <p class="text-sm font-semibold text-slate-400 mt-2 italic">Tactical logistics & real-time stockpile synchronization terminal.</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-6">
                         <div class="hidden md:grid grid-cols-2 gap-4">
                            <div class="rounded-2xl bg-teal-50 border border-teal-100 px-6 py-4 min-w-[150px]">
                                <p class="text-[9px] font-black uppercase tracking-widest text-teal-600">Total SKU</p>
                                <p class="text-xl font-black text-teal-700 mt-1">{{ items.data.length }} Nodes</p>
                            </div>
                            <div class="rounded-2xl bg-rose-50 border border-rose-100 px-6 py-4 min-w-[150px]">
                                <p class="text-[9px] font-black uppercase tracking-widest text-rose-600">Low Pulse</p>
                                <p class="text-xl font-black text-rose-700 mt-1">{{ items.data.filter(i => i.current_stock <= i.min_stock_level).length }} Units</p>
                            </div>
                         </div>
                         <div class="w-px h-12 bg-slate-200 hidden xl:block"></div>
                         <div class="flex items-center gap-4">
                            <button @click="showCreateModal = true" class="h-14 px-10 bg-slate-950 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] shadow-xl hover:bg-teal-600 transition-all flex items-center gap-4 active:scale-95 group">
                                <PlusIcon class="w-5 h-5 text-teal-400 group-hover:rotate-180 transition-transform duration-700" />
                                Initialize SKU
                            </button>
                         </div>
                    </div>
                </div>
            </header>

            <!-- Resource Matrix Terminal -->
            <section class="flex-1 bg-white rounded-[2.5rem] border border-slate-200 shadow-sm overflow-hidden flex flex-col relative z-10 transition-all duration-700 border-t-4 border-t-teal-500">
                <BaseDataTable
                    :rows="items.data"
                    :columns="columns"
                    search-placeholder="FILTER_RESOURCES_BY_IDENTIFIER..."
                    class="flex-1"
                >
                    <template #cell-name="{ row }">
                        <div class="flex items-center gap-5">
                            <div class="w-12 h-12 bg-slate-50 border border-slate-100 rounded-xl flex items-center justify-center text-slate-400 group-hover:bg-slate-900 group-hover:text-teal-400 transition-all duration-300 shadow-sm">
                                <CubeIcon class="w-6 h-6" />
                            </div>
                            <div>
                                <div class="text-base font-black text-slate-900 uppercase tracking-tight group-hover:text-teal-700 transition-colors">{{ row.name }}</div>
                                <div class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-1 opacity-60">ID: {{ String(row.id).padStart(5, '0') }}</div>
                            </div>
                        </div>
                    </template>

                    <template #cell-unit="{ row }">
                        <span class="px-4 py-1.5 bg-slate-950 text-white rounded-lg text-[9px] font-black uppercase tracking-widest italic group-hover:bg-teal-600 transition-colors">
                            {{ row.unit || 'UNIT' }}
                        </span>
                    </template>

                    <template #cell-current_stock="{ row }">
                         <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-black tabular-nums" :class="row.current_stock <= row.min_stock_level ? 'text-rose-600' : 'text-slate-900'">
                                {{ (row.current_stock || 0).toLocaleString() }}
                            </span>
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest italic">In_Stock</span>
                        </div>
                    </template>

                    <template #cell-min_stock_level="{ row }">
                        <div class="text-xs font-black text-slate-400 uppercase tracking-tighter tabular-nums italic group-hover:text-slate-600 transition-colors">
                            {{ row.min_stock_level }} REQUIRED_FLOOR
                        </div>
                    </template>

                    <template #cell-status="{ row }">
                        <div v-if="row.current_stock <= row.min_stock_level" class="px-4 py-2 bg-rose-50 text-rose-600 rounded-xl border border-rose-100 text-[9px] font-black uppercase tracking-[0.2em] shadow-sm flex items-center gap-3 w-fit">
                            <div class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></div>
                            CRITICAL_STOCK
                        </div>
                        <div v-else class="px-4 py-2 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-100 text-[9px] font-black uppercase tracking-[0.2em] flex items-center gap-3 w-fit">
                            <div class="w-2 h-2 rounded-full bg-emerald-500"></div>
                            VITAL_OPTIMAL
                        </div>
                    </template>

                    <template #cell-actions="{ row }">
                        <div class="flex justify-end gap-3 opacity-0 group-hover/row:opacity-100 transition-opacity">
                            <button class="w-10 h-10 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:bg-slate-950 hover:text-white transition-all shadow-sm active:scale-90">
                                <PlusIcon class="w-5 h-5" />
                            </button>
                            <button class="w-10 h-10 bg-white border border-rose-100 rounded-xl flex items-center justify-center text-rose-300 hover:bg-rose-600 hover:text-white transition-all shadow-sm active:scale-90 border-dashed">
                                <BoltIcon class="w-5 h-5" />
                            </button>
                        </div>
                    </template>
                </BaseDataTable>
            </section>
        </div>

        <!-- Initialize SKU Modal -->
        <PremiumModal :show="showCreateModal" @close="showCreateModal = false" title="SKU Initialization" subtitle="Register new resource node to the matrix">
            <form @submit.prevent="submitCreate" class="p-4 space-y-10 italic">
                <div class="space-y-3 px-2">
                    <InputLabel value="Resource Identifier (Name)" />
                    <TextInput v-model="createForm.name" placeholder="E.G. CAT6 CABLE 305M" class="w-full h-16 rounded-2xl" required />
                </div>
                <div class="grid grid-cols-2 gap-8">
                    <div class="space-y-3 px-2">
                        <InputLabel value="Metric Unit" />
                        <TextInput v-model="createForm.unit" placeholder="E.G. BOX, UNIT, METRE" class="w-full h-16 rounded-2xl" required />
                    </div>
                    <div class="space-y-3 px-2">
                        <InputLabel value="Critical Threshold" />
                        <TextInput v-model="createForm.min_stock_level" type="number" class="w-full h-16 rounded-2xl" required />
                    </div>
                </div>
                <div class="flex items-center justify-between pt-10 border-t border-slate-100 mt-10">
                    <button @click="showCreateModal = false" type="button" class="text-[10px] font-black uppercase tracking-[0.4em] text-slate-400 hover:text-rose-500 transition-all italic">Abort Ops</button>
                    <button type="submit" :disabled="createForm.processing" class="h-16 px-12 bg-slate-950 text-white rounded-2xl text-[10px] font-black uppercase tracking-[0.3em] shadow-xl hover:bg-teal-600 transition-all flex items-center gap-4 active:scale-95 disabled:opacity-50 group italic">
                        <CheckCircleIcon v-if="!createForm.processing" class="w-6 h-6 text-teal-400 group-hover:scale-110 transition-transform italic" />
                        <ArrowPathIcon v-else class="w-6 h-6 animate-spin italic" />
                        <span>Confirm SKU Registry</span>
                    </button>
                 </div>
            </form>
        </PremiumModal>
    </MainLayout>
</template>

<style scoped>
/* Any custom layout tweaks */
</style>

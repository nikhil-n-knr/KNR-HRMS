<script setup>
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { 
    ArchiveBoxIcon, 
    ArrowPathIcon,
    ChevronLeftIcon,
    CheckCircleIcon
} from '@heroicons/vue/24/solid';

defineOptions({ layout: MainLayout });

const form = useForm({
    name: '',
    sku: '',
    category: '',
    min_stock_level: 5,
    current_stock: 0,
    unit_cost: 0,
    unit: 'pcs'
});

const submit = () => {
    form.post(route('admin.inventory.store'), {
        onSuccess: () => form.reset()
    });
};
</script>

<template>
    <Head title="Register Resource" />
    
    <div class="h-screen bg-slate-50 flex flex-col font-outfit overflow-hidden -m-8 p-12 relative text-left">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-indigo-500/5 via-transparent to-transparent pointer-events-none"></div>

        <!-- Header Terminal -->
        <div class="bg-white px-10 py-8 flex flex-shrink-0 justify-between items-center z-10 relative overflow-hidden rounded-3xl border border-slate-200 mb-10 shadow-sm">
            <div class="absolute -right-32 -top-32 w-80 h-80 bg-indigo-50 rounded-full blur-[100px]"></div>
            
            <div class="relative z-10 flex items-center gap-8">
                <Link :href="route('admin.inventory.index')" class="w-12 h-12 bg-white border border-slate-200 rounded-xl flex items-center justify-center text-slate-400 hover:text-indigo-600 transition-all shadow-sm">
                    <ChevronLeftIcon class="w-6 h-6" />
                </Link>
                <div>
                    <h1 class="text-3xl font-black text-slate-900 uppercase tracking-tight leading-none">Register Unit</h1>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-2.5 leading-none px-1">Initialize new resource in the supply matrix</p>
                </div>
            </div>

            <div class="z-10 flex items-center gap-4">
                <div class="px-4 py-2 bg-indigo-50 border border-indigo-100 rounded-xl flex items-center gap-3">
                    <ArchiveBoxIcon class="w-4 h-4 text-indigo-600" />
                    <span class="text-[10px] font-black text-indigo-600 uppercase tracking-widest">Protocol 4.0</span>
                </div>
            </div>
        </div>

        <!-- Registration Surface -->
        <div class="flex-1 overflow-y-auto no-scrollbar pb-12 relative z-10">
            <div class="max-w-5xl">
                <form @submit.prevent="submit" class="bg-white rounded-3xl border border-slate-200 p-12 shadow-sm relative overflow-hidden">
                    <div class="absolute -right-32 -bottom-32 w-80 h-80 bg-slate-50 rounded-full blur-[100px]"></div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10 relative z-10">
                        <div class="col-span-2 space-y-4">
                            <InputLabel value="Resource Identity (Name)" class="px-2" />
                            <TextInput v-model="form.name" type="text" class="w-full" placeholder="E.G. OFFICE A4 PAPER BUNDLE" required />
                            <InputError :message="form.errors.name" />
                        </div>

                        <div class="space-y-4">
                            <InputLabel value="Matrix Reference (SKU)" class="px-2" />
                            <TextInput v-model="form.sku" type="text" class="w-full" placeholder="AUTO_GEN_IF_EMPTY" />
                            <InputError :message="form.errors.sku" />
                        </div>

                        <div class="space-y-4">
                            <InputLabel value="Resource Category" class="px-2" />
                            <TextInput v-model="form.category" type="text" placeholder="E.G. STATIONERY" class="w-full" />
                            <InputError :message="form.errors.category" />
                        </div>

                        <div class="space-y-4">
                            <InputLabel value="Initial Presence (Stock)" class="px-2" />
                            <TextInput v-model="form.current_stock" type="number" min="0" class="w-full" />
                        </div>

                        <div class="space-y-4">
                             <InputLabel value="Safety Threshold (Min)" class="px-2" />
                             <TextInput v-model="form.min_stock_level" type="number" min="0" class="w-full" />
                        </div>

                        <div class="space-y-4">
                             <InputLabel value="Unit Cost (INR)" class="px-2" />
                             <TextInput v-model="form.unit_cost" type="number" step="0.01" min="0" class="w-full" placeholder="0.00" />
                        </div>

                        <div class="space-y-4">
                             <InputLabel value="Unit Specification" class="px-2" />
                             <TextInput v-model="form.unit" type="text" class="w-full" placeholder="E.G. PCS, BOX, BUNDLE" />
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-12 border-t border-slate-100 mt-12 relative z-10">
                        <SecondaryButton @click="router.visit(route('admin.inventory.index'))" type="button">Cancel Operation</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing" class="h-14 px-10">
                            <ArrowPathIcon v-if="form.processing" class="w-5 h-5 animate-spin mr-3" />
                            <CheckCircleIcon v-else class="w-5 h-5 mr-3" />
                            {{ form.processing ? 'Syncing Matrix...' : 'Register Resource' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style scoped>
.no-scrollbar::-webkit-scrollbar { display: none; }
</style>

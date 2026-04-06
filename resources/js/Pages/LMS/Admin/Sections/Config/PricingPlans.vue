<template>
    <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
        <div class="bg-white p-8 rounded-[2.5rem] border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
                <h3 class="text-xl font-black text-gray-900 tracking-tight mb-1 uppercase italic">Pricing Engine</h3>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Configure your B2C revenue protocols</p>
            </div>
            <button class="px-6 py-3 bg-indigo-600 text-white rounded-2xl text-[10px] font-black uppercase tracking-widest hover:bg-indigo-700 transition-all shadow-xl shadow-indigo-100">
                <i class="fas fa-plus mr-2"></i> New Pricing Tier
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div v-for="plan in plans" :key="plan.id" class="group bg-white p-10 rounded-[3rem] border border-gray-100 shadow-sm hover:shadow-2xl hover:border-indigo-100 transition-all duration-500 relative overflow-hidden">
                <div class="space-y-8 relative z-10">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[9px] font-black text-indigo-500 uppercase tracking-[0.2em] mb-2 block">Active SKU</span>
                            <h4 class="text-2xl font-black italic uppercase tracking-tighter">{{ plan.name }}</h4>
                        </div>
                        <div class="w-10 h-10 rounded-xl bg-gray-50 flex items-center justify-center text-gray-400">
                            <i class="fas fa-tag"></i>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-4xl font-black tracking-tighter tabular-nums">{{ plan.currency }} {{ plan.price }}</h3>
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest mt-1">Billed {{ plan.billing_interval }}</p>
                    </div>

                    <div class="pt-6 border-t border-gray-50 space-y-4">
                        <div v-for="feature in (plan.features || ['Full Catalog Access', 'Verified Certificates'])" :key="feature" class="flex items-center gap-3">
                            <i class="fas fa-check-circle text-emerald-500 text-[10px]"></i>
                            <span class="text-[10px] font-black text-gray-500 uppercase tracking-widest">{{ feature }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-4">
                        <button class="flex-1 py-3 bg-gray-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-black transition-all">Edit Logic</button>
                        <button class="w-12 h-12 bg-gray-50 text-gray-400 rounded-xl flex items-center justify-center hover:bg-red-50 hover:text-red-500 transition-all">
                             <i class="fas fa-pause"></i>
                        </button>
                    </div>
                </div>
                <!-- Abstract BG -->
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-indigo-50 rounded-full opacity-30 group-hover:scale-150 transition-transform duration-1000"></div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    plans: Array
});
</script>

<template>
    <div class="space-y-8">
        <!-- Promotions Header -->
        <div class="flex items-center justify-between mb-8 pb-8 border-b border-gray-100">
            <div class="flex items-center gap-6">
                <div class="w-14 h-14 rounded-2xl bg-orange-500 flex items-center justify-center text-white shadow-xl shadow-orange-100">
                    <i class="fas fa-rocket text-xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Product Promotions</h2>
                    <p class="text-sm font-bold text-gray-400 uppercase tracking-widest mt-1">Boost Revenue through Targeted Offers</p>
                </div>
            </div>
            
            <button class="px-5 py-3 rounded-xl bg-orange-500 text-white font-black text-xs uppercase tracking-widest hover:bg-orange-600 transition-all flex items-center gap-2 shadow-xl shadow-orange-100">
                <i class="fas fa-plus"></i> New Promotion
            </button>
        </div>

        <!-- Featured Products -->
        <div class="grid grid-cols-12 gap-8">
            <div class="col-span-12 lg:col-span-8 space-y-6">
                <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest mb-6">Active Campaigns</h3>
                    
                    <div class="space-y-4">
                        <div v-for="i in 2" :key="i" class="p-6 bg-gray-50/50 rounded-[32px] border border-gray-50 flex items-center justify-between group hover:border-orange-200 transition-all">
                            <div class="flex items-center gap-5">
                                <div class="w-16 h-16 rounded-2xl bg-white flex items-center justify-center border border-gray-100 shadow-sm overflow-hidden">
                                    <img :src="`https://picsum.photos/seed/${i+10}/100/100`" class="w-full h-full object-cover" />
                                </div>
                                <div>
                                    <p class="text-sm font-black text-gray-900">Summer SaaS Bundle 2026</p>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest mt-0.5">Automated Newsletter • 1,240 Recipients</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-widest rounded-full border border-emerald-100">Live</span>
                                <p class="text-xs font-black text-gray-900 mt-2">12.4% CTR</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-[40px] shadow-sm border border-gray-100 p-8">
                    <h3 class="text-xs font-black text-gray-900 uppercase tracking-widest mb-6">Top Converting Products</h3>
                    <div class="grid grid-cols-3 gap-6">
                        <div v-for="j in 3" :key="j" class="space-y-3">
                            <div class="aspect-square bg-gray-50 rounded-3xl overflow-hidden border border-gray-100 relative group">
                                <img :src="`https://picsum.photos/seed/${j+20}/200/200`" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                                <div class="absolute inset-0 bg-black/20 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <button class="w-10 h-10 rounded-full bg-white text-orange-500 shadow-xl">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Cloud Service</p>
                                <p class="text-sm font-black text-gray-900">Enterprise Node v{{j}}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Stats -->
            <div class="col-span-12 lg:col-span-4 space-y-8">
                <div class="bg-gray-900 rounded-[40px] shadow-2xl p-8 text-white">
                    <p class="text-[10px] font-black text-orange-400 uppercase tracking-widest mb-2">Total Promo Impact</p>
                    <p class="text-4xl font-black tracking-tight mb-1">$124,500</p>
                    <p class="text-xs font-bold text-gray-400">+18% Revenue lift this month</p>
                    
                    <div class="mt-8 pt-8 border-t border-white/10 space-y-6">
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Emails Sent</p>
                                <p class="text-lg font-black">45.2k</p>
                            </div>
                            <div class="w-24 h-1.5 bg-white/10 rounded-full overflow-hidden">
                                <div class="w-3/4 h-full bg-orange-500"></div>
                            </div>
                        </div>
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Conversions</p>
                                <p class="text-lg font-black">892</p>
                            </div>
                            <div class="w-24 h-1.5 bg-white/10 rounded-full overflow-hidden">
                                <div class="w-1/2 h-full bg-emerald-500"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="bg-orange-50 rounded-[40px] border border-orange-100 p-8">
                    <h4 class="text-xs font-black text-orange-900 uppercase tracking-widest mb-3">Newsletter Sync</h4>
                    <p class="text-xs font-bold text-orange-700 leading-relaxed mb-6">Your Mailchimp segment for "Enterprise Leads" was synced 2 hours ago.</p>
                    <button class="w-full py-3 bg-white border border-orange-200 text-orange-600 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-orange-100 transition-colors">
                        Re-sync Contacts
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    promotions: { type: Array, default: () => [] },
    products: { type: Array, default: () => [] }
});
</script>

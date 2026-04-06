<template>
    <footer class="bg-emerald-950 pt-24 pb-12 text-white site-root">
        <div class="max-w-[1400px] mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-16 mb-20">
                <!-- Brand Info -->
                <div class="space-y-8">
                    <Link href="/" class="inline-block">
                        <span class="text-3xl font-black tracking-tighter uppercase italic">
                            {{ siteName }}<span class="text-emerald-500">.</span>
                        </span>
                    </Link>
                    <p class="text-emerald-100/40 text-xs font-bold uppercase tracking-widest leading-loose max-w-xs">
                        Elevating the retail experience through AI-driven commerce and elite fashion curation. Part of the HRMS Indigo ecosystem.
                    </p>
                    <div class="flex gap-4">
                        <a v-for="s in social" :key="s.icon" href="#" class="w-10 h-10 rounded-xl bg-white/5 border border-white/10 flex items-center justify-center hover:bg-emerald-600 transition-all duration-500">
                            <i :class="s.icon" class="text-xs"></i>
                        </a>
                    </div>
                </div>

                <!-- Shopping -->
                <div>
                    <h4 class="text-sm font-black uppercase tracking-[0.3em] text-emerald-400 mb-10">Marketplace</h4>
                    <ul class="space-y-4">
                        <li v-for="cat in categories" :key="cat.id">
                            <Link :href="'/' + cat.slug" class="text-sm font-bold text-emerald-100/60 hover:text-white transition-colors uppercase tracking-tight italic">{{ cat.name }}</Link>
                        </li>
                    </ul>
                </div>

                <!-- Company -->
                <div>
                    <h4 class="text-sm font-black uppercase tracking-[0.3em] text-emerald-400 mb-10">Company</h4>
                    <ul class="space-y-4 text-sm font-bold text-emerald-100/60 uppercase tracking-tight italic">
                        <li><Link href="/about" class="hover:text-white transition-colors">About Indigo</Link></li>
                        <li><Link href="/become-seller" class="hover:text-white transition-colors">Become a Seller</Link></li>
                        <li><Link href="/contact" class="hover:text-white transition-colors">Contact Hub</Link></li>
                        <li><Link href="/careers" class="hover:text-white transition-colors">Careers</Link></li>
                    </ul>
                </div>

                <!-- Newsletter -->
                <div>
                    <h4 class="text-sm font-black uppercase tracking-[0.3em] text-emerald-400 mb-10">Stay Synced</h4>
                    <p class="text-emerald-100/40 text-sm font-black uppercase tracking-widest mb-6 leading-relaxed">Join 50k+ fashion enthusiasts receiving weekly drops.</p>
                    <div class="relative">
                        <input type="email" placeholder="Email Address" 
                            class="w-full h-14 bg-white/5 border border-white/10 rounded-2xl px-6 text-xs font-bold text-white focus:ring-2 focus:ring-emerald-600 focus:border-transparent transition-all" />
                        <button class="absolute right-2 top-2 h-10 px-6 bg-emerald-600 rounded-xl text-sm font-black uppercase tracking-widest hover:bg-white hover:text-emerald-950 transition-all">Join</button>
                    </div>
                </div>
            </div>

            <div class="pt-12 border-t border-white/5 flex flex-col md:flex-row justify-between items-center gap-8">
                <p class="text-sm font-black text-emerald-100/20 uppercase tracking-[0.2em]">
                    © {{ new Date().getFullYear() }} {{ siteName }}. All rights reserved.
                </p>
                <div class="flex gap-8 text-sm font-black text-emerald-100/20 uppercase tracking-[0.2em]">
                    <a href="#" class="hover:text-emerald-400 transition-colors">Privacy Policy</a>
                    <a href="#" class="hover:text-emerald-400 transition-colors">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    siteName: String,
    categories: Array,
});

const social = [
    { icon: 'fab fa-instagram' },
    { icon: 'fab fa-twitter' },
    { icon: 'fab fa-linkedin-in' },
];
</script>

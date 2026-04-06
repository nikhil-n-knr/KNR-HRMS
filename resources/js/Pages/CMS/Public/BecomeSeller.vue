<template>
    <Head title="Become a Seller" />
    <div class="min-h-screen bg-white site-root" :style="themeVars">
        <!-- Hero Section -->
        <header class="relative h-[80vh] flex items-center justify-center overflow-hidden bg-emerald-950">
            <div class="absolute inset-0 z-0">
                <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&q=80&w=2000" 
                    class="w-full h-full object-cover opacity-30 mix-blend-luminosity" />
                <div class="absolute inset-0 bg-gradient-to-b from-emerald-950/50 via-emerald-950 to-white"></div>
            </div>

            <div class="relative z-10 max-w-[1400px] mx-auto px-6 text-center">
                <span class="inline-block px-6 py-2 bg-emerald-600/20 backdrop-blur-md rounded-full text-sm font-black text-emerald-400 uppercase tracking-[0.3em] mb-8 animate-fade-in-down border border-emerald-500/30">
                    Retail Evolution
                </span>
                <h1 class="text-6xl md:text-8xl font-black text-white leading-none tracking-tighter mb-8 uppercase italic italic-black strike-emerald">
                    Empower your <br/>
                    <span class="text-emerald-600">Business Hub.</span>
                </h1>
                <p class="text-emerald-100/60 font-bold text-lg max-w-2xl mx-auto uppercase tracking-wide leading-relaxed">
                    Join India's fastest growing fashion ecosystem. Scale your brand with pro-grade logistics and AI-driven growth tools.
                </p>
                <div class="mt-12 flex flex-col md:flex-row gap-6 justify-center">
                    <a href="#application-form" class="h-16 px-12 bg-emerald-600 text-white rounded-2xl text-xs font-black uppercase tracking-[0.2em] shadow-2xl shadow-emerald-500/40 flex items-center justify-center hover:bg-white hover:text-emerald-950 transition-all duration-500">
                        Start Selling Today
                    </a>
                    <Link href="/" class="h-16 px-12 bg-white/10 backdrop-blur-md text-white border border-white/20 rounded-2xl text-xs font-black uppercase tracking-[0.2em] flex items-center justify-center hover:bg-white/20 transition-all duration-500">
                        Explore Marketplace
                    </Link>
                </div>
            </div>
        </header>

        <!-- Stats / Benefits -->
        <section class="max-w-[1400px] mx-auto px-6 -translate-y-24 z-20 relative">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div v-for="(benefit, i) in benefits" :key="i" class="bg-white p-12 rounded-[3rem] shadow-2xl shadow-emerald-100 border border-gray-100 group hover:-translate-y-4 transition-all duration-700">
                    <div class="w-20 h-20 bg-emerald-50 rounded-3xl flex items-center justify-center text-emerald-600 text-3xl mb-10 group-hover:bg-emerald-600 group-hover:text-white transition-all duration-500">
                        <i :class="benefit.icon"></i>
                    </div>
                    <h3 class="text-2xl font-black text-slate-900 mb-4 uppercase tracking-tighter italic leading-none">{{ benefit.title }}</h3>
                    <p class="text-slate-400 font-bold text-sm leading-relaxed">{{ benefit.desc }}</p>
                </div>
            </div>
        </section>

        <!-- Application Form -->
        <section id="application-form" class="max-w-[1400px] mx-auto px-6 py-32 grid grid-cols-1 lg:grid-cols-2 gap-24 items-start">
            <div>
                <span class="text-emerald-600 font-black text-sm uppercase tracking-[0.3em] mb-6 block">Ready to scale?</span>
                <h2 class="text-5xl font-black text-slate-950 leading-none tracking-tight mb-8 uppercase italic">Join the <br/> Elite Circle.</h2>
                <div class="space-y-10 mt-16">
                    <div v-for="(step, i) in workflow" :key="i" class="flex gap-8">
                        <div class="shrink-0 w-12 h-12 rounded-2xl bg-emerald-950 text-white flex items-center justify-center text-sm font-black italic">{{ i + 1 }}</div>
                        <div>
                            <h4 class="text-lg font-black text-slate-900 uppercase tracking-tight mb-2 italic">{{ step.title }}</h4>
                            <p class="text-slate-400 font-bold text-xs uppercase tracking-widest leading-loose">{{ step.desc }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-slate-50 rounded-[3.5rem] p-16 shadow-inner border border-gray-100">
                <form @submit.prevent="submit" class="space-y-8">
                    <div class="grid grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-4 ml-1">Your Name</label>
                            <input v-model="form.name" required type="text" 
                                class="w-full h-14 bg-white border-none rounded-2xl px-6 text-sm font-bold text-slate-900 shadow-sm focus:ring-2 focus:ring-emerald-600 transition-all" />
                        </div>
                        <div>
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-4 ml-1">Store Name</label>
                            <input v-model="form.store_name" required type="text"
                                class="w-full h-14 bg-white border-none rounded-2xl px-6 text-sm font-bold text-slate-900 shadow-sm focus:ring-2 focus:ring-emerald-600 transition-all" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-8">
                        <div>
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-4 ml-1">Email</label>
                            <input v-model="form.email" required type="email"
                                class="w-full h-14 bg-white border-none rounded-2xl px-6 text-sm font-bold text-slate-900 shadow-sm focus:ring-2 focus:ring-emerald-600 transition-all" />
                        </div>
                        <div>
                            <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-4 ml-1">Phone Number</label>
                            <input v-model="form.phone" required type="text"
                                class="w-full h-14 bg-white border-none rounded-2xl px-6 text-sm font-bold text-slate-900 shadow-sm focus:ring-2 focus:ring-emerald-600 transition-all" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-4 ml-1">Business Details</label>
                        <textarea v-model="form.business_details" rows="4"
                            class="w-full bg-white border-none rounded-3xl p-6 text-sm font-bold text-slate-900 shadow-sm focus:ring-2 focus:ring-emerald-600 transition-all"></textarea>
                    </div>

                    <button :disabled="form.processing" type="submit"
                        class="w-full h-16 bg-emerald-950 text-white rounded-2xl text-xs font-black uppercase tracking-[0.3em] hover:bg-emerald-600 transition-all duration-500 disabled:opacity-50">
                        {{ form.processing ? 'Registering Application...' : 'Submit Application' }}
                    </button>
                    
                    <p v-if="successMessage" class="text-center text-emerald-600 font-black text-sm uppercase tracking-widest animate-bounce-short">
                        <i class="fas fa-check-circle mr-2"></i> {{ successMessage }}
                    </p>
                </form>
            </div>
        </section>

        <!-- Success Toast -->
        <transition enter-active-class="translate-y-0" enter-from-class="translate-y-20" leave-to-class="translate-y-20">
            <div v-if="successMessage" class="fixed bottom-10 left-1/2 -translate-x-1/2 z-[100] px-8 py-4 bg-emerald-600 text-white rounded-2xl shadow-2xl font-black text-sm uppercase tracking-widest flex items-center gap-4">
                <i class="fas fa-rocket"></i> {{ successMessage }}
            </div>
        </transition>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    site: Object,
    theme: Object,
});

const successMessage = ref('');

const form = useForm({
    name: '',
    store_name: '',
    email: '',
    phone: '',
    business_details: '',
});

const benefits = [
    { icon: 'fas fa-chart-line', title: 'Scale Faster', desc: 'Access 1M+ daily shoppers and leverage our high-fidelity search algorithms to maximize your brand visibility.' },
    { icon: 'fas fa-truck-loading', title: 'Pro Logistics', desc: 'Next-day delivery across 20,000 pincodes. We handle the heavy lifting so you can focus on creation.' },
    { icon: 'fas fa-wallet', title: '7-Day Payouts', desc: 'Secure the most flexible settlement periods in the industry. Keep your cash flow fluid and your growth constant.' }
];

const workflow = [
    { title: 'Register Interest', desc: 'Fill out the application form with your brand credentials.' },
    { title: 'Identity Verification', desc: 'Our onboarding specialists will verify your GST and identity proofs.' },
    { title: 'Go Live', desc: 'Access your powerful seller dashboard and start listing your products instantly.' }
];

const submit = () => {
    form.post('/become-seller', {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = 'Application Received! Our specialists will reach out in 24 hours.';
            form.reset();
            setTimeout(() => successMessage.value = '', 5000);
        }
    });
};

const themeVars = computed(() => {
    const colors = props.theme?.colors || {};
    const vars = {};
    Object.entries(colors).forEach(([k, v]) => { vars[`--color-${k}`] = v; });
    return vars;
});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
.site-root { font-family: 'Inter', sans-serif; }
.strike-emerald { text-shadow: 4px 4px 0px #4f46e5; }
.italic-black { font-style: italic; font-weight: 900; }

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-30px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-down { animation: fadeInDown 1s ease-out forwards; }

@keyframes bounce-short {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}
.animate-bounce-short { animation: bounce-short 1.5s infinite; }
</style>

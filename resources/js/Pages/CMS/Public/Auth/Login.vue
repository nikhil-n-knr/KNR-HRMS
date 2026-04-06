<template>
    <Head :title="'Login - ' + site.name" />
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-6 site-root" :style="themeVars">
        <div class="w-full max-w-md bg-white rounded-[2.5rem] shadow-2xl shadow-emerald-100 overflow-hidden border border-gray-100 p-12">
            <div class="text-center mb-10">
                <Link href="/" class="inline-block mb-8">
                    <span class="text-2xl font-black text-emerald-950 tracking-tighter uppercase italic">
                        {{ site.name }}<span class="text-emerald-600">.</span>
                    </span>
                </Link>
                <h1 class="text-2xl font-black text-slate-900 leading-tight">Welcome Back</h1>
                <p class="text-slate-400 font-bold text-xs uppercase tracking-widest mt-2">Enter your details to continue</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Email Address</label>
                    <input v-model="form.email" type="email" required
                        class="w-full h-14 bg-gray-50 border-none rounded-2xl px-6 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-emerald-600 transition-all placeholder:text-slate-300"
                        placeholder="alex@example.com" />
                    <p v-if="form.errors.email" class="text-rose-500 text-sm font-bold mt-2 ml-1">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Password</label>
                    <input v-model="form.password" type="password" required
                        class="w-full h-14 bg-gray-50 border-none rounded-2xl px-6 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-emerald-600 transition-all placeholder:text-slate-300"
                        placeholder="••••••••" />
                </div>

                <div class="flex items-center justify-between px-1">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input type="checkbox" v-model="form.remember" class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-600" />
                        <span class="text-sm font-black text-slate-400 uppercase tracking-widest group-hover:text-slate-600 transition-colors">Keep me signed in</span>
                    </label>
                </div>

                <button :disabled="form.processing" type="submit"
                    class="w-full h-14 bg-emerald-600 text-white rounded-2xl text-base font-black uppercase tracking-[0.2em] shadow-xl shadow-emerald-200 hover:bg-emerald-700 hover:-translate-y-0.5 active:translate-y-0 transition-all disabled:opacity-50">
                    Sign In
                </button>
            </form>

            <div class="mt-8 text-center pt-8 border-t border-gray-50">
                <p class="text-sm font-black text-slate-400 uppercase tracking-widest">
                    New to {{ site.name }}? 
                    <Link href="/customer/register" class="text-emerald-600 ml-2 hover:underline">Create Account</Link>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    site: Object,
    theme: Object,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/customer/login', {
        onFinish: () => form.reset('password'),
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
</style>

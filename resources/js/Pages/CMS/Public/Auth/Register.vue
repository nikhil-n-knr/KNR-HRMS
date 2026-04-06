<template>
    <Head :title="'Join ' + site.name" />
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-6 site-root" :style="themeVars">
        <div class="w-full max-w-md bg-white rounded-[2.5rem] shadow-2xl shadow-emerald-100 overflow-hidden border border-gray-100 p-12">
            <div class="text-center mb-10">
                <Link href="/" class="inline-block mb-8">
                    <span class="text-2xl font-black text-emerald-950 tracking-tighter uppercase italic">
                        {{ site.name }}<span class="text-emerald-600">.</span>
                    </span>
                </Link>
                <h1 class="text-2xl font-black text-slate-900 leading-tight">Create Account</h1>
                <p class="text-slate-400 font-bold text-xs uppercase tracking-widest mt-2">Join our elite community</p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <div>
                    <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Full Name</label>
                    <input v-model="form.name" type="text" required
                        class="w-full h-14 bg-gray-50 border-none rounded-2xl px-6 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-emerald-600 transition-all placeholder:text-slate-300"
                        placeholder="John Doe" />
                    <p v-if="form.errors.name" class="text-rose-500 text-sm font-bold mt-2 ml-1">{{ form.errors.name }}</p>
                </div>

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
                    <p v-if="form.errors.password" class="text-rose-500 text-sm font-bold mt-2 ml-1">{{ form.errors.password }}</p>
                </div>

                <div>
                    <label class="block text-sm font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Confirm Password</label>
                    <input v-model="form.password_confirmation" type="password" required
                        class="w-full h-14 bg-gray-50 border-none rounded-2xl px-6 text-sm font-bold text-slate-900 focus:ring-2 focus:ring-emerald-600 transition-all placeholder:text-slate-300"
                        placeholder="••••••••" />
                </div>

                <div class="pt-2">
                    <button :disabled="form.processing" type="submit"
                        class="w-full h-14 bg-emerald-600 text-white rounded-2xl text-base font-black uppercase tracking-[0.2em] shadow-xl shadow-emerald-200 hover:bg-emerald-700 hover:-translate-y-0.5 active:translate-y-0 transition-all disabled:opacity-50">
                        Create Account
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center pt-8 border-t border-gray-50">
                <p class="text-sm font-black text-slate-400 uppercase tracking-widest">
                    Already a member? 
                    <Link href="/customer/login" class="text-emerald-600 ml-2 hover:underline">Sign In</Link>
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
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/customer/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
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

<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 font-inter">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-xl overflow-hidden sm:rounded-2xl border border-gray-200">
            <div class="mb-8 text-center">
                <h1 class="text-3xl font-black text-gray-900 tracking-tight">Client Portal</h1>
                <p class="text-sm text-gray-500 mt-2">Sign in to manage your project bugs and releases.</p>
            </div>

            <form @submit.prevent="submit">
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Email Address</label>
                    <input 
                        v-model="form.email" 
                        type="email" 
                        required 
                        autofocus
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm" 
                    />
                    <div v-if="form.errors.email" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.email }}</div>
                </div>

                <div class="mt-4">
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Password</label>
                    <input 
                        v-model="form.password" 
                        type="password" 
                        required 
                        class="w-full rounded-xl border-gray-300 focus:ring-indigo-500 focus:border-indigo-500 text-sm shadow-sm" 
                    />
                     <div v-if="form.errors.password" class="text-red-500 text-xs mt-1 font-bold">{{ form.errors.password }}</div>
                </div>

                <div class="block mt-4">
                    <label class="flex items-center">
                        <input type="checkbox" v-model="form.remember" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                        <span class="ml-2 text-sm text-gray-600">Remember me</span>
                    </label>
                </div>

                <div class="flex items-center justify-end mt-8">
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-gradient-to-r from-indigo-600 to-violet-600 text-white font-bold py-3 rounded-xl shadow-lg hover:shadow-indigo-200 transition-all hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50"
                    >
                        Secure Login
                    </button>
                </div>
            </form>
            
            <div class="mt-8 pt-6 border-t border-gray-100 text-center text-xs text-gray-400">
                Authorized access only. Technical issues? <a href="#" class="text-indigo-500 font-bold hover:underline">Contact Support</a>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('client.login.post'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

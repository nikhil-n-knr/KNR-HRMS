<template>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-50 font-inter">
        <div class="w-full sm:max-w-md mt-6 px-10 py-12 bg-white shadow-[0_20px_50px_rgba(8,_112,_184,_0.1)] overflow-hidden sm:rounded-[2rem] border border-white">
            <div class="mb-10 text-center">
                <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-600 mb-6 shadow-xl shadow-emerald-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-4.643-9.513c-.347-.442-.516-1.003-.516-1.587 0-1.933 1.567-3.5 3.5-3.5s3.5 1.567 3.5 3.5c0 1.933-1.567 3.5-3.5 3.5zm12 5c0-1.207-.686-2.257-1.688-2.771M14 13c1.933 0 3.5-1.567 3.5-3.5s-1.567-3.5-3.5-3.5-3.5 1.567-3.5 3.5c0 .356.052.699.151 1.025m2.849 0A3.488 3.488 0 0014 13z" />
                    </svg>
                </div>
                <h1 class="text-3xl font-black tracking-tight text-gray-900">Client Portal</h1>
                <p class="text-gray-500 text-sm mt-2 font-medium">Authentication for Managed Projects</p>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div>
                    <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2" for="email">Email Address</label>
                    <input 
                        id="email" 
                        type="email" 
                        class="block w-full px-4 py-3 bg-gray-50 border-transparent rounded-xl text-gray-900 focus:bg-white focus:ring-4 focus:ring-emerald-50/50 focus:border-emerald-500 transition-all placeholder-gray-400" 
                        v-model="form.email" 
                        placeholder="your@email.com"
                        required 
                        autofocus 
                    />
                    <p v-if="form.errors.email" class="text-rose-500 text-xs mt-2 font-bold">{{ form.errors.email }}</p>
                </div>

                <div>
                    <div class="flex justify-between items-center mb-2">
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest" for="password">Password</label>
                        <a href="#" class="text-base font-bold text-emerald-600 hover:text-emerald-700">Forgot?</a>
                    </div>
                    <input 
                        id="password" 
                        type="password" 
                        class="block w-full px-4 py-3 bg-gray-50 border-transparent rounded-xl text-gray-900 focus:bg-white focus:ring-4 focus:ring-emerald-50/50 focus:border-emerald-500 transition-all placeholder-gray-400" 
                        v-model="form.password" 
                        placeholder="••••••••"
                        required 
                    />
                    <p v-if="form.errors.password" class="text-rose-500 text-xs mt-2 font-bold">{{ form.errors.password }}</p>
                </div>

                <!-- reCAPTCHA Widget -->
                <div v-if="$page.props.recaptcha_site_key" class="flex justify-center my-4">
                  <div 
                    id="recaptcha-container"
                    class="g-recaptcha" 
                    :data-sitekey="$page.props.recaptcha_site_key"
                  ></div>
                </div>
                <p v-if="form.errors.recaptcha_token" class="text-rose-500 text-center text-xs font-bold">{{ form.errors.recaptcha_token }}</p>

                <div class="pt-2">
                    <button 
                        type="submit"
                        class="w-full flex justify-center items-center px-6 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black text-sm uppercase tracking-widest shadow-xl shadow-emerald-100 hover:shadow-emerald-200 transition-all transform hover:-translate-y-0.5 active:translate-y-0 active:shadow-emerald-100" 
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }" 
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Authorizing...</span>
                        <span v-else>Continue To Portal</span>
                    </button>
                    
                    <p class="mt-8 text-center text-xs text-gray-400 font-medium">
                        Secure 256-bit encrypted access provided by <br/>
                        <span class="font-black text-emerald-400 uppercase tracking-tight">Nikhil Infotec</span>
                    </p>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    recaptcha_token: '',
});

const renderRecaptcha = () => {
  if (window.grecaptcha && window.grecaptcha.render) {
    const container = document.getElementById('recaptcha-container');
    if (container && container.innerHTML === '') {
      window.grecaptcha.render('recaptcha-container', {
        'sitekey': usePage().props.recaptcha_site_key,
        'callback': (token) => {
          form.recaptcha_token = token;
        },
        'expired-callback': () => {
          form.recaptcha_token = '';
        }
      });
    }
  } else {
    setTimeout(renderRecaptcha, 500);
  }
};

onMounted(() => {
  renderRecaptcha();
});

const submit = () => {
    if (usePage().props.recaptcha_site_key && !form.recaptcha_token) {
        alert('Please complete the reCAPTCHA');
        return;
    }

    form.post(route('portal.login'), {
        onFinish: () => {
            form.reset('password');
            if (window.grecaptcha) window.grecaptcha.reset();
            form.recaptcha_token = '';
        },
    });
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&display=swap');
.font-inter { font-family: 'Inter', sans-serif; }
</style>

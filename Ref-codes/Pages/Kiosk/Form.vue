<script setup>
import KioskLayout from '@/Layouts/KioskLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    purpose: Object,
    config: Object,
    hosts: Array
});

const form = useForm({
    purpose_id: props.purpose.id,
    name: '',
    phone: '',
    email: '',
    company: '',
    host_id: '',
    photo: null, // Logic for camera capture needed
    nda_agreed: false
});

const submit = () => {
    form.post(route('kiosk.store-walk-in'), {
        onSuccess: () => {
            // Controller will redirect to Success
        }
    });
};
</script>

<template>
    <Head title="Registration" />
    <KioskLayout>
        <div class="h-full flex flex-col p-8 md:p-12 max-w-4xl mx-auto w-full">
            
            <div class="flex-shrink-0 flex justify-between items-center mb-8 border-b border-gray-100 pb-4">
                <div>
                     <h1 class="text-3xl font-bold text-gray-900">{{ purpose.name }} Registration</h1>
                     <p class="text-gray-500">Please fill in your details to proceed.</p>
                </div>
                <Link :href="route('kiosk.check-in')" class="text-gray-400 font-bold tracking-wider text-sm hover:text-red-500">BACK</Link>
            </div>

            <form @submit.prevent="submit" class="flex-1 overflow-y-auto space-y-6 pb-20">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Full Name</label>
                        <input v-model="form.name" type="text" required class="w-full text-lg p-4 border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50" placeholder="John Doe">
                    </div>
                    
                    <div v-if="config.phone !== 'hidden'">
                         <label class="block text-sm font-bold text-gray-700 mb-2">Phone Number</label>
                         <input v-model="form.phone" type="tel" :required="config.phone === 'required'" class="w-full text-lg p-4 border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50">
                    </div>

                    <div v-if="config.email !== 'hidden'">
                         <label class="block text-sm font-bold text-gray-700 mb-2">Email Address</label>
                         <input v-model="form.email" type="email" :required="config.email === 'required'" class="w-full text-lg p-4 border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50">
                    </div>

                    <div v-if="config.company !== 'hidden'">
                         <label class="block text-sm font-bold text-gray-700 mb-2">Company / Organization</label>
                         <input v-model="form.company" type="text" :required="config.company === 'required'" class="w-full text-lg p-4 border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Who are you meeting?</label>
                    <select v-model="form.host_id" required class="w-full text-lg p-4 border-gray-300 rounded-xl focus:ring-indigo-500 focus:border-indigo-500 bg-gray-50">
                        <option value="" disabled>Select Host...</option>
                        <option v-for="host in hosts" :key="host.id" :value="host.id">{{ host.name }} ({{ host.department }})</option>
                    </select>
                </div>

                <!-- NDA (Simple Checkbox for MVP) -->
                <div class="bg-gray-50 p-6 rounded-xl border border-gray-200">
                    <label class="flex items-start gap-4 cursor-pointer">
                        <input v-model="form.nda_agreed" type="checkbox" required class="mt-1 w-6 h-6 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                        <span class="text-sm text-gray-600">
                            I agree to the Visitor Policy and Non-Disclosure Agreement. I declare that I am not capturing any proprietary information without authorization.
                        </span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="fixed bottom-0 left-0 right-0 p-6 bg-white border-t border-gray-200 flex justify-center z-20">
                    <button type="submit" :disabled="form.processing" class="w-full max-w-md bg-indigo-600 text-white text-xl font-bold py-4 rounded-xl shadow-xl hover:bg-indigo-700 disabled:opacity-50">
                        {{ form.processing ? 'Processing...' : 'Complete Check-In' }}
                    </button>
                </div>
            </form>
        </div>
    </KioskLayout>
</template>

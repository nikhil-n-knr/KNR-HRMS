<template>
    <Head title="My Profile" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <section class="max-w-xl">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">Profile Information</h2>
                        <p class="mt-1 text-sm text-gray-600">
                            Update your account's profile information.
                        </p>
                    </header>

                    <form @submit.prevent="updateProfile" class="mt-6 space-y-6">
                        <div>
                            <InputLabel for="name" value="Name" />
                            <TextInput
                                id="name"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.name"
                                required
                                autofocus
                                autocomplete="name"
                            />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput
                                id="email"
                                type="email"
                                class="mt-1 block w-full bg-gray-100 cursor-not-allowed"
                                v-model="form.email"
                                disabled
                            />
                            <p class="mt-1 text-sm text-gray-500">Email cannot be changed directly.</p>
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                            <Transition enter-from-class="opacity-0" leave-to-class="opacity-0" class="transition ease-in-out">
                                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                            </Transition>
                        </div>
                    </form>
                </section>
            </div>

            <!-- Payslips Section -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <UserPayslipList :payslips="payslips" />
            </div>
        </div>
    </div>
</template>

<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import UserPayslipList from './Partials/UserPayslipList.vue'; // Import
import { Head, useForm, usePage } from '@inertiajs/vue3';

defineOptions({ layout: MainLayout });

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    payslips: Array // Prop
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});

const updateProfile = () => {
    form.patch(route('profile.update'));
};
</script>

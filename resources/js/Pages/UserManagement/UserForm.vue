<template>
    <Head :title="isEditing ? 'Edit User' : 'Create User'" />
    <div class="bg-[#f4f5fa]">
        <GradientHeroHeader
            kicker="Administration"
            :title="isEditing ? 'Edit User' : 'Create User'"
            :subtitle="isEditing ? 'Update user details and permissions.' : 'Add a new user to the system.'"
        >
            <template #right>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl px-6 py-4 min-w-[160px]">
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1">Mode</p>
                        <p class="text-3xl font-extrabold text-white leading-none">{{ isEditing ? 'Edit' : 'Create' }}</p>
                    </div>
                    <Link
                        href="/admin/users"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-white text-indigo-700 text-sm font-extrabold hover:bg-indigo-50 active:scale-[0.97] transition-all shadow-lg shadow-black/10"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        <span class="hidden sm:inline">Back</span>
                    </Link>
                </div>
            </template>
        </GradientHeroHeader>

        <div class="mx-0 sm:mx-6 mt-5 pb-12">
            <div class="max-w-3xl mx-auto space-y-6">
                <!-- Form Card -->
                <div class="bg-white/80 backdrop-blur-xl rounded-2xl border border-white/60 shadow-sm p-6">
                    <form @submit.prevent="submit" class="space-y-6">
                     
                     <!-- Basic Info -->
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                         <BaseInput v-model="form.name" label="Full Name" placeholder="John Doe" required :error="form.errors.name" />
                    <BaseInput v-model="form.email" label="Email Address" type="email" placeholder="john@company.com" required :error="form.errors.email" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <BaseInput v-model="form.employee_id" label="Employee ID" placeholder="EMP-001" :error="form.errors.employee_id" />
                    
                    <!-- Password (Optional on Edit) -->
                    <BaseInput 
                        v-model="form.password" 
                        label="Password" 
                        type="password" 
                        :placeholder="isEditing ? 'Leave blank to keep current' : 'Enter password'" 
                        :required="!isEditing"
                        :minlength="8"
                        :error="form.errors.password" 
                    />
                </div>

                <!-- Organization -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                        <select v-model="form.department_id" class="w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500/20 bg-white/50">
                            <option :value="null">Select Department</option>
                            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
                        </select>
                        <p v-if="form.errors.department_id" class="text-red-500 text-xs mt-1">{{ form.errors.department_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                        <select v-model="form.location_id" class="w-full rounded-xl border-gray-200 focus:border-emerald-500 focus:ring-emerald-500/20 bg-white/50">
                            <option :value="null">Select Location</option>
                            <option v-for="loc in locations" :key="loc.id" :value="loc.id">{{ loc.name }}</option>
                        </select>
                        <p v-if="form.errors.location_id" class="text-red-500 text-xs mt-1">{{ form.errors.location_id }}</p>
                    </div>
                </div>

                <!-- Roles -->
                <div>
                     <label class="block text-sm font-medium text-gray-700 mb-2">Roles & Permissions</label>
                     <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <label v-for="role in roles" :key="role.id" class="flex items-center gap-3 p-3 rounded-xl border border-gray-200 bg-white/50 hover:border-emerald-500/30 cursor-pointer transition-all" :class="{'ring-2 ring-emerald-500/20 border-emerald-500': form.role_ids.includes(role.id)}">
                            <input type="checkbox" :value="role.id" v-model="form.role_ids" class="rounded text-emerald-600 focus:ring-emerald-500">
                            <span class="text-sm font-medium text-gray-700">{{ role.name }}</span>
                        </label>
                     </div>
                     <p v-if="form.errors.role_ids" class="text-red-500 text-xs mt-1">{{ form.errors.role_ids }}</p>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                    <Link href="/admin/users" class="px-5 py-2.5 text-gray-600 font-medium hover:bg-gray-100 rounded-xl transition">
                        Cancel
                    </Link>
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-medium rounded-xl shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/30 hover:-translate-y-0.5 disabled:opacity-50 disabled:cursor-not-allowed transition-all"
                    >
                        {{ form.processing ? 'Saving...' : (isEditing ? 'Update User' : 'Create User') }}
                     </button>
                 </div>
             </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useForm, Link, Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import BaseInput from '@/Components/BaseInput.vue';
import { useToastStore } from '@/stores/toast';
import GradientHeroHeader from '@/Components/UI/GradientHeroHeader.vue';

defineOptions({ layout: MainLayout });

const props = defineProps({
    user: Object,
    roles: Array,
    departments: Array,
    locations: Array
});

const toast = useToastStore();
const isEditing = computed(() => !!props.user);

const form = useForm({
    name: props.user?.name || '',
    email: props.user?.email || '',
    employee_id: props.user?.employee_id || '',
    password: '',
    department_id: props.user?.department_id || null,
    location_id: props.user?.location_id || null,
    role_ids: props.user?.roles?.map(r => r.id) || []
});

const submit = () => {
    if (isEditing.value) {
        form.put(`/admin/users/${props.user.id}`, {
            onSuccess: () => toast.success('User updated successfully'),
            onError: () => toast.error('Check form errors')
        });
    } else {
        form.post('/admin/users', {
            onSuccess: () => toast.success('User created successfully'),
            onError: () => toast.error('Check form errors')
        });
    }
};
</script>

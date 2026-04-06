<template>
    <Modal :show="show" @close="close">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Edit Statutory Details</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Numbers -->
                <div class="col-span-2 md:col-span-1">
                    <InputLabel for="uan" value="UAN Number" />
                    <TextInput id="uan" v-model="form.uan_number" class="mt-1 block w-full" placeholder="Universal Account Number" />
                </div>
                 <div class="col-span-2 md:col-span-1">
                    <InputLabel for="esi" value="ESI Number" />
                    <TextInput id="esi" v-model="form.esi_number" class="mt-1 block w-full" placeholder="Insurance Number" />
                </div>
                 <div class="col-span-2">
                    <InputLabel for="pan" value="PAN Number" />
                    <TextInput id="pan" v-model="form.pan_number" class="mt-1 block w-full uppercase" placeholder="Permanent Account Number" maxlength="10" />
                </div>

                <!-- Toggles -->
                <div class="col-span-2 border-t pt-4 mt-2 space-y-4">
                    <h3 class="text-sm font-bold text-gray-700">Compliance Logic</h3>
                    
                    <div class="flex items-start gap-3">
                        <div class="flex h-5 items-center">
                            <input 
                                id="iw" 
                                type="checkbox" 
                                v-model="form.is_international_worker" 
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            >
                        </div>
                        <div class="text-sm">
                            <label for="iw" class="font-medium text-gray-700">International Worker</label>
                            <p class="text-gray-500 text-xs">If checked, PF Wage Cap (₹15,000) will be ignored as per regulations.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="flex h-5 items-center">
                            <input 
                                id="dr" 
                                type="checkbox" 
                                v-model="form.is_director" 
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                            >
                        </div>
                        <div class="text-sm">
                            <label for="dr" class="font-medium text-gray-700">Director / Principal Officer</label>
                            <p class="text-gray-500 text-xs">If checked, this employee is exempt from mandatory PF/ESI coverage.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton @click="close">Cancel</SecondaryButton>
                <PrimaryButton @click="submit" :disabled="form.processing">Save Changes</PrimaryButton>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: Boolean,
    employee: Object
});

const emit = defineEmits(['close', 'saved']);

const form = useForm({
    uan_number: '',
    esi_number: '',
    pan_number: '',
    is_international_worker: false,
    is_director: false
});

watch(() => props.employee, (newVal) => {
    if (newVal) {
        form.uan_number = newVal.uan_number || '';
        form.esi_number = newVal.esi_number || '';
        form.pan_number = newVal.pan_number || '';
        form.is_international_worker = !!newVal.is_international_worker;
        form.is_director = !!newVal.is_director;
    }
}, { immediate: true });

const submit = () => {
    form.put(route('admin.employees.update', props.employee.id), {
        onSuccess: () => {
            emit('saved');
            close();
        }
    });
};

const close = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};
</script>

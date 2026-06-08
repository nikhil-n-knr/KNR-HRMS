<template>
  <div class="space-y-6">
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
          <div class="p-6 border-b border-gray-100">
              <h3 class="font-bold text-gray-800">Compliance Configuration</h3>
              <p class="text-sm text-gray-500">Update Employer Codes and Rules.</p>
          </div>
          
          <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
              
              <!-- PF Settings -->
              <div class="space-y-4">
                  <h4 class="font-bold text-sm text-gray-700 uppercase tracking-wide border-b pb-2">Provident Fund (PF)</h4>
                   <div>
                      <InputLabel value="Employer PF Code" />
                      <TextInput v-model="form.pf_employer_code" class="mt-1 block w-full" placeholder="e.g. KN/BNG/0012345" />
                  </div>
                   <div class="flex items-center gap-2 mt-4">
                      <input type="checkbox" v-model="form.pf_enabled" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                      <span class="text-sm text-gray-700 font-medium">Enable PF Deduction</span>
                  </div>
                  <div class="flex items-center gap-2">
                       <input type="checkbox" v-model="form.pf_wage_ceiling" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                      <span class="text-sm text-gray-700">Apply ₹15,000 Wage Ceiling Cap</span>
                  </div>
              </div>

               <!-- ESI Settings -->
              <div class="space-y-4">
                  <h4 class="font-bold text-sm text-gray-700 uppercase tracking-wide border-b pb-2">ESI (Employee State Insurance)</h4>
                   <div>
                      <InputLabel value="Employer ESI Code" />
                      <TextInput v-model="form.esi_employer_code" class="mt-1 block w-full" placeholder="17-Digit Code" />
                  </div>
                   <div class="flex items-center gap-2 mt-4">
                      <input type="checkbox" v-model="form.esi_enabled" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                      <span class="text-sm text-gray-700 font-medium">Enable ESI Deduction</span>
                  </div>
                  <p class="text-xs text-gray-500">Wage Limit: ₹21,000 (Fixed)</p>
              </div>

               <!-- PT Settings -->
               <div class="space-y-4 col-span-2">
                   <h4 class="font-bold text-sm text-gray-700 uppercase tracking-wide border-b pb-2">Professional Tax (PT)</h4>
                    <div>
                      <InputLabel value="PT Employer Registration No" />
                      <TextInput v-model="form.pt_employer_code" class="mt-1 block w-full" />
                  </div>
               </div>

          </div>
          <div class="bg-gray-50 px-6 py-4 flex justify-end">
              <PrimaryButton @click="save" :disabled="form.processing">Save Configuration</PrimaryButton>
          </div>
      </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { useToastStore } from '@/stores/toast';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    configs: Object // Key-Value map from backend
});

const toast = useToastStore();

const form = useForm({
    pf_employer_code: props.configs?.pf_employer_code?.value || '',
    pf_enabled: props.configs?.pf_enabled?.value == '1',
    pf_wage_ceiling: props.configs?.pf_wage_ceiling?.value == '1',
    esi_employer_code: props.configs?.esi_employer_code?.value || '',
    esi_enabled: props.configs?.esi_enabled?.value == '1',
    pt_employer_code: props.configs?.pt_employer_code?.value || ''
});

const save = () => {
    form.post(route('hr.compliance.config.save'), {
        preserveScroll: true,
        onSuccess: () => toast.success('Configuration Saved')
    });
};
</script>

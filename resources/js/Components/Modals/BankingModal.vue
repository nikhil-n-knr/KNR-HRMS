 <template>
    <Modal :show="show" title="Edit Banking Details" maxWidth="4xl" @close="$emit('close')">
        <form @submit.prevent="save">
            <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3 pb-1 border-b">Primary Account Details</h4>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <BaseInput 
                    v-model="form.bank_name"
                    label="Bank Name"
                    placeholder="e.g. HDFC Bank"
                    maxlength="100"
                />
                <BaseInput 
                    v-model="form.branch_name"
                    label="Branch"
                    placeholder="e.g. MG Road, Bangalore"
                    maxlength="100"
                />
                <BaseInput 
                    v-model="form.account_holder_name"
                    label="Account Holder Name"
                    placeholder="As per bank records"
                    maxlength="100"
                />
                <BaseInput 
                    v-model="form.account_number"
                    label="Account Number"
                    inputClass="font-mono tracking-wider"
                    maxlength="20"
                    placeholder="0000 0000 0000"
                    @input="e => form.account_number = e.target.value.replace(/[^0-9]/g, '')"
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <BaseInput 
                    v-model="form.ifsc_code"
                    label="IFSC Code"
                    inputClass="uppercase font-mono"
                    maxlength="11"
                    placeholder="HDFC0001234"
                    @input="e => form.ifsc_code = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '')"
                />
                <BaseInput 
                    v-model="form.bic_code"
                    label="BIC/SWIFT Code"
                    inputClass="uppercase font-mono"
                    maxlength="11"
                    placeholder="Optional"
                    @input="e => form.bic_code = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '')"
                />
                <BaseSelect
                    v-model="form.account_type"
                    label="Account Type"
                >
                    <option value="savings">Savings</option>
                    <option value="current">Current</option>
                    <option value="salary">Salary</option>
                </BaseSelect>
            </div>

            <div class="flex items-center gap-2 mt-4 p-3 bg-emerald-50 rounded-lg border border-emerald-100">
                <input type="checkbox" v-model="form.is_primary" id="is_primary" class="rounded text-emerald-600 focus:ring-emerald-500 border-gray-300">
                <label for="is_primary" class="text-sm font-medium text-emerald-800 cursor-pointer">Set as Primary Account for Salary Processing</label>
            </div>

                <!-- Errors -->
            <div v-if="Object.keys(errors).length > 0" class="mt-4 bg-red-50 p-3 rounded-lg border border-red-100">
                <ul class="list-disc list-inside text-xs text-red-600">
                    <li v-for="(error, key) in errors" :key="key">{{ error[0] }}</li>
                </ul>
            </div>
        </form>

        <template #footer>
            <button @click="$emit('close')" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg text-sm font-medium transition">
                Cancel
            </button>
            <button 
                @click="save" 
                :disabled="processing"
                class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium shadow-md hover:bg-emerald-700 transition-all disabled:opacity-70 flex items-center gap-2"
            >
                <div v-if="processing" class="w-3 h-3 border-2 border-white/30 border-t-white rounded-full animate-spin"></div>
                Save Account
            </button>
        </template>
    </Modal>
</template>

<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import { useToastStore } from '@/stores/toast';
import Modal from '@/Components/Modal.vue';
import BaseInput from '@/Components/BaseInput.vue';
import BaseSelect from '@/Components/BaseSelect.vue';

const props = defineProps({
  show: Boolean,
  employee: Object,
  detail: [Object, Array]
});

const emit = defineEmits(['close', 'saved']);
const toast = useToastStore();
const processing = ref(false);
const errors = ref({});

const form = ref({});

const initForm = () => ({
    bank_name: '',
    branch_name: '',
    account_holder_name: '',
    account_number: '',
    ifsc_code: '',
    bic_code: '',
    account_type: 'salary',
    is_primary: true
});

watch(() => props.detail, (newVal) => {
    // Current design in EmployeeController eager loads `bankDetails` which is HasMany?
    // Let's assume we pass the *primary* detail or the first one.
    // If props.detail is an array, take first. If object, take it.
    let data = newVal;
    if (Array.isArray(newVal) && newVal.length > 0) {
        data = newVal[0];
    } else if (Array.isArray(newVal) && newVal.length === 0) {
        data = null;
    }

    if (data) {
        form.value = { ...data, is_primary: Boolean(data.is_primary) };
    } else {
        form.value = initForm();
    }
}, { immediate: true });

const save = async () => {
    processing.value = true;
    errors.value = {};
    try {
        await axios.put(`/api/admin/employees/${props.employee.id}/bank`, form.value);
        toast.success("Banking details updated");
        emit('saved');
        emit('close');
    } catch (error) {
         if (error.response?.data?.errors) {
            errors.value = error.response.data.errors;
        } else {
            toast.error("Failed to save details");
        }
    } finally {
        processing.value = false;
    }
};
</script>

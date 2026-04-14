<script setup>
import { computed, ref, watch } from 'vue'
import { Combobox, ComboboxInput, ComboboxButton, ComboboxOptions, ComboboxOption, TransitionRoot } from '@headlessui/vue'
import { CheckIcon, ChevronUpDownIcon, XMarkIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  modelValue: [String, Number, Object, Array],
  items: { type: Array, default: () => [] },
  labelKey: { type: String, default: 'name' }, // What to search/display e.g. 'first_name' or custom function
  valueKey: { type: String, default: 'id' },   // What to return e.g. 'id'
  placeholder: { type: String, default: 'Select...' },
  displayFormat: { type: Function, default: null }, // Optional function to format display text
  multiple: { type: Boolean, default: false }
})

const emit = defineEmits(['update:modelValue'])

const query = ref('')

// Function to get display string from an item
const getDisplay = (item) => {
    if (!item) return '';
    if (props.displayFormat) return props.displayFormat(item);
    return item[props.labelKey] || '';
};

// Filtered items based on query
const filteredItems = computed(() => {
  const items = Array.isArray(props.items) ? props.items : [];
  if (query.value === '') return items;
  
  const search = query.value.toLowerCase().replace(/\s+/g, '');
  return items.filter((item) => {
    const display = getDisplay(item);
    if (!display) return false;
    
    // Search in display text
    const text = String(display).toLowerCase().replace(/\s+/g, '');
    if (text.includes(search)) return true;
    
    // Fallback: search in code if available
    const code = item.employee_code || item.code;
    if (code && String(code).toLowerCase().includes(search)) return true;
    
    // Fallback: search in id
    if (item.id && String(item.id).includes(search)) return true;
    
    return false;
  });
});

// Handle selection: Headless UI returns the whole object usually, but we might want just ID if valueKey is set.
// Actually Headless UI Combobox v-model binds to the selected ITEM.
// We need to sync with parent which might expect an ID.
// Strategy: Internal v-model is the Object. Watch it and emit the valueKey.

const selectedItem = ref(props.multiple ? [] : null);

watch(() => props.modelValue, (newVal) => {
    if (props.multiple) {
        if (Array.isArray(newVal) && newVal.length) {
             selectedItem.value = props.items.filter(i => newVal.includes(i[props.valueKey]));
        } else {
             selectedItem.value = [];
        }
    } else {
        if (newVal && props.items.length) {
            // Find item matching the ID/Value
            const found = props.items.find(i => i[props.valueKey] === newVal);
            if (found) selectedItem.value = found;
        } else {
            selectedItem.value = null;
        }
    }
}, { immediate: true, deep: true });

watch(selectedItem, (newVal) => {
    if (props.multiple) {
        const ids = Array.isArray(newVal) ? newVal.map(i => i[props.valueKey]) : [];
        if (JSON.stringify(ids) !== JSON.stringify(props.modelValue)) {
            emit('update:modelValue', ids);
        }
    } else {
        if (newVal) {
             // Avoid infinite loops if matching
             if (newVal[props.valueKey] !== props.modelValue) {
                emit('update:modelValue', newVal[props.valueKey]);
             }
        } else {
            emit('update:modelValue', null);
        }
    }
});

const displayValueFn = (item) => {
    if (props.multiple) return ''; // Input should be empty for multiple to allow searching
    return getDisplay(item);
};

const removeItem = (item) => {
    if (props.multiple && Array.isArray(selectedItem.value)) {
        selectedItem.value = selectedItem.value.filter(i => i[props.valueKey] !== item[props.valueKey]);
    }
};
</script>

<template>
  <div class="">
    <Combobox v-model="selectedItem" :multiple="multiple" v-slot="{ open }">
      <div class="relative mt-1">
        <div
          class="relative w-full cursor-default overflow-hidden rounded-lg bg-white text-left shadow-sm focus:outline-none focus:ring-1 focus:ring-emerald-500 sm:text-sm border border-gray-300 flex flex-wrap gap-1 p-1 pr-10 items-center min-h-[38px]"
        >
          <!-- Tags for Multiple Selection -->
          <template v-if="multiple && Array.isArray(selectedItem)">
             <span v-for="item in selectedItem" :key="item[valueKey]" class="inline-flex items-center gap-1 rounded bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700 whitespace-nowrap border border-emerald-100">
                {{ getDisplay(item) }}
                <button type="button" @click.stop="removeItem(item)" class="text-emerald-400 hover:text-emerald-900 focus:outline-none">
                    <XMarkIcon class="h-3 w-3" />
                </button>
            </span>
          </template>

          <ComboboxInput
            class="w-full border-none py-1 pl-2 pr-10 text-sm leading-5 text-gray-900 focus:ring-0 focus:outline-none min-w-[50px] flex-1"
            :displayValue="displayValueFn"
            @input="query = $event.target.value"
            :placeholder="placeholder"
          />
          <ComboboxButton
            class="absolute inset-y-0 right-0 flex items-center pr-2"
          >
            <ChevronUpDownIcon
              class="h-5 w-5 text-gray-400"
              aria-hidden="true"
            />
          </ComboboxButton>
        </div>
        <TransitionRoot
          :show="open"
          leave="transition ease-in duration-100"
          leaveFrom="opacity-100"
          leaveTo="opacity-0"
          @after-leave="query = ''"
        >
          <ComboboxOptions
            class="absolute mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm z-[9999]"
          >
            <div
              v-if="filteredItems.length === 0 && query !== ''"
              class="relative cursor-default select-none px-4 py-2 text-gray-700 font-bold uppercase text-[10px] tracking-widest"
            >
              No Operator Found
            </div>

            <ComboboxOption
              v-for="item in filteredItems"
              as="template"
              :key="item[valueKey]"
              :value="item"
              v-slot="{ selected, active }"
            >
              <li
                class="relative cursor-default select-none py-2 pl-10 pr-4"
                :class="{
                  'bg-emerald-600 text-white': active,
                  'text-slate-900': !active,
                }"
              >
                <div class="flex flex-col">
                    <span
                    class="block truncate"
                    :class="{ 'font-black': selected, 'font-medium': !selected }"
                    >
                    {{ getDisplay(item) }}
                    </span>
                 </div>

                <span
                  v-if="selected"
                  class="absolute inset-y-0 left-0 flex items-center pl-3"
                  :class="{ 'text-white': active, 'text-emerald-600': !active }"
                >
                  <CheckIcon class="h-5 w-5" aria-hidden="true" />
                </span>
              </li>
            </ComboboxOption>
          </ComboboxOptions>
        </TransitionRoot>
      </div>
    </Combobox>
  </div>
</template>

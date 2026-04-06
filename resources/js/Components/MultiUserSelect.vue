<script setup>
import { computed, ref, watch } from 'vue'
import { Combobox, ComboboxInput, ComboboxButton, ComboboxOptions, ComboboxOption, TransitionRoot } from '@headlessui/vue'
import { CheckIcon, ChevronUpDownIcon, XMarkIcon } from '@heroicons/vue/20/solid'

const props = defineProps({
  modelValue: { type: Array, default: () => [] }, // Array of IDs
  items: { type: Array, default: () => [] },
  labelKey: { type: String, default: 'name' }, // Display key
  valueKey: { type: String, default: 'id' },   // Value key
  placeholder: { type: String, default: 'Select users...' }
})

const emit = defineEmits(['update:modelValue'])

const query = ref('')

// Filter logic: Search by Name OR ID
const filteredItems = computed(() =>
  query.value === ''
    ? props.items
    : props.items.filter((item) => {
        const text = (item[props.labelKey] || '').toLowerCase().replace(/\s+/g, '');
        const search = query.value.toLowerCase().replace(/\s+/g, '');
        const idMatch = String(item[props.valueKey]).includes(search);
        return text.includes(search) || idMatch;
      })
)

// Internal selection object (Array of Objects)
const selectedItems = ref([]);

// Sync from parent (IDs -> Objects)
watch(() => props.modelValue, (newVal) => {
    if (!newVal) {
        selectedItems.value = [];
        return;
    }
    // Map IDs to Items
    selectedItems.value = props.items.filter(i => newVal.includes(i[props.valueKey]));
}, { immediate: true, deep: true });

// Sync to parent (Objects -> IDs)
watch(selectedItems, (newVal) => {
    const ids = newVal.map(i => i[props.valueKey]);
    // Prevent infinite loop if values match
    if (JSON.stringify(ids) !== JSON.stringify(props.modelValue)) {
        emit('update:modelValue', ids);
    }
});

const removeItem = (item) => {
    selectedItems.value = selectedItems.value.filter(i => i[props.valueKey] !== item[props.valueKey]);
};
</script>

<template>
  <div class="relative">
    <Combobox v-model="selectedItems" multiple v-slot="{ open }">
      <div class="relative mt-1">
        <div
          class="relative w-full cursor-default overflow-hidden rounded-lg bg-white text-left shadow-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 sm:text-sm border border-gray-300 min-h-[42px] flex flex-wrap gap-1 p-1 pr-10"
        >
            <!-- Selected Tags -->
            <span v-for="item in selectedItems" :key="item[valueKey]" class="inline-flex items-center gap-1 rounded bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700">
                {{ item[labelKey] }}
                <button type="button" @click.stop="removeItem(item)" class="text-indigo-400 hover:text-indigo-900 focus:outline-none">
                    <XMarkIcon class="h-3 w-3" />
                </button>
            </span>

          <ComboboxInput
            class="w-full border-none py-1 pl-2 pr-10 text-sm leading-5 text-gray-900 focus:ring-0 focus:outline-none min-w-[100px]"
            :displayValue="() => ''" 
            @change="query = $event.target.value"
            :placeholder="selectedItems.length ? '' : placeholder"
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
            class="absolute mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm z-50 text-left"
          >
            <div
              v-if="filteredItems.length === 0 && query !== ''"
              class="relative cursor-default select-none px-4 py-2 text-gray-700"
            >
              Nothing found.
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
                  'bg-indigo-600 text-white': active,
                  'text-gray-900': !active,
                }"
              >
                <div class="flex flex-col">
                    <span
                    class="block truncate"
                    :class="{ 'font-medium': selected, 'font-normal': !selected }"
                    >
                    {{ item[labelKey] }}
                    </span>
                    <span class="text-[10px] opacity-70">ID: {{ item[valueKey] }}</span>
                </div>
                
                <span
                  v-if="selected"
                  class="absolute inset-y-0 left-0 flex items-center pl-3"
                  :class="{ 'text-white': active, 'text-indigo-600': !active }"
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

<template>
    <TransitionRoot :show="isOpen" as="template" @after-leave="query = ''">
        <Dialog as="div" class="relative z-[100]" @close="isOpen = false">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-emerald-950/40 backdrop-blur-sm" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto p-4 sm:p-6 md:p-20">
                <TransitionChild
                    as="template"
                    enter="duration-300 ease-out"
                    enter-from="opacity-0 scale-95"
                    enter-to="opacity-100 scale-100"
                    leave="duration-200 ease-in"
                    leave-from="opacity-100 scale-100"
                    leave-to="opacity-0 scale-95"
                >
                    <DialogPanel class="mx-auto max-w-2xl transform divide-y divide-emerald-100 overflow-hidden rounded-2xl bg-white/80 backdrop-blur-2xl shadow-2xl ring-1 ring-black ring-opacity-5 transition-all">
                        <div class="relative">
                            <MagnifyingGlassIcon class="pointer-events-none absolute left-4 top-3.5 h-6 w-6 text-emerald-600" aria-hidden="true" />
                            <input 
                                ref="inputRef"
                                class="h-14 w-full border-0 bg-transparent pl-12 pr-4 text-emerald-900 placeholder-emerald-400 focus:ring-0 sm:text-sm font-medium" 
                                placeholder="Search actions... (e.g. 'Apply Leave', 'Clock In')" 
                                v-model="query"
                                @keydown.esc="isOpen = false"
                            />
                        </div>

                        <ul v-if="filteredActions.length > 0" class="max-h-80 scroll-py-2 overflow-y-auto py-2 text-sm text-emerald-800">
                            <li 
                                v-for="action in filteredActions" 
                                :key="action.name" 
                                @click="runAction(action)"
                                class="flex cursor-default select-none items-center px-4 py-3 hover:bg-emerald-600 hover:text-white transition group"
                            >
                                <component :is="action.icon" class="h-5 w-5 mr-3 text-emerald-600 group-hover:text-white" />
                                <div class="flex-grow">
                                    <p class="font-bold">{{ action.name }}</p>
                                    <p class="text-[10px] opacity-70">{{ action.description }}</p>
                                </div>
                                <span class="text-[10px] font-mono opacity-40 group-hover:opacity-100">ENTER</span>
                            </li>
                        </ul>

                        <div v-if="query !== '' && filteredActions.length === 0" class="px-6 py-14 text-center sm:px-14">
                            <ExclamationCircleIcon class="mx-auto h-6 w-6 text-emerald-400" />
                            <p class="mt-4 text-sm text-emerald-900">No actions found for "{{ query }}".</p>
                        </div>
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { 
    Dialog, DialogPanel, TransitionChild, TransitionRoot 
} from '@headlessui/vue';
import { 
    MagnifyingGlassIcon, 
    ExclamationCircleIcon,
    CalendarIcon,
    ClockIcon,
    CurrencyDollarIcon,
    DocumentArrowDownIcon
} from '@heroicons/vue/24/outline';

const isOpen = ref(false);
const query = ref('');
const inputRef = ref(null);

const actions = [
    { name: 'Apply Leave', description: 'File a new leave request', icon: CalendarIcon, route: '/leave' },
    { name: 'Clock In', description: 'Register your attendance start', icon: ClockIcon, action: 'clock-in' },
    { name: 'Download Payslip', description: 'Get your latest salary receipt', icon: DocumentArrowDownIcon, route: '/payroll' },
    { name: 'View My Tasks', description: 'Open project task manager', icon: MagnifyingGlassIcon, route: '/projects' },
    { name: 'Expense Reclaim', description: 'Submit a new expense bill', icon: CurrencyDollarIcon, route: '/expenses' },
];

const filteredActions = computed(() =>
    query.value === ''
        ? actions
        : actions.filter((action) =>
            action.name.toLowerCase().includes(query.value.toLowerCase())
        )
);

const runAction = (action) => {
    console.log('Running action:', action.name);
    isOpen.value = false;
    // router.visit(action.route) or emit action
};

const handleKeyDown = (event) => {
    if ((event.metaKey || event.ctrlKey) && event.key === 'k') {
        event.preventDefault();
        isOpen.value = true;
    }
};

onMounted(() => window.addEventListener('keydown', handleKeyDown));
onUnmounted(() => window.removeEventListener('keydown', handleKeyDown));
</script>

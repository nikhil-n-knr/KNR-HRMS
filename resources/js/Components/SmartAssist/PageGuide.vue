<script setup>
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { XMarkIcon, ClipboardDocumentIcon, CheckIcon } from '@heroicons/vue/24/outline';
import { ref } from 'vue';

const props = defineProps({
    visible: Boolean,
    title: String
});

const emit = defineEmits(['update:visible', 'close']);

const close = () => {
    emit('update:visible', false);
    emit('close');
};

const copyToClipboard = async (text) => {
    try {
        await navigator.clipboard.writeText(text);
        // Could enable a tooltip checkmark here
    } catch (err) {
        console.error('Failed to copy', err);
    }
};

const activeTab = ref(0);
const tabs = ref([]); 
// We will register tabs via slot content scanning if possible, or just use simple v-if logic in parent 
// But a cleaner way for this 'Smart Assist' is to have generic structure.
// Let's implement Tabs support via Provide/Inject or just simple slot usage.
// For simplicity: The parent manages the content. This is just the shell.
</script>

<template>
    <TransitionRoot as="template" :show="visible">
        <Dialog as="div" class="relative z-50" @close="close">
             <TransitionChild as="template" enter="ease-in-out duration-500" enter-from="opacity-0" enter-to="opacity-100" leave="ease-in-out duration-500" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                        <TransitionChild as="template" enter="transform transition ease-in-out duration-500 sm:duration-700" enter-from="translate-x-full" enter-to="translate-x-0" leave="transform transition ease-in-out duration-500 sm:duration-700" leave-from="translate-x-0" leave-to="translate-x-full">
                            <DialogPanel class="pointer-events-auto w-screen max-w-md">
                                <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                                    <div class="bg-indigo-700 px-4 py-6 sm:px-6">
                                        <div class="flex items-center justify-between">
                                            <h2 class="text-base font-semibold leading-6 text-white" id="slide-over-title">
                                                {{ title || 'Guide' }}
                                            </h2>
                                            <div class="ml-3 flex h-7 items-center">
                                                <button type="button" class="relative rounded-md bg-indigo-700 text-indigo-200 hover:text-white focus:outline-none focus:ring-2 focus:ring-white" @click="close">
                                                    <span class="absolute -inset-2.5" />
                                                    <span class="sr-only">Close panel</span>
                                                    <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mt-1">
                                            <p class="text-sm text-indigo-200">
                                                Follow these steps to configure your integration correctly.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="relative flex-1 px-4 py-6 sm:px-6">
                                        <!-- Content Slot -->
                                        <slot />
                                    </div>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

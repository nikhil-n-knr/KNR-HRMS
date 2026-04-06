<script setup>
import { useDrawerStore } from '@/stores/drawer';
import { XMarkIcon } from '@heroicons/vue/24/outline';

const drawer = useDrawerStore();
</script>

<template>
    <teleport to="body">
        <div v-if="drawer.isOpen" class="relative z-[60]" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
            <!-- Background backdrop -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="drawer.close"></div>

            <div class="fixed inset-0 overflow-hidden">
                <div class="absolute inset-0 overflow-hidden">
                    <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                        <div class="pointer-events-auto w-screen max-w-md transform transition duration-500 ease-in-out sm:duration-700">
                            <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                                <!-- Header -->
                                <div class="px-4 py-6 sm:px-6 bg-gradient-to-r from-indigo-500 to-indigo-600">
                                    <div class="flex items-start justify-between">
                                        <h2 class="text-base font-semibold leading-6 text-white" id="slide-over-title">
                                            {{ drawer.title || 'Quick View' }}
                                        </h2>
                                        <div class="ml-3 flex h-7 items-center">
                                            <button type="button" @click="drawer.close" class="relative rounded-md text-indigo-200 hover:text-white focus:outline-none">
                                                <span class="absolute -inset-2.5"></span>
                                                <span class="sr-only">Close panel</span>
                                                <XMarkIcon class="h-6 w-6" aria-hidden="true" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Content -->
                                <div class="relative flex-1 px-4 py-6 sm:px-6">
                                    <component :is="drawer.view" v-bind="drawer.props" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>

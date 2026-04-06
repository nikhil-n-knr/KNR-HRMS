<template>
    <div class="bg-indigo-900 rounded-[3rem] p-12 text-white shadow-2xl relative overflow-hidden group">
        <!-- Abstract Background -->
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,rgba(99,102,241,0.2),transparent)] pointer-events-none"></div>

        <div class="relative z-10 space-y-10 text-center">
            <transition name="fade" mode="out-in">
                <div v-if="!submitted" key="form" class="space-y-10">
                    <div class="space-y-4">
                        <h3 class="text-4xl font-black tracking-tighter">Mission Accomplished?</h3>
                        <p class="text-indigo-200 text-lg font-medium opacity-80">How would you rate the resolution quality of this anomaly?</p>
                    </div>

                    <div class="flex justify-center gap-6">
                        <button v-for="n in 5" :key="n" 
                                @click="rating = n"
                                :class="[
                                    'h-16 w-16 rounded-[1.5rem] flex flex-col items-center justify-center transition-all border-2 active:scale-90',
                                    rating === n ? 'bg-white border-white scale-110 shadow-2xl shadow-white/20' : 'bg-white/5 border-white/5 hover:bg-white/10'
                                ]">
                            <span :class="['text-2xl', rating === n ? 'grayscale-0' : 'grayscale opacity-50']">{{ emojis[n-1] }}</span>
                        </button>
                    </div>

                    <transition name="fade">
                        <div v-if="rating" class="space-y-6 animate-in slide-in-from-bottom duration-500">
                            <textarea 
                                v-model="feedback" 
                                placeholder="Optional debriefing notes..." 
                                class="w-full bg-black/20 border-white/10 rounded-2xl p-6 text-sm text-white placeholder-indigo-300 focus:ring-1 focus:ring-white transition-all resize-none"
                            ></textarea>
                            
                            <button 
                                @click="submitRating"
                                :disabled="submitting"
                                class="px-12 py-5 bg-white text-indigo-900 rounded-2xl text-base font-black uppercase tracking-[0.2em] shadow-2xl hover:scale-105 active:scale-95 transition-all w-full sm:w-auto"
                            >
                                {{ submitting ? 'Transmitting...' : 'Confirm Rating' }}
                            </button>
                        </div>
                    </transition>
                </div>

                <div v-else key="success" class="py-20 space-y-6 animate-in zoom-in duration-500">
                    <div class="h-20 w-20 bg-emerald-500 rounded-full flex items-center justify-center mx-auto shadow-2xl shadow-emerald-500/40">
                        <CheckIcon class="h-10 w-10 text-white stroke-[4]" />
                    </div>
                    <div>
                        <h3 class="text-3xl font-black tracking-tighter">Feedback Received</h3>
                        <p class="text-indigo-200 font-medium opacity-80 mt-2">Operational log is being finalized. Thank you.</p>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Decor -->
        <div class="absolute -right-12 -bottom-12 h-48 w-48 bg-white/5 rounded-full blur-3xl group-hover:bg-white/10 transition-colors"></div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { CheckIcon } from '@heroicons/vue/24/outline';
import axios from 'axios';

const props = defineProps(['ticketId']);
const emit = defineEmits(['rated']);

const rating = ref(0);
const feedback = ref('');
const submitting = ref(false);
const submitted = ref(false);
const emojis = ['😡', '🙁', '😐', '😊', '🤩'];

const submitRating = async () => {
    submitting.value = true;
    try {
        await axios.post(route('portal.tickets.rate', props.ticketId), {
            rating: rating.value,
            feedback: feedback.value
        });
        submitted.value = true;
        setTimeout(() => {
            emit('rated');
        }, 2000);
    } catch (e) {
        console.error("Transmission failure", e);
    } finally {
        submitting.value = false;
    }
};
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.5s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>

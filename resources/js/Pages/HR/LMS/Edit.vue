<template>
  <MainLayout>
    <Head :title="`Edit: ${course.title}`" />
    
    <div class="py-6">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
          <div class="flex items-center gap-3">
            <Link :href="route('hr.lms.show', course.id)" class="text-gray-500 hover:text-gray-700">
              <ArrowLeftIcon class="h-5 w-5" />
            </Link>
            <h1 class="text-3xl font-bold text-gray-900">Edit Course</h1>
          </div>
          <p class="mt-1 text-sm text-gray-600">{{ course.title }}</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submitForm">
          <div class="bg-white rounded-lg shadow-sm p-6 mb-6 space-y-6">
            <!-- Basic Info -->
            <div>
              <h2 class="text-xl font-semibold text-gray-900 mb-4">Basic Information</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                  <InputLabel for="title" value="Course Title *" />
                  <TextInput
                    id="title"
                    v-model="form.title"
                    type="text"
                    class="mt-1 block w-full"
                    required
                  />
                  <InputError :message="form.errors.title" class="mt-2" />
                </div>

                <div class="md:col-span-2">
                  <InputLabel for="description" value="Description" />
                  <textarea
                    id="description"
                    v-model="form.description"
                    rows="4"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                  />
                </div>

                <div>
                  <InputLabel for="category" value="Category" />
                  <select
                    id="category"
                    v-model="form.category"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                  >
                    <option value="">Select category</option>
                    <option value="compliance">Compliance</option>
                    <option value="safety">Safety</option>
                    <option value="technical">Technical</option>
                    <option value="soft-skills">Soft Skills</option>
                  </select>
                </div>

                <div class="flex items-center">
                  <input
                    id="is_active"
                    v-model="form.is_active"
                    type="checkbox"
                    class="rounded border-gray-300"
                  />
                  <label for="is_active" class="ml-2 text-sm text-gray-700">
                    Active
                  </label>
                </div>
              </div>
            </div>

            <!-- Configuration -->
            <div>
              <h2 class="text-xl font-semibold text-gray-900 mb-4">Configuration</h2>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <InputLabel for="passing_score" value="Passing Score (%) *" />
                  <TextInput
                    id="passing_score"
                    v-model.number="form.passing_score"
                    type="number"
                    min="0"
                    max="100"
                    class="mt-1 block w-full"
                    required
                  />
                </div>

                <div>
                  <InputLabel for="max_attempts" value="Max Attempts *" />
                  <TextInput
                    id="max_attempts"
                    v-model.number="form.max_attempts"
                    type="number"
                    min="1"
                    max="10"
                    class="mt-1 block w-full"
                    required
                  />
                </div>

                <div>
                  <InputLabel for="timer_minutes" value="Time Limit (minutes)" />
                  <TextInput
                    id="timer_minutes"
                    v-model.number="form.timer_minutes"
                    type="number"
                    min="0"
                    class="mt-1 block w-full"
                  />
                </div>

                <div>
                  <InputLabel for="validity_days" value="Validity Period (days) *" />
                  <TextInput
                    id="validity_days"
                    v-model.number="form.validity_days"
                    type="number"
                    min="1"
                    class="mt-1 block w-full"
                    required
                  />
                </div>

                <div class="md:col-span-2">
                  <h3 class="text-lg font-medium text-gray-900 mb-4">Anti-Cheat Settings</h3>
                  <div class="grid grid-cols-2 gap-4">
                    <div class="flex items-center">
                      <input
                        id="prevent_copy_paste"
                        v-model="form.prevent_copy_paste"
                        type="checkbox"
                        class="rounded border-gray-300"
                      />
                      <label for="prevent_copy_paste" class="ml-2 text-sm text-gray-700">
                        Prevent Copy/Paste
                      </label>
                    </div>

                    <div class="flex items-center">
                      <input
                        id="track_tab_switches"
                        v-model="form.track_tab_switches"
                        type="checkbox"
                        class="rounded border-gray-300"
                      />
                      <label for="track_tab_switches" class="ml-2 text-sm text-gray-700">
                        Track Tab Switches
                      </label>
                    </div>

                    <div v-if="form.track_tab_switches">
                      <InputLabel for="max_tab_switches" value="Max Tab Switches" />
                      <TextInput
                        id="max_tab_switches"
                        v-model.number="form.max_tab_switches"
                        type="number"
                        min="0"
                        class="mt-1 block w-full"
                      />
                    </div>

                    <div class="flex items-center">
                      <input
                        id="auto_generate_certificate"
                        v-model="form.auto_generate_certificate"
                        type="checkbox"
                        class="rounded border-gray-300"
                      />
                      <label for="auto_generate_certificate" class="ml-2 text-sm text-gray-700">
                        Auto-Generate Certificate
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-between">
            <Link
              :href="route('hr.lms.show', course.id)"
              class="btn-secondary"
            >
              Cancel
            </Link>

            <button
              type="submit"
              class="btn-primary"
              :disabled="form.processing"
            >
              {{ form.processing ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </MainLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import { ArrowLeftIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  course: Object
});

const form = useForm({
  title: props.course.title,
  description: props.course.description,
  category: props.course.category,
  is_active: props.course.is_active,
  passing_score: props.course.passing_score,
  max_attempts: props.course.max_attempts,
  timer_minutes: props.course.timer_minutes,
  validity_days: props.course.validity_days,
  target_audience_type: props.course.target_audience_type,
  target_audience_config: props.course.target_audience_config,
  deadline_days_from_joining: props.course.deadline_days_from_joining,
  prevent_copy_paste: props.course.prevent_copy_paste,
  track_tab_switches: props.course.track_tab_switches,
  max_tab_switches: props.course.max_tab_switches,
  auto_generate_certificate: props.course.auto_generate_certificate,
  action_on_fail: props.course.action_on_fail,
  cooloff_hours: props.course.cooloff_hours,
  shuffle_questions: props.course.shuffle_questions,
  shuffle_options: props.course.shuffle_options
});

const submitForm = () => {
  form.put(route('hr.lms.update', props.course.id));
};
</script>

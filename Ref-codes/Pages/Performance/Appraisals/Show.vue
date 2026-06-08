<script setup>
import { computed } from 'vue';
import { useForm, Head } from '@inertiajs/vue3';
import MainLayout from '@/Layouts/MainLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
  appraisal: Object
});

const isSelfReview = computed(() => props.appraisal.stage === 'Self Review');
const isManagerReview = computed(() => props.appraisal.stage === 'Manager Review');
const isReadOnly = computed(() => !isSelfReview.value && !isManagerReview.value);

// Initialize Form
// We map the goal ratings from the backend into a mutable structure
const form = useForm({
  self_comments: props.appraisal.self_comments || '',
  manager_comments: props.appraisal.manager_comments || '',
  ratings: props.appraisal.goal_ratings?.length ? props.appraisal.goal_ratings.map(gr => ({
    goal_id: gr.goal_id,
    title: gr.goal.title,
    description: gr.goal.description,
    weightage: gr.goal.weightage,
    self_rating: gr.self_rating || 0,
    self_remarks: gr.self_remarks || '',
    manager_rating: gr.manager_rating || 0,
    manager_remarks: gr.manager_remarks || ''
  })) : 
  // If no ratings exist yet (fresh appraisal), map from cycle goals? 
  // For now assuming backend seeded them or empty.
  []
});

const save = () => {
  form.put(route('performance.appraisals.update', props.appraisal.id), {
     preserveScroll: true
  });
};

const submitAppraisal = () => {
    if (!confirm('Are you sure you want to submit? This action cannot be undone.')) return;
    form.post(route('performance.appraisals.submit', props.appraisal.id));
};
</script>

<template>
  <Head title="Appraisal Review" />

  <MainLayout>
    <div class="px-6 py-4">
      <div class="flex justify-between items-center">
        <div>
           <h2 class="font-semibold text-xl text-gray-800 leading-tight">
             Appraisal: {{ appraisal.cycle.name }}
           </h2>
           <p class="text-sm text-gray-500">Employee: {{ appraisal.employee.first_name }} {{ appraisal.employee.last_name }}</p>
        </div>
        <div class="flex gap-2">
           <span class="px-3 py-1 rounded bg-indigo-100 text-indigo-800 font-bold">
               Stage: {{ appraisal.stage }}
           </span>
        </div>
      </div>
    </div>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
         
         <!-- Instructions Alert -->
         <div class="bg-blue-50 border border-blue-200 text-blue-800 p-4 rounded-lg">
             <strong v-if="isSelfReview">Rate your performance against each goal (1-5). Add remarks to support your rating.</strong>
             <strong v-if="isManagerReview">Review the employee's self-ratings and provide your assessment.</strong>
             <strong v-if="isReadOnly">This appraisal is closed for editing.</strong>
         </div>

         <!-- Goals & Ratings -->
         <div class="bg-white p-6 rounded-lg shadow space-y-8">
            <h3 class="text-lg font-bold border-b pb-2">Goals Assessment</h3>
            
            <div v-for="(rating, index) in form.ratings" :key="rating.goal_id" class="grid grid-cols-12 gap-6 pb-6 border-b last:border-0 border-gray-100">
                <!-- Goal Info -->
                <div class="col-span-12 md:col-span-4">
                    <h4 class="font-bold text-gray-900">{{ rating.title }}</h4>
                    <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">Weight: {{ rating.weightage }}%</span>
                    <p class="text-sm text-gray-500 mt-2">{{ rating.description }}</p>
                </div>

                <!-- Self Rating -->
                <div class="col-span-12 md:col-span-4 space-y-2 bg-gray-50 p-4 rounded">
                   <p class="text-xs font-bold text-gray-500 uppercase">Self Assessment</p>
                   
                   <div class="flex items-center gap-2">
                       <label class="text-sm">Rating (1-5):</label>
                       <input type="number" step="0.1" min="1" max="5" 
                              v-model="rating.self_rating" 
                              :disabled="!isSelfReview"
                              class="w-20 rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                   </div>
                   
                   <textarea v-model="rating.self_remarks" 
                             :disabled="!isSelfReview"
                             placeholder="My remarks..."
                             class="w-full text-sm rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="3"></textarea>
                </div>

                <!-- Manager Rating -->
                <div class="col-span-12 md:col-span-4 space-y-2 bg-blue-50 p-4 rounded">
                   <p class="text-xs font-bold text-blue-500 uppercase">Manager Assessment</p>
                   
                   <div class="flex items-center gap-2">
                       <label class="text-sm">Rating (1-5):</label>
                       <input type="number" step="0.1" min="1" max="5"
                              v-model="rating.manager_rating" 
                              :disabled="!isManagerReview"
                              class="w-20 rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                   </div>
                   
                   <textarea v-model="rating.manager_remarks" 
                             :disabled="!isManagerReview"
                             placeholder="Manager remarks..."
                             class="w-full text-sm rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" rows="3"></textarea>
                </div>
            </div>
         </div>

         <!-- Overall Comments -->
         <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
             <div class="bg-white p-6 rounded-lg shadow">
                 <h3 class="text-lg font-bold mb-4">Overall Self Comments</h3>
                 <textarea v-model="form.self_comments" :disabled="!isSelfReview" class="w-full rounded border-gray-300 shadow-sm h-32"></textarea>
             </div>
             
             <div class="bg-white p-6 rounded-lg shadow">
                 <h3 class="text-lg font-bold mb-4">Overall Manager Comments</h3>
                 <textarea v-model="form.manager_comments" :disabled="!isManagerReview" class="w-full rounded border-gray-300 shadow-sm h-32"></textarea>
             </div>
         </div>

         <!-- Actions -->
         <div class="flex justify-end gap-4 pb-12">
             <SecondaryButton @click="save" :disabled="form.processing || isReadOnly">Save Draft</SecondaryButton>
             <PrimaryButton @click="submitAppraisal" :disabled="form.processing || isReadOnly">
                 {{ isSelfReview ? 'Submit Self Review' : 'Complete Manager Review' }}
             </PrimaryButton>
         </div>

      </div>
    </div>
  </MainLayout>
</template>

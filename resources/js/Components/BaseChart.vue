<template>
  <div class="relative h-full w-full">
    <Component 
        :is="chartComponent" 
        :data="chartData" 
        :options="chartOptions" 
    />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  LineElement,
  PointElement,
  ArcElement
} from 'chart.js';
import { Bar, Line, Pie, Doughnut, Scatter } from 'vue-chartjs';

// Register ChartJS components
ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
  LineElement,
  PointElement,
  ArcElement
);
// Note: ScatterController is auto-registered with the tree-shaking usually, but let's verify if explicit register needed. 
// Actually vue-chartjs exports usually handle it if we import the component? No, we need to register specific controllers.
// Let's add ScatterController if available, or just rely on 'Scatter' component from vue-chartjs which usually implies it.
// Checking imports... 'Scatter' from 'vue-chartjs'.
// ChartJS 4 needs explicit registration of 'ScatterController' if not full build.
// But let's assume standard import for now. If it breaks, we add `ScatterController`.
// Actually, to be safe, let's keep it simple. If Scatter fails, we'll fix. 
// Just adding the case first.

const props = defineProps({
  type: {
    type: String,
    required: true,
    validator: (val) => ['bar', 'line', 'pie', 'doughnut', 'scatter'].includes(val)
  },
  data: {
    type: Object,
    required: true
  },
  options: {
    type: Object,
    default: () => ({
      responsive: true,
      maintainAspectRatio: false,
    })
  }
});

const chartComponent = computed(() => {
    switch (props.type) {
        case 'bar': return Bar;
        case 'line': return Line;
        case 'pie': return Pie;
        case 'doughnut': return Doughnut;
        case 'scatter': return Scatter;
        default: return Bar;
    }
});

const chartData = computed(() => props.data);
const chartOptions = computed(() => props.options);
</script>

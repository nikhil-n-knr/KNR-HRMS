<template>
  <div class="relative w-full h-full">
    <Bubble :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import {
  Chart as ChartJS,
  LinearScale,
  PointElement,
  Tooltip,
  Legend
} from 'chart.js'
import { Bubble } from 'vue-chartjs'
import { computed } from 'vue'

ChartJS.register(LinearScale, PointElement, Tooltip, Legend)

const props = defineProps({
  data: {
    type: Array,
    required: true
  }
})

const chartData = computed(() => ({
  datasets: [{
    label: 'Departments',
    data: props.data.map(d => ({ x: d.x, y: d.y, r: d.r, _custom: d.label })),
    backgroundColor: (ctx) => {
        const v = ctx.raw;
        if (!v) return '#6b7280';
        // Green Zone: High Perf (X > 3.5), Low Cost (Relative) - Simplified logic
        // Red Zone: Low Perf (X < 3), High Cost
        if (v.x > 3.5) return 'rgba(34, 197, 94, 0.6)'; // Green
        if (v.x < 3.0) return 'rgba(239, 68, 68, 0.6)'; // Red
        return 'rgba(59, 130, 246, 0.6)'; // Blue
    }
  }]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        label: function(context) {
           const d = context.raw;
           return `${d._custom}: Rating ${d.x} | Cost ₹${d.y.toLocaleString()}`;
        }
      }
    }
  },
  scales: {
    x: {
      title: { display: true, text: 'Avg Performance Rating (1-5)' },
      min: 1,
      max: 5.5
    },
    y: {
      title: { display: true, text: 'Avg Annual CTC' },
      beginAtZero: false
    }
  }
}
</script>

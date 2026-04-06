<template>
  <div class="relative w-full h-full">
    <Scatter :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import {
  Chart as ChartJS,
  LinearScale,
  PointElement,
  LineElement,
  Tooltip,
  Legend
} from 'chart.js'
import { Scatter } from 'vue-chartjs'
import { computed } from 'vue'

ChartJS.register(LinearScale, PointElement, LineElement, Tooltip, Legend)

const props = defineProps({
  points: {
    type: Array, // [{ x: 5, y: 10, name: 'John' }]
    required: true
  },
  title: {
    type: String,
    default: 'Pay vs Performance'
  }
})

const chartData = computed(() => ({
  datasets: [{
    label: 'Employees',
    data: props.points,
    backgroundColor: (ctx) => {
        // Dynamic Color Logic: Red if High Perf + Low Hike, or Low Perf + High Hike
        const v = ctx.raw;
        if (!v) return '#3b82f6';
        if (v.x >= 4.5 && v.y < 5) return '#ef4444'; // Star underpaid (Red)
        if (v.x <= 2.5 && v.y > 10) return '#f59e0b'; // Low perf overpaid (Orange)
        return '#3b82f6'; // Normal (Blue)
    },
    pointRadius: 6,
    pointHoverRadius: 8
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
           const p = context.raw;
           return `${p.name}: Rating ${p.x} -> Hike ${p.y}%`;
        }
      }
    }
  },
  scales: {
    x: {
      title: { display: true, text: 'Performance Rating (1-5)' },
      min: 0,
      max: 5.5
    },
    y: {
      title: { display: true, text: 'Hike Percentage (%)' },
      beginAtZero: true
    }
  }
}
</script>

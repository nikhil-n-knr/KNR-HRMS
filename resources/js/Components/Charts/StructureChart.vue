<template>
  <div class="relative w-full h-full">
    <Bar :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale
} from 'chart.js'
import { Bar } from 'vue-chartjs'
import { computed } from 'vue'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const props = defineProps({
  data: {
    type: Array, // [{ x: 'Senior Dev', min: 10, q1: 12, median: 14, q3: 16, max: 20 }]
    required: true
  }
})

const chartData = computed(() => ({
  labels: props.data.map(d => d.x),
  datasets: [
      {
          label: 'Total Range (Min-Max)',
          data: props.data.map(d => [d.min, d.max]), // Floating Bar
          backgroundColor: '#f3f4f6', // Gray 100
          barPercentage: 0.2, // Thin line
          order: 2
      },
      {
          label: 'Typical Range (Q1-Q3)',
          data: props.data.map(d => [d.q1, d.q3]), // Floating Bar
          backgroundColor: '#818cf8', // Indigo 400
          barPercentage: 0.6, // Thicker box
          order: 1
      },
      // Median (Hack: Small slice)
      {
          label: 'Median',
          type: 'bar',
          data: props.data.map(d => [d.median - 0.05, d.median + 0.05]),
          backgroundColor: '#1f2937', // Black
          barPercentage: 1.0,
          order: 0
      }
  ]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: true },
    tooltip: {
       callbacks: {
           label: function(ctx) {
               // Show full stats on hover of the "Box"
               if (ctx.datasetIndex === 1) {
                   const d = props.data[ctx.dataIndex];
                   return `Median: ${d.median}L | Range: ${d.min}L - ${d.max}L`;
               }
               return null;
           }
       }
    }
  },
  scales: {
    x: { grid: { display: false } },
    y: { 
        beginAtZero: false,
        title: { display: true, text: 'Annual CTC (Lakhs)' }    
    }
  }
}
</script>

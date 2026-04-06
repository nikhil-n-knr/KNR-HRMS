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
    type: Array, // [{ year: 2022, base: 100, inflation_segment: 10, hike_segment: 5 }]
    required: true
  }
})

const chartData = computed(() => ({
  labels: props.data.map(d => d.year),
  datasets: [
      {
          label: 'Base Payroll',
          data: props.data.map(d => d.base),
          backgroundColor: '#93c5fd', // Blue 300
      },
      {
          label: 'Inflation / Market',
          data: props.data.map(d => d.inflation_segment),
          backgroundColor: '#fdba74', // Orange 300
      },
      {
          label: 'Growth / Hikes',
          data: props.data.map(d => d.hike_segment),
          backgroundColor: '#86efac', // Green 300
      }
  ]
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'top' },
    tooltip: {
      mode: 'index',
      intersect: false,
    }
  },
  scales: {
    x: {
      stacked: true,
      grid: { display: false }
    },
    y: {
      stacked: true,
      beginAtZero: true,
       ticks: {
         callback: function(value) {
            return '₹' + (value/100000).toFixed(0) + 'L';
         }
      }
    }
  }
}
</script>

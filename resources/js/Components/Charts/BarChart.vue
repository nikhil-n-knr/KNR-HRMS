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
  labels: {
    type: Array, // ['1', '2', '3']
    required: true
  },
  datasets: {
    type: Array,
    required: true
  },
  title: String,
  colors: Array // Optional override
})

const chartData = computed(() => ({
  labels: props.labels,
  datasets: props.datasets
}))

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top',
    },
    title: {
      display: !!props.title,
      text: props.title
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
         display: false
      }
    },
    x: {
      grid: {
        display: false
      }
    }
  }
}
</script>

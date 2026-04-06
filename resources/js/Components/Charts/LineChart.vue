<template>
  <div class="relative w-full h-full">
    <Line :data="chartData" :options="chartOptions" />
  </div>
</template>

<script setup>
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'
import { Line } from 'vue-chartjs'
import { computed } from 'vue'

ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const props = defineProps({
  labels: {
    type: Array,
    required: true
  },
  datasets: {
    type: Array,
    required: true
  },
  title: {
    type: String,
    default: ''
  }
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
    },
    tooltip: {
      mode: 'index',
      intersect: false,
      backgroundColor: 'rgba(255, 255, 255, 0.9)',
      titleColor: '#1f2937',
      bodyColor: '#4b5563',
      borderColor: '#e5e7eb',
      borderWidth: 1,
      padding: 10,
      boxPadding: 4
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      grid: {
        color: '#f3f4f6'
      },
      ticks: {
         callback: function(value) {
            return '₹' + value.toLocaleString();
         }
      }
    },
    x: {
      grid: {
        display: false
      }
    }
  },
  interaction: {
    mode: 'nearest',
    axis: 'x',
    intersect: false
  }
}
</script>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Bar } from 'vue-chartjs'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const props = defineProps({
    title: {
        type: String,
        default: 'Productos Más Vendidos'
    },
    products: {
        type: Array,
        required: true
    }
})

const isDark = ref(false)

onMounted(() => {
    isDark.value = document.documentElement.classList.contains('dark')

    const observer = new MutationObserver(() => {
        isDark.value = document.documentElement.classList.contains('dark')
    })

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    })
})

const chartData = {
  labels: props.products.map(p => p.name),
  datasets: [{
    label: 'Unidades Vendidas',
    data: props.products.map(p => p.quantity),
    backgroundColor: [
      'rgba(59, 130, 246, 0.8)',
      'rgba(16, 185, 129, 0.8)',
      'rgba(245, 158, 11, 0.8)',
      'rgba(239, 68, 68, 0.8)',
      'rgba(139, 92, 246, 0.8)',
    ],
    borderRadius: 6,
  }]
}

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  indexAxis: 'y',
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: isDark.value ? 'rgba(255, 255, 255, 0.9)' : 'rgba(0, 0, 0, 0.8)',
      titleColor: isDark.value ? '#1f2937' : '#fff',
      bodyColor: isDark.value ? '#1f2937' : '#fff',
      padding: 12,
      callbacks: {
        label: function(context) {
          return ' ' + context.parsed.x + ' unidades';
        }
      }
    }
  },
  scales: {
    x: {
      beginAtZero: true,
      ticks: {
        color: isDark.value ? '#9ca3af' : '#4b5563'
      },
      grid: {
        color: isDark.value ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)'
      }
    },
    y: {
      ticks: {
        color: isDark.value ? '#9ca3af' : '#4b5563'
      },
      grid: {
        display: false
      }
    }
  }
}))
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ title }}</h3>
        <div class="h-64">
            <Bar :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>

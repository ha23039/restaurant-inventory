<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Line } from 'vue-chartjs'
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

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler)

const props = defineProps({
    title: {
        type: String,
        default: 'Ventas de la Semana'
    },
    labels: {
        type: Array,
        default: () => ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom']
    },
    data: {
        type: Array,
        required: true
    }
})

const isDark = ref(false)

// Detectar dark mode
onMounted(() => {
    isDark.value = document.documentElement.classList.contains('dark')

    // Observar cambios
    const observer = new MutationObserver(() => {
        isDark.value = document.documentElement.classList.contains('dark')
    })

    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    })
})

const chartData = computed(() => ({
  labels: props.labels,
  datasets: [{
    label: 'Ventas ($)',
    data: props.data,
    borderColor: 'rgb(59, 130, 246)',
    backgroundColor: 'rgba(59, 130, 246, 0.1)',
    fill: true,
    tension: 0.4,
    pointRadius: 5,
    pointHoverRadius: 7,
    pointBackgroundColor: 'rgb(59, 130, 246)',
    pointBorderColor: isDark.value ? '#1f2937' : '#fff',
    pointBorderWidth: 2,
  }]
}))

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      backgroundColor: isDark.value ? 'rgba(255, 255, 255, 0.9)' : 'rgba(0, 0, 0, 0.8)',
      titleColor: isDark.value ? '#1f2937' : '#fff',
      bodyColor: isDark.value ? '#1f2937' : '#fff',
      padding: 12,
      titleFont: {
        size: 14,
        weight: 'bold'
      },
      bodyFont: {
        size: 13
      },
      callbacks: {
        label: function(context) {
          return ' $' + context.parsed.y.toLocaleString('es-MX', { minimumFractionDigits: 2 });
        }
      }
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        color: isDark.value ? '#9ca3af' : '#4b5563',
        callback: function(value) {
          return '$' + value.toLocaleString('es-MX');
        }
      },
      grid: {
        color: isDark.value ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)'
      }
    },
    x: {
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
            <Line :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>

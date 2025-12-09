<script setup>
import { ref, computed, onMounted } from 'vue'
import { Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend
} from 'chart.js'

ChartJS.register(ArcElement, Tooltip, Legend)

const props = defineProps({
    title: {
        type: String,
        default: 'Métodos de Pago'
    },
    data: {
        type: Object,
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

const chartData = computed(() => ({
  labels: ['Efectivo', 'Tarjeta', 'Transferencia', 'Mixto'],
  datasets: [{
    data: [
      props.data.efectivo || 0,
      props.data.tarjeta || 0,
      props.data.transferencia || 0,
      props.data.mixto || 0,
    ],
    backgroundColor: [
      'rgba(16, 185, 129, 0.8)',
      'rgba(59, 130, 246, 0.8)',
      'rgba(245, 158, 11, 0.8)',
      'rgba(139, 92, 246, 0.8)',
    ],
    borderWidth: 2,
    borderColor: isDark.value ? '#1f2937' : '#fff',
  }]
}))

const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        color: isDark.value ? '#9ca3af' : '#4b5563',
        padding: 15,
        font: {
          size: 12
        },
        usePointStyle: true,
        pointStyle: 'circle'
      }
    },
    tooltip: {
      backgroundColor: isDark.value ? 'rgba(255, 255, 255, 0.9)' : 'rgba(0, 0, 0, 0.8)',
      titleColor: isDark.value ? '#1f2937' : '#fff',
      bodyColor: isDark.value ? '#1f2937' : '#fff',
      padding: 12,
      callbacks: {
        label: function(context) {
          const label = context.label || '';
          const value = context.parsed || 0;
          const total = context.dataset.data.reduce((a, b) => a + b, 0);
          const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
          return ` ${label}: $${value.toLocaleString('es-MX', { minimumFractionDigits: 2 })} (${percentage}%)`;
        }
      }
    }
  }
}))
</script>

<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">{{ title }}</h3>
        <div class="h-64 flex items-center justify-center">
            <Doughnut :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>

<template>
    <div class="chart-container">
        <Line :data="chartData" :options="chartOptions" />
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Line } from 'vue-chartjs';
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
} from 'chart.js';

// Register Chart.js components
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

// Detect dark mode
const isDarkMode = ref(document.documentElement.classList.contains('dark'));

const updateDarkMode = () => {
    isDarkMode.value = document.documentElement.classList.contains('dark');
};

onMounted(() => {
    // Watch for dark mode changes
    const observer = new MutationObserver(updateDarkMode);
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class']
    });

    onUnmounted(() => observer.disconnect());
});

const props = defineProps({
    data: {
        type: Object,
        required: true,
        validator: (value) => {
            return value.labels && value.datasets;
        }
    },
    title: {
        type: String,
        default: 'Tendencias Financieras'
    },
    height: {
        type: Number,
        default: 300
    }
});

const chartData = computed(() => ({
    labels: props.data.labels || [],
    datasets: (props.data.datasets || []).map((dataset, index) => ({
        label: dataset.label,
        data: dataset.data,
        borderColor: index === 0 ? '#10b981' : '#ef4444', // green for income, red for expenses
        backgroundColor: index === 0 ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)',
        borderWidth: 2,
        fill: true,
        tension: 0.4, // Smooth curves
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBackgroundColor: index === 0 ? '#10b981' : '#ef4444',
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
    }))
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'top',
            labels: {
                usePointStyle: true,
                padding: 15,
                font: {
                    size: 12,
                    family: "'Inter', sans-serif"
                },
                color: isDarkMode.value ? '#d1d5db' : '#374151', // gray-300 : gray-700
            }
        },
        title: {
            display: true,
            text: props.title,
            font: {
                size: 16,
                weight: 'bold',
                family: "'Inter', sans-serif"
            },
            color: isDarkMode.value ? '#f3f4f6' : '#111827', // gray-100 : gray-900
            padding: {
                top: 10,
                bottom: 20
            }
        },
        tooltip: {
            mode: 'index',
            intersect: false,
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            borderColor: 'rgba(255, 255, 255, 0.1)',
            borderWidth: 1,
            titleFont: {
                size: 13,
                weight: 'bold'
            },
            bodyFont: {
                size: 12
            },
            callbacks: {
                label: function(context) {
                    let label = context.dataset.label || '';
                    if (label) {
                        label += ': ';
                    }
                    if (context.parsed.y !== null) {
                        label += new Intl.NumberFormat('es-MX', {
                            style: 'currency',
                            currency: 'MXN'
                        }).format(context.parsed.y);
                    }
                    return label;
                }
            }
        }
    },
    scales: {
        x: {
            grid: {
                display: false
            },
            ticks: {
                font: {
                    size: 11
                },
                color: isDarkMode.value ? '#9ca3af' : '#6b7280', // gray-400 : gray-500
            }
        },
        y: {
            beginAtZero: true,
            grid: {
                color: isDarkMode.value ? 'rgba(75, 85, 99, 0.2)' : 'rgba(0, 0, 0, 0.05)', // gray-600 with opacity
                drawBorder: false
            },
            ticks: {
                font: {
                    size: 11
                },
                color: isDarkMode.value ? '#9ca3af' : '#6b7280', // gray-400 : gray-500
                callback: function(value) {
                    return '$' + new Intl.NumberFormat('es-MX', {
                        notation: 'compact',
                        compactDisplay: 'short'
                    }).format(value);
                }
            }
        }
    },
    interaction: {
        mode: 'nearest',
        axis: 'x',
        intersect: false
    }
}));
</script>

<style scoped>
.chart-container {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 300px;
}
</style>

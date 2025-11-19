<template>
    <div class="chart-container">
        <Doughnut :data="chartData" :options="chartOptions" />
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Doughnut } from 'vue-chartjs';
import {
    Chart as ChartJS,
    ArcElement,
    Tooltip,
    Legend
} from 'chart.js';

// Register Chart.js components
ChartJS.register(ArcElement, Tooltip, Legend);

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
        default: 'Gastos por Categoría'
    },
    height: {
        type: Number,
        default: 300
    }
});

const chartData = computed(() => ({
    labels: props.data.labels || [],
    datasets: (props.data.datasets || []).map(dataset => ({
        ...dataset,
        backgroundColor: dataset.backgroundColor || [
            '#ef4444', // red-500
            '#f59e0b', // amber-500
            '#3b82f6', // blue-500
            '#8b5cf6', // violet-500
            '#10b981', // emerald-500
            '#ec4899', // pink-500
        ],
        borderColor: '#fff',
        borderWidth: 2,
        hoverOffset: 4
    }))
}));

const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: true,
            position: 'bottom',
            labels: {
                usePointStyle: true,
                padding: 15,
                font: {
                    size: 12,
                    family: "'Inter', sans-serif"
                },
                generateLabels: function(chart) {
                    const data = chart.data;
                    if (data.labels.length && data.datasets.length) {
                        return data.labels.map((label, i) => {
                            const value = data.datasets[0].data[i];
                            const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);

                            return {
                                text: `${label} (${percentage}%)`,
                                fillStyle: data.datasets[0].backgroundColor[i],
                                hidden: false,
                                index: i
                            };
                        });
                    }
                    return [];
                }
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
            padding: {
                top: 10,
                bottom: 20
            }
        },
        tooltip: {
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
                    const label = context.label || '';
                    const value = context.parsed || 0;
                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                    const percentage = ((value / total) * 100).toFixed(1);

                    const formattedValue = new Intl.NumberFormat('es-MX', {
                        style: 'currency',
                        currency: 'MXN'
                    }).format(value);

                    return `${label}: ${formattedValue} (${percentage}%)`;
                }
            }
        }
    },
    cutout: '60%', // Makes it a doughnut chart
}));
</script>

<style scoped>
.chart-container {
    position: relative;
    width: 100%;
    height: 100%;
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

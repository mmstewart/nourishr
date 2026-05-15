<script setup lang="ts">
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import {
    type ChartOptions,
    type ChartData,
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend
} from 'chart.js';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"

// Register Chart.js components
ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend);

const props = defineProps<{
    title: string;
    abbreviation: string;
    description: string;
    data: any[];
    config: any;
    dataKey: string;
    class?: string;
}>();

const chartColor = props.config[props.dataKey]?.color || '#2563eb';

// Chart.js Data Object
const chartData = computed<ChartData<'line'>>(() => ({
    labels: props.data.map(d => d.date),
    datasets: [{
        label: ' ' + props.title + ' ' + props.abbreviation,
        data: props.data.map(d => Number(d[props.dataKey])),
        clip: false,
        borderColor: chartColor,
        backgroundColor: chartColor,
        pointRadius: 4,
        pointBackgroundColor: chartColor,
        pointBorderColor: '#fff',
        pointBorderWidth: 2,
        pointHoverRadius: 6,
        pointHoverBorderWidth: 3,
    }]
}));

// Chart.js Options
const chartOptions = computed<ChartOptions<'line'>>(() => {
    const values = props.data.map(d => Number(d[props.dataKey]));
    let yMin = 0;
    let yMax = 100;

    if (values.length > 0) {
        const min = Math.min(...values);
        const max = Math.max(...values);

        if (props.dataKey === 'weight') {
            yMin = Math.floor((min - 20) / 20) * 20;
            yMax = Math.ceil((max + 20) / 20) * 20;
        } else {
            // Calories logic
            yMin = 0;
            yMax = Math.ceil((max + 100) / 20) * 20;
        }
    }

    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        plugins: {
            legend: { display: false },
        },
        scales: {
            x: {
                display: true,
                grid: {
                    display: true,
                    drawOnChartArea: false,
                    drawTicks: false,
                    color: getComputedStyle(document.documentElement).getPropertyValue('--border') || '#e2e8f0',
                },
                min: 0,
                max: props.data.length <= 1 ? 6 : props.data.length - 1,
                ticks: {
                    display: true,
                    align: 'center',
                    padding: 12,
                }
            },
            y: {
                display: true,
                min: yMin,
                max: yMax,
                beginAtZero: props.dataKey !== 'weight',
                grid: {
                    display: true,
                    drawTicks: false,
                    color: getComputedStyle(document.documentElement).getPropertyValue('--border') || '#e2e8f0',
                },
                ticks: {
                    padding: 12,
                    callback: function (value) {
                        return value.toLocaleString();
                    }
                }
            }
        }
    };
});
</script>

<template>
    <Card :class="['@container/card', props.class]">
        <CardHeader class="flex items-center gap-2 space-y-0 border-b sm:flex-row">
            <div class="grid flex-1 gap-1">
                <CardTitle>{{ title }}</CardTitle>
                <CardDescription>{{ description }}</CardDescription>
            </div>
            <slot />
        </CardHeader>
        <CardContent class="p-6 h-75 w-full">
            <Line :data="chartData" :options="chartOptions" />
        </CardContent>
    </Card>
</template>
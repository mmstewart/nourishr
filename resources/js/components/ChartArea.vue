<script setup lang="ts">
import { computed } from 'vue';
import { VisAxis, VisLine, VisXYContainer } from "@unovis/vue"
import { CurveType } from "@unovis/ts"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { ChartContainer, ChartCrosshair, ChartTooltip, ChartTooltipContent, componentToString } from "@/components/ui/chart"

const props = defineProps<{
    title: string;
    description: string;
    data: any[];
    config: any;
    dataKey: string;
    class?: string;
}>();

const getX = (d: any, i: number) => i;
const getY = (d: any) => Number(d[props.dataKey]);

const chartColor = props.config[props.dataKey]?.color;

const xTickValues = computed(() => {
    const total = props.data.length;
    if (total <= 1) return [0];

    const targetTicks = 7; // Exactly what you asked for

    // Calculate the gap between indices to get exactly 7 points
    // For 17 points, this would be 16 / 6 = ~2.66
    const step = (total - 1) / (targetTicks - 1);

    return Array.from({ length: targetTicks }, (_, i) => Math.round(i * step));
});

const yTickValues = computed(() => {
    const [start, end] = yDomain.value;
    const range = end - start;

    const rawStep = range / 6;

    const magnitude = Math.pow(10, Math.floor(Math.log10(rawStep)));
    const step = Math.ceil(rawStep / magnitude) * magnitude;

    const ticks = [];
    for (let i = start; i <= end; i += step) {
        ticks.push(i);
    }

    return ticks;
});

const xDomain = computed(() => {
    if (props.data.length === 0) return [0, 0];

    return [0, props.data.length - 1];
});

const yDomain = computed(() => {
    const values = props.data.map(d => Number(d[props.dataKey]));

    if (values.length === 0) return [0, 100];

    const min = Math.min(...values);
    const max = Math.max(...values);

    if (props.dataKey === 'weight') {
        // Round down min to nearest 20, subtract 20 for padding
        const floor = Math.floor((min - 20) / 20) * 20;

        // Round up max to nearest 20, add 20 for padding
        const ceil = Math.ceil((max + 20) / 20) * 20;

        return [floor, ceil];
    }

    // For Calories, start at 0 and go to a multiple of 20
    const calMax = Math.ceil((max + 100) / 20) * 20;

    return [0, calMax];
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
        <CardContent class="relative flex-1 min-h-0 p-0">
            <ChartContainer :config="config" class="absolute inset-0 h-full w-full aspect-auto">
                <VisXYContainer :data="data" :x-domain="xDomain" :y-domain="yDomain" :margin="{ left: 20, right: 20 }">
                    <VisLine :x="getX" :y="getY" :color="chartColor" :line-width="3" :curve-type="CurveType.Linear" />
                    <VisAxis type="x" :duration="0" :x="getX" :tick-line="false" :domain-line="false" :grid-line="false"
                        :tick-values="xTickValues" :tick-format="(d: number) => data[d]?.date" />
                    <VisAxis type="y" :duration="0" :tick-values="yTickValues" :tick-line="false" :domain-line="false"
                        :tick-format="(v: number) => v.toLocaleString()" />
                    <ChartTooltip />
                    <ChartCrosshair color="#fff" :template="componentToString(config, ChartTooltipContent, {
                        labelFormatter: (d: number | Date) => {
                            const index = Math.round(Number(d));
                            const record = props.data[index];

                            if (record.date) {
                                const year = new Date().getFullYear();

                                return record.date.includes(year.toString()) ? record.date : `${record.date}, ${year}`;
                            }

                            return '';
                        }
                    })" />
                </VisXYContainer>
            </ChartContainer>
        </CardContent>
    </Card>
</template>

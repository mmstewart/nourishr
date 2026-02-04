<script setup lang="ts">
import { computed, ref } from 'vue';
import type { ChartConfig } from "@/components/ui/chart"
import { VisArea, VisAxis, VisLine, VisXYContainer } from "@unovis/vue"
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card"
import {
    ChartContainer,
    ChartCrosshair,
    ChartTooltip,
    ChartTooltipContent,
    componentToString,
} from "@/components/ui/chart"
import { TrendingDown, TrendingUp, Target } from 'lucide-vue-next'

// Assume 'user' is passed as a prop from your Inertia controller
const props = defineProps<{
    user: any; // Ideally use your User interface here
}>();

const description = "Weekly weight progress vs goal"

/**
 * 1. GENERATE WEEKLY DATA
 * This maps your user data into a 7-day format for the chart.
 */
const chartData = computed(() => {
    const goal = props.user.weight_goal;
    if (!goal) return [];

    const days = [];
    const now = new Date();

    for (let i = 6; i >= 0; i--) {
        const date = new Date();
        date.setDate(now.getDate() - i);

        days.push({
            date: date,
            // If i is 0 (today), show current weight. 
            // In a real app, you'd pull this from a WeightHistory table.
            weight: i === 0 ? Number(goal.current_weight) : Number(goal.start_weight),
            target: Number(goal.goal_weight)
        });
    }
    return days;
});

type Data = typeof chartData.value[number]

const chartConfig = {
    weight: {
        label: "Actual Weight",
        color: "hsl(var(--chart-1))",
    },
    target: {
        label: "Goal Weight",
        color: "hsl(var(--chart-2))",
    },
} satisfies ChartConfig

const svgDefs = `
  <linearGradient id="fillWeight" x1="0" y1="0" x2="0" y2="1">
    <stop offset="5%" stop-color="var(--color-weight)" stop-opacity="0.8"/>
    <stop offset="95%" stop-color="var(--color-weight)" stop-opacity="0.1"/>
  </linearGradient>
`

/**
 * 3. DYNAMIC Y-AXIS RANGE
 * Zooms the chart in on the user's specific weight range
 */
const yDomain = computed(() => {
    const goal = props.user.weight_goal;
    if (!goal) return [0, 250];
    const min = Math.min(goal.goal_weight, goal.current_weight, goal.start_weight);
    const max = Math.max(goal.goal_weight, goal.current_weight, goal.start_weight);
    return [min - 10, max + 10];
});

/**
 * 4. GOAL INTENT LOGIC
 */
const goalType = computed(() => {
    const id = props.user.weight_goal?.weekly_goal_id;
    return {
        isLosing: [1, 2, 3, 4].includes(id),
        isGaining: [6, 7].includes(id),
        isMaintaining: id === 5
    };
});
</script>

<template>
    <Card class="overflow-hidden">
        <CardHeader class="flex flex-col items-start gap-2 border-b py-5 sm:flex-row sm:items-center">
            <div class="grid flex-1 gap-1">
                <CardTitle>Weekly Progress</CardTitle>
                <CardDescription>
                    Your weight trend over the last 7 days
                </CardDescription>
            </div>

            <div class="flex items-center gap-2 font-medium text-sm">
                <TrendingDown v-if="goalType.isLosing" class="size-4 text-blue-500" />
                <TrendingUp v-else-if="goalType.isGaining" class="size-4 text-green-500" />
                <Target v-else class="size-4 text-purple-500" />
                <span>Goal: {{ user.weight_goal.goal_weight }} lbs</span>
            </div>
        </CardHeader>

        <CardContent class="px-2 pt-4 sm:px-6 sm:pt-6">
            <ChartContainer :config="chartConfig" class="aspect-auto h-[250px] w-full">
                <VisXYContainer :data="chartData" :svg-defs="svgDefs" :margin="{ left: 0, right: 0 }"
                    :y-domain="yDomain">
                    <VisArea :x="(d: Data) => d.date" :y="(d: Data) => d.weight" color="url(#fillWeight)"
                        :opacity="0.4" />

                    <VisLine :x="(d: Data) => d.date" :y="(d: Data) => d.weight" :color="chartConfig.weight.color"
                        :line-width="3" />

                    <VisLine :x="(d: Data) => d.date" :y="(d: Data) => d.target" :color="chartConfig.target.color"
                        :line-width="2" :line-dasharray="[5, 5]" />

                    <VisAxis type="x" :x="(d: Data) => d.date" :tick-line="false" :domain-line="false"
                        :grid-line="false" :num-ticks="7" :tick-format="(d: number) => {
                            return new Date(d).toLocaleDateString('en-US', { weekday: 'short' })
                        }" />

                    <VisAxis type="y" :num-ticks="5" :tick-line="false" :domain-line="false"
                        :tick-format="(d: number) => `${d}lb`" />

                    <ChartTooltip />

                    <ChartCrosshair :template="componentToString(chartConfig, ChartTooltipContent, {
                        labelFormatter: (d) => new Date(d).toLocaleDateString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric'
                        })
                    })" />
                </VisXYContainer>
            </ChartContainer>
        </CardContent>
    </Card>
</template>
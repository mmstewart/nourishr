<script setup lang="ts">
import { ref, watch, computed, type HTMLAttributes } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { type NutritionEntry } from '@/types';

import {
    Card,
    CardDescription,
    CardHeader,
    CardTitle,
} from "@/components/ui/card"
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"
import { Button } from '@/components/ui/button';
import ChartArea from '../../components/ChartArea.vue';
import { Plus, ChevronRight } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Weight Entries',
        href: '/weight-entries',
    },
];

const props = defineProps<{
    class?: HTMLAttributes["class"],
    nutritionEntries: NutritionEntry[];
    chartData: any[];
    filters: { range: string };
    today: string;
}>()

const timeRange = ref(props.filters.range || '7');

const timeRangeOptions = [
    { value: 'all', label: 'All' },
    { value: '365', label: 'Last 1 year' },
    { value: '90', label: 'Last 3 months' },
    { value: '30', label: 'Last 30 days' },
    { value: '7', label: 'Last 7 days' },
];

const chartDescription = computed(() =>
    timeRangeOptions.find(tro => tro.value === timeRange.value)?.label || 'Last 7 days'
);

const calorieConfig = {
    calories: { label: "Calories", color: "#fff" }
}

const todayCalories = computed(() => {
    return props.nutritionEntries
        .filter(e => e.date_raw === props.today)
        .reduce((sum, e) => sum + e.calories, 0);
});

const avgCalories = computed(() => {
    if (!props.chartData.length) return null;

    const total = props.chartData.reduce((sum, day) => sum + day.calories, 0);

    return Math.round(total / props.chartData.length);
});

watch(timeRange, (newRange) => {
    router.get('/nutrition-entries', { range: newRange }, {
        preserveState: false,
        preserveScroll: true,
        only: ['chartData', 'filters', 'nutritionEntries']
    })
});
</script>

<template>

    <Head title="Weight Entries" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4">
            <div
                class="grid auto-rows-min gap-4 *:data-[slot=card]:bg-linear-to-t *:data-[slot=card]:from-primary/5 *:data-[slot=card]:to-card *:data-[slot=card]:shadow-xs md:grid-cols-2 dark:*:data-[slot=card]:bg-card">
                <Card class="@container/card">
                    <CardHeader>
                        <CardDescription>Today's Calories</CardDescription>
                        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
                            {{ todayCalories }} calories
                        </CardTitle>
                    </CardHeader>
                </Card>
                <Card class="@container/card">
                    <CardHeader>
                        <CardDescription>Average Daily Calories</CardDescription>
                        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
                            {{ avgCalories }} calories
                        </CardTitle>
                    </CardHeader>
                </Card>
            </div>
            <ChartArea title="Calories" :description="chartDescription" :data="chartData" :config="calorieConfig"
                dataKey="calories" class="h-75 md:h-100">
                <Select v-model="timeRange">
                    <SelectTrigger class="hidden w-40 rounded-lg sm:ml-auto sm:flex">
                        <SelectValue :placeholder="timeRangeOptions.find(o => o.value === timeRange)?.label" />
                    </SelectTrigger>
                    <SelectContent class="rounded-xl">
                        <SelectItem v-for="option in timeRangeOptions" :key="option.value" :value="option.value"
                            class="rounded-lg">
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </ChartArea>
            <div class="min-h-0">
                <Card class="@container/card py-0! gap-0!">
                    <CardHeader class="flex justify-between items-center gap-2 space-y-0 border-b py-5 sm:flex-row">
                        <CardTitle>Nutrition Entries</CardTitle>
                        <Button variant="outline" size="sm" as-child>
                            <a href="/nutrition-entries/create">
                                <Plus class="size-4.5" />
                            </a>
                        </Button>
                    </CardHeader>
                    <ul role="list" class="divide-y divide-white/5">
                        <li v-for="entry in nutritionEntries" :key="entry.id">
                            <a :href="`/nutrition-entries/${entry.id}/edit`"
                                class="flex items-center justify-between gap-x-6 px-6 py-5 hover:bg-white/5 transition-colors">
                                <div class="min-w-0">
                                    <p class="text-sm/6 font-semibold text-white capitalize">
                                        {{ entry.food_item }}
                                    </p>
                                    <p class="mt-1 truncate text-xs/5 text-gray-400">
                                        {{ entry.date }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-x-6 shrink-0">
                                    <div class="hidden sm:block text-right">
                                        <p class="text-sm/6 text-white">{{ entry.calories }} calories</p>
                                        <p class="mt-1 text-xs/5 text-gray-400">
                                            P {{ entry.protein }}g
                                            &nbsp;·&nbsp;
                                            C {{ entry.carbohydrates }}g
                                            &nbsp;·&nbsp;
                                            F {{ entry.fats }}g
                                        </p>
                                    </div>
                                    <ChevronRight class="size-5 text-gray-500 shrink-0" />
                                </div>
                            </a>
                        </li>
                    </ul>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
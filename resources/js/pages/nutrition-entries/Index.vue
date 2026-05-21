<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type NutritionGroup } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch, type HTMLAttributes } from 'vue';

import { Button } from '@/components/ui/button';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { ChevronRight, ChevronsUpDown, CirclePlus } from 'lucide-vue-next';
import ChartArea from '../../components/ChartArea.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Nutrition Entries',
        href: '/nutrition-entries',
    },
];

const props = defineProps<{
    class?: HTMLAttributes['class'];
    nutritionEntries: NutritionGroup[];
    chartData: any[];
    filters: { range: string };
    today: string;
}>();

const timeRange = ref(props.filters.range || '7');

const timeRangeOptions = [
    { value: 'all', label: 'All' },
    { value: '365', label: 'Last 1 year' },
    { value: '90', label: 'Last 3 months' },
    { value: '30', label: 'Last 30 days' },
    { value: '7', label: 'Last 7 days' },
];

const chartDescription = computed(
    () => timeRangeOptions.find((tro) => tro.value === timeRange.value)?.label || 'Last 7 days',
);

const calorieConfig = {
    calories: { label: 'Calories', color: '#00c951' },
};

const todayCalories = computed(() => {
    const todayGroup = props.nutritionEntries.find((group) => group.date_raw === props.today);

    return todayGroup ? todayGroup.total_calories : 0;
});

const avgCalories = computed(() => {
    if (!props.chartData.length) return null;

    const total = props.chartData.reduce((sum, day) => sum + day.calories, 0);

    return Math.round(total / props.chartData.length);
});

watch(timeRange, (newRange) => {
    router.get(
        '/nutrition-entries',
        { range: newRange },
        {
            preserveState: false,
            preserveScroll: true,
            only: ['chartData', 'filters', 'nutritionEntries'],
        },
    );
});
</script>

<template>
    <Head title="Nutrition Entries" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <Button class="bg-green-500 hover:bg-green-400 dark:text-white" as-child>
                <a href="/nutrition-entries/create">
                    <CirclePlus class="size-4" />
                    Log
                </a>
            </Button>
        </template>
        <div class="flex flex-col gap-4 p-4">
            <div
                class="grid auto-rows-min gap-4 *:data-[slot=card]:bg-linear-to-tl *:data-[slot=card]:from-primary/5 *:data-[slot=card]:to-card *:data-[slot=card]:shadow-xs md:grid-cols-2 dark:*:data-[slot=card]:bg-card">
                <Card class="@container/card">
                    <CardHeader>
                        <CardDescription>Today's Calories</CardDescription>
                        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
                            {{ todayCalories }} kcal
                        </CardTitle>
                    </CardHeader>
                </Card>
                <Card class="@container/card">
                    <CardHeader>
                        <CardDescription>Average Daily Calories</CardDescription>
                        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
                            {{ avgCalories }} kcal
                        </CardTitle>
                    </CardHeader>
                </Card>
            </div>
            <ChartArea
                title="Calories"
                :description="chartDescription"
                :data="chartData"
                :config="calorieConfig"
                dataKey="calories"
                abbreviation="(kcal)"
                class="h-75 md:h-100">
                <Select v-model="timeRange">
                    <SelectTrigger class="hidden w-40 rounded-md sm:ml-auto sm:flex">
                        <SelectValue :placeholder="timeRangeOptions.find((o) => o.value === timeRange)?.label" />
                    </SelectTrigger>
                    <SelectContent class="w-40.25 rounded-md">
                        <SelectItem
                            v-for="option in timeRangeOptions"
                            :key="option.value"
                            :value="option.value"
                            class="rounded-md">
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </ChartArea>
            <div class="min-h-0">
                <Card class="@container/card gap-0! py-0!">
                    <CardHeader class="flex items-center justify-between gap-2 space-y-0 border-b py-5 sm:flex-row">
                        <CardTitle>Nutrition Entries</CardTitle>
                    </CardHeader>
                    <ul role="list" class="divide-y dark:divide-white/5">
                        <template v-if="!nutritionEntries.length">
                            <li class="py-10 text-center">No nutrition entries logged yet.</li>
                        </template>
                        <template v-else>
                            <li v-for="group in nutritionEntries" :key="group.date_raw">
                                <ul class="divide-y dark:divide-white/5">
                                    <li>
                                        <Collapsible>
                                            <div
                                                class="flex items-center justify-between gap-x-6 px-6 py-4 transition-colors">
                                                <div class="min-w-0">
                                                    <p class="text-sm/6 font-semibold capitalize">{{ group.date }}</p>
                                                    <p class="mt-1 text-xs/5 text-muted-foreground capitalize">
                                                        {{ group.entries.length }}
                                                        {{ group.entries.length === 1 ? 'entry' : 'entries' }}
                                                    </p>
                                                </div>
                                                <div class="flex shrink-0 items-center gap-x-6">
                                                    <div class="flex items-center gap-2">
                                                        <div
                                                            class="hidden items-center justify-end gap-2 text-right text-xs text-muted-foreground sm:flex">
                                                            <span
                                                                class="inline-flex items-center rounded-full bg-blue-400/10 px-2 py-1 text-xs font-medium text-blue-400 inset-ring inset-ring-blue-400/30">
                                                                {{ group.total_calories }} kcal
                                                            </span>
                                                            <span
                                                                class="inline-flex items-center rounded-full bg-indigo-400/10 px-2 py-1 text-xs font-medium text-indigo-400 inset-ring inset-ring-indigo-400/30">
                                                                {{
                                                                    group.entries
                                                                        .reduce((s, e) => s + Number(e.protein), 0)
                                                                        .toFixed(2)
                                                                }}g
                                                                <span class="ml-1 sm:hidden">P</span>
                                                                <span class="ml-1 hidden sm:inline">Protein</span>
                                                            </span>
                                                            <span
                                                                class="inline-flex items-center rounded-full bg-red-400/10 px-2 py-1 text-xs font-medium text-red-400 inset-ring inset-ring-red-400/20">
                                                                {{
                                                                    group.entries
                                                                        .reduce(
                                                                            (s, e) => s + Number(e.carbohydrates),
                                                                            0,
                                                                        )
                                                                        .toFixed(2)
                                                                }}g
                                                                <span class="ml-1 sm:hidden">C</span>
                                                                <span class="ml-1 hidden sm:inline">Carbohydrates</span>
                                                            </span>
                                                            <span
                                                                class="inline-flex items-center rounded-full bg-yellow-400/10 px-2 py-1 text-xs font-medium text-yellow-500 inset-ring inset-ring-yellow-400/20">
                                                                {{
                                                                    group.entries
                                                                        .reduce((s, e) => s + Number(e.fats), 0)
                                                                        .toFixed(2)
                                                                }}g
                                                                <span class="ml-1 sm:hidden">F</span>
                                                                <span class="ml-1 hidden sm:inline">Fats</span>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <CollapsibleTrigger as-child>
                                                        <ChevronsUpDown
                                                            class="size-5 shrink-0 cursor-pointer self-center text-gray-500" />
                                                        <span class="sr-only">Toggle</span>
                                                    </CollapsibleTrigger>
                                                </div>
                                            </div>
                                            <!-- Entries -->
                                            <CollapsibleContent>
                                                <ul class="divide-y border-t dark:divide-white/5">
                                                    <li v-for="entry in group.entries" :key="entry.id">
                                                        <a
                                                            :href="`/nutrition-entries/${entry.id}`"
                                                            class="flex items-start justify-between gap-x-6 px-6 py-4 transition-colors hover:bg-muted/50 dark:hover:bg-white/5">
                                                            <div class="min-w-0">
                                                                <p class="text-sm/6 font-semibold">
                                                                    {{ entry.food_item }}
                                                                </p>
                                                                <p class="mt-1 flex items-center gap-2">
                                                                    <span
                                                                        class="inline-flex items-center rounded-full bg-blue-400/10 px-2 py-1 text-xs font-medium text-blue-400 inset-ring inset-ring-blue-400/30">
                                                                        {{ entry.calories }} kcal
                                                                    </span>
                                                                    <span
                                                                        class="inline-flex items-center rounded-full bg-indigo-400/10 px-2 py-1 text-xs font-medium text-indigo-400 inset-ring inset-ring-indigo-400/30">
                                                                        {{ entry.protein }}g
                                                                        <span class="ml-1 sm:hidden">P</span>
                                                                        <span class="ml-1 hidden sm:inline">
                                                                            Protein
                                                                        </span>
                                                                    </span>
                                                                    <span
                                                                        class="inline-flex items-center rounded-full bg-red-400/10 px-2 py-1 text-xs font-medium text-red-400 inset-ring inset-ring-red-400/20">
                                                                        {{ entry.carbohydrates }}g
                                                                        <span class="ml-1 sm:hidden">C</span>
                                                                        <span class="ml-1 hidden sm:inline">
                                                                            Carbohydrates
                                                                        </span>
                                                                    </span>
                                                                    <span
                                                                        class="inline-flex items-center rounded-full bg-yellow-400/10 px-2 py-1 text-xs font-medium text-yellow-500 inset-ring inset-ring-yellow-400/20">
                                                                        {{ entry.fats }}g
                                                                        <span class="ml-1 sm:hidden">F</span>
                                                                        <span class="ml-1 hidden sm:inline">Fats</span>
                                                                    </span>
                                                                </p>
                                                            </div>
                                                            <div class="flex shrink-0 items-start gap-x-6">
                                                                <div
                                                                    class="hidden shrink-0 sm:flex sm:flex-col sm:items-end">
                                                                    <p class="text-sm/6 capitalize">
                                                                        {{ entry.meal_type }}
                                                                    </p>
                                                                    <p class="mt-1 text-xs/5 text-gray-400">
                                                                        {{ entry.time }}
                                                                    </p>
                                                                </div>
                                                                <ChevronRight
                                                                    class="size-5 shrink-0 self-center text-gray-500" />
                                                            </div>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </CollapsibleContent>
                                        </Collapsible>
                                    </li>
                                </ul>
                            </li>
                        </template>
                    </ul>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

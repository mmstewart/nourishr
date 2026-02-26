<script setup lang="ts">
import { ref, watch, computed, type HTMLAttributes } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { type WeightEntry } from '@/types';

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
import { Plus, Pencil } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Weight Entries',
        href: '/weight-entries',
    },
];

const props = defineProps<{
    class?: HTMLAttributes["class"],
    weightEntries: WeightEntry[];
    chartData: any[];
    filters: { range: string };
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

const weightConfig = {
    weight: { label: "Weight", color: "#fff" }
}

const currentWeight = computed(() => {
    return props.weightEntries.length > 0
        ? props.weightEntries[0].weight
        : null;
});

const totalLost = computed(() => {
    if (!currentWeight.value || !props.chartData.length) return 0;

    const startWeight = props.chartData[0].weight;
    const diff = startWeight - currentWeight.value;

    return diff.toFixed(1);
});

watch(timeRange, (newRange) => {
    router.get('/weight-entries', { range: newRange }, {
        preserveState: false,
        preserveScroll: true,
        only: ['chartData', 'filters', 'weightEntries']
    })
});
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-4 p-4">
            <div
                class="grid auto-rows-min gap-4 *:data-[slot=card]:bg-linear-to-t *:data-[slot=card]:from-primary/5 *:data-[slot=card]:to-card *:data-[slot=card]:shadow-xs md:grid-cols-2 dark:*:data-[slot=card]:bg-card">
                <Card class="@container/card">
                    <CardHeader>
                        <CardDescription>Current Weight</CardDescription>
                        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
                            {{ currentWeight }} lbs
                        </CardTitle>
                    </CardHeader>
                </Card>
                <Card class="@container/card">
                    <CardHeader>
                        <CardDescription>Total Progress</CardDescription>
                        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
                            {{ Number(totalLost) > 0 ? '-' : '+' }}{{ Math.abs(Number(totalLost)) }} lbs
                        </CardTitle>
                    </CardHeader>
                </Card>
            </div>
            <ChartArea title="Weight" :description="chartDescription" :data="chartData" :config="weightConfig"
                dataKey="weight" class="h-75 md:h-100">
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
                        <CardTitle>Weight Entries</CardTitle>
                        <Button variant="outline" size="sm" as-child>
                            <a href="/weight-entries/create">
                                <Plus class="size-4.5" />
                            </a>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <ul role="list" class="divide-y divide-white/5">
                            <li v-for="weightEntry in weightEntries" :key="weightEntry.id"
                                class="flex justify-between items-center gap-x-6 py-5 px-6">
                                <div class="flex min-w-0 gap-x-4">
                                    <div class="min-w-0 flex-auto">
                                        <p class="text-sm/6 font-semibold text-white">{{ weightEntry.weight }} lbs
                                        </p>
                                        <p class="mt-1 truncate text-xs/5 text-gray-400">{{ weightEntry.date
                                            }}</p>
                                    </div>
                                </div>
                                <Button variant="outline" size="sm" as-child>
                                    <a :href="`/weight-entries/${weightEntry.id}/edit`">
                                        <Pencil class="size-4.5" />
                                    </a>
                                </Button>
                            </li>
                        </ul>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

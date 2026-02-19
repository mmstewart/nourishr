<script setup lang="ts">
import { ref, watch, computed, type HTMLAttributes } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { type WeightEntry } from '@/types';

import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
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
    weight: { label: "Weight", color: "#4299e1" }
}

watch(timeRange, (newRange) => {
    router.get('/weight-entries', { range: newRange }, {
        preserveState: true,
        preserveScroll: true,
        only: ['chartData', 'filters']
    })
});
</script>

<template>

    <Head title="Weight Entries" />

    <AppLayout :breadcrumbs="breadcrumbs">

        <div class="flex flex-1 flex-col gap-4 p-4">
            <ChartArea title="Weight" :description="chartDescription" :data="chartData" :config="weightConfig"
                dataKey="weight" class="h-1/2">
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
            <div class="flex-1 min-h-0">
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
                                class="flex justify-between items-center gap-x-6 py-5">
                                <div class="flex min-w-0 gap-x-4">
                                    <div class="min-w-0 flex-auto">
                                        <p class="text-sm/6 font-semibold text-white">{{ weightEntry.weight }} lbs
                                        </p>
                                        <p class="mt-1 truncate text-xs/5 text-gray-400">{{ weightEntry.date
                                        }}</p>
                                    </div>
                                </div>
                                <Button variant="outline" size="sm" as-child>
                                    <a href="/weight-entries/{{ weightEntry.id }}/edit">
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
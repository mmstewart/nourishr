<script setup lang="ts">
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { Badge } from "@/components/ui/badge"
import {
    Card,
    CardAction,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from "@/components/ui/card";
import { Progress } from '@/components/ui/progress';
import { Weight, TrendingDown } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

interface WeightGoal {
    id: number;
    start_date: string;
    start_weight: number;
    weekly_goal_id: number;
    current_weight: number;
    goal_weight: number;
}

const props = defineProps<{
    weightGoal: WeightGoal,
}>()

const progress = computed(() => {
    const start = props.weightGoal?.start_weight ?? 0;
    const current = props.weightGoal?.current_weight ?? 0;
    const goal = props.weightGoal?.goal_weight ?? 0;

    const totalToLose = start - goal;

    if (totalToLose <= 0) return 0;

    const lostSoFar = start - current;

    const percentage = (lostSoFar / totalToLose) * 100;

    return Math.min(Math.max(percentage, 0), 100);
});

// Calculate remaining lbs for the label
const lbsToGoal = computed(() => {
    const diff = (props.weightGoal?.current_weight ?? 0) - (props.weightGoal?.goal_weight ?? 0);
    return diff > 0 ? diff.toFixed(1) : 0;
});
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div
                class="*:data-[slot=card]:from-primary/5 *:data-[slot=card]:to-card dark:*:data-[slot=card]:bg-card grid auto-rows-min gap-4 md:grid-cols-3 *:data-[slot=card]:bg-gradient-to-t *:data-[slot=card]:shadow-xs">
                <Card class="@container/card">
                    <CardHeader>
                        <CardDescription>New Customers</CardDescription>
                        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
                            1,234
                        </CardTitle>
                        <CardAction>
                            <Badge variant="outline">
                                <IconTrendingDown />
                                -20%
                            </Badge>
                        </CardAction>
                    </CardHeader>
                    <CardFooter class="flex-col items-start gap-1.5 text-sm">
                        <div class="line-clamp-1 flex gap-2 font-medium">
                            Down 20% this period
                            <IconTrendingDown class="size-4" />
                        </div>
                        <div class="text-muted-foreground">
                            Acquisition needs attention
                        </div>
                    </CardFooter>
                </Card>
                <Card class="@container/card">
                    <CardHeader>
                        <CardDescription>Nutrition Goals</CardDescription>
                        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
                            45,678
                        </CardTitle>
                    </CardHeader>
                    <CardFooter class="flex-col items-start gap-1.5 text-sm">
                        <div class="line-clamp-1 flex gap-2 font-medium">
                            Strong user retention
                            <IconTrendingUp class="size-4" />
                        </div>
                        <div class="text-muted-foreground">
                            Engagement exceed targets
                        </div>
                    </CardFooter>
                </Card>
                <Card class="@container/card relative overflow-hidden">
                    <CardHeader>
                        <CardDescription>Current Weight</CardDescription>
                        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
                            {{ props.weightGoal.current_weight }} lbs
                        </CardTitle>
                        <CardAction>
                            <Weight class="mr-1 size-4.5" />
                        </CardAction>
                    </CardHeader>
                    <CardContent>
                        <div class="flex justify-between mb-2">
                            <span class="text-sm text-muted-foreground">{{ props.weightGoal.current_weight }}
                                lbs</span>
                            <span class="text-sm font-medium tabular-nums">{{ props.weightGoal.goal_weight }}
                                lbs</span>
                        </div>
                        <Progress v-model="progress" class="w-full" />
                    </CardContent>
                    <CardFooter class="text-sm">
                        <div class="flex items-center gap-2 font-medium">
                            <TrendingDown class="size-4.5" />
                            {{ lbsToGoal }} lbs to goal
                        </div>
                    </CardFooter>
                </Card>
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
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
import { Weight, TrendingDown, TrendingUp, BicepsFlexed, Target, Flame } from 'lucide-vue-next';
import ChartArea from '../components/ChartArea.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const page = usePage();
const user = page.props.auth.user;

const weightStats = computed(() => {
    const { start_weight: start, current_weight: current, goal_weight: goal } = user.weight_goal;

    const goalId = user.weight_goal.weekly_goal_id;

    const isLosing = [1, 2, 3, 4].includes(goalId);
    const isGaining = [6, 7].includes(goalId);
    const isMaintaining = goalId === 5;

    // Weight difference between start and goal
    const weightDifference = Math.abs(start - goal);

    // Weight covered so far
    const weightCovered = Math.abs(start - current);

    // Progress percentage
    const progressValue = isMaintaining || weightDifference === 0
        ? 100
        : Math.min(Math.max((weightCovered / weightDifference) * 100, 0), 100);

    const lbsToGoal = Math.abs(current - goal).toFixed(1);

    let statusLabel = '';

    if (isMaintaining) {
        statusLabel = 'Staying on track';
    } else {
        statusLabel = current > goal ? 'left to lose' : 'left to gain';
    }

    return {
        progress: progressValue,
        lbsToGoal,
        isLosing,
        isGaining,
        isMaintaining,
        statusLabel
    };
});

const totalEatenCalories = computed(() => {
    return user.nutrition_entries.reduce((total, entry) => total + entry.calories, 0);
});

const calorieProgress = computed(() => {
    const target = user.weight_goal.target_calories;

    return Math.min((totalEatenCalories.value / target) * 100, 100);
});

const kcalRemaining = computed(() => {
    return Math.max(user.weight_goal.target_calories - totalEatenCalories.value, 0);
});

const totalProtein = computed(() => {
    return user.nutrition_entries.reduce((total, entry) => total + entry.protein, 0);
});

const proteinProgress = computed(() => {
    const target = user.weight_goal.protein_grams;

    return Math.min((totalProtein.value / target) * 100, 100);
});

const totalCarbohydrates = computed(() => {
    return user.nutrition_entries.reduce((total, entry) => total + entry.carbohydrates, 0);
});

const carbohydratesProgress = computed(() => {
    const target = user.weight_goal.carb_grams;

    return Math.min((totalCarbohydrates.value / target) * 100, 100);
});

const totalFats = computed(() => {
    return user.nutrition_entries.reduce((total, entry) => total + entry.fats, 0);
});

const fatsProgress = computed(() => {
    const target = user.weight_goal.fat_grams;

    return Math.min((totalFats.value / target) * 100, 100);
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
                        <CardDescription>Daily Calories</CardDescription>
                        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
                            {{ totalEatenCalories }} kcal
                        </CardTitle>
                        <CardAction>
                            <Flame class="mr-1 size-4.5" />
                        </CardAction>
                    </CardHeader>
                    <CardContent>
                        <div class="text-sm font-medium tabular-nums text-end">{{ user.weight_goal.target_calories
                        }}
                            kcal</div>
                        <Progress :model-value="calorieProgress" class="w-full mt-2" />
                    </CardContent>
                    <CardFooter class="text-sm">
                        <div class="flex items-center gap-2 font-medium">
                            <TrendingUp class="size-4.5 text-green-500" />
                            {{ kcalRemaining }} kcal remaining
                        </div>
                    </CardFooter>
                </Card>
                <Card class="@container/card">
                    <CardHeader>
                        <CardDescription>Nutrition Goals</CardDescription>
                        <CardAction>
                            <Target class="mr-1 size-4.5" />
                        </CardAction>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-sm text-muted-foreground">Protein</span>
                                    <span class="text-sm font-medium tabular-nums">{{ totalProtein }} / {{
                                        user.weight_goal.protein_grams }}
                                        g</span>
                                </div>
                                <Progress :model-value="proteinProgress" class="w-full" />
                            </div>
                            <div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-sm text-muted-foreground">Carbs</span>
                                    <span class="text-sm font-medium tabular-nums">{{ totalCarbohydrates }} / {{
                                        user.weight_goal.carb_grams }}
                                        g</span>
                                </div>
                                <Progress :model-value="carbohydratesProgress" class="w-full" />
                            </div>
                            <div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-sm text-muted-foreground">Fats</span>
                                    <span class="text-sm font-medium tabular-nums">{{ totalFats }} / {{
                                        user.weight_goal.fat_grams }}
                                        g</span>
                                </div>
                                <Progress :model-value="fatsProgress" class="w-full" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card class="@container/card relative overflow-hidden">
                    <CardHeader>
                        <CardDescription>Current Weight</CardDescription>
                        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
                            {{ user.weight_goal.current_weight }} lbs
                        </CardTitle>
                        <CardAction>
                            <Weight class="mr-1 size-4.5" />
                        </CardAction>
                    </CardHeader>
                    <CardContent>
                        <div class="text-sm font-medium tabular-nums text-end">Goal: {{ user.weight_goal.goal_weight }}
                            lbs</div>
                        <Progress :model-value="weightStats.progress" class="w-full mt-2" />
                    </CardContent>
                    <CardFooter class="text-sm">
                        <div class="flex items-center gap-2 font-medium">
                            <TrendingDown v-if="weightStats.isLosing" class="size-4.5 text-blue-500" />
                            <TrendingUp v-else-if="weightStats.isGaining" class="size-4.5 text-green-500" />
                            <BicepsFlexed v-else class="size-4.5 text-purple-500" />

                            <span>{{ weightStats.lbsToGoal }} lbs {{ weightStats.statusLabel }}</span>
                        </div>
                    </CardFooter>
                </Card>
            </div>
            <div
                class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <ChartArea :user="user" />
            </div>
        </div>
    </AppLayout>
</template>

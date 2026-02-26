<script setup lang="ts">
import {
    Card,
    CardAction,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Progress } from '@/components/ui/progress';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import {
    BicepsFlexed,
    Flame,
    Target,
    TrendingDown,
    TrendingUp,
    Weight,
} from 'lucide-vue-next';
import { computed } from 'vue';
import ChartArea from '../components/ChartArea.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const props = defineProps(['chartData', 'latestWeight']);

const page = usePage();
const user = page.props.auth.user;

// Match the YYYY-MM-DD format
const todayDate = new Date().toLocaleDateString('en-CA');

const weightStats = computed(() => {
    const {
        start_weight: start,
        goal_weight: goal,
        weekly_goal_id: goalId,
    } = user.weight_goal;

    const current = props.latestWeight ?? start;

    const isLosing = [1, 2, 3, 4].includes(goalId);
    const isGaining = [6, 7].includes(goalId);
    const isMaintaining = goalId === 5;

    const totalChangeNeeded = goal - start;
    const actualChangeMade = current - start;

    let progressValue = 0;

    if (isMaintaining || totalChangeNeeded === 0) {
        progressValue = 100;
    } else {
        const rawProgress = (actualChangeMade / totalChangeNeeded) * 100;

        progressValue = Math.min(Math.max(rawProgress, 0), 100);
    }

    const lbsToGoal = Math.abs(current - goal).toFixed(1);

    let label = '';

    if (isMaintaining) {
        label = 'Staying on track';
    } else {
        label = `${current > goal ? 'left to lose' : 'left to gain'}`;
    }

    return {
        currentDisplay: current,
        progress: progressValue,
        lbsToGoal,
        isLosing,
        isGaining,
        isMaintaining,
        statusLabel: label,
    };
});

const todayEntries = computed(() => {
    return (user.nutrition_entries || []).filter(
        (entry) => entry.date === todayDate,
    );
});

const totalEatenCalories = computed(() => {
    return todayEntries.value.reduce(
        (total, entry) => total + (entry.calories || 0),
        0,
    );
});

const calorieProgress = computed(() => {
    const target = user.weight_goal.target_calories;

    return target > 0
        ? Math.min((totalEatenCalories.value / target) * 100, 100)
        : 0;
});

const kcalRemaining = computed(() => {
    return Math.max(
        user.weight_goal.target_calories - totalEatenCalories.value,
        0,
    );
});

const totalProtein = computed(() =>
    todayEntries.value.reduce(
        (total, entry) => total + (entry.protein || 0),
        0,
    ),
);
const proteinProgress = computed(() =>
    Math.min((totalProtein.value / user.weight_goal.protein_grams) * 100, 100),
);

const totalCarbohydrates = computed(() =>
    todayEntries.value.reduce(
        (total, entry) => total + (entry.carbohydrates || 0),
        0,
    ),
);
const carbohydratesProgress = computed(() =>
    Math.min(
        (totalCarbohydrates.value / user.weight_goal.carb_grams) * 100,
        100,
    ),
);

const totalFats = computed(() =>
    todayEntries.value.reduce((total, entry) => total + (entry.fats || 0), 0),
);
const fatsProgress = computed(() =>
    Math.min((totalFats.value / user.weight_goal.fat_grams) * 100, 100),
);

const weightConfig = {
    weight: { label: 'Weight', color: '#fff', valueFormatter: (val: any) => `${val}` },
};

const calorieConfig = {
    calories: { label: 'Calories', color: '#fff', valueFormatter: (val: any) => `${val}` },
};
</script>

<template>

    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div
                class="grid auto-rows-min gap-4 *:data-[slot=card]:bg-linear-to-t *:data-[slot=card]:from-primary/5 *:data-[slot=card]:to-card *:data-[slot=card]:shadow-xs md:grid-cols-3 dark:*:data-[slot=card]:bg-card">
                <Card class="@container/card">
                    <CardHeader>
                        <CardDescription>Calories</CardDescription>
                        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
                            {{ totalEatenCalories }} calories
                        </CardTitle>
                        <CardAction>
                            <Flame class="mr-1 size-4.5" />
                        </CardAction>
                    </CardHeader>
                    <CardContent>
                        <div class="text-end text-sm font-medium tabular-nums">
                            {{ user.weight_goal.target_calories }} calories
                        </div>
                        <Progress :model-value="calorieProgress" class="mt-2 w-full"
                            :class="{ '[&>div]:hidden': calorieProgress <= 0 }" />
                    </CardContent>
                    <CardFooter class="text-sm">
                        <div class="flex items-center gap-2 font-medium">
                            <TrendingUp class="size-4.5 text-green-500" />
                            {{ kcalRemaining }} calories remaining
                        </div>
                    </CardFooter>
                </Card>
                <Card class="@container/card">
                    <CardHeader>
                        <CardDescription>Macros</CardDescription>
                        <CardAction>
                            <Target class="mr-1 size-4.5" />
                        </CardAction>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <div class="mb-2 flex justify-between">
                                    <span class="text-sm text-muted-foreground">Protein</span>
                                    <span class="text-sm font-medium tabular-nums">{{ totalProtein }} /
                                        {{ user.weight_goal.protein_grams }}
                                        g</span>
                                </div>
                                <Progress :model-value="proteinProgress" class="w-full" :class="{
                                    '[&>div]:hidden': proteinProgress <= 0,
                                }" />
                            </div>
                            <div>
                                <div class="mb-2 flex justify-between">
                                    <span class="text-sm text-muted-foreground">Carbs</span>
                                    <span class="text-sm font-medium tabular-nums">{{ totalCarbohydrates }} /
                                        {{ user.weight_goal.carb_grams }}
                                        g</span>
                                </div>
                                <Progress :model-value="carbohydratesProgress" class="w-full" :class="{
                                    '[&>div]:hidden':
                                        carbohydratesProgress <= 0,
                                }" />
                            </div>
                            <div>
                                <div class="mb-2 flex justify-between">
                                    <span class="text-sm text-muted-foreground">Fats</span>
                                    <span class="text-sm font-medium tabular-nums">{{ totalFats }} /
                                        {{ user.weight_goal.fat_grams }} g</span>
                                </div>
                                <Progress :model-value="fatsProgress" class="w-full" :class="{
                                    '[&>div]:hidden': fatsProgress <= 0,
                                }" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card class="@container/card relative overflow-hidden">
                    <CardHeader>
                        <CardDescription>Weight</CardDescription>
                        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
                            {{ weightStats.currentDisplay }} lbs
                        </CardTitle>
                        <CardAction>
                            <Weight class="mr-1 size-4.5" />
                        </CardAction>
                    </CardHeader>
                    <CardContent>
                        <div class="text-end text-sm font-medium tabular-nums">
                            {{ user.weight_goal.goal_weight }} lbs
                        </div>
                        <Progress :model-value="weightStats.progress" class="mt-2 w-full" :class="{
                            '[&>div]:hidden': weightStats.progress <= 0,
                        }" />
                    </CardContent>
                    <CardFooter class="text-sm">
                        <div class="flex items-center gap-2 font-medium">
                            <TrendingDown v-if="weightStats.isLosing" class="size-4.5 text-blue-500" />
                            <TrendingUp v-else-if="weightStats.isGaining" class="size-4.5 text-green-500" />
                            <BicepsFlexed v-else class="size-4.5 text-purple-500" />
                            <span>{{ weightStats.lbsToGoal }} lbs
                                {{ weightStats.statusLabel }}</span>
                        </div>
                    </CardFooter>
                </Card>
            </div>
            <div class="relative grid min-h-0 flex-1 auto-rows-min gap-4 rounded-xl md:grid-cols-2 md:grid-rows-[1fr]">
                <ChartArea title="Calories" description="Calories consumed over the last 7 days" :data="chartData"
                    :config="calorieConfig" dataKey="calories" chartType="line" />
                <ChartArea title="Weight" description="Scale weigh-ins over the last 7 days" :data="chartData"
                    :config="weightConfig" dataKey="weight" chartType="line" />
            </div>
        </div>
    </AppLayout>
</template>

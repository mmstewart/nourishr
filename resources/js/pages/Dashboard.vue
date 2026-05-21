<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import {
    Card,
    CardAction,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';

import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Progress } from '@/components/ui/progress';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import {
    BicepsFlexed,
    CircleCheck,
    CirclePlus,
    Flame,
    PieChart,
    Scale,
    TrendingDown,
    TrendingUp,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const props = defineProps(['latestWeight', 'today']);

const page = usePage();
const user = page.props.auth.user;

const showSuccessMessage = ref(false);

const weightStats = computed(() => {
    const { start_weight: start, goal_weight: goal, weekly_goal_id: goalId } = user.weight_goal;

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

    const lbsToGoal = Math.abs(current - goal).toFixed(2);

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
    return (user.nutrition_entries || []).filter((entry) => entry.date === props.today);
});

const totalEatenCalories = computed(() => {
    return todayEntries.value.reduce((total, entry) => total + (entry.calories || 0), 0);
});

const calorieProgress = computed(() => {
    const target = user.weight_goal.target_calories;

    return target > 0 ? Math.min((totalEatenCalories.value / target) * 100, 100) : 0;
});

const kcalRemaining = computed(() => {
    return Math.max(user.weight_goal.target_calories - totalEatenCalories.value, 0);
});

const formatMacro = (key: 'protein' | 'carbohydrates' | 'fats') => {
    const total = todayEntries.value.reduce((total, entry) => total + Number(entry[key] || 0), 0);

    return parseFloat(total.toFixed(2));
};

const totalProtein = computed(() => formatMacro('protein'));
const totalCarbohydrates = computed(() => formatMacro('carbohydrates'));
const totalFats = computed(() => formatMacro('fats'));

const proteinProgress = computed(() => Math.min((totalProtein.value / user.weight_goal.protein_grams) * 100, 100));

const carbohydratesProgress = computed(() =>
    Math.min((totalCarbohydrates.value / user.weight_goal.carb_grams) * 100, 100),
);

const fatsProgress = computed(() => Math.min((totalFats.value / user.weight_goal.fat_grams) * 100, 100));

watch(
    () => page.props.flash.success,
    (newMessage) => {
        if (newMessage) {
            showSuccessMessage.value = true;

            setTimeout(() => {
                showSuccessMessage.value = false;
            }, 5000);
        }
    },
    { immediate: true },
);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <DropdownMenu>
                <DropdownMenuTrigger as-child>
                    <Button class="cursor-pointer bg-green-500 hover:bg-green-400 dark:text-white">
                        <CirclePlus class="size-4" />
                        Log
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent class="w-32" align="end">
                    <DropdownMenuItem class="cursor-pointer" as-child>
                        <a href="/weight-entries/create">Weight Entry</a>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem class="cursor-pointer" as-child>
                        <a href="/nutrition-entries/create">Nutrition Entry</a>
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </template>
        <div v-if="showSuccessMessage" class="fixed right-4 bottom-4 z-50 w-full max-w-md">
            <Alert>
                <CircleCheck class="stroke-green-500" />
                <AlertTitle class="text-green-500">Thank you for the feedback!</AlertTitle>
                <AlertDescription>
                    {{ page.props.flash.success }}
                </AlertDescription>
            </Alert>
        </div>
        <div class="flex h-full flex-1 flex-col justify-center gap-4 overflow-x-auto rounded-md p-4">
            <div
                class="grid auto-rows-min gap-4 *:data-[slot=card]:bg-linear-to-tl *:data-[slot=card]:from-primary/5 *:data-[slot=card]:to-card *:data-[slot=card]:shadow-xs lg:grid-cols-3 dark:*:data-[slot=card]:bg-card">
                <Card class="@container/card justify-between">
                    <CardHeader>
                        <CardDescription>Calories</CardDescription>
                        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
                            {{ totalEatenCalories }} kcal
                        </CardTitle>
                        <CardAction>
                            <Flame class="mr-1 size-4.5" />
                        </CardAction>
                    </CardHeader>
                    <CardContent>
                        <div class="invisible" aria-hidden="true">
                            <div class="mb-2 flex justify-between"><span class="text-sm">Spacer</span></div>
                            <div class="h-2"></div>
                        </div>
                        <div class="mb-2 text-end text-sm font-medium tabular-nums">
                            {{ user.weight_goal.target_calories }} kcal
                        </div>
                        <Progress
                            :model-value="calorieProgress"
                            class="w-full"
                            :class="{ '[&>div]:hidden': calorieProgress <= 0 }" />
                        <div class="invisible" aria-hidden="true"></div>
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
                        <CardDescription>Macros</CardDescription>
                        <CardAction>
                            <PieChart class="mr-1 size-4.5" />
                        </CardAction>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div>
                                <div class="mb-2 flex justify-between">
                                    <span class="text-sm text-muted-foreground">Protein</span>
                                    <span class="text-sm font-medium tabular-nums">
                                        {{ totalProtein }} / {{ user.weight_goal.protein_grams }}g
                                    </span>
                                </div>
                                <Progress
                                    :model-value="proteinProgress"
                                    class="w-full"
                                    :class="{
                                        '[&>div]:hidden': proteinProgress <= 0,
                                    }" />
                            </div>
                            <div>
                                <div class="mb-2 flex justify-between">
                                    <span class="text-sm text-muted-foreground">Carbohydrates</span>
                                    <span class="text-sm font-medium tabular-nums">
                                        {{ totalCarbohydrates }} / {{ user.weight_goal.carb_grams }}g
                                    </span>
                                </div>
                                <Progress
                                    :model-value="carbohydratesProgress"
                                    class="w-full"
                                    :class="{
                                        '[&>div]:hidden': carbohydratesProgress <= 0,
                                    }" />
                            </div>
                            <div>
                                <div class="mb-2 flex justify-between">
                                    <span class="text-sm text-muted-foreground">Fat</span>
                                    <span class="text-sm font-medium tabular-nums">
                                        {{ totalFats }} / {{ user.weight_goal.fat_grams }}g
                                    </span>
                                </div>
                                <Progress
                                    :model-value="fatsProgress"
                                    class="w-full"
                                    :class="{
                                        '[&>div]:hidden': fatsProgress <= 0,
                                    }" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
                <Card class="@container/card justify-between">
                    <CardHeader>
                        <CardDescription>Weight</CardDescription>
                        <CardTitle class="text-2xl font-semibold tabular-nums @[250px]/card:text-3xl">
                            {{ weightStats.currentDisplay }} lbs
                        </CardTitle>
                        <CardAction>
                            <Scale class="mr-1 size-4.5" />
                        </CardAction>
                    </CardHeader>
                    <CardContent>
                        <div class="invisible" aria-hidden="true">
                            <div class="mb-2 flex justify-between"><span class="text-sm">Spacer</span></div>
                            <div class="h-2"></div>
                        </div>
                        <div class="mb-2 text-end text-sm font-medium tabular-nums">
                            {{ user.weight_goal.goal_weight }} lbs
                        </div>
                        <Progress
                            :model-value="weightStats.progress"
                            class="w-full"
                            :class="{
                                '[&>div]:hidden': weightStats.progress <= 0,
                            }" />
                        <div class="invisible" aria-hidden="true"></div>
                    </CardContent>
                    <CardFooter class="text-sm">
                        <div class="flex items-center gap-2 font-medium">
                            <TrendingDown v-if="weightStats.isLosing" class="size-4.5 text-blue-500" />
                            <TrendingUp v-else-if="weightStats.isGaining" class="size-4.5 text-green-500" />
                            <BicepsFlexed v-else class="size-4.5 text-purple-500" />
                            <span>
                                {{ weightStats.lbsToGoal }} lbs
                                {{ weightStats.statusLabel }}
                            </span>
                        </div>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

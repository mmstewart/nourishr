<script setup lang="ts">
import { ref, computed, type HTMLAttributes } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { cn } from "@/lib/utils"

import { Button } from '@/components/ui/button'
import {
    Field,
    FieldDescription,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field'
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { InputGroup, InputGroupAddon, InputGroupInput, InputGroupText } from '@/components/ui/input-group'
import { Spinner } from '@/components/ui/spinner';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Weight goal settings',
        href: '/settings/weight-goal',
    },
];

const page = usePage();
const user = page.props.auth.user;

defineProps<{
    class?: HTMLAttributes["class"],
    weeklyGoals: Array<{ id: number, name: string }>,
    activityLevels: Array<{ id: number, name: string }>,
}>()

const form = useForm({
    weekly_goal_id: user.weight_goal.weekly_goal_id,
    activity_level_id: user.weight_goal.activity_level_id,
    current_weight: user.weight_goal.current_weight,
    goal_weight: user.weight_goal.goal_weight,
})

// Touch tracking so we don't show errors before the user interacts
const touchedWeeklyGoalId = ref(false)
const touchedActivityLevelId = ref(false)
const touchedCurrentWeight = ref(false)
const touchedGoalWeight = ref(false)

const weeklyGoalError = computed(() => {
    if (!form.weekly_goal_id && touchedWeeklyGoalId.value) return 'Please select a weekly goal.'
    return ''
})

const activityLevelError = computed(() => {
    if (!form.activity_level_id && touchedActivityLevelId.value) return 'Please select an activity level.'
    return ''
})

const validateWeight = (value: string | number | null, isTouched: boolean, label: string) => {
    if (!isTouched) return '';

    if (value === null || value === '') return `${label} weight is required.`

    const num = Number(value)

    if (num < 30 || num > 999) return `${label} weight must be between 30 and 999 lbs.`

    return ''
}

const currentWeightError = computed(() =>
    validateWeight(form.current_weight, touchedCurrentWeight.value, 'Current')
);

const goalWeightError = computed(() =>
    validateWeight(form.goal_weight, touchedGoalWeight.value, 'Goal')
);

const hasClientErrors = computed(() => {
    return !!(weeklyGoalError.value || activityLevelError.value || currentWeightError.value || goalWeightError.value)
})

function submit() {
    touchedWeeklyGoalId.value = true
    touchedActivityLevelId.value = true
    touchedCurrentWeight.value = true
    touchedGoalWeight.value = true

    if (hasClientErrors.value) return

    form.put('/settings/weight-goal')
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Weight goal settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <form @submit.prevent="submit">
                    <FieldGroup>
                        <div class="flex flex-col items-start text-left">
                            <span class="sr-only">Weight Goals</span>
                            <h3 class="text-base font-medium mb-0.5">
                                Weight Goals
                            </h3>
                            <FieldDescription>
                                Set your targets and adjust your weight tracking preferences
                            </FieldDescription>
                        </div>
                        <Field>
                            <FieldLabel for="weekly_goal_id">
                                What is your weekly goal?
                            </FieldLabel>
                            <Select v-model="form.weekly_goal_id" @blur="touchedWeeklyGoalId = true">
                                <SelectTrigger id="weekly_goal_id" name="weekly_goal_id" :class="cn(
                                    'w-full dark:bg-input/30',
                                    (weeklyGoalError || form.errors.weekly_goal_id) && 'border-red-500 ring-red-500'
                                )">
                                    <SelectValue placeholder="Select a weekly goal" />
                                </SelectTrigger>
                                <SelectContent class="w-143.75">
                                    <SelectGroup>
                                        <SelectLabel>Weekly goals</SelectLabel>
                                        <SelectItem v-for="weeklyGoal in weeklyGoals" :key="weeklyGoal.id"
                                            :value="weeklyGoal.id">
                                            {{ weeklyGoal.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <div v-if="weeklyGoalError || form.errors.weekly_goal_id" class="text-red-500 text-sm">
                                {{ weeklyGoalError || form.errors.weekly_goal_id }}
                            </div>
                        </Field>
                        <Field>
                            <FieldLabel for="activity_level_id">
                                What is your activity level?
                            </FieldLabel>
                            <Select v-model="form.activity_level_id" @blur="touchedActivityLevelId = true">
                                <SelectTrigger id="activity_level_id" name="activity_level_id" :class="cn(
                                    'w-full dark:bg-input/30',
                                    (activityLevelError || form.errors.activity_level_id) && 'border-red-500 ring-red-500'
                                )">
                                    <SelectValue placeholder="Select an activity level" />
                                </SelectTrigger>
                                <SelectContent class="w-143.75">
                                    <SelectGroup>
                                        <SelectLabel>Activity levels</SelectLabel>
                                        <SelectItem v-for="activityLevel in activityLevels" :key="activityLevel.id"
                                            :value="activityLevel.id">
                                            {{ activityLevel.name }}
                                        </SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <div v-if="activityLevelError || form.errors.activity_level_id"
                                class="text-red-500 text-sm">
                                {{ activityLevelError || form.errors.activity_level_id }}
                            </div>
                        </Field>
                        <Field>
                            <FieldLabel for="current_weight">
                                What is your current weight?
                            </FieldLabel>
                            <InputGroup :class="{ 'border-red-500': currentWeightError || form.errors.current_weight }">
                                <InputGroupInput v-model="form.current_weight" @blur="touchedCurrentWeight = true"
                                    step="0.1" inputmode="decimal" id="current_weight" name="current_weight"
                                    type="number" />
                                <InputGroupAddon align="inline-end">
                                    <InputGroupText>lbs</InputGroupText>
                                </InputGroupAddon>
                            </InputGroup>
                            <div v-if="currentWeightError || form.errors.current_weight" class="text-red-500 text-sm">
                                {{ currentWeightError || form.errors.current_weight }}
                            </div>
                        </Field>
                        <Field>
                            <FieldLabel for="goal_weight">
                                What is your goal weight?
                            </FieldLabel>
                            <InputGroup :class="{ 'border-red-500': goalWeightError || form.errors.goal_weight }">
                                <InputGroupInput v-model="form.goal_weight" @blur="touchedGoalWeight = true" step="0.1"
                                    inputmode="decimal" id="goal_weight" name="goal_weight" type="number" />
                                <InputGroupAddon align="inline-end">
                                    <InputGroupText>lbs</InputGroupText>
                                </InputGroupAddon>
                            </InputGroup>
                            <div v-if="goalWeightError || form.errors.goal_weight" class="text-red-500 text-sm">
                                {{ goalWeightError || form.errors.goal_weight }}
                            </div>
                        </Field>
                        <Field class="flex items-center gap-4">
                            <Button :disabled="form.processing || hasClientErrors" type="submit"
                                class="inline-flex items-start w-auto! self-start bg-green-500 hover:bg-green-400 dark:text-white">
                                <Spinner v-if="form.processing" class="mr-2" />
                                Save
                            </Button>
                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">
                                    Weight goals updated.
                                </p>
                            </Transition>
                        </Field>
                    </FieldGroup>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
<script setup lang="ts">
import { ref, computed, type HTMLAttributes } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3';
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

import DeleteUser from '@/components/DeleteUser.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Weight Goal settings',
        href: '/settings/weight-goal',
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
    class?: HTMLAttributes["class"],
    weeklyGoals: Array<{ id: number, name: string }>,
    weightGoal: WeightGoal,
}>()

const form = useForm({
    start_date: props.weightGoal.start_date ?? '',
    start_weight: props.weightGoal.start_weight ?? '',
    weekly_goal_id: props.weightGoal.weekly_goal_id ?? '',
    current_weight: props.weightGoal.current_weight ?? '',
    goal_weight: props.weightGoal.goal_weight ?? '',
})

// Touch tracking so we don't show errors before the user interacts
const touchedWeeklyGoalId = ref(false)
const touchedCurrentWeight = ref(false)
const touchedGoalWeight = ref(false)
const attemptedSubmit = ref(false)

function markTouched(field: string) {
    if (field === 'weekly_goal_id') touchedWeeklyGoalId.value = true
    if (field === 'current_weight') touchedCurrentWeight.value = true
    if (field === 'goal_weight') touchedGoalWeight.value = true
}

function hasNumber(value: string): boolean {
    return /\d/.test(value)
}

const weeklyGoalError = computed(() => {
    if (!form.weekly_goal_id && (touchedWeeklyGoalId.value || attemptedSubmit.value)) return 'Please select a weekly goal.'
    return ''
})

const currentWeightError = computed(() => {
    if (!form.current_weight && !hasNumber(String(form.current_weight)) && (touchedCurrentWeight.value || attemptedSubmit.value)) return 'Current weight is required.'
    return ''
})

const goalWeightError = computed(() => {
    if (!form.goal_weight && !hasNumber(String(form.goal_weight)) && (touchedGoalWeight.value || attemptedSubmit.value)) return 'Goal weight is required.'
    return ''
})

const hasClientErrors = computed(() => {
    return !!(weeklyGoalError.value || currentWeightError.value || goalWeightError.value)
})

const allFieldsFilled = computed(() => {
    return !!(form.weekly_goal_id && form.current_weight && form.goal_weight)
})

function submit() {
    attemptedSubmit.value = true
    touchedWeeklyGoalId.value = true
    touchedCurrentWeight.value = true
    touchedGoalWeight.value = true

    if (hasClientErrors.value) {
        return
    }

    form.put('/settings/weight-goal')
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Weight Goal settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <form action="/settings/weight-goal" method="put" @submit.prevent="submit">
                    <FieldGroup>
                        <div class="flex flex-col items-start text-left">
                            <span class="sr-only">Weight Goals</span>
                            <h3 class="text-base font-medium mb-0.5">
                                Weight Goals
                            </h3>
                            <FieldDescription>
                                Update your weight goals
                            </FieldDescription>
                        </div>
                        <Field>
                            <FieldLabel for="weekly_goal_id">
                                What is your weekly goal?
                            </FieldLabel>
                            <Select v-model="form.weekly_goal_id" @blur="markTouched('weekly_goal_id')">
                                <SelectTrigger id="weekly_goal_id" name="weekly_goal_id" :class="cn(
                                    'w-full dark:bg-input/30',
                                    (weeklyGoalError || form.errors.weekly_goal_id) && 'border-red-500 ring-red-500'
                                )">
                                    <SelectValue placeholder="Select a weekly goal" />
                                </SelectTrigger>
                                <SelectContent class="w-[575px]">
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
                            <FieldLabel for="current_weight">
                                What is your current weight?
                            </FieldLabel>
                            <InputGroup :class="{ 'border-red-500': currentWeightError || form.errors.current_weight }">
                                <InputGroupInput v-model="form.current_weight" @blur="markTouched('current_weight')"
                                    step="0.1" min="0" inputmode="decimal" id="current_weight" name="current_weight"
                                    type="number" v-focus />
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
                                <InputGroupInput v-model="form.goal_weight" @blur="markTouched('goal_weight')"
                                    step="0.1" min="0" inputmode="decimal" id="goal_weight" name="goal_weight"
                                    type="number" v-focus />
                                <InputGroupAddon align="inline-end">
                                    <InputGroupText>lbs</InputGroupText>
                                </InputGroupAddon>
                            </InputGroup>
                            <div v-if="goalWeightError || form.errors.goal_weight" class="text-red-500 text-sm">
                                {{ goalWeightError || form.errors.goal_weight }}
                            </div>
                        </Field>
                        <Field class="flex items-center gap-4">
                            <Button :disabled="form.processing || !allFieldsFilled" type="submit"
                                class="inline-flex items-start w-auto! self-start">
                                <Spinner v-if="form.processing" class="mr-2" />
                                Save
                            </Button>
                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">
                                    Saved.
                                </p>
                            </Transition>
                        </Field>
                    </FieldGroup>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
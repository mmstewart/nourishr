<script setup lang="ts">
import { ref, nextTick, computed, useTemplateRef, type HTMLAttributes } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { cn } from "@/lib/utils"

import { Button } from '@/components/ui/button'
import { Spinner } from '@/components/ui/spinner'
import {
    Field,
    FieldGroup,
    FieldLabel,
} from "@/components/ui/field"
import { InputGroup, InputGroupAddon, InputGroupInput, InputGroupText } from '@/components/ui/input-group'
import { Check, Circle, Dot } from 'lucide-vue-next'
import { Stepper, StepperDescription, StepperIndicator, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from '@/components/ui/stepper'
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'

const setupRef = useTemplateRef('setupRef')
const step = ref(1)
const totalSteps = 4

const steps = [
    {
        step: 1,
        title: 'Goal',
        description: 'Select your weekly goal',
    },
    {
        step: 2,
        title: 'Activity',
        description: 'Select your activity level',
    },
    {
        step: 3,
        title: 'Current',
        description: 'Add your current weight',
    },
    {
        step: 4,
        title: 'Target',
        description: 'Add your target weight',
    },
]

const props = defineProps<{
    class?: HTMLAttributes["class"],
    weeklyGoals: Array<{ id: number, name: string }>,
    activityLevels: Array<{ id: number, name: string }>,
}>()

const form = useForm({
    weekly_goal_id: '',
    activity_level_id: '',
    current_weight: '',
    goal_weight: '',
})

// Touch tracking so we don't show errors before the user interacts
const touchedWeeklyGoalId = ref(false)
const touchedActivityLevelId = ref(false)
const touchedCurrentWeight = ref(false)
const touchedGoalWeight = ref(false)
const attemptedSubmit = ref(false)

function markTouched(field: string) {
    if (field === 'weekly_goal_id') touchedWeeklyGoalId.value = true
    if (field === 'activity_level_id') touchedActivityLevelId.value = true
    if (field === 'current_weight') touchedCurrentWeight.value = true
    if (field === 'goal_weight') touchedGoalWeight.value = true
}

const weeklyGoalError = computed(() => {
    if (!form.weekly_goal_id && (touchedWeeklyGoalId.value || attemptedSubmit.value)) return 'Please select a weekly goal.'
    return ''
})

const activityLevelError = computed(() => {
    if (!form.activity_level_id && (touchedActivityLevelId.value || attemptedSubmit.value)) return 'Please select an activity level.'
    return ''
})

const validateWeight = (value: string | number | null, isTouched: boolean, label: string) => {
    if (!(isTouched || attemptedSubmit.value)) return '';

    if (value === null || value === '') {
        return `${label} weight is required.`;
    }

    const num = Number(value);

    if (num < 30 || num > 999) {
        return `${label} weight must be between 30 and 999 lbs.`;
    }

    return '';
};

const currentWeightError = computed(() =>
    validateWeight(form.current_weight, touchedCurrentWeight.value, 'Current')
);

const goalWeightError = computed(() =>
    validateWeight(form.goal_weight, touchedGoalWeight.value, 'Goal')
);

const prevStep = () => {
    if (step.value > 1) step.value--;
};

const hasClientErrors = computed(() => {
    return !!(weeklyGoalError.value || activityLevelError.value || currentWeightError.value || goalWeightError.value)
})

const handleContinue = () => {
    attemptedSubmit.value = true;

    if (step.value === 1 && weeklyGoalError.value) return;
    if (step.value === 2 && activityLevelError.value) return;
    if (step.value === 3 && currentWeightError.value) return;
    if (step.value === 4 && goalWeightError.value) return;

    if (step.value < totalSteps) {
        step.value++;

        nextTick(() => {
            setupRef.value?.querySelector('input')?.focus();
        });

        attemptedSubmit.value = false;
    } else {
        submit();
    }
};

function submit() {
    if (hasClientErrors.value) return

    form.post('/setup', {
        preserveState: true,
    });
}
</script>

<template>
    <div class="bg-background flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
        <Stepper v-model="step" class="flex w-1/2 items-start gap-2">
            <StepperItem v-for="stepItem in steps" :key="stepItem.step" v-slot="{ state }"
                class="relative flex w-full flex-col items-center justify-center" :step="stepItem.step">
                <StepperSeparator v-if="stepItem.step !== steps[steps.length - 1]?.step"
                    class="absolute left-[calc(50%+20px)] right-[calc(-50%+10px)] top-5 block h-0.5 shrink-0 rounded-full bg-muted group-data-[state=completed]:bg-primary" />
                <StepperTrigger as-child :disabled="stepItem.step > step">
                    <Button :variant="state === 'completed' || state === 'active' ? 'default' : 'outline'" size="icon"
                        class="z-10 rounded-full shrink-0"
                        :class="[state === 'active' && 'ring-2 ring-ring ring-offset-2 ring-offset-background']">
                        <Check v-if="state === 'completed'" class="size-5" />
                        <Circle v-if="state === 'active'" />
                        <Dot v-if="state === 'inactive'" />
                    </Button>
                </StepperTrigger>
                <div class="mt-5 flex flex-col items-center text-center">
                    <StepperTitle :class="[state === 'active' && 'text-primary']"
                        class="text-sm font-semibold transition lg:text-base">
                        {{ stepItem.title }}
                    </StepperTitle>
                    <StepperDescription :class="[state === 'active' && 'text-primary']"
                        class="sr-only text-xs text-muted-foreground transition md:not-sr-only lg:text-sm">
                        {{ stepItem.description }}
                    </StepperDescription>
                </div>
            </StepperItem>
        </Stepper>
        <div class="w-full max-w-sm mt-10">
            <div ref="setupRef" :class="cn('flex flex-col gap-6', props.class)">
                <form action="/setup" method="post" @submit.prevent="handleContinue">
                    <FieldGroup>
                        <div class="flex flex-col items-center gap-2 text-center">
                            <span class="sr-only">Setup for Nourishr</span>
                            <h1 class="text-xl font-bold">
                                Let's map out your journey
                            </h1>
                        </div>
                        <template v-if="step === 1">
                            <Field>
                                <FieldLabel for="weekly_goal_id">
                                    What is your weekly goal?
                                </FieldLabel>
                                <Select v-model="form.weekly_goal_id" @blur="markTouched('weekly_goal_id')">
                                    <SelectTrigger v-focus id="weekly_goal_id" name="weekly_goal_id" :class="cn(
                                        'w-full dark:bg-input/30',
                                        (weeklyGoalError || form.errors.weekly_goal_id) && 'border-red-500 ring-red-500'
                                    )">
                                        <SelectValue placeholder="Select a weekly goal" />
                                    </SelectTrigger>
                                    <SelectContent class="w-96.25">
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
                        </template>
                        <template v-if="step === 2">
                            <Field>
                                <FieldLabel for="activity_level_id">
                                    What is your activity level?
                                </FieldLabel>
                                <Select v-model="form.activity_level_id" @blur="markTouched('activity_level_id')">
                                    <SelectTrigger v-focus id="activity_level_id" name="activity_level_id" :class="cn(
                                        'w-full dark:bg-input/30',
                                        (activityLevelError || form.errors.activity_level_id) && 'border-red-500 ring-red-500'
                                    )">
                                        <SelectValue placeholder="Select an activity level" />
                                    </SelectTrigger>
                                    <SelectContent class="w-96.25">
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
                        </template>
                        <template v-if="step === 3">
                            <Field>
                                <FieldLabel for="current_weight">
                                    What is your current weight?
                                </FieldLabel>
                                <InputGroup
                                    :class="{ 'border-red-500': currentWeightError || form.errors.current_weight }">
                                    <InputGroupInput v-model="form.current_weight" @blur="markTouched('current_weight')"
                                        step="0.1" inputmode="decimal" id="current_weight" name="current_weight"
                                        type="number" v-focus />
                                    <InputGroupAddon align="inline-end">
                                        <InputGroupText>lbs</InputGroupText>
                                    </InputGroupAddon>
                                </InputGroup>
                                <div v-if="currentWeightError || form.errors.current_weight"
                                    class="text-red-500 text-sm">
                                    {{ currentWeightError || form.errors.current_weight }}
                                </div>
                            </Field>
                        </template>
                        <template v-if="step === 4">
                            <Field>
                                <FieldLabel for="goal_weight">
                                    What is your goal weight?
                                </FieldLabel>
                                <InputGroup :class="{ 'border-red-500': goalWeightError || form.errors.goal_weight }">
                                    <InputGroupInput v-model="form.goal_weight" @blur="markTouched('goal_weight')"
                                        step="0.1" inputmode="decimal" id="goal_weight" name="goal_weight" type="number"
                                        v-focus />
                                    <InputGroupAddon align="inline-end">
                                        <InputGroupText>lbs</InputGroupText>
                                    </InputGroupAddon>
                                </InputGroup>
                                <div v-if="goalWeightError || form.errors.goal_weight" class="text-red-500 text-sm">
                                    {{ goalWeightError || form.errors.goal_weight }}
                                </div>
                            </Field>
                        </template>
                        <Field orientation="horizontal">
                            <Button v-if="step > 1" variant="outline" type="button" @click="prevStep">
                                Back
                            </Button>
                            <Button type="submit">
                                <Spinner v-if="form.processing" class="mr-2" />
                                {{ step === totalSteps ? 'Finish' : 'Continue' }}
                            </Button>
                        </Field>
                    </FieldGroup>
                </form>
            </div>
        </div>
    </div>
</template>
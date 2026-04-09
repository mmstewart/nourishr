<script setup lang="ts">
import { ref, nextTick, useTemplateRef, onMounted, type HTMLAttributes } from 'vue'
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
import { Stepper, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from '@/components/ui/stepper'
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'

const props = defineProps<{
    class?: HTMLAttributes["class"],
    weeklyGoals?: Array<{ id: number, name: string }>,
    activityLevels?: Array<{ id: number, name: string }>,
}>()

const steps = [
    { step: 1, title: 'Goal', fields: ['weekly_goal_id'] },
    { step: 2, title: 'Activity', fields: ['activity_level_id'] },
    { step: 3, title: 'Current Weight', fields: ['current_weight'] },
    { step: 4, title: 'Target Weight', fields: ['goal_weight'] },
]

const totalSteps = steps.length;
const step = ref(1)
const isValidating = ref(false)
const setupRef = useTemplateRef('setupRef')

const form = useForm('post', '/setup-weight-goal', {
    weekly_goal_id: '',
    activity_level_id: '',
    current_weight: '',
    goal_weight: '',
});

const focusOnInput = () => {
    nextTick(() => {
        const el = setupRef.value?.querySelector('input, [role="combobox"]') as HTMLElement;
        el?.focus();
    });
}

onMounted(() => {
    focusOnInput();
})

const prevStep = () => { if (step.value > 1) step.value-- }

const handleContinue = () => {
    const currentStepConfig = steps.find(s => s.step === step.value);
    if (!currentStepConfig) return;

    isValidating.value = true;

    form.validate({
        only: currentStepConfig.fields as Array<keyof typeof form.data>,
        onSuccess: () => {
            if (step.value === totalSteps) {
                form.post('/setup-weight-goal');
            } else {
                step.value++;
                focusOnInput();
            }
        },
        onValidationError: () => {
            isValidating.value = false;
        },
        onFinish: () => {
            isValidating.value = false;
        }
    })
};
</script>

<template>
    <div class="bg-gray-100 flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
        <Stepper v-model="step" class="flex w-1/2 items-start gap-2">
            <StepperItem v-for="stepItem in steps" :key="stepItem.step" v-slot="{ state }"
                class="relative flex w-full flex-col items-center justify-center" :step="stepItem.step">
                <StepperSeparator v-if="stepItem.step !== steps[steps.length - 1]?.step"
                    class="absolute left-[calc(50%+20px)] right-[calc(-50%+10px)] top-5 block h-0.5 shrink-0 rounded-full bg-gray-300 group-data-[state=completed]:bg-primary" />
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
                </div>
            </StepperItem>
        </Stepper>
        <div class="w-full max-w-sm mt-10">
            <div ref="setupRef" :class="cn('flex flex-col gap-6', props.class)">
                <form @submit.prevent="handleContinue">
                    <FieldGroup>
                        <div class="flex flex-col items-center gap-2 text-center">
                            <h1 class="text-xl font-bold">
                                Setup your weight goals
                            </h1>
                        </div>
                        <template v-if="step === 1">
                            <Field>
                                <FieldLabel for="weekly_goal_id">
                                    What is your weekly goal?
                                </FieldLabel>
                                <Select v-model="form.weekly_goal_id" @change="form.validate('weekly_goal_id')">
                                    <SelectTrigger id="weekly_goal_id" name="weekly_goal_id"
                                        :class="{ 'border-red-500': form.errors.weekly_goal_id }" autofocus>
                                        <SelectValue placeholder="Select your weekly goal" />
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
                                <div v-if="form.invalid('weekly_goal_id')" class="text-red-500 text-sm">
                                    {{ form.errors.weekly_goal_id }}
                                </div>
                            </Field>
                        </template>
                        <template v-if="step === 2">
                            <Field>
                                <FieldLabel for="activity_level_id">
                                    What is your activity level?
                                </FieldLabel>
                                <Select v-model="form.activity_level_id" @change="form.validate('activity_level_id')">
                                    <SelectTrigger id="activity_level_id" name="activity_level_id"
                                        :class="{ 'border-red-500': form.errors.activity_level_id }">
                                        <SelectValue placeholder="Select your activity level" />
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
                                <div v-if="form.invalid('activity_level_id')" class="text-red-500 text-sm">
                                    {{ form.errors.activity_level_id }}
                                </div>
                            </Field>
                        </template>
                        <template v-if="step === 3">
                            <Field>
                                <FieldLabel for="current_weight">
                                    What is your current weight?
                                </FieldLabel>
                                <InputGroup :class="{ 'border-red-500': form.errors.current_weight }">
                                    <InputGroupInput v-model="form.current_weight"
                                        @change="form.validate('current_weight')" step="0.1" inputmode="decimal"
                                        id="current_weight" name="current_weight" type="number" />
                                    <InputGroupAddon align="inline-end">
                                        <InputGroupText>lbs</InputGroupText>
                                    </InputGroupAddon>
                                </InputGroup>
                                <div v-if="form.invalid('current_weight')" class="text-red-500 text-sm">
                                    {{ form.errors.current_weight }}
                                </div>
                            </Field>
                        </template>
                        <template v-if="step === 4">
                            <Field>
                                <FieldLabel for="goal_weight">
                                    What is your goal weight?
                                </FieldLabel>
                                <InputGroup :class="{ 'border-red-500': form.errors.goal_weight }">
                                    <InputGroupInput v-model="form.goal_weight" @change="form.validate('goal_weight')"
                                        step="0.1" inputmode="decimal" id="goal_weight" name="goal_weight"
                                        type="number" />
                                    <InputGroupAddon align="inline-end">
                                        <InputGroupText>lbs</InputGroupText>
                                    </InputGroupAddon>
                                </InputGroup>
                                <div v-if="form.invalid('goal_weight')" class="text-red-500 text-sm">
                                    {{ form.errors.goal_weight }}
                                </div>
                            </Field>
                        </template>
                        <Field orientation="horizontal">
                            <Button v-if="step > 1" variant="outline" type="button" @click="prevStep">Back</Button>
                            <Button type="submit" :disabled="form.processing || isValidating"
                                class="bg-green-500 hover:bg-green-400 dark:text-white">
                                <Spinner v-if="form.processing || isValidating" class="mr-2" />
                                {{ step === totalSteps ? 'Finish' : 'Continue' }}
                            </Button>
                        </Field>
                    </FieldGroup>
                </form>
            </div>
        </div>
    </div>
</template>
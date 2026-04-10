<script setup lang="ts">
import { ref, computed, nextTick, useTemplateRef, onMounted, type HTMLAttributes } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { Field, FieldGroup, FieldLabel } from '@/components/ui/field'

import { Stepper, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from '@/components/ui/stepper'
import { Input } from '@/components/ui/input'
import { Spinner } from '@/components/ui/spinner'
import { Check, Circle, Dot } from 'lucide-vue-next'

const props = defineProps<{
    class?: HTMLAttributes['class'];
    name?: string | null;
    email?: string | null;
}>();

const steps = [
    { step: 1, title: 'Name', fields: ['name'] },
    { step: 2, title: 'Email', fields: ['email'] },
    { step: 3, title: 'Password', fields: ['password', 'password_confirmation'] },
]

const totalSteps = steps.length;
const step = ref(1)
const isValidating = ref(false)
const setupRef = useTemplateRef('setupRef')

const form = useForm('post', '/setup-account', {
    name: props.name ?? '',
    email: props.email ?? '',
    password: '',
    password_confirmation: '',
});

const passwordsMatch = computed(() => form.password === form.password_confirmation)

const passwordHasMinLength = (val: string) => val.length >= 10
const passwordHasNumber = (val: string) => /\d/.test(val)
const passwordHasSpecialChar = (val: string) => /[^A-Za-z0-9\s]/.test(val)

const focusOnInput = () => {
    nextTick(() => {
        const el = setupRef.value?.querySelector('input') as HTMLElement;
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
                form.post('/setup-account');
            } else {
                step.value++;
                focusOnInput();
            }
        },
        onValidationError: () => {
            if (form.errors.password === 'The password field confirmation does not match.') {
                form.clearErrors('password')
                form.setError('password_confirmation', 'Passwords do not match.')
            }

            isValidating.value = false;
        },
        onFinish: () => {
            isValidating.value = false;
        }
    });
}
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
                        <Check v-if="state === 'completed'" />
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
            <div ref="setupRef" :class="cn('flex flex-col gap-6', props.class)" aria-live="polite">
                <form @submit.prevent="handleContinue">
                    <FieldGroup>
                        <div class="flex flex-col items-center gap-2 text-center mb-4">
                            <h1 class="text-xl font-bold">Setup your account</h1>
                        </div>
                        <template v-if="step === 1">
                            <Field>
                                <FieldLabel for="name">Full Name</FieldLabel>
                                <Input v-model="form.name" @input="form.clearErrors('name')" id="name" name="name"
                                    :class="{ 'border-red-500': form.errors.name }" />
                                <div v-if="form.invalid('name')" class="text-red-500 text-sm">{{ form.errors.name }}
                                </div>
                            </Field>
                        </template>
                        <template v-else-if="step === 2">
                            <Field>
                                <FieldLabel for="email">Email</FieldLabel>
                                <Input v-model="form.email" @input="form.validate('email')" id="email" name="email"
                                    type="email" :class="{ 'border-red-500': form.errors.email }" />
                                <div v-if="form.invalid('email')" class="text-red-500 text-sm">{{ form.errors.email }}
                                </div>
                            </Field>
                        </template>
                        <template v-else-if="step === 3">
                            <Field>
                                <FieldLabel for="password">Password</FieldLabel>
                                <Input v-model="form.password" @input="form.clearErrors('password')" id="password"
                                    name="password" type="password"
                                    :class="{ 'border-red-500': form.errors.password }" />
                                <div v-if="form.invalid('password')" class="text-red-500 text-sm">
                                    {{ form.errors.password }}
                                </div>
                                <transition enter-active-class="transition duration-200 ease-out"
                                    enter-from-class="transform -translate-y-2 opacity-0"
                                    enter-to-class="transform translate-y-0 opacity-100"
                                    leave-active-class="transition duration-150 ease-in" leave-from-class="opacity-100"
                                    leave-to-class="opacity-0">
                                    <div v-if="form.password"
                                        class="flex flex-col gap-1 text-base mt-2 border border-muted-foreground/30 px-3 space-y-0.5 md:text-sm py-1 rounded-md">
                                        <div class="flex gap-x-2"
                                            :class="passwordHasMinLength(form.password) ? 'text-green-600' : 'text-muted-foreground'">
                                            {{ passwordHasMinLength(form.password) ? '✓' : '•' }} Contains at least 10
                                            characters
                                        </div>
                                        <div class="flex gap-x-2"
                                            :class="passwordHasNumber(form.password) ? 'text-green-600' : 'text-muted-foreground'">
                                            {{ passwordHasNumber(form.password) ? '✓' : '•' }} Contains a number
                                        </div>
                                        <div class="flex gap-x-2"
                                            :class="passwordHasSpecialChar(form.password) ? 'text-green-600' : 'text-muted-foreground'">
                                            {{ passwordHasSpecialChar(form.password) ? '✓' : '•' }} Contains a special
                                            character
                                        </div>
                                    </div>
                                </transition>
                            </Field>
                            <Field>
                                <FieldLabel for="password_confirmation">Confirm Password</FieldLabel>
                                <Input v-model="form.password_confirmation" @input="form.clearErrors('password')"
                                    id="password_confirmation" name="password_confirmation" type="password"
                                    :class="{ 'border-red-500': form.errors.password_confirmation }" />
                                <div v-if="form.invalid('password_confirmation')" class="text-red-500 text-sm">{{
                                    form.errors.password_confirmation }}
                                </div>
                                <div v-if="form.password_confirmation"
                                    class="flex flex-col gap-1 text-base mt-2 border border-muted-foreground/30 px-3 space-y-0.5 md:text-sm py-1 rounded-md">
                                    <div class="flex gap-x-2"
                                        :class="passwordsMatch ? 'text-green-600' : 'text-muted-foreground'">
                                        {{ passwordsMatch ? '✓' : '•' }} Passwords match
                                    </div>
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
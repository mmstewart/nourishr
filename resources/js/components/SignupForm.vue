<script setup lang="ts">
import { ref, computed, type HTMLAttributes } from 'vue'
import { useForm } from '@inertiajs/vue3'

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import {
    Field,
    FieldDescription,
    FieldGroup,
    FieldLabel,
    FieldSeparator,
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
import { Input } from '@/components/ui/input'
import { Separator } from '@/components/ui/separator'
import { Spinner } from '@/components/ui/spinner';
import { Icon } from "@iconify/vue";

const props = defineProps<{
    class?: HTMLAttributes["class"]
}>()

const form = useForm({
    name: '',
    age: '',
    gender: '',
    feet: '',
    inches: '',
    email: '',
    password: '',
    password_confirmation: '',
})

// Touch tracking so we don't show errors before the user interacts
const touchedName = ref(false)
const touchedAge = ref(false)
const touchedGender = ref(false)
const touchedFeet = ref(false)
const touchedInches = ref(false)
const touchedEmail = ref(false)
const touchedPassword = ref(false)
const touchedPasswordConfirmation = ref(false)
const attemptedSubmit = ref(false)

function markTouched(field: string) {
    if (field === 'name') touchedName.value = true
    if (field === 'age') touchedAge.value = true
    if (field === 'gender') touchedGender.value = true
    if (field === 'feet') touchedFeet.value = true
    if (field === 'inches') touchedInches.value = true
    if (field === 'email') touchedEmail.value = true
    if (field === 'password') touchedPassword.value = true
    if (field === 'password_confirmation') touchedPasswordConfirmation.value = true
}

function validateEmail(value: string): boolean {
    // https://medium.com/@python-javascript-php-html-css/the-best-regular-expression-for-email-address-verification-42bf83ba2885
    return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value)
}

function hasNumber(value: string): boolean {
    return /\d/.test(value)
}

function hasSpecialCharacter(value: string): boolean {
    return /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value)
}

function hasMinimumLength(value: string, minLength: number = 10): boolean {
    return value.length >= minLength
}

const nameError = computed(() => {
    if (!form.name && (touchedName.value || attemptedSubmit.value)) return 'Name is required.'
    return ''
})

const ageError = computed(() => {
    if (!form.age && (touchedAge.value || attemptedSubmit.value)) return 'Age is required.'
    if (form.age) {
        const ageNum = Number(form.age)

        if (isNaN(ageNum) || ageNum < 13 || ageNum > 120) {
            return 'Age must be a number between 13 and 120.'
        }
    }
    return ''
})

const genderError = computed(() => {
    if (!form.gender && (touchedGender.value || attemptedSubmit.value)) return 'Gender is required.'
    return ''
})

const inchesError = computed(() => {
    if (!form.inches && (touchedInches.value || attemptedSubmit.value)) return 'Inches is required.'
    if (form.inches) {
        const inchesNum = Number(form.inches)

        if (isNaN(inchesNum) || inchesNum < 0 || inchesNum >= 12) {
            return 'Inches must be a number between 0 and 11.'
        }
    }
    return ''
})

const feetError = computed(() => {
    if (!form.feet && (touchedInches.value || attemptedSubmit.value)) return 'Inches is required.'
    if (form.feet) {
        const feetNum = Number(form.feet)

        if (isNaN(feetNum) || feetNum < 1 || feetNum > 8) {
            return 'Feet must be a number between 1 and 8.'
        }
    }
    return ''
})

const emailError = computed(() => {
    // todo: going to need to validation email in mail registrar
    if (!form.email && !validateEmail(form.email) && (touchedEmail.value || attemptedSubmit.value)) return 'Email is required.'
    if (form.email && !validateEmail(form.email) && (touchedEmail.value || attemptedSubmit.value)) return 'Email is not valid.'
    return ''
})

const passwordHasNumber = computed(() => hasNumber(form.password))
const passwordHasSpecialChar = computed(() => hasSpecialCharacter(form.password))
const passwordHasMinLength = computed(() => hasMinimumLength(form.password))
const passwordsMatch = computed(() => form.password === form.password_confirmation)

const passwordError = computed(() => {
    if (!form.password && (touchedPassword.value || attemptedSubmit.value)) return 'Password is required.'
    return ''
})

const passwordConfirmationError = computed(() => {
    if (!form.password_confirmation && (touchedPasswordConfirmation.value || attemptedSubmit.value)) return 'Please confirm your password.'
    if (form.password_confirmation && form.password_confirmation !== form.password && (touchedPasswordConfirmation.value || attemptedSubmit.value)) return 'Passwords do not match.'
    return ''
})

const passwordRulesPassed = computed(() => {
    return (
        passwordHasMinLength.value &&
        passwordHasNumber.value &&
        passwordHasSpecialChar.value &&
        passwordsMatch.value
    )
})

const hasClientErrors = computed(() => {
    return !!(nameError.value || emailError.value || ageError.value || genderError.value || feetError.value || inchesError.value || passwordError.value || passwordConfirmationError.value || !passwordRulesPassed.value)
})

const allFieldsFilled = computed(() => {
    return !!(form.name && form.email && form.age && form.gender && form.feet && form.inches && form.password && form.password_confirmation)
})

function submit() {
    attemptedSubmit.value = true
    touchedName.value = true
    touchedAge.value = true
    touchedGender.value = true
    touchedFeet.value = true
    touchedInches.value = true
    touchedEmail.value = true
    touchedPassword.value = true
    touchedPasswordConfirmation.value = true

    if (hasClientErrors.value || !passwordRulesPassed.value) return

    form.transform(data => ({
        ...data,
        height_inches: Number(data.feet) * 12 + Number(data.inches),
    }))
        .post('/register')
}
</script>

<template>
    <div :class="cn('flex flex-col gap-6', props.class)">
        <form action="/register" method="post" @submit.prevent="submit">
            <FieldGroup>
                <div class="flex flex-col items-center gap-2 text-center">
                    <a href="/" class="flex flex-col items-center gap-2 font-medium">
                        <div class="flex size-8 items-center justify-center rounded-md">
                            <AppLogoIcon class="size-6" />
                        </div>
                        <span class="sr-only">Nourishr</span>
                    </a>
                    <h1 class="text-xl font-bold">
                        Welcome to Nourishr
                    </h1>
                    <FieldDescription>
                        Already have an account? <a href="/login">Sign in</a>
                    </FieldDescription>
                </div>
                <Field>
                    <FieldLabel for="name">
                        Full Name
                    </FieldLabel>
                    <Input v-model="form.name" @blur="markTouched('name')" id="name" name="name" type="text"
                        :class="{ 'border-red-500': nameError || form.errors.name }" />
                    <div v-if="nameError || form.errors.name" class="text-red-500 text-sm">
                        {{ nameError || form.errors.name }}
                    </div>
                </Field>
                <Field>
                    <FieldLabel for="email">
                        Email
                    </FieldLabel>
                    <Input v-model="form.email" @blur="markTouched('email')" id="email" name="email" type="email"
                        :class="{ 'border-red-500': emailError || form.errors.email }" />
                    <div v-if="emailError || form.errors.email" class="text-red-500 text-sm">
                        {{ emailError || form.errors.email }}
                    </div>
                </Field>
                <Field>
                    <Field class="grid grid-cols-2 gap-4">
                        <Field>
                            <FieldLabel for="gender">
                                Gender
                            </FieldLabel>
                            <Select v-model="form.gender" @blur="markTouched('gender')">
                                <SelectTrigger id="gender" name="gender" :class="cn(
                                    'w-full dark:bg-input/30',
                                    (genderError || form.errors.gender) && 'border-red-500 ring-red-500'
                                )">
                                    <SelectValue placeholder="Select your gender" />
                                </SelectTrigger>
                                <SelectContent class="w-[185px]">
                                    <SelectGroup>
                                        <SelectLabel>Gender</SelectLabel>
                                        <SelectItem value="male">Male</SelectItem>
                                        <SelectItem value="female">Female</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>
                            <div v-if="genderError || form.errors.gender" class="text-red-500 text-sm">
                                {{ genderError || form.errors.gender }}
                            </div>
                        </Field>
                        <Field class="flex flex-col">
                            <FieldLabel for="age">
                                Age
                            </FieldLabel>
                            <Input v-model="form.age" @blur="markTouched('age')" id="age" name="age" step="1"
                                type="number" class="h-10" :class="{ 'border-red-500': ageError || form.errors.age }" />
                            <div v-if="ageError || form.errors.age" class="text-red-500 text-sm">
                                {{ ageError || form.errors.age }}
                            </div>
                        </Field>
                    </Field>
                </Field>
                <Field>
                    <FieldLegend>Height</FieldLegend>
                    <Field class="grid grid-cols-2 gap-4">
                        <Field class="flex flex-col">
                            <FieldLabel for="feet">
                                Feet
                            </FieldLabel>
                            <InputGroup :class="{ 'border-red-500': feetError || form.errors.feet }" class="h-10">
                                <InputGroupInput v-model="form.feet" @blur="markTouched('feet')" id="feet" name="feet"
                                    type="number" />
                                <InputGroupAddon align="inline-end">
                                    <InputGroupText>ft</InputGroupText>
                                </InputGroupAddon>
                            </InputGroup>
                            <div v-if="feetError || form.errors.feet" class="text-red-500 text-sm">
                                {{ feetError || form.errors.feet }}
                            </div>
                        </Field>
                        <Field class="flex flex-col">
                            <FieldLabel for="inches">
                                Inches
                            </FieldLabel>
                            <InputGroup :class="{ 'border-red-500': inchesError || form.errors.inches }" class="h-10">
                                <InputGroupInput v-model="form.inches" @blur="markTouched('inches')" id="inches"
                                    name="inches" type="number" />
                                <InputGroupAddon align="inline-end">
                                    <InputGroupText>in</InputGroupText>
                                </InputGroupAddon>
                            </InputGroup>
                            <div v-if="inchesError || form.errors.inches" class="text-red-500 text-sm">
                                {{ inchesError || form.errors.inches }}
                            </div>
                        </Field>
                    </Field>
                </Field>
                <Field>
                    <FieldLabel for="password">
                        Password
                    </FieldLabel>
                    <Input v-model="form.password" @blur="markTouched('password')" id="password" name="password"
                        type="password" :class="{ 'border-red-500': passwordError || form.errors.password }" />
                    <div v-if="passwordError || form.errors.password" class="text-red-500 text-sm">
                        {{ passwordError || form.errors.password }}
                    </div>
                    <div v-if="form.password"
                        class="flex flex-col gap-1 text-base mt-2 border border-muted-foreground/30 px-3 space-y-0.5 md:text-sm py-1 rounded-md">
                        <div class="flex gap-x-2"
                            :class="passwordHasMinLength ? 'text-green-600' : 'text-muted-foreground'">
                            <div>{{ passwordHasMinLength ? '✓' : '•' }}</div>
                            <div>At least 10 characters</div>
                        </div>
                        <div class="flex gap-x-2"
                            :class="passwordHasNumber ? 'text-green-600' : 'text-muted-foreground'">
                            <div>{{ passwordHasNumber ? '✓' : '•' }}</div>
                            <div>Contains a number</div>
                        </div>
                        <div class="flex gap-x-2"
                            :class="passwordHasSpecialChar ? 'text-green-600' : 'text-muted-foreground'">
                            <div>{{ passwordHasSpecialChar ? '✓' : '•' }}</div>
                            <div>Contains a special character</div>
                        </div>
                    </div>
                </Field>
                <Field>
                    <FieldLabel for="password_confirmation">
                        Confirm Password
                    </FieldLabel>
                    <Input v-model="form.password_confirmation" @blur="markTouched('password_confirmation')"
                        id="password_confirmation" name="password_confirmation" type="password"
                        :class="{ 'border-red-500': passwordConfirmationError || form.errors.password_confirmation }" />
                    <div v-if="passwordConfirmationError || form.errors.password_confirmation"
                        class="text-red-500 text-sm">
                        {{ passwordConfirmationError || form.errors.password_confirmation }}
                    </div>
                    <div v-if="form.password_confirmation"
                        class="flex flex-col gap-1 text-base mt-2 border border-muted-foreground/30 px-3 space-y-0.5 md:text-sm py-1 rounded-md">
                        <div :class="passwordsMatch ? 'text-green-600' : 'text-muted-foreground'" class="flex gap-x-2">
                            <div>{{ passwordsMatch ? '✓' : '•' }}</div>
                            <div>Passwords match</div>
                        </div>
                    </div>
                </Field>
                <Field>
                    <Button :disabled="form.processing || !allFieldsFilled || hasClientErrors || !passwordRulesPassed"
                        type="submit">
                        <Spinner v-if="form.processing" class="mr-2" />
                        Create Account
                    </Button>
                </Field>
                <FieldSeparator>Or</FieldSeparator>
                <FieldGroup class="grid gap-4">
                    <Button variant="outline" type="button">
                        <a href="/auth/google/redirect" class="flex gap-x-2">
                            <Icon icon="logos:google-icon" class="w-5 h-5" />
                            Continue with Google
                        </a>
                    </Button>
                </FieldGroup>
            </FieldGroup>
        </form>
        <FieldDescription class="flex justify-center text-center space-x-4">
            <a href="#">
                Terms of Use
            </a>
            <Separator orientation="vertical" class="h-auto!" />
            <a href="#">
                Privacy Policy
            </a>
        </FieldDescription>
    </div>
</template>
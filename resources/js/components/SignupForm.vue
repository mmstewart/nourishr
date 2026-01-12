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
import { Input } from '@/components/ui/input'
import { Separator } from '@/components/ui/separator'
import { Spinner } from '@/components/ui/spinner';
import { Icon } from "@iconify/vue";

const props = defineProps<{
    class?: HTMLAttributes["class"]
}>()

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

// Touch tracking so we don't show errors before the user interacts
const touchedName = ref(false)
const touchedEmail = ref(false)
const touchedPassword = ref(false)
const touchedPasswordConfirmation = ref(false)
const attemptedSubmit = ref(false)

function markTouched(field: string) {
    if (field === 'name') touchedName.value = true
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

const hasClientErrors = computed(() => {
    return !!(nameError.value || emailError.value || passwordError.value || passwordConfirmationError.value)
})

const allFieldsFilled = computed(() => {
    return !!(form.name && form.email && form.password && form.password_confirmation)
})

function submit() {
    attemptedSubmit.value = true
    touchedName.value = true
    touchedEmail.value = true
    touchedPassword.value = true
    touchedPasswordConfirmation.value = true

    if (hasClientErrors.value) {
        return
    }

    form.post('/register')
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
                    <Button :disabled="form.processing || !allFieldsFilled" type="submit">
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
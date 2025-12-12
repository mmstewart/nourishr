<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { request } from '@/routes/password';
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

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
import { Icon } from "@iconify/vue"
import { Spinner } from '@/components/ui/spinner';
import TextLink from '@/components/TextLink.vue';

const props = defineProps<{
    class?: HTMLAttributes["class"];
}>()

const form = useForm({
    email: '',
    password: '',
})

// Touch tracking so we don't show errors before the user interacts
const touchedEmail = ref(false)
const touchedPassword = ref(false)
const attemptedSubmit = ref(false)

function markTouched(field: string) {
    if (field === 'email') touchedEmail.value = true
    if (field === 'password') touchedPassword.value = true
}

function validateEmail(value: string): boolean {
    // https://medium.com/@python-javascript-php-html-css/the-best-regular-expression-for-email-address-verification-42bf83ba2885
    return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value)
}

const emailError = computed(() => {
    if (!form.email && !validateEmail(form.email) && (touchedEmail.value || attemptedSubmit.value)) return 'Email is required.'
    if (form.email && !validateEmail(form.email) && (touchedEmail.value || attemptedSubmit.value)) return 'Email is not valid.'
    return ''
})

const passwordError = computed(() => {
    if (!form.password && (touchedPassword.value || attemptedSubmit.value)) return 'Password is required.'
    return ''
})

const hasClientErrors = computed(() => {
    return !!(emailError.value || passwordError.value)
})

const allFieldsFilled = computed(() => {
    return !!(form.email && form.password)
})

function submit() {
    attemptedSubmit.value = true
    touchedEmail.value = true
    touchedPassword.value = true

    if (hasClientErrors.value) {
        return
    }

    form.post('/login', {
        preserveState: true,
    })
}
</script>

<template>
    <div :class="cn('flex flex-col gap-6', props.class)">
        <form action="/login" method="post" @submit.prevent="submit">
            <FieldGroup>
                <div class="flex flex-col items-center gap-2 text-center">
                    <span class="sr-only">Login to Nourishr</span>
                    <h1 class="text-xl font-bold">
                        Welcome back
                    </h1>
                    <FieldDescription>
                        Don't have an account?
                        <a href="/register">
                            Sign up
                        </a>
                    </FieldDescription>
                </div>
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
                    <div class="flex items-center">
                        <FieldLabel for="password">
                            Password
                        </FieldLabel>
                        <TextLink :href="request()" class="ml-auto inline-block text-sm no-underline! hover:underline!">
                            Forgot your password?
                        </TextLink>
                    </div>
                    <Input v-model="form.password" @blur="markTouched('password')" id="password" name="password"
                        type="password" :class="{ 'border-red-500': passwordError || form.errors.password }" />
                    <div v-if="passwordError || form.errors.password" class="text-red-500 text-sm">
                        {{ passwordError || form.errors.password }}
                    </div>
                </Field>
                <Field>
                    <Button :disabled="form.processing || !allFieldsFilled" type="submit">
                        <Spinner v-if="form.processing" class="mr-2" />
                        Login
                    </Button>
                </Field>
                <FieldSeparator>Or</FieldSeparator>
                <Button variant="outline" type="button">
                    <a href="/auth/google/redirect" class="flex gap-x-2">
                        <Icon icon="logos:google-icon" class="w-5 h-5" />
                        Continue with Google
                    </a>
                </Button>
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
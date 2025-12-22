<script setup lang="ts">
import type { HTMLAttributes } from "vue"

import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button';
import {
    Field,
    FieldDescription,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field'
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';

const props = defineProps<{
    class?: HTMLAttributes["class"];
}>()

const form = useForm({
    email: '',
})

// Touch tracking so we don't show errors before the user interacts
const touchedEmail = ref(false)
const attemptedSubmit = ref(false)

function markTouched(field: string) {
    if (field === 'email') touchedEmail.value = true
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

const hasClientErrors = computed(() => {
    return !!emailError.value
})

const allFieldsFilled = computed(() => {
    return !!form.email
})

function submit() {
    attemptedSubmit.value = true
    touchedEmail.value = true

    if (hasClientErrors.value) {
        return
    }

    form.post('/forgot-password', {
        preserveState: true,
    })
}
</script>

<template>
    <div class="flex min-h-svh w-full items-center justify-center p-6 md:p-10">
        <div class="w-full max-w-sm">

            <Head title="Forgot password" />
            <div :class="cn('flex flex-col gap-6', props.class)">
                <form action="/login" method="post" @submit.prevent="submit">
                    <FieldGroup>
                        <div class="flex flex-col items-center gap-2 text-center">
                            <span class="sr-only">Nourishr - Forgot password</span>
                            <h1 class="text-xl font-bold">
                                Forgot password
                            </h1>
                            <FieldDescription>
                                Enter your email to receive a password reset link
                            </FieldDescription>
                        </div>
                        <Field>
                            <FieldLabel for="email">
                                Email
                            </FieldLabel>
                            <Input v-model="form.email" @blur="markTouched('email')" id="email" name="email"
                                type="email" :class="{ 'border-red-500': emailError || form.errors.email }" />
                            <div v-if="emailError || form.errors.email" class="text-red-500 text-sm">
                                {{ emailError || form.errors.email }}
                            </div>
                        </Field>
                        <Field>
                            <Button :disabled="form.processing || !allFieldsFilled" type="submit">
                                <Spinner v-if="form.processing" class="mr-2" />
                                Send reset link
                            </Button>
                        </Field>
                    </FieldGroup>
                </form>
                <FieldDescription class="text-center">
                    <span>Or, return to</span>
                    <a href="/login">
                        sign in
                    </a>
                </FieldDescription>
            </div>
        </div>
    </div>
</template>

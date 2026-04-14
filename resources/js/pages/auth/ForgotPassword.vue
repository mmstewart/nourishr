<script setup lang="ts">
import { ref, computed, type HTMLAttributes } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
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

function validateEmail(value: string): boolean {
    // https://medium.com/@python-javascript-php-html-css/the-best-regular-expression-for-email-address-verification-42bf83ba2885
    return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value)
}

const emailError = computed(() => {
    if (!form.email && !validateEmail(form.email) && touchedEmail.value) return 'Email is required.'
    if (form.email && !validateEmail(form.email) && touchedEmail.value) return 'Email is not valid.'
    return ''
})

function submit() {
    touchedEmail.value = true

    if (emailError.value) return

    form.post('/forgot-password', {
        preserveScroll: true,
        onSuccess: () => {
            touchedEmail.value = false

            form.reset('email')
        },
    })
}
</script>

<template>
    <div class="bg-gray-100 flex min-h-svh w-full items-center justify-center p-6 md:p-10">
        <div class="w-full max-w-sm">

            <Head title="Forgot password" />
            <div :class="cn('flex flex-col gap-6', props.class)">
                <form @submit.prevent="submit">
                    <FieldGroup>
                        <div class="flex flex-col items-center gap-2 text-center">
                            <span class="sr-only">Forgot password</span>
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
                            <Input v-model="form.email" @blur="touchedEmail = true" id="email" name="email" type="email"
                                :class="{ 'border-red-500': emailError || form.errors.email }" />
                            <div v-if="emailError || form.errors.email" class="text-red-500 text-sm">
                                {{ emailError || form.errors.email }}
                            </div>
                        </Field>
                        <Field>
                            <Button :disabled="form.processing || !form.email || !!emailError" type="submit"
                                class="bg-green-500 hover:bg-green-400 dark:text-white">
                                <Spinner v-if="form.processing" class="mr-2" />
                                Send reset link
                            </Button>
                        </Field>
                    </FieldGroup>
                </form>
                <FieldDescription class="text-center">
                    <span>Return to </span>
                    <a href="/login">
                        sign in
                    </a>
                </FieldDescription>
            </div>
        </div>
    </div>
</template>

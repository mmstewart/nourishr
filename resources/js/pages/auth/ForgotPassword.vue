<script setup lang="ts">
import { cn } from '@/lib/utils';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, type HTMLAttributes } from 'vue';

import { Button } from '@/components/ui/button';
import { Field, FieldDescription, FieldGroup, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { Check } from 'lucide-vue-next';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();

const form = useForm({
    email: '',
});

const wasSent = ref(false);
// Touch tracking so we don't show errors before the user interacts
const touchedEmail = ref(false);

function validateEmail(value: string): boolean {
    // https://medium.com/@python-javascript-php-html-css/the-best-regular-expression-for-email-address-verification-42bf83ba2885
    return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value);
}

const emailError = computed(() => {
    if (!form.email && !validateEmail(form.email) && touchedEmail.value) return 'Email is required.';
    if (form.email && !validateEmail(form.email) && touchedEmail.value) return 'Email is not valid.';
    return '';
});

function submit() {
    touchedEmail.value = true;

    if (emailError.value) return;

    form.post('/forgot-password', {
        preserveScroll: true,
        onSuccess: () => {
            touchedEmail.value = false;
            wasSent.value = true;

            form.reset('email');
        },
    });
}
</script>

<template>
    <div class="flex min-h-svh w-full items-center justify-center bg-gray-100 p-6 md:p-10">
        <div class="w-full max-w-sm">
            <Head title="Forgot password" />
            <div v-if="wasSent" :class="cn('flex flex-col gap-7 text-center')">
                <div class="flex flex-col items-center gap-2 text-center">
                    <div class="flex justify-center">
                        <div class="rounded-full bg-green-100 p-3">
                            <Check class="size-6 text-green-500" />
                        </div>
                    </div>
                    <h1 class="text-xl font-bold">Check your email</h1>
                    <p class="text-sm text-muted-foreground">
                        We've sent a password reset link to your email address. It should arrive in a few minutes.
                    </p>
                </div>
                <Button @click="wasSent = false" variant="outline" class="cursor-pointer">
                    Didn't get the email? Try again
                </Button>
            </div>
            <div v-else :class="cn('flex flex-col gap-6', props.class)">
                <form @submit.prevent="submit">
                    <FieldGroup>
                        <div class="flex flex-col items-center gap-2 text-center">
                            <span class="sr-only">Forgot password</span>
                            <h1 class="text-xl font-bold">Forgot password</h1>
                            <FieldDescription>Enter your email to receive a password reset link</FieldDescription>
                        </div>
                        <Field>
                            <FieldLabel for="email">Email</FieldLabel>
                            <Input
                                v-model="form.email"
                                @blur="touchedEmail = true"
                                id="email"
                                name="email"
                                type="email"
                                :class="{ 'border-red-500': emailError || form.errors.email }" />
                            <div v-if="emailError || form.errors.email" class="text-sm text-red-500">
                                {{ emailError || form.errors.email }}
                            </div>
                        </Field>
                        <Field>
                            <Button
                                :disabled="form.processing || !form.email || !!emailError"
                                type="submit"
                                class="cursor-pointer bg-green-500 hover:bg-green-400 dark:text-white">
                                <Spinner v-if="form.processing" class="mr-2" />
                                Send reset link
                            </Button>
                        </Field>
                    </FieldGroup>
                </form>
                <FieldDescription class="text-center">
                    <span>Return to</span>
                    <a href="/login">sign in</a>
                </FieldDescription>
            </div>
        </div>
    </div>
</template>

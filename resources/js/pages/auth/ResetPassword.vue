<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Field, FieldDescription, FieldGroup, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { cn } from '@/lib/utils';
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref, type HTMLAttributes } from 'vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
    token: string;
    email: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const touchedPassword = ref(false);
const touchedPasswordConfirmation = ref(false);

const passwordsMatch = computed(() => form.password === form.password_confirmation);
const passwordHasMinLength = (val: string) => val.length >= 10;
const passwordHasNumber = (val: string) => /\d/.test(val);
const passwordHasSpecialChar = (val: string) => /[^A-Za-z0-9\s]/.test(val);

const passwordError = computed(() => {
    if (!form.password && touchedPassword.value) return 'Password is required.';
    return '';
});

const passwordConfirmationError = computed(() => {
    if (!form.password_confirmation && touchedPasswordConfirmation.value) return 'Please confirm your password.';
    if (form.password_confirmation && !passwordsMatch.value && touchedPasswordConfirmation.value)
        return 'Passwords do not match.';
    return '';
});

const hasClientErrors = computed(() => !!passwordError.value || !!passwordConfirmationError.value);

function submit() {
    touchedPassword.value = true;
    touchedPasswordConfirmation.value = true;

    if (hasClientErrors.value) return;

    form.post('/reset-password', {
        preserveScroll: true,
        onSuccess: () => {
            touchedPassword.value = false;
            touchedPasswordConfirmation.value = false;

            form.reset('password', 'password_confirmation');
        },
    });
}
</script>

<template>
    <div class="flex min-h-svh w-full items-center justify-center bg-gray-100 p-6 md:p-10">
        <div class="w-full max-w-sm">
            <Head title="Reset password" />
            <div :class="cn('flex flex-col gap-6', props.class)">
                <form @submit.prevent="submit">
                    <FieldGroup>
                        <div class="flex flex-col items-center gap-2 text-center">
                            <span class="sr-only">Reset password</span>
                            <h1 class="text-xl font-bold">Reset password</h1>
                            <FieldDescription>Please enter your new password below</FieldDescription>
                        </div>
                        <Field>
                            <FieldLabel for="email">Email</FieldLabel>
                            <Input id="email" v-model="form.email" type="email" name="email" disabled />
                        </Field>
                        <Field>
                            <FieldLabel for="password">Password</FieldLabel>
                            <Input
                                v-model="form.password"
                                @blur="touchedPassword = true"
                                id="password"
                                name="password"
                                type="password"
                                :class="{ 'border-red-500': passwordError || form.errors.password }" />
                            <div v-if="passwordError || form.errors.password" class="text-sm text-red-500">
                                {{ passwordError || form.errors.password }}
                            </div>
                            <transition
                                enter-active-class="transition duration-200 ease-out"
                                enter-from-class="transform -translate-y-2 opacity-0"
                                enter-to-class="transform translate-y-0 opacity-100"
                                leave-active-class="transition duration-150 ease-in"
                                leave-from-class="opacity-100"
                                leave-to-class="opacity-0">
                                <div
                                    v-if="form.password"
                                    class="mt-2 flex flex-col gap-1 space-y-0.5 rounded-md border border-muted-foreground/30 px-3 py-1 text-base md:text-sm">
                                    <div
                                        class="flex gap-x-2"
                                        :class="
                                            passwordHasMinLength(form.password)
                                                ? 'text-green-600'
                                                : 'text-muted-foreground'
                                        ">
                                        {{ passwordHasMinLength(form.password) ? '✓' : '•' }} At least 10 characters
                                    </div>
                                    <div
                                        class="flex gap-x-2"
                                        :class="
                                            passwordHasNumber(form.password)
                                                ? 'text-green-600'
                                                : 'text-muted-foreground'
                                        ">
                                        {{ passwordHasNumber(form.password) ? '✓' : '•' }} Contains a number
                                    </div>
                                    <div
                                        class="flex gap-x-2"
                                        :class="
                                            passwordHasSpecialChar(form.password)
                                                ? 'text-green-600'
                                                : 'text-muted-foreground'
                                        ">
                                        {{ passwordHasSpecialChar(form.password) ? '✓' : '•' }} Contains a special
                                        character
                                    </div>
                                </div>
                            </transition>
                        </Field>
                        <Field>
                            <FieldLabel for="password_confirmation">Confirm Password</FieldLabel>
                            <Input
                                v-model="form.password_confirmation"
                                @blur="touchedPasswordConfirmation = true"
                                id="password_confirmation"
                                name="password_confirmation"
                                type="password"
                                :class="{
                                    'border-red-500': passwordConfirmationError || form.errors.password_confirmation,
                                }" />
                            <div
                                v-if="passwordConfirmationError || form.errors.password_confirmation"
                                class="text-sm text-red-500">
                                {{ passwordConfirmationError || form.errors.password_confirmation }}
                            </div>
                            <transition
                                enter-active-class="transition duration-200 ease-out"
                                enter-from-class="transform -translate-y-2 opacity-0"
                                enter-to-class="transform translate-y-0 opacity-100"
                                leave-active-class="transition duration-150 ease-in"
                                leave-from-class="opacity-100"
                                leave-to-class="opacity-0">
                                <div
                                    v-if="form.password_confirmation"
                                    class="mt-2 flex flex-col gap-1 space-y-0.5 rounded-md border border-muted-foreground/30 px-3 py-1 text-base md:text-sm">
                                    <div
                                        class="flex gap-x-2"
                                        :class="passwordsMatch ? 'text-green-600' : 'text-muted-foreground'">
                                        {{ passwordsMatch ? '✓' : '•' }} Passwords match
                                    </div>
                                </div>
                            </transition>
                        </Field>
                        <Field>
                            <Button
                                :disabled="form.processing || hasClientErrors"
                                type="submit"
                                class="cursor-pointer bg-green-500 hover:bg-green-400 dark:text-white">
                                <Spinner v-if="form.processing" class="mr-2" />
                                Reset password
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

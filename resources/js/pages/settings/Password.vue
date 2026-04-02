<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/user-password';
import { Head } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button'
import {
    Field,
    FieldDescription,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import { Spinner } from '@/components/ui/spinner';
import { type BreadcrumbItem } from '@/types';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Password settings',
        href: edit().url,
    },
];

defineProps<{
    class?: HTMLAttributes["class"]
}>()

const form = useForm({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
})

// Touch tracking so we don't show errors before the user interacts
const touchedCurrentPassword = ref(false)
const touchedNewPassword = ref(false)
const touchedNewPasswordConfirmation = ref(false)
const attemptedSubmit = ref(false)

function markTouched(field: string) {
    if (field === 'current_password') touchedCurrentPassword.value = true
    if (field === 'new_password') touchedNewPassword.value = true
    if (field === 'new_password_confirmation') touchedNewPasswordConfirmation.value = true
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

const newPasswordHasNumber = computed(() => hasNumber(form.new_password))
const newPasswordHasSpecialChar = computed(() => hasSpecialCharacter(form.new_password))
const newPasswordHasMinLength = computed(() => hasMinimumLength(form.new_password))
const newPasswordsMatch = computed(() => form.new_password === form.new_password_confirmation)

const currentPasswordError = computed(() => {
    if (!form.current_password && (touchedCurrentPassword.value || attemptedSubmit.value)) return 'Password is required.'
    return ''
})

const newPasswordError = computed(() => {
    if (!form.new_password && (touchedNewPassword.value || attemptedSubmit.value)) return 'Password is required.'
    return ''
})

const newPasswordConfirmationError = computed(() => {
    if (!form.new_password_confirmation && (touchedNewPasswordConfirmation.value || attemptedSubmit.value)) return 'Please confirm your password.'
    if (form.new_password_confirmation && form.new_password_confirmation !== form.new_password && (touchedNewPasswordConfirmation.value || attemptedSubmit.value)) return 'Passwords do not match.'
    return ''
})

const newPasswordRulesPassed = computed(() => {
    return (
        newPasswordHasMinLength.value &&
        newPasswordHasNumber.value &&
        newPasswordHasSpecialChar.value &&
        newPasswordsMatch.value
    )
})

const hasClientErrors = computed(() => {
    return !!(currentPasswordError.value || newPasswordError.value || newPasswordConfirmationError.value || !newPasswordRulesPassed.value)
})

const allFieldsFilled = computed(() => {
    return !!(form.current_password && form.new_password && form.new_password_confirmation)
})


function submit() {
    attemptedSubmit.value = true
    touchedCurrentPassword.value = true
    touchedNewPassword.value = true
    touchedNewPasswordConfirmation.value = true

    if (hasClientErrors.value || !newPasswordRulesPassed.value) return

    form.put('/settings/password', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('current_password', 'new_password', 'new_password_confirmation');
        },
    })
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Password settings" />
        <SettingsLayout>
            <div class="space-y-6">
                <form action="/settings/password" method="put" @submit.prevent="submit">
                    <FieldGroup>
                        <div class="flex flex-col items-start text-left">
                            <span class="sr-only">Update password</span>
                            <h3 class="text-base font-medium mb-0.5">
                                Update password
                            </h3>
                            <FieldDescription>
                                Ensure your account stays protected with a strong, unique password
                            </FieldDescription>
                        </div>
                        <Field>
                            <FieldLabel for="current_password">
                                Current Password
                            </FieldLabel>
                            <Input v-model="form.current_password" @blur="markTouched('current_password')"
                                id="current_password" name="current_password" type="password"
                                :class="{ 'border-red-500': currentPasswordError || form.errors.current_password }" />
                            <div v-if="currentPasswordError || form.errors.current_password"
                                class="text-red-500 text-sm">
                                {{ currentPasswordError || form.errors.current_password }}
                            </div>
                        </Field>
                        <Field>
                            <FieldLabel for="new_password">
                                New Password
                            </FieldLabel>
                            <Input v-model="form.new_password" @blur="markTouched('new_password')" id="new_password"
                                name="new_password" type="password"
                                :class="{ 'border-red-500': newPasswordError || form.errors.new_password }" />
                            <div v-if="newPasswordError || form.errors.new_password" class="text-red-500 text-sm">
                                {{ newPasswordError || form.errors.new_password }}
                            </div>
                            <div v-if="form.new_password"
                                class="flex flex-col gap-1 text-base mt-2 border border-muted-foreground/30 px-3 space-y-0.5 md:text-sm py-1 rounded-md">
                                <div class="flex gap-x-2"
                                    :class="newPasswordHasMinLength ? 'text-green-600' : 'text-muted-foreground'">
                                    <div>{{ newPasswordHasMinLength ? '✓' : '•' }}</div>
                                    <div>At least 10 characters</div>
                                </div>
                                <div class="flex gap-x-2"
                                    :class="newPasswordHasNumber ? 'text-green-600' : 'text-muted-foreground'">
                                    <div>{{ newPasswordHasNumber ? '✓' : '•' }}</div>
                                    <div>Contains a number</div>
                                </div>
                                <div class="flex gap-x-2"
                                    :class="newPasswordHasSpecialChar ? 'text-green-600' : 'text-muted-foreground'">
                                    <div>{{ newPasswordHasSpecialChar ? '✓' : '•' }}</div>
                                    <div>Contains a special character</div>
                                </div>
                            </div>
                        </Field>
                        <Field>
                            <FieldLabel for="new_password_confirmation">
                                Confirm Password
                            </FieldLabel>
                            <Input v-model="form.new_password_confirmation"
                                @blur="markTouched('new_password_confirmation')" id="new_password_confirmation"
                                name="new_password_confirmation" type="password"
                                :class="{ 'border-red-500': newPasswordConfirmationError || form.errors.new_password_confirmation }" />
                            <div v-if="newPasswordConfirmationError || form.errors.new_password_confirmation"
                                class="text-red-500 text-sm">
                                {{ newPasswordConfirmationError || form.errors.new_password_confirmation }}
                            </div>
                            <div v-if="form.new_password_confirmation"
                                class="flex flex-col gap-1 text-base mt-2 border border-muted-foreground/30 px-3 space-y-0.5 md:text-sm py-1 rounded-md">
                                <div :class="newPasswordsMatch ? 'text-green-600' : 'text-muted-foreground'"
                                    class="flex gap-x-2">
                                    <div>{{ newPasswordsMatch ? '✓' : '•' }}</div>
                                    <div>Passwords match</div>
                                </div>
                            </div>
                        </Field>
                        <Field class="flex items-center gap-4">
                            <Button
                                :disabled="form.processing || !allFieldsFilled || hasClientErrors || !newPasswordRulesPassed"
                                type="submit"
                                class="inline-flex items-start w-auto! self-start bg-green-500 hover:bg-green-400">
                                <Spinner v-if="form.processing" class="mr-2" />
                                Save
                            </Button>
                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">
                                    Password updated.
                                </p>
                            </Transition>
                        </Field>
                    </FieldGroup>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

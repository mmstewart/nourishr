<script setup lang="ts">
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

import { Button } from '@/components/ui/button'
import {
    Field,
    FieldDescription,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import { Spinner } from '@/components/ui/spinner';

import DeleteUser from '@/components/DeleteUser.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
})

// Touch tracking so we don't show errors before the user interacts
const touchedName = ref(false)
const touchedEmail = ref(false)
const attemptedSubmit = ref(false)

function markTouched(field: string) {
    if (field === 'name') touchedName.value = true
    if (field === 'email') touchedEmail.value = true
}

function validateEmail(value: string): boolean {
    // https://medium.com/@python-javascript-php-html-css/the-best-regular-expression-for-email-address-verification-42bf83ba2885
    return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value)
}

const nameError = computed(() => {
    if (!form.name && (touchedName.value || attemptedSubmit.value)) return 'Name is required.'
    return ''
})

const emailError = computed(() => {
    if (!form.email && !validateEmail(form.email) && (touchedEmail.value || attemptedSubmit.value)) return 'Email is required.'
    if (form.email && !validateEmail(form.email) && (touchedEmail.value || attemptedSubmit.value)) return 'Email is not valid.'
    return ''
})

const hasClientErrors = computed(() => {
    return !!(nameError.value || emailError.value)
})

const allFieldsFilled = computed(() => {
    return !!(form.name && form.email)
})

function submit() {
    attemptedSubmit.value = true
    touchedName.value = true
    touchedEmail.value = true

    if (hasClientErrors.value) {
        return
    }

    form.put('/settings/profile')
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <form action="/settings/profile" method="put" @submit.prevent="submit">
                    <FieldGroup>
                        <div class="flex flex-col items-start text-left">
                            <span class="sr-only">Profile information</span>
                            <h3 class="text-base font-medium mb-0.5">
                                Profile information
                            </h3>
                            <FieldDescription>
                                Update your name and email address
                            </FieldDescription>
                        </div>
                        <Field>
                            <FieldLabel for="name">
                                Name
                            </FieldLabel>
                            <Input v-model="form.name" @blur="markTouched('name')" id="name" name="name" type=""
                                :class="{ 'border-red-500': nameError || form.errors.name }" />
                            <div v-if="nameError || form.errors.email" class="text-red-500 text-sm">
                                {{ nameError || form.errors.name }}
                            </div>
                        </Field>
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
                        <!-- todo -->
                        <FieldDescription v-if="mustVerifyEmail && !user.email_verified_at">
                            <p class="-mt-4 text-sm text-muted-foreground">
                                Your email address is unverified.
                                <Link :href="send()" as="button"
                                    class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500">
                                    Click here to resend the verification email.
                                </Link>
                            </p>
                            <div v-if="status === 'verification-link-sent'"
                                class="mt-2 text-sm font-medium text-green-600">
                                A new verification link has been sent to your email
                                address.
                            </div>
                        </FieldDescription>
                        <Field class="flex items-center gap-4">
                            <Button :disabled="form.processing || !allFieldsFilled" type="submit"
                                class="inline-flex items-start w-auto! self-start">
                                <Spinner v-if="form.processing" class="mr-2" />
                                Save
                            </Button>
                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">
                                    Saved.
                                </p>
                            </Transition>
                        </Field>
                    </FieldGroup>
                </form>
            </div>
            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>

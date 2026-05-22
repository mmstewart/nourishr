<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, type HTMLAttributes } from 'vue';

import { Button } from '@/components/ui/button';
import { Field, FieldDescription, FieldGroup, FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Feedback',
        href: '/feedback',
    },
];

defineProps<{
    class?: HTMLAttributes['class'];
}>();

const page = usePage();
const user = page.props.auth.user;

const form = useForm({
    name: user?.name || '',
    email: user?.email || '',
    message: '',
    phone_number: '',
});

// Touch tracking so we don't show errors before the user interacts
const touchedName = ref(false);
const touchedEmail = ref(false);
const touchedMessage = ref(false);

const nameError = computed(() => {
    if (!form.name && touchedName.value) return 'Name is required.';
    return '';
});

function validateEmail(value: string): boolean {
    // https://medium.com/@python-javascript-php-html-css/the-best-regular-expression-for-email-address-verification-42bf83ba2885
    return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value);
}

const emailError = computed(() => {
    if (!form.email && !validateEmail(form.email) && touchedEmail.value) return 'Email is required.';
    if (form.email && !validateEmail(form.email) && touchedEmail.value) return 'Email is not valid.';
    return '';
});

const messageError = computed(() => {
    if (!form.message && touchedMessage.value) return 'Message is required.';
    return '';
});

const hasClientErrors = computed(() => {
    return !!(nameError.value || emailError.value || messageError.value);
});

function submit() {
    touchedName.value = true;
    touchedEmail.value = true;
    touchedMessage.value = true;

    if (hasClientErrors.value) return;

    form.post('/feedback', {
        onSuccess: () => {
            touchedName.value = false;
            touchedEmail.value = false;
            touchedMessage.value = false;

            form.reset();
        },
        onError: (errors) => {
            if (!errors.name && !errors.email && !errors.message) {
                form.setError('message', "Slow down! You're sending messages too fast.");
            }
        },
    });
}
</script>

<template>
    <Head title="Feedback" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 md:max-w-2xl">
            <section class="max-w-xl space-y-12">
                <div class="flex flex-1 flex-col gap-4 p-4">
                    <div class="space-y-6">
                        <form @submit.prevent="submit">
                            <input
                                v-model="form.phone_number"
                                type="text"
                                class="hidden"
                                tabindex="-1"
                                autocomplete="off" />
                            <FieldGroup>
                                <div class="flex flex-col items-start text-left">
                                    <span class="sr-only">Feedback</span>
                                    <h3 class="mb-0.5 text-base font-medium">Feedback</h3>
                                    <FieldDescription>
                                        Share your feedback with us to help improve Nourishr.
                                    </FieldDescription>
                                </div>
                                <Field>
                                    <FieldLabel for="name">Name</FieldLabel>
                                    <Input
                                        v-model="form.name"
                                        @blur="touchedName = true"
                                        id="name"
                                        name="name"
                                        type="text"
                                        :disabled="!!user?.name"
                                        :class="{ 'border-red-500': nameError || form.errors.name }" />
                                    <div v-if="nameError || form.errors.name" class="text-sm text-red-500">
                                        {{ nameError || form.errors.name }}
                                    </div>
                                </Field>
                                <Field>
                                    <FieldLabel for="email">Email</FieldLabel>
                                    <Input
                                        v-model="form.email"
                                        @blur="touchedEmail = true"
                                        id="email"
                                        name="email"
                                        type="email"
                                        :disabled="!!user?.email"
                                        :class="{ 'border-red-500': emailError || form.errors.email }" />
                                    <div v-if="emailError || form.errors.email" class="text-sm text-red-500">
                                        {{ emailError || form.errors.email }}
                                    </div>
                                </Field>
                                <Field>
                                    <FieldLabel for="message">Message</FieldLabel>
                                    <Textarea
                                        v-model="form.message"
                                        @blur="touchedMessage = true"
                                        id="message"
                                        name="message"
                                        :class="[
                                            'min-h-40',
                                            { 'border-red-500': messageError || form.errors.message },
                                        ]" />
                                    <div v-if="messageError || form.errors.message" class="text-sm text-red-500">
                                        {{ messageError || form.errors.message }}
                                    </div>
                                </Field>
                                <Field class="flex items-center gap-4">
                                    <Button
                                        :disabled="form.processing || hasClientErrors"
                                        type="submit"
                                        class="inline-flex w-auto! cursor-pointer items-start self-start bg-green-500 hover:bg-green-400 dark:text-white">
                                        <Spinner v-if="form.processing" class="mr-2" />
                                        Submit
                                    </Button>
                                </Field>
                            </FieldGroup>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

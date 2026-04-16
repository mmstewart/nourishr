<script setup lang="ts">
import { ref, computed, type HTMLAttributes } from 'vue'
import { Head, useForm } from '@inertiajs/vue3';

import {
    Field,
    FieldDescription,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Button } from '@/components/ui/button'
import { Spinner } from '@/components/ui/spinner'

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Contact',
        href: '/contact',
    },
];

defineProps<{
    class?: HTMLAttributes["class"];
}>()

const form = useForm({
    name: '',
    email: '',
    message: '',
    company: '',
})

// Touch tracking so we don't show errors before the user interacts
const touchedName = ref(false)
const touchedEmail = ref(false)
const touchedMessage = ref(false)

const nameError = computed(() => {
    if (!form.name && touchedName.value) return 'Name is required.'
    return ''
})

function validateEmail(value: string): boolean {
    // https://medium.com/@python-javascript-php-html-css/the-best-regular-expression-for-email-address-verification-42bf83ba2885
    return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value)
}

const emailError = computed(() => {
    if (!form.email && !validateEmail(form.email) && touchedEmail.value) return 'Email is required.'
    if (form.email && !validateEmail(form.email) && touchedEmail.value) return 'Email is not valid.'
    return ''
})

const messageError = computed(() => {
    if (!form.message && touchedMessage.value) return 'Message is required.'
    return ''
})

const hasClientErrors = computed(() => {
    return !!(nameError.value || emailError.value || messageError.value)
})

function submit() {
    touchedName.value = true
    touchedEmail.value = true
    touchedMessage.value = true

    if (hasClientErrors.value) return

    form.post('/contact', {
        onSuccess: () => {
            touchedName.value = false
            touchedEmail.value = false
            touchedMessage.value = false

            form.reset()
        },
        onError: (errors) => {
            if (!errors.name && !errors.email && !errors.message) {
                form.setError('message', "Slow down! You're sending messages too fast.")
            }
        }
    })
}
</script>

<template>

    <Head title="Contact Us" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 md:max-w-2xl">
            <section class="max-w-xl space-y-12">
                <div class="flex flex-1 flex-col gap-4 p-4">
                    <div class="space-y-6">
                        <form @submit.prevent="submit">
                            <input v-model="form.company" type="text" class="hidden" tabindex="-1" autocomplete="off" />
                            <FieldGroup>
                                <div class="flex flex-col items-start text-left">
                                    <span class="sr-only">Contact</span>
                                    <h3 class="text-base font-medium mb-0.5">
                                        Contact Us
                                    </h3>
                                    <FieldDescription>
                                        Have a question or need assistance? Contact us and let’s find a solution
                                        together!
                                    </FieldDescription>
                                </div>
                                <Field>
                                    <FieldLabel for="name">
                                        Name
                                    </FieldLabel>
                                    <Input v-model="form.name" @blur="touchedName = true" id="name" name="name"
                                        type="text" :class="{ 'border-red-500': nameError || form.errors.name }" />
                                    <div v-if="nameError || form.errors.name" class="text-red-500 text-sm">
                                        {{ nameError || form.errors.name }}
                                    </div>
                                </Field>
                                <Field>
                                    <FieldLabel for="email">
                                        Email
                                    </FieldLabel>
                                    <Input v-model="form.email" @blur="touchedEmail = true" id="email" name="email"
                                        type="email" :class="{ 'border-red-500': emailError || form.errors.email }" />
                                    <div v-if="emailError || form.errors.email" class="text-red-500 text-sm">
                                        {{ emailError || form.errors.email }}
                                    </div>
                                </Field>
                                <Field>
                                    <FieldLabel for="message">
                                        Message
                                    </FieldLabel>
                                    <Textarea v-model="form.message" @blur="touchedMessage = true" id="message"
                                        name="message"
                                        :class="['min-h-40', { 'border-red-500': messageError || form.errors.message }]" />
                                    <div v-if="messageError || form.errors.message" class="text-red-500 text-sm">
                                        {{ messageError || form.errors.message }}
                                    </div>
                                </Field>
                                <Field class="flex items-center gap-4">
                                    <Button :disabled="form.processing || hasClientErrors" type="submit"
                                        class="inline-flex items-start w-auto! self-start bg-green-500 hover:bg-green-400 dark:text-white cursor-pointer">
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
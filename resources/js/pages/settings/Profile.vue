<script setup lang="ts">
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Head, Link, usePage, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue'

import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import {
    Field,
    FieldDescription,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field'
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Input } from '@/components/ui/input'
import { InputGroup, InputGroupAddon, InputGroupInput, InputGroupText } from '@/components/ui/input-group'
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

const feet = Math.floor(user.height_inches / 12);
const inches = user.height_inches % 12;

const form = useForm({
    name: user.name,
    age: user.age,
    gender: user.gender,
    feet: feet,
    inches: inches,
    email: user.email,
})

// Touch tracking so we don't show errors before the user interacts
const touchedName = ref(false)
const touchedAge = ref(false)
const touchedGender = ref(false)
const touchedFeet = ref(false)
const touchedInches = ref(false)
const touchedEmail = ref(false)
const attemptedSubmit = ref(false)

function markTouched(field: string) {
    if (field === 'name') touchedName.value = true
    if (field === 'age') touchedAge.value = true
    if (field === 'gender') touchedGender.value = true
    if (field === 'feet') touchedFeet.value = true
    if (field === 'inches') touchedInches.value = true
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

const ageError = computed(() => {
    if (!form.age && (touchedAge.value || attemptedSubmit.value)) return 'Age is required.'
    if (form.age) {
        const ageNum = Number(form.age)

        if (isNaN(ageNum) || ageNum < 13 || ageNum > 120) {
            return 'Age must be a number between 13 and 120.'
        }
    }
    return ''
})

const genderError = computed(() => {
    if (!form.gender && (touchedGender.value || attemptedSubmit.value)) return 'Gender is required.'
    return ''
})

const inchesError = computed(() => {
    if (!form.inches && (touchedInches.value || attemptedSubmit.value)) return 'Inches is required.'
    if (form.inches) {
        const inchesNum = Number(form.inches)

        if (isNaN(inchesNum) || inchesNum < 0 || inchesNum >= 12) {
            return 'Inches must be a number between 0 and 11.'
        }
    }
    return ''
})

const feetError = computed(() => {
    if (!form.feet && (touchedInches.value || attemptedSubmit.value)) return 'Inches is required.'
    if (form.feet) {
        const feetNum = Number(form.feet)

        if (isNaN(feetNum) || feetNum < 1 || feetNum > 8) {
            return 'Feet must be a number between 1 and 8.'
        }
    }
    return ''
})

const emailError = computed(() => {
    if (!form.email && !validateEmail(form.email) && (touchedEmail.value || attemptedSubmit.value)) return 'Email is required.'
    if (form.email && !validateEmail(form.email) && (touchedEmail.value || attemptedSubmit.value)) return 'Email is not valid.'
    return ''
})

const hasClientErrors = computed(() => {
    return !!(nameError.value || ageError.value || genderError.value || inchesError.value || feetError.value || emailError.value)
})

const allFieldsFilled = computed(() => {
    return !!(form.name && form.age && form.gender && form.inches && form.feet && form.email)
})

function submit() {
    attemptedSubmit.value = true
    touchedName.value = true
    touchedAge.value = true
    touchedGender.value = true
    touchedInches.value = true
    touchedFeet.value = true
    touchedEmail.value = true

    if (hasClientErrors.value) return

    form.transform(data => ({
        ...data,
        height_inches: Number(data.feet) * 12 + Number(data.inches),
    }))
        .put('/settings/profile')
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
                                Update your personal information
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
                        <Field>
                            <Field class="grid grid-cols-2 gap-4">
                                <Field>
                                    <FieldLabel for="gender">
                                        Gender
                                    </FieldLabel>
                                    <Select v-model="form.gender" @blur="markTouched('gender')">
                                        <SelectTrigger id="gender" name="gender" :class="cn(
                                            'w-full dark:bg-input/30',
                                            (genderError || form.errors.gender) && 'border-red-500 ring-red-500'
                                        )">
                                            <SelectValue placeholder="Select your gender" />
                                        </SelectTrigger>
                                        <SelectContent class="w-[185px]">
                                            <SelectGroup>
                                                <SelectLabel>Gender</SelectLabel>
                                                <SelectItem value="male">Male</SelectItem>
                                                <SelectItem value="female">Female</SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <div v-if="genderError || form.errors.gender" class="text-red-500 text-sm">
                                        {{ genderError || form.errors.gender }}
                                    </div>
                                </Field>
                                <Field class="flex flex-col">
                                    <FieldLabel for="age">
                                        Age
                                    </FieldLabel>
                                    <Input v-model="form.age" @blur="markTouched('age')" id="age" name="age" step="1"
                                        type="number" class="h-10"
                                        :class="{ 'border-red-500': ageError || form.errors.age }" />
                                    <div v-if="ageError || form.errors.age" class="text-red-500 text-sm">
                                        {{ ageError || form.errors.age }}
                                    </div>
                                </Field>
                            </Field>
                        </Field>
                        <Field>
                            <FieldLegend>Height</FieldLegend>
                            <Field class="grid grid-cols-2 gap-4">
                                <Field class="flex flex-col">
                                    <FieldLabel for="feet">
                                        Feet
                                    </FieldLabel>
                                    <InputGroup :class="{ 'border-red-500': feetError || form.errors.feet }"
                                        class="h-10">
                                        <InputGroupInput v-model="form.feet" @blur="markTouched('feet')" id="feet"
                                            name="feet" type="number" />
                                        <InputGroupAddon align="inline-end">
                                            <InputGroupText>ft</InputGroupText>
                                        </InputGroupAddon>
                                    </InputGroup>
                                    <div v-if="feetError || form.errors.feet" class="text-red-500 text-sm">
                                        {{ feetError || form.errors.feet }}
                                    </div>
                                </Field>
                                <Field class="flex flex-col">
                                    <FieldLabel for="inches">
                                        Inches
                                    </FieldLabel>
                                    <InputGroup :class="{ 'border-red-500': inchesError || form.errors.inches }"
                                        class="h-10">
                                        <InputGroupInput v-model="form.inches" @blur="markTouched('inches')" id="inches"
                                            name="inches" type="number" />
                                        <InputGroupAddon align="inline-end">
                                            <InputGroupText>in</InputGroupText>
                                        </InputGroupAddon>
                                    </InputGroup>
                                    <div v-if="inchesError || form.errors.inches" class="text-red-500 text-sm">
                                        {{ inchesError || form.errors.inches }}
                                    </div>
                                </Field>
                            </Field>
                        </Field>
                        <Field class="flex items-center gap-4">
                            <Button :disabled="form.processing || !allFieldsFilled || hasClientErrors" type="submit"
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

<script setup lang="ts">
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Head, usePage, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue'
import axios from 'axios';

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
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover'
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList
} from '@/components/ui/command'
import { Calendar } from '@/components/ui/calendar'
import { Badge } from '@/components/ui/badge'
import { ChevronsUpDown, Check, CalendarIcon } from 'lucide-vue-next';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';
import { today, getLocalTimeZone, parseDate } from '@internationalized/date'

const props = defineProps<{
    mustVerifyEmail: boolean;
    status?: string;
    countries: Record<string, string>;
}>();

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
    dob: parseDate(user.dob) as any,
    gender: user.gender,
    feet: feet,
    inches: inches,
    email: user.email,
    country_code: user.country_code,
    timezone: user.timezone,
})

// Touch tracking so we don't show errors before the user interacts
const touchedName = ref(false)
const touchedDob = ref(false)
const touchedGender = ref(false)
const touchedFeet = ref(false)
const touchedInches = ref(false)
const touchedEmail = ref(false)
const touchedCountryCode = ref(false)
const touchedTimezone = ref(false)
const attemptedSubmit = ref(false)

const timezoneOpen = ref(false)
const countryOpen = ref(false)
const timezones = ref([]);
const loadingTimezones = ref(false);

const defaultPlaceholder = today(getLocalTimeZone())

function markTouched(field: string) {
    if (field === 'name') touchedName.value = true
    if (field === 'dob') touchedDob.value = true
    if (field === 'gender') touchedGender.value = true
    if (field === 'feet') touchedFeet.value = true
    if (field === 'inches') touchedInches.value = true
    if (field === 'email') touchedEmail.value = true
    if (field === 'country_code') touchedCountryCode.value = true
    if (field === 'timezone') touchedTimezone.value = true
}

function validateEmail(value: string): boolean {
    // https://medium.com/@python-javascript-php-html-css/the-best-regular-expression-for-email-address-verification-42bf83ba2885
    return /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value)
}

const nameError = computed(() => {
    if (!form.name && (touchedName.value || attemptedSubmit.value)) return 'Name is required.'
    return ''
})

const dobError = computed(() => {
    if (!form.dob && (touchedDob.value || attemptedSubmit.value)) return 'Please select a date.'
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

const countryCodeError = computed(() => {
    if (!form.country_code && (touchedCountryCode.value || attemptedSubmit.value)) return 'Country is required.'
    return ''
})

const timezoneError = computed(() => {
    if (!form.timezone && (touchedTimezone.value || attemptedSubmit.value)) return 'Timezone is required.'
    return ''
})

const hasClientErrors = computed(() => {
    return !!(nameError.value || dobError.value || genderError.value || inchesError.value || feetError.value || emailError.value || countryCodeError.value || timezoneError.value)
})

const allFieldsFilled = computed(() => {
    return !!(form.name && form.dob && form.gender && form.inches && form.feet && form.email && form.country_code && form.timezone)
})

watch(() => form.country_code, async (countryCode, oldCountryCode) => {
    if (oldCountryCode !== undefined) {
        form.timezone = '';
    }

    if (!countryCode) {
        timezones.value = [];
        return;
    }

    loadingTimezones.value = true;

    try {
        const response = await axios.get(`/api/countries/${countryCode}/timezones`);

        timezones.value = response.data;
    } catch (error) {
        console.error("Failed to load timezones", error);
    } finally {
        loadingTimezones.value = false;
    }
}, { immediate: true });

const age = computed(() => {
    if (!form.dob) return null;

    const now = today(getLocalTimeZone());

    let calculatedAge = now.year - form.dob.year;

    const birthDayThisYear = form.dob.add({ years: calculatedAge });

    if (birthDayThisYear.compare(now) > 0) {
        calculatedAge--;
    }

    return calculatedAge;
});

const selectedCountryLabel = computed(() => {
    return props.countries[form.country_code] || 'Select a country'
})

const selectedTimezoneLabel = computed(() => {
    return form.timezone ? form.timezone : 'Select a timezone'
})

function submit() {
    attemptedSubmit.value = true

    touchedName.value = true
    touchedDob.value = true
    touchedGender.value = true
    touchedInches.value = true
    touchedFeet.value = true
    touchedEmail.value = true
    touchedCountryCode.value = true
    touchedTimezone.value = true

    if (hasClientErrors.value) return

    form.transform(data => ({
        ...data,
        dob: data.dob ? data.dob.toString() : null,
        height_inches: Number(data.feet) * 12 + Number(data.inches),
    }))
        .put('/settings/profile', {
            onSuccess: () => {
                attemptedSubmit.value = false
            }
        });
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
                                Manage your personal details, physical metrics, and regional settings
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
                        <FieldDescription v-if="mustVerifyEmail && !user.email_verified_at">
                            <p class="-mt-4 text-sm text-muted-foreground">
                                Your email address is unverified.
                                <Button :href="send().url" method="post" variant="link" class="p-0!">
                                    Click here to resend the verification email.
                                </Button>
                            </p>
                            <div v-if="status === 'verification-link-sent'"
                                class="mt-2 text-sm font-medium text-green-600">
                                A new verification link has been sent to your email
                                address.
                            </div>
                        </FieldDescription>
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
                                    <SelectContent class="w-70.25">
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
                                <div class="flex justify-between items-center">
                                    <FieldLabel for="dob">Date of birth</FieldLabel>
                                    <Badge v-if="age !== null" variant="secondary">
                                        {{ age }} years old
                                    </Badge>
                                </div>
                                <Popover v-slot="{ close }">
                                    <PopoverTrigger as-child>
                                        <Button id="dob" name="dob" variant="outline" :class="cn(
                                            'w-70 justify-start text-left font-normal',
                                            !form.dob && 'text-muted-foreground',
                                            { 'border-red-500!': form.errors.dob }
                                        )">
                                            <CalendarIcon class="mr-2 h-4 w-4" />
                                            {{ form.dob ? form.dob.toDate(getLocalTimeZone()).toLocaleDateString()
                                                : "Pick a date" }}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-96.25 p-0" align="start">
                                        <Calendar v-model="form.dob" :default-placeholder="defaultPlaceholder"
                                            layout="month-and-year" calendar-label="Date of birth"
                                            :max-value="defaultPlaceholder" @update:model-value="close"
                                            disable-days-outside-current-view initial-focus />
                                    </PopoverContent>
                                </Popover>
                                <div v-if="dobError || form.errors.dob" class="text-red-500 text-sm">
                                    {{ dobError || form.errors.dob }}
                                </div>
                            </Field>
                        </Field>
                        <Field>
                            <FieldLegend>Height</FieldLegend>
                            <Field class="grid grid-cols-2 gap-4">
                                <Field class="flex flex-col">
                                    <FieldLabel for="feet">
                                        Feet
                                    </FieldLabel>
                                    <InputGroup :class="{ 'border-red-500': feetError || form.errors.feet }">
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
                                    <InputGroup :class="{ 'border-red-500': inchesError || form.errors.inches }">
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
                        <Field class="grid grid-cols-2 gap-4">
                            <Field>
                                <FieldLabel for="country_code">Country</FieldLabel>
                                <Popover v-model:open="countryOpen">
                                    <PopoverTrigger as-child>
                                        <Button id="country_code" name="country_code" variant="outline" role="combobox"
                                            :aria-expanded="countryOpen" :class="[
                                                'w-full justify-between',
                                                { 'border-red-500!': countryCodeError || form.errors.country_code }
                                            ]">
                                            {{ selectedCountryLabel }}
                                            <ChevronsUpDown class="opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-70.25 p-0">
                                        <Command>
                                            <CommandInput class="h-9" placeholder="Search country" />
                                            <CommandList>
                                                <CommandEmpty>No country found.</CommandEmpty>
                                                <CommandGroup>
                                                    <CommandItem v-for="(name, code) in countries" :key="code"
                                                        :value="name" @select="() => {
                                                            form.country_code = code as string
                                                            countryOpen = false
                                                            markTouched('country_code')
                                                        }">
                                                        <Check :class="cn(
                                                            'mr-2 h-4 w-4',
                                                            form.country_code === code ? 'opacity-100' : 'opacity-0'
                                                        )" />
                                                        {{ name }}
                                                    </CommandItem>
                                                </CommandGroup>
                                            </CommandList>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                                <div v-if="countryCodeError || form.errors.country_code" class="text-red-500 text-sm">{{
                                    countryCodeError || form.errors.country_code }}
                                </div>
                            </Field>
                            <Field>
                                <FieldLabel for="timezone">Timezone</FieldLabel>
                                <Popover v-model:open="timezoneOpen">
                                    <PopoverTrigger as-child>
                                        <Button id="timezone" name="timezone" variant="outline" role="combobox"
                                            :aria-expanded="timezoneOpen" :class="[
                                                'w-full justify-between',
                                                { 'border-red-500! ring-red-500/50': timezoneError || form.errors.timezone }
                                            ]">
                                            {{ selectedTimezoneLabel }}
                                            <ChevronsUpDown class="opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-70.25 p-0">
                                        <Command>
                                            <CommandInput class="h-9" placeholder="Search timezone" />
                                            <CommandList>
                                                <CommandEmpty>No timezone found.</CommandEmpty>
                                                <CommandGroup>
                                                    <CommandItem v-for="tz in timezones" :key="tz" :value="tz" @select="() => {
                                                        form.timezone = tz
                                                        timezoneOpen = false
                                                        markTouched('timezone')
                                                    }">
                                                        <Check :class="cn(
                                                            'mr-2 h-4 w-4',
                                                            form.timezone === tz ? 'opacity-100' : 'opacity-0'
                                                        )" />
                                                        {{ tz }}
                                                    </CommandItem>
                                                </CommandGroup>
                                            </CommandList>
                                        </Command>
                                    </PopoverContent>
                                </Popover>
                                <div v-if="timezoneError || form.errors.timezone" class="text-red-500 text-sm">{{
                                    timezoneError || form.errors.timezone }}
                                </div>
                            </Field>
                        </Field>
                        <Field class="flex items-center gap-4">
                            <Button :disabled="form.processing || !allFieldsFilled || hasClientErrors" type="submit"
                                class="inline-flex items-start w-auto! self-start bg-green-500 hover:bg-green-400 dark:text-white">
                                <Spinner v-if="form.processing" class="mr-2" />
                                Save
                            </Button>
                            <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                                <p v-show="form.recentlySuccessful" class="text-sm text-neutral-600">
                                    Profile updated.
                                </p>
                            </Transition>
                        </Field>
                    </FieldGroup>
                </form>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

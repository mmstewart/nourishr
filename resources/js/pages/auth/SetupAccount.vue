<script setup lang="ts">
import { ref, computed, watch, nextTick, useTemplateRef, type HTMLAttributes } from 'vue'
import axios from 'axios';
import { useForm } from '@inertiajs/vue3'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { Field, FieldGroup, FieldLabel, FieldLegend } from '@/components/ui/field'
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command'
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Stepper, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from '@/components/ui/stepper'
import { InputGroup, InputGroupAddon, InputGroupInput, InputGroupText } from '@/components/ui/input-group'
import { Input } from '@/components/ui/input'
import { Spinner } from '@/components/ui/spinner'
import { Check, Circle, Dot, ChevronsUpDown } from 'lucide-vue-next'

const props = defineProps<{
    class?: HTMLAttributes['class'];
    countries: Record<string, string>;
}>();

const totalSteps = 7
const step = ref(1)
const isValidating = ref(false)
const timezoneOpen = ref(false)
const countryOpen = ref(false)
const timezones = ref([]);
const loadingTimezones = ref(false);
const setupRef = useTemplateRef('setupRef')

const steps = [
    { step: 1, title: 'Name', fields: ['name'] },
    { step: 2, title: 'Email', fields: ['email'] },
    { step: 3, title: 'Age', fields: ['age'] },
    { step: 4, title: 'Gender', fields: ['gender'] },
    { step: 5, title: 'Height', fields: ['feet', 'inches'] },
    { step: 6, title: 'Country', fields: ['country_code', 'timezone'] },
    { step: 7, title: 'Password', fields: ['password', 'password_confirmation'] },
]

const form = useForm('post', '/setup-account', {
    name: '',
    age: '',
    gender: '',
    feet: '',
    inches: '',
    email: '',
    country_code: '',
    timezone: '',
    password: '',
    password_confirmation: '',
}).transform((data) => ({
    ...data,
    height_inches: (Number(data.feet) * 12) + Number(data.inches),
}));

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
});

const selectedCountryLabel = computed(() => {
    return props.countries[form.country_code] || 'Select a country'
})

const selectedTimezoneLabel = computed(() => {
    return form.timezone ? form.timezone : 'Select a timezone'
})

const passwordsMatch = computed(() => form.password === form.password_confirmation)

const passwordHasMinLength = (val: string) => val.length >= 10
const passwordHasNumber = (val: string) => /\d/.test(val)
const passwordHasSpecialChar = (val: string) => /[!@#$%^&*]/.test(val)

const focusOnInput = () => {
    nextTick(() => {
        const el = setupRef.value?.querySelector('input, [role="combobox"]') as HTMLElement;
        el?.focus();
    });
}

const prevStep = () => { if (step.value > 1) step.value-- }

const handleContinue = () => {
    const currentStep = steps.find(s => s.step === step.value);
    if (!currentStep) return;

    if (step.value === totalSteps) {
        form.post('/setup-account', {
            preserveState: true,
            onBefore: () => { isValidating.value = true },
            onFinish: () => { isValidating.value = false },
        });

        return;
    }

    isValidating.value = true;

    form.validate({
        only: [...currentStep.fields] as any,
        onSuccess: () => {
            step.value++

            focusOnInput();
        },
        onValidationError: () => {
            isValidating.value = false;
        },
        onFinish: () => {
            isValidating.value = false;
        }
    })
}
</script>

<template>
    <div class="bg-gray-100 flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10">
        <Stepper v-model="step" class="flex w-2/3 items-start gap-2">
            <StepperItem v-for="stepItem in steps" :key="stepItem.step" v-slot="{ state }"
                class="relative flex w-full flex-col items-center justify-center" :step="stepItem.step">
                <StepperSeparator v-if="stepItem.step !== steps[steps.length - 1]?.step"
                    class="absolute left-[calc(50%+20px)] right-[calc(-50%+10px)] top-5 block h-0.5 shrink-0 rounded-full bg-gray-300 group-data-[state=completed]:bg-primary" />
                <StepperTrigger as-child :disabled="stepItem.step > step">
                    <Button :variant="state === 'completed' || state === 'active' ? 'default' : 'outline'" size="icon"
                        class="z-10 rounded-full shrink-0"
                        :class="[state === 'active' && 'ring-2 ring-ring ring-offset-2 ring-offset-background']">
                        <Check v-if="state === 'completed'" />
                        <Circle v-if="state === 'active'" />
                        <Dot v-if="state === 'inactive'" />
                    </Button>
                </StepperTrigger>
                <div class="mt-5 flex flex-col items-center text-center">
                    <StepperTitle :class="[state === 'active' && 'text-primary']"
                        class="text-sm font-semibold transition lg:text-base">
                        {{ stepItem.title }}
                    </StepperTitle>
                </div>
            </StepperItem>
        </Stepper>
        <div class="w-full max-w-sm mt-10">
            <div ref="setupRef" :class="cn('flex flex-col gap-6', props.class)">
                <form @submit.prevent="handleContinue">
                    <FieldGroup>
                        <div class="flex flex-col items-center gap-2 text-center mb-4">
                            <h1 class="text-xl font-bold">Create your account</h1>
                        </div>
                        <template v-if="step === 1">
                            <Field>
                                <FieldLabel for="name">Full Name</FieldLabel>
                                <Input v-model="form.name" id="name" name="name"
                                    :class="{ 'border-red-500': form.errors.name }" autofocus />
                                <div v-if="form.invalid('name')" class="text-red-500 text-sm">{{ form.errors.name }}
                                </div>
                            </Field>
                        </template>
                        <template v-else-if="step === 2">
                            <Field>
                                <FieldLabel for="email">Email</FieldLabel>
                                <Input v-model="form.email" @change="form.validate('email')" id="email" name="email"
                                    type="email" :class="{ 'border-red-500': form.errors.email }" />
                                <div v-if="form.invalid('email')" class="text-red-500 text-sm">{{ form.errors.email }}
                                </div>
                            </Field>
                        </template>
                        <template v-else-if="step === 3">
                            <Field>
                                <FieldLabel for="age">Age</FieldLabel>
                                <Input v-model="form.age" id="age" name="age" type="number"
                                    :class="{ 'border-red-500': form.errors.age }" />
                                <div v-if="form.invalid('age')" class="text-red-500 text-sm">{{ form.errors.age }}</div>
                            </Field>
                        </template>
                        <template v-else-if="step === 4">
                            <Field>
                                <FieldLabel for="gender">Gender</FieldLabel>
                                <Select v-model="form.gender" @change="form.validate('gender')">
                                    <SelectTrigger id="gender" name="gender"
                                        :class="{ 'border-red-500': form.errors.gender }">
                                        <SelectValue placeholder="Select your gender" />
                                    </SelectTrigger>
                                    <SelectContent class="w-96.25">
                                        <SelectItem value="male">Male</SelectItem>
                                        <SelectItem value="female">Female</SelectItem>
                                    </SelectContent>
                                </Select>
                                <div v-if="form.invalid('gender')" class="text-red-500 text-sm">{{ form.errors.gender }}
                                </div>
                            </Field>
                        </template>
                        <template v-else-if="step === 5">
                            <Field>
                                <FieldLegend>Height</FieldLegend>
                                <Field class="grid grid-cols-2 gap-4">
                                    <Field>
                                        <FieldLabel for="feet">Feet</FieldLabel>
                                        <InputGroup :class="{ 'border-red-500': form.errors.feet }">
                                            <InputGroupInput v-model="form.feet" id="feet" name="feet" type="number" />
                                            <InputGroupAddon align="inline-end">
                                                <InputGroupText>ft</InputGroupText>
                                            </InputGroupAddon>
                                        </InputGroup>
                                        <div v-if="form.invalid('feet')" class="text-red-500 text-sm">{{
                                            form.errors.feet }}
                                        </div>
                                    </Field>
                                    <Field>
                                        <FieldLabel for="inches">Inches</FieldLabel>
                                        <InputGroup :class="{ 'border-red-500': form.errors.inches }">
                                            <InputGroupInput v-model="form.inches" id="inches" type="number" />
                                            <InputGroupAddon align="inline-end">
                                                <InputGroupText>in</InputGroupText>
                                            </InputGroupAddon>
                                        </InputGroup>
                                        <div v-if="form.invalid('inches')" class="text-red-500 text-sm">{{
                                            form.errors.inches }}
                                        </div>
                                    </Field>
                                </Field>
                            </Field>
                        </template>
                        <template v-else-if="step === 6">
                            <Field>
                                <FieldLabel for="country_code">Country</FieldLabel>
                                <Popover v-model:open="countryOpen">
                                    <PopoverTrigger as-child>
                                        <Button id="country_code" name="country_code" variant="outline" role="combobox"
                                            :aria-expanded="countryOpen" :class="[
                                                'w-full justify-between',
                                                { 'border-red-500!': form.errors.country_code }
                                            ]">
                                            {{ selectedCountryLabel }}
                                            <ChevronsUpDown class="opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-96.25 p-0">
                                        <Command>
                                            <CommandInput class="h-9" placeholder="Search country" />
                                            <CommandList>
                                                <CommandEmpty>No country found.</CommandEmpty>
                                                <CommandGroup>
                                                    <CommandItem v-for="(name, code) in countries" :key="code"
                                                        :value="name" @select="() => {
                                                            form.country_code = code as string
                                                            countryOpen = false
                                                            form.validate('country_code')
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
                                <div v-if="form.invalid('country_code')" class="text-red-500 text-sm">{{
                                    form.errors.country_code }}
                                </div>
                            </Field>
                            <Field>
                                <FieldLabel for="timezone">Timezone</FieldLabel>
                                <Popover v-model:open="timezoneOpen">
                                    <PopoverTrigger as-child>
                                        <Button id="timezone" name="timezone" variant="outline" role="combobox"
                                            :aria-expanded="timezoneOpen" :class="[
                                                'w-full justify-between',
                                                { 'border-red-500!': form.errors.timezone }
                                            ]">
                                            {{ selectedTimezoneLabel }}
                                            <ChevronsUpDown class="opacity-50" />
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-96.25 p-0">
                                        <Command>
                                            <CommandInput class="h-9" placeholder="Search timezone" />
                                            <CommandList>
                                                <CommandEmpty>No timezone found.</CommandEmpty>
                                                <CommandGroup>
                                                    <CommandItem v-for="tz in timezones" :key="tz" :value="tz" @select="() => {
                                                        form.timezone = tz
                                                        timezoneOpen = false
                                                        form.validate('timezone')
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
                                <div v-if="form.invalid('timezone')" class="text-red-500 text-sm">{{
                                    form.errors.timezone }}
                                </div>
                            </Field>
                        </template>
                        <template v-else-if="step === 7">
                            <Field>
                                <FieldLabel for="password">Password</FieldLabel>
                                <Input v-model="form.password" @change="form.validate('password')" id="password"
                                    name="password" type="password"
                                    :class="{ 'border-red-500': form.errors.password }" />
                                <div v-if="form.invalid('password')" class="text-red-500 text-sm">
                                    {{ form.errors.password }}
                                </div>
                                <div v-if="form.password"
                                    class="flex flex-col gap-1 text-base mt-2 border border-muted-foreground/30 px-3 space-y-0.5 md:text-sm py-1 rounded-md">
                                    <div class="flex gap-x-2"
                                        :class="passwordHasMinLength(form.password) ? 'text-green-600' : 'text-muted-foreground'">
                                        {{ passwordHasMinLength(form.password) ? '✓' : '•' }} Contains at least 10
                                        characters
                                    </div>
                                    <div class="flex gap-x-2"
                                        :class="passwordHasNumber(form.password) ? 'text-green-600' : 'text-muted-foreground'">
                                        {{ passwordHasNumber(form.password) ? '✓' : '•' }} Contains a number
                                    </div>
                                    <div class="flex gap-x-2"
                                        :class="passwordHasSpecialChar(form.password) ? 'text-green-600' : 'text-muted-foreground'">
                                        {{ passwordHasSpecialChar(form.password) ? '✓' : '•' }} Contains a special
                                        character
                                    </div>
                                </div>
                            </Field>
                            <Field>
                                <FieldLabel for="password_confirmation">Confirm Password</FieldLabel>
                                <Input v-model="form.password_confirmation"
                                    @change="form.validate('password_confirmation')" id="password_confirmation"
                                    name="password_confirmation" type="password"
                                    :class="{ 'border-red-500': form.errors.password_confirmation }" />
                                <div v-if="form.invalid('password_confirmation')" class="text-red-500 text-sm">{{
                                    form.errors.password_confirmation }}
                                </div>
                                <div v-if="form.password_confirmation"
                                    class="flex flex-col gap-1 text-base mt-2 border border-muted-foreground/30 px-3 space-y-0.5 md:text-sm py-1 rounded-md">
                                    <div class="flex gap-x-2"
                                        :class="passwordsMatch ? 'text-green-600' : 'text-muted-foreground'">
                                        {{ passwordsMatch ? '✓' : '•' }} Passwords match
                                    </div>
                                </div>
                            </Field>
                        </template>
                        <Field orientation="horizontal">
                            <Button v-if="step > 1" variant="outline" type="button" @click="prevStep">Back</Button>
                            <Button type="submit" :disabled="form.processing || isValidating"
                                class="bg-green-500 hover:bg-green-400 dark:text-white">
                                <Spinner v-if="form.processing || isValidating" class="mr-2" />
                                {{ step === totalSteps ? 'Finish' : 'Continue' }}
                            </Button>
                        </Field>
                    </FieldGroup>
                </form>
            </div>
        </div>
    </div>
</template>
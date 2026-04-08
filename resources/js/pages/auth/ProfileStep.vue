<script setup lang="ts">
import { ref, computed, watch, nextTick, useTemplateRef, onMounted, type HTMLAttributes } from 'vue'
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
import { Calendar } from '@/components/ui/calendar'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Stepper, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from '@/components/ui/stepper'
import { InputGroup, InputGroupAddon, InputGroupInput, InputGroupText } from '@/components/ui/input-group'
import { Spinner } from '@/components/ui/spinner'
import { Check, Circle, Dot, ChevronsUpDown, CalendarIcon } from 'lucide-vue-next'
import { today, getLocalTimeZone } from '@internationalized/date'

const props = withDefaults(defineProps<{
    class?: HTMLAttributes['class'];
    countries?: Record<string, string>;
}>(), {
    countries: () => ({})
});

const steps = [
    { step: 1, title: 'Date of Birth', fields: ['dob'] },
    { step: 2, title: 'Gender', fields: ['gender'] },
    { step: 3, title: 'Height', fields: ['feet', 'inches'] },
    { step: 4, title: 'Country', fields: ['country_code', 'timezone'] },
]

const totalSteps = steps.length;
const step = ref(1)
const isValidating = ref(false)
const timezoneOpen = ref(false)
const countryOpen = ref(false)
const timezones = ref([]);
const loadingTimezones = ref(false);
const setupRef = useTemplateRef('setupRef')

const form = useForm('post', '/setup-profile', {
    dob: today(getLocalTimeZone()) as any,
    gender: '',
    feet: '',
    inches: '',
    country_code: '',
    timezone: '',
}).transform((data) => ({
    ...data,
    height_inches: (Number(data.feet) * 12) + Number(data.inches),
}));

const defaultPlaceholder = today(getLocalTimeZone())

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

const selectedCountryLabel = computed(() => {
    return props.countries[form.country_code] || 'Select a country'
})

const selectedTimezoneLabel = computed(() => {
    return form.timezone ? form.timezone : 'Select a timezone'
})

const focusOnInput = () => {
    nextTick(() => {
        const el = setupRef.value?.querySelector('input, [role="combobox"]') as HTMLElement;
        el?.focus();
    });
}

onMounted(() => {
    focusOnInput();
})

const prevStep = () => { if (step.value > 1) step.value-- }

const handleContinue = () => {
    const currentStepConfig = steps.find(s => s.step === step.value);
    if (!currentStepConfig) return;

    isValidating.value = true;

    form.validate({
        only: currentStepConfig.fields as Array<keyof typeof form.data>,
        onSuccess: () => {
            if (step.value === totalSteps) {
                form.post('/setup-profile');
            } else {
                step.value++;
                focusOnInput();
            }
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
        <Stepper v-model="step" class="flex w-1/2 items-start gap-2">
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
                            <h1 class="text-xl font-bold">Setup your account</h1>
                        </div>
                        <template v-if="step === 1">
                            <Field>
                                <FieldLabel for="dob">Age</FieldLabel>
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
                                <div v-if="form.invalid('dob')" class="text-red-500 text-sm">{{ form.errors.dob }}</div>
                            </Field>
                        </template>
                        <template v-else-if="step === 2">
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
                        <template v-else-if="step === 3">
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
                        <template v-else-if="step === 4">
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
                                    <PopoverContent class="w-96.25 p-0" align="start">
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
                                    <PopoverContent class="w-96.25 p-0" align="start">
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
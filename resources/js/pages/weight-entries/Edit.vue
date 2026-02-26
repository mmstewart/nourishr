<script setup lang="ts">
import { ref, computed, type HTMLAttributes } from 'vue'
import { Head, useForm } from '@inertiajs/vue3';
import { cn } from "@/lib/utils"
import { type WeightEntry } from '@/types';

import { parseDate, getLocalTimeZone, today } from '@internationalized/date'
import { CalendarIcon } from 'lucide-vue-next'

import { Button } from '@/components/ui/button'
import { Calendar } from '@/components/ui/calendar'
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover'
import { InputGroup, InputGroupAddon, InputGroupInput, InputGroupText } from '@/components/ui/input-group'
import {
    Field,
    FieldDescription,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field'
import { Spinner } from '@/components/ui/spinner';

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Weight Entries',
        href: '/weight-entries',
    },
];

const props = defineProps<{
    class?: HTMLAttributes["class"],
    weightEntry: WeightEntry,
}>()

const form = useForm({
    date: parseDate(props.weightEntry.date) as any,
    weight: props.weightEntry.weight,
})

// Touch tracking so we don't show errors before the user interacts
const touchedDate = ref(false)
const touchedWeight = ref(false)
const attemptedSubmit = ref(false)

function markTouched(field: string) {
    if (field === 'date') touchedDate.value = true
    if (field === 'weight') touchedWeight.value = true
}

const defaultPlaceholder = today(getLocalTimeZone())

const dateError = computed(() => {
    if (!form.date && (touchedDate.value || attemptedSubmit.value)) return 'Please select a date.'
    return ''
})

const validateWeight = (value: string | number | null, isTouched: boolean, label: string) => {
    if (!(isTouched || attemptedSubmit.value)) return '';

    if (value === null || value === '') {
        return `${label} weight is required.`;
    }

    const num = Number(value);

    if (num < 30 || num > 999) {
        return `${label} weight must be between 30 and 999 lbs.`;
    }

    return '';
};

const weightError = computed(() =>
    validateWeight(form.weight, touchedWeight.value, 'Weight')
);

const hasClientErrors = computed(() => {
    return !!(dateError.value || weightError.value)
})

const allFieldsFilled = computed(() => {
    return !!(form.date && form.weight)
})

function submit() {
    attemptedSubmit.value = true
    touchedDate.value = true
    touchedWeight.value = true

    if (hasClientErrors.value) return

    form
        .transform((data) => ({
            ...data,
            date: data.date.toString(),
        }))
        .put(`/weight-entries/${props.weightEntry.id}`)
}
</script>

<template>

    <Head title="Edit Weight Entry" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 md:max-w-2xl">
            <section class="max-w-xl space-y-12">
                <div class="flex flex-1 flex-col gap-4 p-4">
                    <div class="space-y-6">
                        <form @submit.prevent="submit">
                            <FieldGroup>
                                <div class="flex flex-col items-start text-left">
                                    <span class="sr-only">Weight Goals</span>
                                    <h3 class="text-base font-medium mb-0.5">
                                        Weight Entries
                                    </h3>
                                    <FieldDescription>
                                        Create your weight entry to track your progress towards your weight goal.
                                    </FieldDescription>
                                </div>
                                <!-- todo: make it so that the calendar depends on the timezone -->
                                <Field>
                                    <FieldLabel for="date">
                                        Date
                                    </FieldLabel>
                                    <Popover v-slot="{ close }"
                                        @update:open="(val) => { if (!val) touchedDate = true }">
                                        <PopoverTrigger as-child>
                                            <Button id="date" name="date" variant="outline" :class="cn(
                                                'w-70 justify-start text-left font-normal h-10',
                                                !form.date && 'text-muted-foreground',
                                                { 'border-red-500!': form.errors.date || dateError }
                                            )">
                                                <CalendarIcon class=" mr-2 h-4 w-4" />
                                                {{ form.date ? form.date.toDate(getLocalTimeZone()).toLocaleDateString()
                                                    : "Pick a date" }}
                                            </Button>
                                        </PopoverTrigger>
                                        <PopoverContent class="w-auto p-0">
                                            <Calendar v-model="form.date" :default-placeholder="defaultPlaceholder"
                                                layout="month-and-year" @update:model-value="close"
                                                :max-value="defaultPlaceholder" disable-days-outside-current-view
                                                initial-focus />
                                        </PopoverContent>
                                    </Popover>
                                    <div v-if="dateError || form.errors.date" class="text-red-500 text-sm">
                                        {{ dateError || form.errors.date }}
                                    </div>
                                </Field>
                                <Field>
                                    <FieldLabel for="weight">
                                        Weight
                                    </FieldLabel>
                                    <InputGroup :class="{ 'border-red-500': weightError || form.errors.weight }">
                                        <InputGroupInput v-model="form.weight" @blur="markTouched('weight')" step="0.1"
                                            inputmode="decimal" id="weight" name="weight" type="number" />
                                        <InputGroupAddon align="inline-end">
                                            <InputGroupText>lbs</InputGroupText>
                                        </InputGroupAddon>
                                    </InputGroup>
                                    <div v-if="weightError || form.errors.weight" class="text-red-500 text-sm">
                                        {{ weightError || form.errors.weight }}
                                    </div>
                                </Field>
                                <Field class="flex items-center gap-4">
                                    <Button :disabled="form.processing || !allFieldsFilled || hasClientErrors"
                                        type="submit" class="inline-flex items-start w-auto! self-start">
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
                </div>
            </section>
        </div>
    </AppLayout>
</template>
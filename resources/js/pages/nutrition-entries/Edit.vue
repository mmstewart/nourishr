<script setup lang="ts">
import { ref, computed, type HTMLAttributes } from 'vue'
import { Head, useForm } from '@inertiajs/vue3';
import { cn } from "@/lib/utils"

import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"
import {
    Field,
    FieldDescription,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field'
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover'
import { Button } from '@/components/ui/button'
import { Calendar } from '@/components/ui/calendar'
import { CalendarIcon } from 'lucide-vue-next'
import { parseDate, getLocalTimeZone, today } from '@internationalized/date'
import { Spinner } from '@/components/ui/spinner'

import AppLayout from '@/layouts/AppLayout.vue';
import { NutritionEntry, BreadcrumbItem } from '@/types';
import NutrientGrid from '@/components/NutrientGrid.vue'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Nutrition Entries',
        href: '/nutrition-entries',
    },
];

const props = defineProps<{
    class?: HTMLAttributes["class"],
    nutritionEntry: NutritionEntry,
    servingOptions: Array<{ label: string, gramWeight: number }>,
}>()

const servingSizeExists = props.servingOptions.some(
    o => o.label === props.nutritionEntry.serving_size
)

const initialServingSize = servingSizeExists
    ? props.nutritionEntry.serving_size
    : (props.servingOptions[0]?.label ?? '')

const form = useForm({
    date: parseDate(props.nutritionEntry.date) as any,
    quantity: String(props.nutritionEntry.quantity),
    serving_size: initialServingSize,
    meal_type: props.nutritionEntry.meal_type,
})

// Touch tracking so we don't show errors before the user interacts
const touchedDate = ref(false)
const touchedQuantity = ref(false)
const touchedServingSize = ref(false)
const touchedMealType = ref(false)

const defaultPlaceholder = today(getLocalTimeZone())
const mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'] as const

const liveMacros = computed(() => {
    const originalWeight = props.servingOptions.find(o => o.label === initialServingSize)?.gramWeight ?? 1;
    const currentWeight = props.servingOptions.find(o => o.label === form.serving_size)?.gramWeight ?? 0;

    const baseTotal = originalWeight * props.nutritionEntry.quantity;
    const currentTotal = currentWeight * Number(form.quantity || 0);
    const multiplier = baseTotal > 0 ? currentTotal / baseTotal : 0;

    return {
        calories: Math.round(props.nutritionEntry.calories * multiplier),
        protein: parseFloat((props.nutritionEntry.protein * multiplier).toFixed(2)),
        carbohydrates: parseFloat((props.nutritionEntry.carbohydrates * multiplier).toFixed(2)),
        fats: parseFloat((props.nutritionEntry.fats * multiplier).toFixed(2)),
    };
});

const dateError = computed(() => {
    if (!form.date && touchedDate.value) return 'Please select a date.'
    return ''
})

const quantityError = computed(() => {
    if (!form.quantity && touchedQuantity.value) return 'Please enter a quantity.'
    return ''
})

const servingSizeError = computed(() => {
    if (!form.serving_size && touchedServingSize.value) return 'Please select a serving size.'
    return ''
})

const mealTypeError = computed(() => {
    if (!form.meal_type && touchedMealType.value) return 'Please select a meal type.'
    return ''
})

const hasClientErrors = computed(() => {
    return !!(dateError.value || quantityError.value || servingSizeError.value || mealTypeError.value)
})

function submit() {
    touchedDate.value = true
    touchedQuantity.value = true
    touchedServingSize.value = true
    touchedMealType.value = true

    if (hasClientErrors.value) return

    form
        .transform((data) => ({
            ...data,
            ...liveMacros.value,
            date: data.date ? data.date.toString() : null,
        }))
        .put(`/nutrition-entries/${props.nutritionEntry.id}`);
}
</script>

<template>

    <Head title="Edit Nutrition Entry" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 md:max-w-2xl">
            <section class="max-w-xl space-y-12">
                <div class="flex flex-1 flex-col gap-4 p-4">
                    <div class="space-y-6">
                        <form @submit.prevent="submit">
                            <FieldGroup>
                                <div class="flex flex-col items-start text-left">
                                    <span class="sr-only">Edit Nutrition Entry</span>
                                    <h3 class="text-base font-medium mb-0.5">
                                        Edit Nutrition Entry
                                    </h3>
                                    <FieldDescription>
                                        Update your nutrition entry to track your progress towards your nutrition goals.
                                    </FieldDescription>
                                </div>
                                <Field>
                                    <FieldLabel for="date">
                                        Date
                                    </FieldLabel>
                                    <Popover v-slot="{ close }"
                                        @update:open="(val) => { if (!val) touchedDate = true }">
                                        <PopoverTrigger as-child>
                                            <Button id="date" name="date" variant="outline" :disabled="true" :class="cn(
                                                'w-70 justify-start text-left font-normal pointer-events-none',
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
                                    <FieldLabel for="meal_type">Meal type</FieldLabel>
                                    <Select v-model="form.meal_type">
                                        <SelectTrigger id="meal_type" name="meal_type" :class="cn(
                                            'w-full dark:bg-input/30',
                                            (mealTypeError || form.errors.meal_type) && 'border-red-500 ring-red-500'
                                        )">
                                            <SelectValue placeholder="Select your meal type" />
                                        </SelectTrigger>
                                        <SelectContent class="w-136">
                                            <SelectItem v-for="meal in mealTypes" :key="meal" :value="meal">
                                                {{ meal.charAt(0).toUpperCase() + meal.slice(1) }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div v-if="mealTypeError || form.errors.meal_type" class="text-red-500 text-sm">
                                        {{ mealTypeError || form.errors.meal_type }}
                                    </div>
                                </Field>
                                <Field>
                                    <FieldLabel for="quantity">Quantity</FieldLabel>
                                    <Select v-model="form.quantity">
                                        <SelectTrigger id="quantity" name="quantity" :class="cn(
                                            'w-full dark:bg-input/30',
                                            (quantityError || form.errors.quantity) && 'border-red-500 ring-red-500'
                                        )">
                                            <SelectValue placeholder="Select quantity" />
                                        </SelectTrigger>
                                        <SelectContent class="w-136">
                                            <SelectItem value="0.25">¼</SelectItem>
                                            <SelectItem value="0.5">½</SelectItem>
                                            <SelectItem value="0.75">¾</SelectItem>
                                            <SelectItem v-for="n in 10" :key="n" :value="String(n)">
                                                {{ n }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <div v-if="quantityError || form.errors.quantity" class="text-red-500 text-sm">
                                        {{ quantityError || form.errors.quantity }}
                                    </div>
                                </Field>
                                <Field>
                                    <FieldLabel for="serving_size">Serving size</FieldLabel>
                                    <Select v-model="form.serving_size">
                                        <SelectTrigger id="serving_size" name="serving_size" :class="cn(
                                            'w-full dark:bg-input/30',
                                            (servingSizeError || form.errors.serving_size) && 'border-red-500 ring-red-500'
                                        )">
                                            <SelectValue placeholder="Select your serving size" />
                                        </SelectTrigger>
                                        <SelectContent class="w-136">
                                            <SelectItem v-for="option in props.servingOptions" :key="option.label"
                                                :value="option.label" class="first-letter:uppercase">
                                                {{ option.label }} ({{ option.gramWeight }}g)
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </Field>
                                <Field>
                                    <FieldLabel> Nutrition facts</FieldLabel>
                                    <NutrientGrid :nutrients="liveMacros" backgroundClass="dark:bg-input/30" />
                                </Field>
                                <Field class="flex items-center gap-4">
                                    <Button :disabled="form.processing || hasClientErrors" type="submit"
                                        class="inline-flex items-start w-auto! self-start bg-green-500 hover:bg-green-400 dark:text-white cursor-pointer">
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
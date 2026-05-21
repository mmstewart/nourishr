<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onUnmounted, ref, watch, type HTMLAttributes } from 'vue';

import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Field, FieldDescription, FieldLabel } from '@/components/ui/field';
import { InputGroup, InputGroupAddon, InputGroupInput } from '@/components/ui/input-group';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { getLocalTimeZone, today, type DateValue } from '@internationalized/date';
import { useDebounceFn } from '@vueuse/core';
import { BarcodeFormat, BrowserMultiFormatReader, DecodeHintType } from '@zxing/library';
import { CalendarIcon } from 'lucide-vue-next';

import NutrientGrid from '@/components/NutrientGrid.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, NutritionEntry } from '@/types';
import { Plus, ScanBarcode, Search, X } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Nutrition Entries',
        href: '/nutrition-entries',
    },
];

defineProps<{
    class?: HTMLAttributes['class'];
}>();

const form = useForm({ date: '', entries: [] as any[] });

const mode = ref<'text' | 'scan'>('text');
const mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'] as const;
const mealType = ref<(typeof mealTypes)[number]>('breakfast');
const entryDate = ref<any>(today(getLocalTimeZone()));

const tabs = [
    { value: 'text', Icon: Search, label: 'Search' },
    { value: 'scan', Icon: ScanBarcode, label: 'Scan' },
] as const;

// Search
const search = ref('');
const searchResults = ref<any[]>([]);
const isSearching = ref(false);

const nutritionEntries = ref<
    Pick<
        NutritionEntry,
        | 'fdc_id'
        | 'food_item'
        | 'calories'
        | 'protein'
        | 'carbohydrates'
        | 'fats'
        | 'quantity'
        | 'serving_size'
        | 'meal_type'
    >[]
>([]);
const pendingProduct = ref<any>(null);
const selectedServing = ref<any>(null);
const selectedServingLabel = ref('');
const pendingQuantity = ref('1');

// Scanner
const videoEl = ref<HTMLVideoElement | null>(null);
const scanError = ref('');
const scanSuccess = ref('');
let codeReader: BrowserMultiFormatReader | null = null;

function extractNutrient(product: any, standardName: string, factor: number): number {
    const rawVal = product?.foodNutrients?.find((n: any) => n.nutrientName === standardName)?.value ?? 0;
    return rawVal * factor;
}

const totals = computed(() => {
    const sum = (key: 'calories' | 'protein' | 'carbohydrates' | 'fats') =>
        nutritionEntries.value.reduce((acc, item) => acc + item[key] * item.quantity, 0);
    return {
        calories: sum('calories'),
        protein: sum('protein'),
        carbohydrates: sum('carbohydrates'),
        fats: sum('fats'),
    };
});

// Search
watch(selectedServingLabel, (label) => {
    selectedServing.value = pendingProduct.value?.servingOptions.find((o: any) => o.label === label) ?? null;
});

watch(
    search,
    useDebounceFn(async (newQuery) => {
        const query = newQuery.trim();

        if (!query) {
            searchResults.value = [];
            return;
        }

        isSearching.value = true;

        try {
            const response = await axios.get('/api/foodfacts', {
                params: { food_item: query, type: 'search' },
            });

            searchResults.value = response.data.foods ?? [];
        } catch (error) {
            console.error('Failed to load food facts', error);
        } finally {
            isSearching.value = false;
        }
    }, 700),
);

// Scanner
async function startScanner() {
    scanError.value = '';
    scanSuccess.value = '';

    if (!codeReader) {
        const hints = new Map();

        hints.set(DecodeHintType.TRY_HARDER, true);

        hints.set(DecodeHintType.POSSIBLE_FORMATS, [
            BarcodeFormat.UPC_A,
            BarcodeFormat.UPC_E,
            BarcodeFormat.EAN_13,
            BarcodeFormat.EAN_8,
        ]);

        codeReader = new BrowserMultiFormatReader(hints);
    }

    codeReader = new BrowserMultiFormatReader();

    try {
        if (!videoEl.value) {
            scanError.value = 'Camera display not found.';
            return;
        }

        const constraints: MediaStreamConstraints = {
            video: {
                facingMode: { ideal: 'environment' },
                width: { min: 640, ideal: 1280 },
                height: { min: 480, ideal: 720 },
            },
        };

        await codeReader.decodeFromConstraints(constraints, videoEl.value, async (result, error) => {
            if (result) {
                codeReader?.reset();
                await lookupNutritionLabel(result.getText());
            }

            if (error) {
            }
        });
    } catch (e) {
        scanError.value = 'Could not access camera. Please check permissions.';
    }
}

function stopScanner() {
    codeReader?.reset();
    codeReader = null;
}

async function lookupNutritionLabel(label: string) {
    isSearching.value = true;
    scanError.value = '';

    try {
        const response = await axios.get(`/api/foodfacts`, {
            params: { food_item: label, type: 'scan' },
        });

        if (response.data && Object.keys(response.data).length) {
            const product = response.data;
            const nutriments = product.nutriments ?? {};

            nutritionEntries.value.push({
                fdc_id: product.fdcId,
                food_item: product.product_name,
                calories: nutriments['energy-kcal_100g'] ?? 0,
                protein: nutriments.proteins_100g ?? 0,
                carbohydrates: nutriments.carbohydrates_100g ?? 0,
                fats: nutriments.fat_100g ?? 0,
                quantity: 1,
                serving_size: '100g',
                meal_type: mealType.value,
            });

            scanSuccess.value = `Added: ${product.product_name}`;

            mode.value = 'scan';
        } else {
            scanError.value = 'Product not found for this nutrition label.';

            setTimeout(() => {
                if (mode.value === 'scan') startScanner();
            }, 2000);
        }
    } catch (e) {
        scanError.value = 'Failed to look up product.';

        setTimeout(() => {
            if (mode.value === 'scan') startScanner();
        }, 2000);
    } finally {
        isSearching.value = false;
    }
}

watch(mode, (newMode) => {
    if (newMode === 'scan') {
        // Wait for video element to mount
        setTimeout(() => startScanner(), 50);
    } else {
        stopScanner();
    }
});

// watch(mode, async (newMode) => {
//     if (newMode === 'scan') {
//         // Wait until Vue updates the DOM and mounts the <video> element
//         await nextTick()
//         startScanner()
//     } else {
//         stopScanner()
//     }
// })

onUnmounted(() => stopScanner());

// Called when user clicks a search result
function selectProduct(product: any) {
    const options = [
        { label: '100g', gramWeight: 100 },
        ...(product.servingSize && product.householdServingFullText
            ? [
                  {
                      label: product.householdServingFullText.toLowerCase(),
                      gramWeight: product.servingSize,
                  },
              ]
            : []),
        ...(product.foodMeasures ?? []).map((m: any) => ({
            label: m.disseminationText,
            gramWeight: m.gramWeight,
        })),
    ];

    pendingProduct.value = { ...product, servingOptions: options };
    const defaultOption = options[1] ?? options[0];
    selectedServing.value = defaultOption;
    selectedServingLabel.value = defaultOption.label;
}

// Meal items
function addNutritionEntry() {
    if (!pendingProduct.value || !selectedServing.value) {
        return { calories: 0, protein: 0, carbohydrates: 0, fats: 0 };
    }

    const factor = (selectedServing.value.gramWeight / 100) * parseFloat(pendingQuantity.value);
    const prod = pendingProduct.value;

    nutritionEntries.value.push({
        fdc_id: pendingProduct.value.fdcId,
        food_item: pendingProduct.value.description,
        calories: Math.round(extractNutrient(prod, 'Energy', factor)),
        protein: parseFloat(extractNutrient(prod, 'Protein', factor).toFixed(2)),
        carbohydrates: parseFloat(extractNutrient(prod, 'Carbohydrate, by difference', factor).toFixed(2)),
        fats: parseFloat(extractNutrient(prod, 'Total lipid (fat)', factor).toFixed(2)),
        quantity: parseFloat(pendingQuantity.value),
        serving_size: selectedServing.value.label + ' (' + selectedServing.value.gramWeight + 'g)',
        meal_type: mealType.value,
    });

    pendingProduct.value = null;
    selectedServing.value = null;
    pendingQuantity.value = '1';
    selectedServingLabel.value = '';
    searchResults.value = [];
    search.value = '';
}

function removeNutritionEntry(index: number) {
    nutritionEntries.value.splice(index, 1);
}

const pendingNutrients = computed(() => {
    if (!pendingProduct.value || !selectedServing.value) {
        return { calories: 0, protein: 0, carbohydrates: 0, fats: 0 };
    }
    const factor = (selectedServing.value.gramWeight / 100) * parseFloat(pendingQuantity.value);
    const prod = pendingProduct.value;

    return {
        calories: Math.round(extractNutrient(prod, 'Energy', factor)),
        protein: parseFloat(extractNutrient(prod, 'Protein', factor).toFixed(2)),
        carbohydrates: parseFloat(extractNutrient(prod, 'Carbohydrate, by difference', factor).toFixed(2)),
        fats: parseFloat(extractNutrient(prod, 'Total lipid (fat)', factor).toFixed(2)),
    };
});

function submit() {
    if (!nutritionEntries.value.length) return;

    useForm({
        date: entryDate.value.toString(),
        entries: nutritionEntries.value.map((entry) => ({
            fdc_id: entry.fdc_id,
            food_item: entry.food_item,
            calories: Math.round(entry.calories * entry.quantity),
            protein: parseFloat((entry.protein * entry.quantity).toFixed(2)),
            carbohydrates: parseFloat((entry.carbohydrates * entry.quantity).toFixed(2)),
            fats: parseFloat((entry.fats * entry.quantity).toFixed(2)),
            serving_size: entry.serving_size,
            quantity: entry.quantity,
            meal_type: mealType.value,
        })),
    }).post('/nutrition-entries');
}
</script>

<template>
    <Head title="Create Nutrition Entry" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex max-w-2xl flex-col gap-7 p-4">
            <div class="space-y-6">
                <div class="flex flex-col items-start text-left">
                    <span class="sr-only">Create Nutrition Entry</span>
                    <h3 class="mb-0.5 text-base font-medium">Create Nutrition Entry</h3>
                    <FieldDescription>
                        Create your nutrition entry to track your progress towards your nutrition goals.
                    </FieldDescription>
                </div>
            </div>
            <div class="flex gap-1 rounded-lg bg-muted p-1">
                <button
                    v-for="tab in tabs"
                    :key="tab.value"
                    @click="mode = tab.value"
                    :class="[
                        'flex flex-1 items-center justify-center gap-2 rounded-md py-1.5 text-sm font-medium transition-all duration-200',
                        mode === tab.value
                            ? 'bg-background text-foreground shadow-sm'
                            : 'text-muted-foreground hover:text-foreground',
                    ]">
                    <component :is="tab.Icon" class="h-4 w-4" />
                    {{ tab.label }}
                </button>
            </div>
            <div v-if="mode === 'text'" class="space-y-4">
                <div class="relative">
                    <InputGroup>
                        <InputGroupInput
                            v-model="search"
                            id="search"
                            name="search"
                            placeholder="Search"
                            autocomplete="off"
                            :disabled="isSearching" />
                        <InputGroupAddon v-if="!isSearching">
                            <Search class="size-4 text-muted-foreground" />
                        </InputGroupAddon>
                        <InputGroupAddon v-else align="inline-end">
                            <Spinner class="size-4" />
                        </InputGroupAddon>
                    </InputGroup>
                </div>
                <div
                    v-if="searchResults.length && !pendingProduct"
                    class="max-h-60 divide-y divide-border overflow-y-auto rounded-md border border-input bg-background">
                    <li
                        v-for="product in searchResults"
                        :key="product.fdcId ?? product.product_name"
                        @click="selectProduct(product)"
                        class="group flex cursor-pointer items-center gap-3 px-4 py-3 transition-colors hover:bg-muted">
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium capitalize">
                                {{ (product.description ?? product.product_name).toLowerCase() }}
                            </p>
                            <div class="flex flex-wrap items-center gap-1 text-xs text-muted-foreground">
                                <span v-if="product?.brandName" class="capitalize">
                                    {{ product.brandName.toLowerCase() }}
                                </span>
                                <div v-if="product?.brandName" class="size-0.5 rounded-full bg-gray-500"></div>
                                <span>
                                    {{
                                        product.foodNutrients?.find((n: any) => n.nutrientName === 'Energy')?.value ?? 0
                                    }}
                                    kcal
                                </span>
                            </div>
                        </div>
                        <Button
                            size="icon"
                            variant="ghost"
                            class="hidden cursor-pointer items-center gap-0! group-hover:flex">
                            <Plus class="size-3" />
                            Add
                        </Button>
                    </li>
                </div>
                <p
                    v-else-if="search && !searchResults.length && !isSearching"
                    class="py-2 text-center text-sm text-muted-foreground">
                    No items found. Try a different search term.
                </p>
                <div
                    v-if="pendingProduct"
                    class="to-default relative space-y-4 rounded-md border border-input bg-linear-to-tl from-primary/5 p-4">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium capitalize">
                                {{ (pendingProduct.description ?? pendingProduct.product_name).toLowerCase() }}
                            </p>
                            <p v-if="pendingProduct.brandName" class="text-xs text-muted-foreground capitalize">
                                {{ pendingProduct.brandName.toLowerCase() }}
                            </p>
                        </div>
                        <X @click="pendingProduct = null" class="size-4 shrink-0 cursor-pointer" />
                    </div>
                    <Field>
                        <FieldLabel class="text-xs">Meal type</FieldLabel>
                        <Select v-model="mealType">
                            <SelectTrigger class="w-full capitalize">
                                <SelectValue :placeholder="mealType" />
                            </SelectTrigger>
                            <SelectContent class="w-151.5">
                                <SelectItem v-for="meal in mealTypes" :key="meal" :value="meal" class="capitalize">
                                    {{ meal }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </Field>
                    <div class="grid grid-cols-2 gap-3">
                        <Field>
                            <FieldLabel class="text-xs">Serving size</FieldLabel>
                            <Select v-model="selectedServingLabel">
                                <SelectTrigger class="w-full">
                                    <SelectValue :placeholder="selectedServingLabel" />
                                </SelectTrigger>
                                <SelectContent class="w-74">
                                    <SelectItem
                                        v-for="option in pendingProduct.servingOptions"
                                        :key="option.label"
                                        :value="option.label"
                                        class="text-sm">
                                        {{ option.label }} ({{ option.gramWeight }}g)
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </Field>
                        <Field>
                            <FieldLabel class="text-xs">Quantity</FieldLabel>
                            <Select v-model="pendingQuantity">
                                <SelectTrigger class="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent class="w-74">
                                    <SelectItem value="0.25">¼</SelectItem>
                                    <SelectItem value="0.5">½</SelectItem>
                                    <SelectItem value="0.75">¾</SelectItem>
                                    <SelectItem v-for="n in 10" :key="n" :value="String(n)">
                                        {{ n }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </Field>
                    </div>
                    <div class="flex flex-col gap-3">
                        <FieldLabel class="text-xs">Nutrition facts</FieldLabel>
                        <NutrientGrid :nutrients="pendingNutrients" />
                    </div>
                    <Button @click="addNutritionEntry" size="sm" class="cursor-pointer">Add Item</Button>
                </div>
            </div>
            <div v-if="mode === 'scan'" class="space-y-4">
                <div
                    class="relative flex aspect-video w-full items-center justify-center overflow-hidden rounded-md border border-input dark:bg-input/30">
                    <video ref="videoEl" class="h-full w-full object-cover"></video>
                    <div v-if="isSearching" class="absolute inset-0 flex items-center justify-center bg-background/50">
                        <Spinner />
                    </div>
                </div>
                <p v-if="scanError" class="text-center text-sm font-medium text-destructive">{{ scanError }}</p>
                <p v-if="scanSuccess" class="text-center text-sm font-medium text-green-600">{{ scanSuccess }}</p>
                <p class="text-center text-xs text-muted-foreground">
                    Center the barcode inside the camera viewpoint to scan.
                </p>
            </div>
            <form @submit.prevent="submit">
                <div v-if="nutritionEntries.length" class="space-y-3 pt-2">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold tracking-tight">Nutrition Entries</h2>
                        <span class="text-xs text-muted-foreground">{{ nutritionEntries.length }} item(s)</span>
                    </div>
                    <div
                        class="to-default relative space-y-4 rounded-md border border-input bg-linear-to-tl from-primary/5 p-4">
                        <ul v-if="nutritionEntries.length" class="divide-y divide-input">
                            <li
                                v-for="(entry, index) in nutritionEntries"
                                :key="index"
                                class="py-3 first:pt-0 last:pb-0">
                                <div class="flex items-start justify-between gap-x-6 transition-colors">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium capitalize">
                                            {{ entry.food_item.toLowerCase() }}
                                        </p>
                                        <div class="mt-1 flex items-center gap-2">
                                            <div
                                                class="mt-1 flex flex-col flex-wrap gap-1 text-xs text-muted-foreground">
                                                <p>{{ entry.serving_size }}</p>
                                                <p class="capitalize">{{ entry.meal_type }}</p>
                                                <p>Protein: {{ (entry.protein * entry.quantity).toFixed(2) }}g</p>
                                                <p>
                                                    Carbohydrates:
                                                    {{ (entry.carbohydrates * entry.quantity).toFixed(2) }}g
                                                </p>
                                                <p>Fat: {{ (entry.fats * entry.quantity).toFixed(2) }}g</p>
                                            </div>
                                        </div>
                                    </div>
                                    <X @click="removeNutritionEntry(index)" class="size-4 shrink-0 cursor-pointer" />
                                </div>
                                <div class="mt-2 flex items-center justify-between gap-x-6 transition-colors">
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium">
                                            {{ Math.round(entry.calories * entry.quantity) }} kcal
                                        </p>
                                    </div>
                                    <Select
                                        :model-value="String(entry.quantity ?? 1)"
                                        @update:model-value="(val) => (entry.quantity = parseFloat(val as string))">
                                        <SelectTrigger class="w-16">
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent class="w-10">
                                            <SelectItem value="0.25">¼</SelectItem>
                                            <SelectItem value="0.5">½</SelectItem>
                                            <SelectItem value="0.75">¾</SelectItem>
                                            <SelectItem v-for="n in 10" :key="n" :value="String(n)">
                                                {{ n }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </li>
                        </ul>
                        <hr class="border-t border-input" />
                        <Field>
                            <FieldLabel class="text-xs font-medium">Date</FieldLabel>
                            <Popover v-slot="{ close }">
                                <PopoverTrigger as-child>
                                    <Button
                                        variant="outline"
                                        class="w-full justify-start bg-background! text-left font-normal">
                                        <CalendarIcon class="mr-2 h-4 w-4" />
                                        {{
                                            entryDate
                                                ? entryDate.toDate(getLocalTimeZone()).toLocaleDateString()
                                                : 'Pick a date'
                                        }}
                                    </Button>
                                </PopoverTrigger>
                                <PopoverContent class="w-auto p-0" align="start">
                                    <Calendar
                                        :model-value="entryDate"
                                        :max-value="today(getLocalTimeZone())"
                                        @update:model-value="
                                            (val) => {
                                                entryDate = val as DateValue;
                                                close();
                                            }
                                        "
                                        initial-focus />
                                </PopoverContent>
                            </Popover>
                        </Field>
                        <hr class="border-t border-input" />
                        <div class="flex flex-col gap-3">
                            <FieldLabel class="text-xs">Nutrition facts</FieldLabel>
                            <NutrientGrid :nutrients="totals" />
                        </div>
                        <Button
                            type="submit"
                            :disabled="!nutritionEntries.length || form.processing"
                            class="cursor-pointer bg-green-500 hover:bg-green-400 dark:text-white">
                            <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />
                            Save
                        </Button>
                    </div>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

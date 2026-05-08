<script setup lang="ts">
import { ref, watch, computed, onUnmounted, type HTMLAttributes } from 'vue'
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
    Field,
    FieldDescription,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select"
import { InputGroup, InputGroupAddon, InputGroupButton, InputGroupInput, InputGroupText, InputGroupTextarea } from '@/components/ui/input-group'
import { Spinner } from '@/components/ui/spinner'
import { BrowserMultiFormatReader } from '@zxing/library'
import { useDebounceFn } from '@vueuse/core'

import AppLayout from '@/layouts/AppLayout.vue';
import { NutritionEntry, BreadcrumbItem } from '@/types';
import { Search, ScanBarcode, X } from 'lucide-vue-next'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Nutrition Entries',
        href: '/nutrition-entries',
    },
];

defineProps<{
    class?: HTMLAttributes["class"],
}>()

const form = useForm({
    meal_type: '',
    date: '',
    items: [] as any[],
})


const tabs = [
    { value: 'text', Icon: Search, label: 'Search' },
    { value: 'scan', Icon: ScanBarcode, label: 'Scan' },
] as const

const nutritionEntries = ref<Pick<NutritionEntry, 'food_item' | 'calories' | 'protein' | 'carbohydrates' | 'fats' | 'quantity' | 'serving_size' | 'meal_type'>[]>([])

const mealTypes = ['breakfast', 'lunch', 'dinner', 'snack'] as const

const search = ref('')
const searchResults = ref<any[]>([])
const isSearching = ref(false)
const mode = ref<'text' | 'scan'>('text')
const mealType = ref<typeof mealTypes[number]>('breakfast')
const touchedSearch = ref(false)

// Pending product for serving size picker
const pendingProduct = ref<any>(null)
const selectedServing = ref<any>(null)
const selectedServingLabel = ref('')
const pendingQuantity = ref('1')

// Scanner
const videoEl = ref<HTMLVideoElement | null>(null)
const scanError = ref('')
const scanSuccess = ref('')
let codeReader: BrowserMultiFormatReader | null = null

const totals = computed(() => ({
    calories: nutritionEntries.value.reduce((sum, item) => sum + (item.calories * item.quantity), 0),
    protein: nutritionEntries.value.reduce((sum, item) => sum + (item.protein * item.quantity), 0),
    carbohydrates: nutritionEntries.value.reduce((sum, item) => sum + (item.carbohydrates * item.quantity), 0),
    fats: nutritionEntries.value.reduce((sum, item) => sum + (item.fats * item.quantity), 0),
}))

// Search
const debouncedSearch = useDebounceFn((query) => {
    searchFood(query)
}, 500)

watch(search, (newVal) => {
    debouncedSearch(newVal)
})

watch(selectedServingLabel, (label) => {
    selectedServing.value = pendingProduct.value?.servingOptions.find((o: any) => o.label === label) ?? null
})

async function searchFood(query: string) {
    if (!query.trim()) {
        searchResults.value = []
        return
    }

    isSearching.value = true

    try {
        const response = await axios.get('/api/foodfacts', {
            params: { food_item: query, type: 'search' }
        })

        console.log(response.data.foods)

        searchResults.value = response.data.foods ?? []
    } catch (error) {
        console.error('Failed to load food facts', error)
    } finally {
        isSearching.value = false
    }
}

// Scanner
async function startScanner() {
    scanError.value = ''
    scanSuccess.value = ''

    codeReader = new BrowserMultiFormatReader()

    try {
        await codeReader.decodeFromVideoDevice(null, videoEl.value!, async (result) => {
            if (result) {
                codeReader?.reset()

                await lookupBarcode(result.getText())
            }
        })
    } catch (e) {
        scanError.value = 'Could not access camera. Please check permissions.'
    }
}

function stopScanner() {
    codeReader?.reset()
    codeReader = null
}

async function lookupBarcode(barcode: string) {
    isSearching.value = true

    try {
        const response = await axios.get(`/api/foodfacts`, {
            params: { food_item: barcode, type: 'barcode' }
        })

        if (response.data && Object.keys(response.data).length) {
            const product = response.data
            const nutriments = product.nutriments ?? {}

            nutritionEntries.value.push({
                food_item: product.product_name,
                calories: nutriments['energy-kcal_100g'] ?? 0,
                protein: nutriments.proteins_100g ?? 0,
                carbohydrates: nutriments.carbohydrates_100g ?? 0,
                fats: nutriments.fat_100g ?? 0,
                quantity: 1,
                serving_size: 100,
                meal_type: mealType.value,
            })

            scanSuccess.value = `Added: ${product.product_name}`
            mode.value = 'text'
        } else {
            scanError.value = 'Product not found for this barcode.'
        }
    } catch (e) {
        scanError.value = 'Failed to look up product.'
    } finally {
        isSearching.value = false
    }
}

watch(mode, (newMode) => {
    if (newMode === 'scan') {
        // Wait for video element to mount
        setTimeout(() => startScanner(), 100)
    } else {
        stopScanner()
    }
})

onUnmounted(() => stopScanner())

// Called when user clicks a search result — shows serving picker
function selectProduct(product: any) {
    const options = [
        { label: '100g', gramWeight: 100 },
        ...(product.servingSize && product.householdServingFullText ? [{
            label: product.householdServingFullText.toLowerCase(),
            gramWeight: product.servingSize,
        }] : []),
        ...(product.foodMeasures ?? []).map((m: any) => ({
            label: m.disseminationText,
            gramWeight: m.gramWeight,
        })),
    ]

    pendingProduct.value = { ...product, servingOptions: options }
    const defaultOption = options[1] ?? options[0]
    selectedServing.value = defaultOption
    selectedServingLabel.value = defaultOption.label
}

// Meal items
function addNutritionEntry() {
    if (!pendingProduct.value || !selectedServing.value) return

    const factor = selectedServing.value.gramWeight / 100
    const getNutrient = (name: string) =>
        pendingProduct.value.foodNutrients?.find((n: any) => n.nutrientName === name)?.value ?? 0

    nutritionEntries.value.push({
        food_item: pendingProduct.value.description,
        calories: Math.round(getNutrient('Energy') * factor),
        protein: parseFloat((getNutrient('Protein') * factor).toFixed(1)),
        carbohydrates: parseFloat((getNutrient('Carbohydrate, by difference') * factor).toFixed(1)),
        fats: parseFloat((getNutrient('Total lipid (fat)') * factor).toFixed(1)),
        quantity: parseFloat(pendingQuantity.value),
        serving_size: selectedServing.value.gramWeight,
        meal_type: mealType.value,
    })

    pendingProduct.value = null
    selectedServing.value = null
    pendingQuantity.value = '1'
    selectedServingLabel.value = ''
    searchResults.value = []
    search.value = ''
}

function removeNutritionEntry(index: number) {
    nutritionEntries.value.splice(index, 1)
}

const pendingNutrients = computed(() => {
    if (!pendingProduct.value || !selectedServing.value) return { calories: 0, protein: 0, carbohydrates: 0, fats: 0 }

    const factor = selectedServing.value.gramWeight / 100 * parseFloat(pendingQuantity.value)
    const get = (name: string) => pendingProduct.value.foodNutrients?.find((n: any) => n.nutrientName === name)?.value ?? 0

    return {
        calories: Math.round(get('Energy') * factor),
        protein: (get('Protein') * factor).toFixed(1),
        carbohydrates: (get('Carbohydrate, by difference') * factor).toFixed(1),
        fats: (get('Total lipid (fat)') * factor).toFixed(1),
    }
})

function submit() {
    if (!nutritionEntries.value.length) return

    useForm({
        meal_type: mealType.value,
        date: new Date().toISOString().split('T')[0],
        entries: nutritionEntries.value.map(entry => ({
            food_item: entry.food_item,
            calories: Math.round(entry.calories * entry.quantity),
            protein: Math.round(entry.protein * entry.quantity),
            carbohydrates: Math.round(entry.carbohydrates * entry.quantity),
            fats: Math.round(entry.fats * entry.quantity),
            serving_size: entry.serving_size,
            quantity: entry.quantity,
        }))
    }).post('/nutrition-entries')
}
</script>

<template>

    <Head title="Create Nutrition Entry" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 md:max-w-4xl">
            <section class="max-w-7xl space-y-12">
                <div class="flex flex-1 flex-col gap-4 p-4">
                    <div class="space-y-6">
                        <form @submit.prevent="submit">
                            <FieldGroup>
                                <div class="flex flex-col items-start text-left">
                                    <span class="sr-only">Nutrition Goals</span>
                                    <h3 class="text-base font-medium mb-0.5">
                                        Nutrition Entries
                                    </h3>
                                    <FieldDescription>
                                        Create your nutrition entry to track your progress towards your nutrition goals.
                                    </FieldDescription>
                                </div>
                                <div class="w-fit! inline-flex gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800">
                                    <button v-for="{ value, Icon, label } in tabs" :key="value" type="button"
                                        @click="mode = value" :class="[
                                            'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                                            mode === value
                                                ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                                                : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
                                        ]">
                                        <component :is="Icon" class="-ml-1 h-4 w-4" />
                                        <span class="ml-1.5 text-sm">{{ label }}</span>
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 gap-8">
                                    <div>
                                        <div v-if="mode === 'text'">
                                            <Field>
                                                <FieldLabel for="search">Search</FieldLabel>
                                                <InputGroup>
                                                    <InputGroupInput v-model="search" @blur="touchedSearch = true"
                                                        id="search" name="search" placeholder="Search..."
                                                        autocomplete="off" :disabled="isSearching" />
                                                    <InputGroupAddon v-if="!isSearching">
                                                        <Search class="size-4 text-muted-foreground" />
                                                    </InputGroupAddon>
                                                    <InputGroupAddon v-else align="inline-end">
                                                        <Spinner class="size-4" />
                                                    </InputGroupAddon>
                                                </InputGroup>
                                            </Field>
                                            <ul v-if="searchResults.length"
                                                class="mt-2 divide-y rounded-lg border h-64 overflow-y-auto">
                                                <li v-for="product in searchResults"
                                                    :key="product.fdcId ?? product.product_name"
                                                    class="flex items-center gap-3 px-4 py-3 hover:bg-muted cursor-pointer transition-colors group"
                                                    @click="selectProduct(product)">
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium capitalize truncate">
                                                            {{ (product.description ??
                                                                product.product_name).toLowerCase() }}
                                                        </p>
                                                        <div
                                                            class="flex items-center gap-1.5 text-xs text-muted-foreground flex-wrap">
                                                            <span v-if="product?.brandName" class="capitalize">{{
                                                                product.brandName.toLowerCase() }}</span>
                                                            <span v-if="product?.brandName"
                                                                class="text-muted-foreground/40">·</span>
                                                            <span>{{product.foodNutrients?.find((n: any) =>
                                                                n.nutrientName ===
                                                                'Energy')?.value ?? 0}} kcal</span>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="shrink-0 text-xs text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity">
                                                        Add +
                                                    </div>
                                                </li>
                                            </ul>
                                            <p v-else-if="search && !isSearching"
                                                class="mt-2 text-sm text-muted-foreground">
                                                No results found.
                                            </p>
                                            <div v-if="pendingProduct"
                                                class="space-y-4 rounded-lg border p-4 bg-linear-to-tl from-primary/5 to-default mt-2">
                                                <div class="flex items-start justify-between">
                                                    <div>
                                                        <p class="text-sm font-medium capitalize">
                                                            {{ pendingProduct.description.toLowerCase() }}
                                                        </p>
                                                        <p v-if="pendingProduct.brandName"
                                                            class="text-xs text-muted-foreground capitalize">
                                                            {{ pendingProduct.brandName.toLowerCase() }}
                                                        </p>
                                                    </div>
                                                    <Button size="icon" @click="pendingProduct = null"
                                                        class="cursor-pointer size-5">
                                                        <X />
                                                    </Button>
                                                </div>
                                                <div class="space-y-6">
                                                    <Field>
                                                        <FieldLabel class="text-xs font-medium">Meal Type</FieldLabel>
                                                        <Select v-model="mealType">
                                                            <SelectTrigger class="w-full">
                                                                <SelectValue />
                                                            </SelectTrigger>
                                                            <SelectContent>
                                                                <SelectItem v-for="meal in mealTypes" :key="meal"
                                                                    :value="meal">
                                                                    {{ meal.charAt(0).toUpperCase() + meal.slice(1) }}
                                                                </SelectItem>
                                                            </SelectContent>
                                                        </Select>
                                                    </Field>
                                                    <Field class="grid grid-cols-2 gap-4">
                                                        <Field>
                                                            <FieldLabel class="text-xs font-medium">Quantity
                                                            </FieldLabel>
                                                            <Select v-model="pendingQuantity">
                                                                <SelectTrigger class="w-full">
                                                                    <SelectValue />
                                                                </SelectTrigger>
                                                                <SelectContent>
                                                                    <SelectItem value="0.25">¼</SelectItem>
                                                                    <SelectItem value="0.5">½</SelectItem>
                                                                    <SelectItem value="0.75">¾</SelectItem>
                                                                    <SelectItem v-for="n in 10" :key="n"
                                                                        :value="String(n)">
                                                                        {{ n }}</SelectItem>
                                                                </SelectContent>
                                                            </Select>
                                                        </Field>
                                                        <Field>
                                                            <FieldLabel class="text-xs font-medium">Serving Size
                                                            </FieldLabel>
                                                            <Select v-model="selectedServingLabel">
                                                                <SelectTrigger class="w-full">
                                                                    <SelectValue />
                                                                </SelectTrigger>
                                                                <SelectContent>
                                                                    <SelectItem
                                                                        v-for="option in pendingProduct.servingOptions"
                                                                        :key="option.label" :value="option.label">
                                                                        {{ option.label }} ({{ option.gramWeight }}g)
                                                                    </SelectItem>
                                                                </SelectContent>
                                                            </Select>
                                                        </Field>
                                                    </Field>
                                                    <div v-if="selectedServing"
                                                        class="grid grid-cols-4 gap-2 text-center text-xs">
                                                        <div>
                                                            <p class="font-medium">{{ pendingNutrients.calories }}</p>
                                                            <p class="text-muted-foreground">kcal</p>
                                                        </div>
                                                        <div>
                                                            <p class="font-medium">{{ pendingNutrients.protein }}g</p>
                                                            <p class="text-muted-foreground">protein</p>
                                                        </div>
                                                        <div>
                                                            <p class="font-medium">{{ pendingNutrients.carbohydrates }}g
                                                            </p>
                                                            <p class="text-muted-foreground">carbs</p>
                                                        </div>
                                                        <div>
                                                            <p class="font-medium">{{ pendingNutrients.fats }}g</p>
                                                            <p class="text-muted-foreground">fat</p>
                                                        </div>
                                                    </div>
                                                    <Button @click="addNutritionEntry" class="cursor-pointer">
                                                        Add to Entries
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Barcode scan -->
                                        <div v-else class="space-y-3">
                                            <div class="relative overflow-hidden rounded-lg bg-black aspect-video">
                                                <video ref="videoEl" class="w-full h-full object-cover" />
                                                <!-- scanning line animation -->
                                                <div
                                                    class="absolute inset-x-0 h-0.5 bg-red-500 animate-bounce top-1/2" />
                                            </div>
                                            <p class="text-sm text-muted-foreground text-center">
                                                Point your camera at a barcode
                                            </p>
                                            <p v-if="scanError" class="text-sm text-red-500 text-center">{{ scanError }}
                                            </p>
                                            <p v-if="scanSuccess" class="text-sm text-green-500 text-center">{{
                                                scanSuccess
                                                }}</p>
                                        </div>
                                    </div>
                                    <div v-if="nutritionEntries.length" class="space-y-2">
                                        <h4 class="text-sm font-medium">Added Entries</h4>

                                        <div
                                            class="space-y-4 rounded-lg border p-4 bg-linear-to-tl from-primary/5 to-default">
                                            <ul>
                                                <li v-for="(entry, index) in nutritionEntries" :key="index"
                                                    class="flex items-center justify-between py-3">
                                                    <div class="flex-1 min-w-0 mr-4">
                                                        <p class="text-sm font-medium truncate capitalize">{{
                                                            entry.food_item.toLowerCase() }}</p>
                                                        <p class="text-xs text-muted-foreground">
                                                            <span class="capitalize">{{ entry.meal_type }}</span>
                                                            <span class="text-muted-foreground/40 mx-1">·</span>
                                                            {{ Math.round(entry.calories * entry.quantity) }} kcal
                                                        </p>
                                                    </div>
                                                    <div class="flex items-center gap-2 shrink-0">
                                                        <span class="text-xs text-muted-foreground">Qty:</span>
                                                        <Input type="number" min="0.5" step="0.5"
                                                            v-model.number="entry.quantity"
                                                            class="h-8 w-16 text-center" />
                                                        <Button size="icon" @click="removeNutritionEntry(index)"
                                                            class="cursor-pointer size-5">
                                                            <X />
                                                        </Button>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="rounded-lg border px-4 py-3 space-y-2">
                                            <h4 class="text-sm font-medium">Totals</h4>
                                            <div class="grid grid-cols-4 gap-2 text-center">
                                                <div>
                                                    <p class="text-sm font-medium">{{ Math.round(totals.calories) }}
                                                    </p>
                                                    <p class="text-xs text-muted-foreground">Calories</p>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium">{{ totals.protein.toFixed(1) }}g
                                                    </p>
                                                    <p class="text-xs text-muted-foreground">Protein</p>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium">{{
                                                        totals.carbohydrates.toFixed(1) }}g
                                                    </p>
                                                    <p class="text-xs text-muted-foreground">Carbs</p>
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium">{{ totals.fats.toFixed(1) }}g</p>
                                                    <p class="text-xs text-muted-foreground">Fat</p>
                                                </div>
                                            </div>
                                        </div>
                                        <Button type="submit" :disabled="!nutritionEntries.length || form.processing"
                                            class="bg-green-500 hover:bg-green-400 dark:text-white cursor-pointer">
                                            <Spinner v-if="form.processing" class="mr-2 h-4 w-4" />
                                            Save
                                        </Button>
                                    </div>
                                </div>
                            </FieldGroup>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
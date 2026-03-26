<script setup lang="ts">
import { ref, watch, computed, type HTMLAttributes } from 'vue'
import { Head, useForm } from '@inertiajs/vue3';
import { cn } from "@/lib/utils"
import axios from 'axios';

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { InputGroup, InputGroupAddon, InputGroupInput, InputGroupText } from '@/components/ui/input-group'
import {
    Field,
    FieldDescription,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field'
import { Spinner } from '@/components/ui/spinner';
import { useDebounceFn } from '@vueuse/core'

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Search, ScanBarcode } from 'lucide-vue-next'

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
    search: '',
    food_item: '',
    calories: 0,
    protein: 0,
    carbohydrates: 0,
    fats: 0,
})

const debouncedSearch = useDebounceFn((query) => {
    searchFood(query);
}, 400);

const tabs = [
    { value: 'text', Icon: Search, label: 'Search' },
    { value: 'scan', Icon: ScanBarcode, label: 'Scan' },
] as const

const searchResults = ref([])
const isSearching = ref(false)
const mode = ref<'text' | 'scan'>('text')

// Touch tracking so we don't show errors before the user interacts
const touchedSearch = ref(false)
const attemptedSubmit = ref(false)

function markTouched(field: string) {
    if (field === 'search') touchedSearch.value = true
}

const searchError = computed(() => {
    if (!form.search && (touchedSearch.value || attemptedSubmit.value)) return 'Search is required.'
    return ''
})

watch(() => form.search, (newVal) => {
    debouncedSearch(newVal);
});

async function searchFood(query: string) {
    if (!query.trim()) {
        searchResults.value = []
        return
    }

    isSearching.value = true

    try {
        console.log('Searching for:', query)
        console.log('Searching test:', encodeURIComponent(query))

        const response = await axios.get(`/api/foodfacts?food_item=${encodeURIComponent(query)}`);

        console.log('Search results:', response.data);

        searchResults.value = response.data.products ?? []
    } catch (error) {
        console.error("Failed to load food facts", error);
    } finally {
        isSearching.value = false
    }
}

function selectProduct(product: any) {
    const nutriments = product.nutriments ?? {}

    form.food_item = product.product_name
    form.calories = nutriments['energy-kcal_100g'] ?? 0
    form.protein = nutriments.proteins_100g ?? 0
    form.carbohydrates = nutriments.carbohydrates_100g ?? 0
    form.fats = nutriments.fat_100g ?? 0

    // Clear search results after selecting
    searchResults.value = []
    form.search = ''
}

watch(() => form.search, debouncedSearch)

function submit() {
    // attemptedSubmit.value = true
    // touchedDate.value = true
    // touchedWeight.value = true

    // if (hasClientErrors.value) return

    // form
    //     .transform((data) => ({
    //         ...data,
    //         date: data.date.toString(),
    //     }))
    //     .post('/nutrition-entries')
}
</script>

<template>

    <Head title="Create Nutrition Entry" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 md:max-w-2xl">
            <section class="max-w-xl space-y-12">
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
                                <div v-if="mode === 'text'">
                                    <Field>
                                        <FieldLabel for="search">
                                            Search
                                        </FieldLabel>
                                        <Input v-model="form.search" @blur="markTouched('search')" id="search"
                                            name="search" step="1" type="text" class="h-9"
                                            :class="{ 'border-red-500': searchError || form.errors.search }" />
                                        <div v-if="searchError || form.errors.search" class="text-red-500 text-sm">
                                            {{ searchError || form.errors.search }}
                                        </div>
                                    </Field>

                                    <ul v-if="searchResults.length"
                                        class="mt-2 divide-y divide-white/5 rounded-lg border border-white/10">
                                        <li v-for="product in searchResults" :key="product"
                                            class="flex items-center justify-between px-4 py-3 hover:bg-white/5 cursor-pointer transition-colors"
                                            @click="selectProduct(product)">
                                            <div>
                                                <!-- <p class="text-sm font-medium text-white">{{ product.product_name }}</p>
                                                <p class="text-xs text-gray-400">{{ product.nutriments?.energy_kcal }}
                                                    kcal per 100g</p> -->
                                            </div>
                                        </li>
                                    </ul>

                                    <p v-else-if="form.search && !isSearching" class="mt-2 text-sm text-gray-400">
                                        No results found.
                                    </p>
                                </div>
                                <!-- Barcode scan -->
                                <div v-else>
                                    <!-- Camera/barcode scanner goes here -->
                                </div>
                            </FieldGroup>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
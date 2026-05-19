<script setup lang="ts">
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem, type NutritionEntry } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Pencil, Trash } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Nutrition Entries',
        href: '/nutrition-entries',
    },
];

const props = defineProps<{
    nutritionEntry: NutritionEntry,
}>()

const open = ref(false)
const form = useForm({})

function submit() {
    form.delete(`/nutrition-entries/${props.nutritionEntry.id}`, {
        onSuccess: () => open.value = false,
    });
}
</script>

<template>

    <Head title="Nutrition Entry" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <template #actions>
            <div class="flex items-center gap-2">
                <Button variant="secondary" as-child>
                    <a :href="`/nutrition-entries/${props.nutritionEntry.id}/edit`">
                        <Pencil class="size-4" />
                        Edit Entry
                    </a>
                </Button>
                <Dialog v-model:open="open">
                    <DialogTrigger as-child>
                        <Button variant="destructive" class="cursor-pointer">
                            <Trash class="size-4" />
                            Delete Entry
                        </Button>
                    </DialogTrigger>
                    <DialogContent>
                        <form @submit.prevent="submit" class="space-y-6">
                            <DialogHeader>
                                <DialogTitle>Delete Nutrition Entry</DialogTitle>
                                <DialogDescription>
                                    Are you sure you want to delete {{ props.nutritionEntry.food_item }}?
                                    This action cannot be undone.
                                </DialogDescription>
                            </DialogHeader>
                            <DialogFooter class="gap-2">
                                <DialogClose as-child>
                                    <Button variant="secondary" class="cursor-pointer">
                                        Cancel
                                    </Button>
                                </DialogClose>
                                <Button :disabled="form.processing" variant="destructive" type="submit"
                                    class="cursor-pointer">
                                    <span v-if="form.processing">Deleting...</span>
                                    <span v-else>Delete entry</span>
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </template>
        <div class="flex flex-col gap-4 p-4">
            <div>
                <div class="px-4 sm:px-0">
                    <h3 class="text-base/7 font-semibold capitalize">
                        {{ props.nutritionEntry.food_item }}
                    </h3>
                    <p class="mt-1 max-w-2xl text-sm/6 text-gray-400 capitalize">
                        {{ props.nutritionEntry.meal_type }}
                    </p>
                </div>
                <div class="mt-6 border-t border-input">
                    <div class="divide-y divide-input">
                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                            <div class="text-sm/6 font-medium">Calories</div>
                            <div class="mt-1 text-sm/6 text-gray-400 sm:col-span-2 sm:mt-0">
                                {{ props.nutritionEntry.calories }} kcal
                            </div>
                        </div>
                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                            <div class="text-sm/6 font-medium">Protein</div>
                            <div class="mt-1 text-sm/6 text-gray-400 sm:col-span-2 sm:mt-0">
                                {{ props.nutritionEntry.protein }}g
                            </div>
                        </div>
                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                            <div class="text-sm/6 font-medium">Carbohydrates</div>
                            <div class="mt-1 text-sm/6 text-gray-400 sm:col-span-2 sm:mt-0">
                                {{ props.nutritionEntry.carbohydrates }}g
                            </div>
                        </div>
                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                            <div class="text-sm/6 font-medium">Fat</div>
                            <div class="mt-1 text-sm/6 text-gray-400 sm:col-span-2 sm:mt-0">
                                {{ props.nutritionEntry.fats }}g
                            </div>
                        </div>
                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                            <div class="text-sm/6 font-medium">Serving size</div>
                            <div class="mt-1 text-sm/6 text-gray-400 sm:col-span-2 sm:mt-0">
                                {{ props.nutritionEntry.serving_size }}
                            </div>
                        </div>
                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                            <div class="text-sm/6 font-medium">Quantity</div>
                            <div class="mt-1 text-sm/6 text-gray-400 sm:col-span-2 sm:mt-0">
                                {{ props.nutritionEntry.quantity }}
                            </div>
                        </div>
                        <div class="px-4 py-6 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-0">
                            <div class="text-sm/6 font-medium">Date</div>
                            <div class="mt-1 text-sm/6 text-gray-400 sm:col-span-2 sm:mt-0">
                                {{ props.nutritionEntry.date }}, {{ props.nutritionEntry.time }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
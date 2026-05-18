<script setup lang="ts">
interface Nutrients {
    calories: number
    protein: number
    carbohydrates: number
    fats: number
}

const { nutrients, backgroundClass = 'bg-background' } = defineProps<{
    nutrients: Nutrients
    backgroundClass?: string
}>()

const formatValue = (val: string | number, decimals = 2): string => {
    const num = typeof val === 'string' ? parseFloat(val) : val

    return isNaN(num) ? '0.00' : num.toFixed(decimals)
}
</script>

<template>
    <div :class="`rounded-md border border-input px-4 py-3 space-y-2 ${backgroundClass}`">
        <div class="grid grid-cols-4 gap-2 text-center">
            <div>
                <p class="font-bold text-md">
                    {{ Math.round(typeof nutrients.calories === 'string'
                        ? parseFloat(nutrients.calories)
                        : nutrients.calories) || 0 }}
                </p>
                <p class="text-xs text-muted-foreground">Calories</p>
            </div>
            <div>
                <div class="flex items-baseline justify-center">
                    <p class="font-bold text-md">{{ formatValue(nutrients.protein) }}</p>
                    <span class="font-normal text-muted-foreground text-sm">g</span>
                </div>
                <p class="text-xs text-muted-foreground">Protein</p>
            </div>
            <div>
                <div class="flex items-baseline justify-center">
                    <p class="font-bold text-md">{{ formatValue(nutrients.carbohydrates) }}</p>
                    <span class="font-normal text-muted-foreground text-sm">g</span>
                </div>
                <p class="text-xs text-muted-foreground">Carbohydrates</p>
            </div>
            <div>
                <div class="flex items-baseline justify-center">
                    <p class="font-bold text-md">{{ formatValue(nutrients.fats) }}</p>
                    <span class="font-normal text-muted-foreground text-sm">g</span>
                </div>
                <p class="text-xs text-muted-foreground">Fat</p>
            </div>
        </div>
    </div>
</template>
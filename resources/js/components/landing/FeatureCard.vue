<script setup lang="ts">
import { motion } from 'motion-v';

defineProps<{
    icon: any;
    title: string;
    description: string;
    index: number;
}>();
</script>

<template>
    <motion.div
        class="group relative rounded-md border bg-white/70 p-8 backdrop-blur-sm transition-all duration-500 hover:border-green-500"
        :initial="{ opacity: 0, y: 40 }"
        :while-in-view="{ opacity: 1, y: 0 }"
        :in-view-options="{ once: true, margin: '-50px' }"
        :transition="{
            duration: 0.45,
            delay: index * 0.1,
            ease: [0.16, 1, 0.3, 1],
        }">
        <div
            class="mb-6 flex h-10 w-10 items-center justify-center rounded-md bg-gray-200/60 transition-colors duration-500 group-hover:bg-green-500">
            <component :is="icon" class="h-5 w-5" />
        </div>
        <h3 class="mb-3 text-lg font-semibold tracking-tight">
            {{ title }}
        </h3>
        <p class="text-sm leading-relaxed text-muted-foreground">
            {{ description }}
        </p>
    </motion.div>
</template>

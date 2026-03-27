<script setup lang="ts">
import { motion } from "motion-v";

defineProps<{
    icon: any;
    title: string;
    description: string;
    index: number;
}>();
</script>

<template>
    <motion.div
        class="group relative p-8 bg-white/70 backdrop-blur-sm border rounded-md hover:border-green-500 transition-all duration-500"
        :initial="{ opacity: 0, y: 40 }" :while-in-view="{ opacity: 1, y: 0 }"
        :viewport="{ once: true, margin: '-50px' }" :transition="{
            duration: 0.45,
            delay: index * 0.1,
            ease: [0.16, 1, 0.3, 1]
        }">
        <div
            class="w-10 h-10 flex items-center justify-center rounded-md bg-gray-200/60 mb-6 group-hover:bg-green-500 transition-colors duration-500">
            <component :is="icon" class="w-5 h-5" />
        </div>
        <h3 class="font-semibold text-lg mb-3 tracking-tight">
            {{ title }}
        </h3>
        <p class="text-sm text-muted-foreground leading-relaxed">
            {{ description }}
        </p>
    </motion.div>
</template>
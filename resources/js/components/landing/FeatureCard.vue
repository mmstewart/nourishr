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
        class="group relative p-8 bg-white/70 backdrop-blur-sm border border-carbon/[0.06] rounded-sm hover:border-metabolic/40 transition-all duration-500"
        :initial="{ opacity: 0, y: 40 }" :while-in-view="{ opacity: 1, y: 0 }"
        :viewport="{ once: true, margin: '-50px' }" :transition="{
            duration: 0.7,
            delay: index * 0.1,
            ease: [0.16, 1, 0.3, 1]
        }">
        <div
            class="w-10 h-10 flex items-center justify-center rounded-sm bg-oxygen-blue/60 text-carbon mb-6 group-hover:bg-metabolic/10 transition-colors duration-500">
            <component :is="icon" class="w-5 h-5" />
        </div>

        <h3 class="font-space font-semibold text-lg text-carbon mb-3 tracking-tight">
            {{ title }}
        </h3>

        <p class="font-inter text-sm text-carbon/50 leading-relaxed">
            {{ description }}
        </p>

        <div class="absolute bottom-0 left-0 w-0 h-[1px] bg-metabolic group-hover:w-full transition-all duration-700" />
    </motion.div>
</template>
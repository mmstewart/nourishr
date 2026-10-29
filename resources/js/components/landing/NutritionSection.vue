<script setup lang="ts">
import { PieChart, Scan, Target, Zap } from 'lucide-vue-next';
import { motion } from 'motion-v';
import FeatureCard from './FeatureCard.vue';

interface Feature {
    icon: any;
    title: string;
    description: string;
}

defineProps<{
    image: string;
}>();

const features: Feature[] = [
    {
        icon: Scan,
        title: 'Visual Food Scanning',
        description:
            'Log meals instantly. Our AI-powered scanner identifies foods and calculates macros with clinical precision.',
    },
    {
        icon: PieChart,
        title: 'Macro Decomposition',
        description:
            'Break down every meal into proteins, carbohydrates, and fats. See your nutritional architecture in real-time.',
    },
    {
        icon: Zap,
        title: 'Metabolic Insights',
        description: 'Understand your metabolic rate and how each calorie is being utilized by your biological system.',
    },
    {
        icon: Target,
        title: 'Goal Calibration',
        description:
            'Set precise caloric targets aligned with your body composition goals. Deficit, surplus, or maintenance.',
    },
];
</script>

<template>
    <section id="features" class="relative overflow-hidden py-20 md:py-28">
        <motion.div
            class="absolute top-20 left-6 md:left-12"
            :initial="{ opacity: 0 }"
            :while-in-view="{ opacity: 1 }"
            :in-view-options="{ once: true }">
            <span class="text-[10px] tracking-[0.4em] text-green-500 uppercase">01 — Nutrition Engine</span>
        </motion.div>
        <div class="mx-auto max-w-7xl px-6 md:px-12">
            <div class="mb-12 grid items-center gap-16 lg:grid-cols-2">
                <motion.div
                    :initial="{ opacity: 0, x: -40 }"
                    :while-in-view="{ opacity: 1, x: 0 }"
                    :in-view-options="{ once: true }"
                    :transition="{ duration: 0.8, ease: [0.16, 1, 0.3, 1] }">
                    <h2 class="mb-6 text-4xl leading-[1.05] font-bold tracking-tight md:text-5xl lg:text-6xl">
                        Healthy eating,
                        <span class="text-green-500">simplified.</span>
                    </h2>
                    <p class="max-w-md text-lg leading-relaxed text-muted-foreground/50">
                        Nourishr uses AI-powered scanning to log meals in seconds. Search foods effortlessly and track
                        your calories without the stress of manual entry.
                    </p>
                </motion.div>
                <motion.div
                    class="relative mx-auto aspect-square max-w-md overflow-hidden rounded-md lg:mx-0"
                    :initial="{ opacity: 0, scale: 0.95 }"
                    :while-in-view="{ opacity: 1, scale: 1 }"
                    :in-view-options="{ once: true }"
                    :transition="{ duration: 0.8, delay: 0.2 }">
                    <img
                        :src="image"
                        alt="Macro photography"
                        class="h-full w-full object-cover grayscale transition-all duration-1000 hover:grayscale-0" />
                    <div class="absolute inset-0 bg-linear-to-t from-gray-300/10 to-transparent" />
                </motion.div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <FeatureCard v-for="(f, i) in features" :key="f.title" v-bind="f" :index="i" />
            </div>
        </div>
    </section>
</template>

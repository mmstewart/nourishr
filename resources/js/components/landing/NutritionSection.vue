<script setup lang="ts">
import { motion } from "motion-v";
import { Scan, PieChart, Zap, Target } from 'lucide-vue-next';
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
        title: "Visual Food Scanning",
        description: "Log meals instantly. Our AI-powered scanner identifies foods and calculates macros with clinical precision.",
    },
    {
        icon: PieChart,
        title: "Macro Decomposition",
        description: "Break down every meal into proteins, carbohydrates, and fats. See your nutritional architecture in real-time.",
    },
    {
        icon: Zap,
        title: "Metabolic Insights",
        description: "Understand your metabolic rate and how each calorie is being utilized by your biological system.",
    },
    {
        icon: Target,
        title: "Goal Calibration",
        description: "Set precise caloric targets aligned with your body composition goals. Deficit, surplus, or maintenance.",
    },
];
</script>

<template>
    <section id="features" class="relative py-20 md:py-28 overflow-hidden">
        <motion.div class="absolute top-20 left-6 md:left-12" :initial="{ opacity: 0 }" :while-in-view="{ opacity: 1 }"
            :viewport="{ once: true }">
            <span class="text-[10px] uppercase tracking-[0.4em] text-green-500">
                01 — Nutrition Engine
            </span>
        </motion.div>
        <div class="max-w-7xl mx-auto px-6 md:px-12">
            <div class="grid lg:grid-cols-2 gap-16 items-center mb-12">
                <motion.div :initial="{ opacity: 0, x: -40 }" :while-in-view="{ opacity: 1, x: 0 }"
                    :viewport="{ once: true }" :transition="{ duration: 0.8, ease: [0.16, 1, 0.3, 1] }">
                    <h2 class="font-bold text-4xl md:text-5xl lg:text-6xl leading-[1.05] tracking-tight mb-6">
                        Healthy eating,
                        <span class="text-green-500">simplified.</span>
                    </h2>
                    <p class="text-lg text-muted-foreground/50 leading-relaxed max-w-md">
                        Nourishr uses AI-powered scanning to log meals in seconds. Search foods effortlessly and track
                        your calories without the stress of manual entry.
                    </p>
                </motion.div>
                <motion.div class="relative aspect-square max-w-md mx-auto lg:mx-0 rounded-md overflow-hidden"
                    :initial="{ opacity: 0, scale: 0.95 }" :while-in-view="{ opacity: 1, scale: 1 }"
                    :viewport="{ once: true }" :transition="{ duration: 0.8, delay: 0.2 }">
                    <img :src="image" alt="Macro photography"
                        class="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-1000" />
                    <div class="absolute inset-0 bg-linear-to-t from-gray-300/10 to-transparent" />
                </motion.div>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <FeatureCard v-for="(f, i) in features" :key="f.title" v-bind="f" :index="i" />
            </div>
        </div>
    </section>
</template>
<script setup lang="ts">
import { computed } from 'vue';
import { motion } from "motion-v";
import { TrendingDown, Activity, Scale, BarChart3 } from 'lucide-vue-next';
import FeatureCard from './FeatureCard.vue';

interface Feature {
    icon: any;
    title: string;
    description: string;
}

const trendData = [82, 74, 70, 65, 60, 54, 48];

const linePath = computed(() => {
    return trendData
        .map((val, i) => {
            const x = (i / (trendData.length - 1)) * 100;
            const y = 100 - val;
            return `${x},${y}`;
        })
        .join(' ');
});

const areaPath = computed(() => {
    const firstX = 0;
    const lastX = 100;
    // We take the line path and add the bottom-right and bottom-left corners
    return `${linePath.value} ${lastX},100 ${firstX},100`;
});

const features: Feature[] = [
    {
        icon: Scale,
        title: "Daily Weigh-Ins",
        description: "Track your weight with zero friction. Log in seconds and watch your trend line emerge over time.",
    },
    {
        icon: TrendingDown,
        title: "Trend Smoothing",
        description: "Stop obsessing over daily scale changes. We map your historical data to show the consistent patterns of your transformation.",
    },
    {
        icon: Activity,
        title: "Body Composition",
        description: "Go beyond the scale. Track the relationship between your nutrition input and weight output.",
    },
    {
        icon: BarChart3,
        title: "Progress Reports",
        description: "Weekly and monthly reports that visualize your biological transformation with clinical clarity.",
    },
];

defineProps<{
    image: string;
}>();
</script>

<template>
    <section id="science" class="relative py-32 md:py-40 overflow-hidden"
        :style="{ background: 'linear-gradient(180deg, #f3f4f6 0%, #f0faf3 50%, #f3f4f6 100%)' }">
        <motion.div class="absolute top-20 right-6 md:right-12 text-right" :initial="{ opacity: 0 }"
            :while-in-view="{ opacity: 1 }" :viewport="{ once: true }">
            <span class="text-[10px] uppercase tracking-[0.4em] text-green-500">
                02 — Gravity Suite
            </span>
        </motion.div>
        <div class="max-w-7xl mx-auto px-6 md:px-12">
            <div class="grid lg:grid-cols-2 gap-16 items-center mb-20">
                <motion.div class="relative aspect-3/2 rounded-md overflow-hidden order-2 lg:order-1"
                    :initial="{ opacity: 0, scale: 0.95 }" :while-in-view="{ opacity: 1, scale: 1 }"
                    :viewport="{ once: true }" :transition="{ duration: 0.8 }">
                    <img :src="image" alt="Macro photography of water droplets"
                        class="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-1000" />
                    <div class="absolute inset-0 bg-linear-to-b from-transparent to-gray-300/10" />
                    <div class="absolute bottom-6 left-6 right-6 p-4 bg-white/80 backdrop-blur-md rounded-md border">
                        <div class="flex items-end justify-between mb-2">
                            <span class="text-[10px] uppercase tracking-[0.2em]">Last 7
                                days</span>
                            <span class="text-xs text-green-500">-3 lbs</span>
                        </div>
                        <div class="h-12 w-full px-1">
                            <svg viewBox="0 0 100 100" preserveAspectRatio="none"
                                class="w-full h-full overflow-visible">
                                <defs>
                                    <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#22c55e" stop-opacity="0.3" />
                                        <stop offset="100%" stop-color="#22c55e" stop-opacity="0" />
                                    </linearGradient>
                                </defs>
                                <polygon :points="areaPath" fill="url(#chartGradient)" />
                                <polyline fill="none" stroke="#22c55e" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" :points="linePath"
                                    style="vector-effect: non-scaling-stroke;" />
                                <path v-for="(val, i) in trendData" :key="i"
                                    :d="`M ${(i / (trendData.length - 1)) * 100},${100 - val} L ${(i / (trendData.length - 1)) * 100},${100 - val}`"
                                    stroke="#22c55e" stroke-width="6" stroke-linecap="round"
                                    class="cursor-crosshair transition-all duration-200 hover:stroke-green-400"
                                    style="vector-effect: non-scaling-stroke;" />
                            </svg>
                        </div>
                    </div>
                </motion.div>
                <motion.div class="order-1 lg:order-2" :initial="{ opacity: 0, x: 40 }"
                    :while-in-view="{ opacity: 1, x: 0 }" :viewport="{ once: true }"
                    :transition="{ duration: 0.8, ease: [0.16, 1, 0.3, 1] }">
                    <h2 class="font-bold text-4xl md:text-5xl lg:text-6xl leading-[1.05] tracking-tight mb-6">
                        Your weight,
                        <span class="text-green-500">mapped.</span>
                    </h2>
                    <p class="text-lg text-muted-foreground/50 leading-relaxed max-w-md">
                        Whether you're building better habits or staying mindful of what you eat, Nourishr gives you
                        clear, helpful insights to support your everyday health.
                    </p>
                </motion.div>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <FeatureCard v-for="(f, i) in features" :key="f.title" v-bind="f" :index="i" />
            </div>
        </div>
    </section>
</template>
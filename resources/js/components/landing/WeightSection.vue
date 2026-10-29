<script setup lang="ts">
import { Activity, BarChart3, Scale, TrendingDown } from 'lucide-vue-next';
import { motion } from 'motion-v';
import { computed } from 'vue';
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
        title: 'Daily Weigh-Ins',
        description: 'Track your weight with zero friction. Log in seconds and watch your trend line emerge over time.',
    },
    {
        icon: TrendingDown,
        title: 'Trend Smoothing',
        description:
            'Stop obsessing over daily scale changes. We map your historical data to show the consistent patterns of your transformation.',
    },
    {
        icon: Activity,
        title: 'Body Composition',
        description: 'Go beyond the scale. Track the relationship between your nutrition input and weight output.',
    },
    {
        icon: BarChart3,
        title: 'Progress Reports',
        description: 'Weekly and monthly reports that visualize your biological transformation with clinical clarity.',
    },
];

defineProps<{
    image: string;
}>();
</script>

<template>
    <section
        id="science"
        class="relative overflow-hidden py-32 md:py-40"
        :style="{ background: 'linear-gradient(180deg, #f3f4f6 0%, #f0faf3 50%, #f3f4f6 100%)' }">
        <motion.div
            class="absolute top-20 right-6 text-right md:right-12"
            :initial="{ opacity: 0 }"
            :while-in-view="{ opacity: 1 }"
            :in-view-options="{ once: true }">
            <span class="text-[10px] tracking-[0.4em] text-green-500 uppercase">02 — Gravity Suite</span>
        </motion.div>
        <div class="mx-auto max-w-7xl px-6 md:px-12">
            <div class="mb-20 grid items-center gap-16 lg:grid-cols-2">
                <motion.div
                    class="relative order-2 aspect-3/2 overflow-hidden rounded-md lg:order-1"
                    :initial="{ opacity: 0, scale: 0.95 }"
                    :while-in-view="{ opacity: 1, scale: 1 }"
                    :in-view-options="{ once: true }"
                    :transition="{ duration: 0.8 }">
                    <img
                        :src="image"
                        alt="Macro photography of water droplets"
                        class="h-full w-full object-cover grayscale transition-all duration-1000 hover:grayscale-0" />
                    <div class="absolute inset-0 bg-linear-to-b from-transparent to-gray-300/10" />
                    <div class="absolute right-6 bottom-6 left-6 rounded-md border bg-white/80 p-4 backdrop-blur-md">
                        <div class="mb-2 flex items-end justify-between">
                            <span class="text-[10px] tracking-[0.2em] uppercase">Last 7 days</span>
                            <span class="text-xs text-green-500">-3 lbs</span>
                        </div>
                        <div class="h-12 w-full px-1">
                            <svg
                                viewBox="0 0 100 100"
                                preserveAspectRatio="none"
                                class="h-full w-full overflow-visible">
                                <defs>
                                    <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                                        <stop offset="0%" stop-color="#22c55e" stop-opacity="0.3" />
                                        <stop offset="100%" stop-color="#22c55e" stop-opacity="0" />
                                    </linearGradient>
                                </defs>
                                <polygon :points="areaPath" fill="url(#chartGradient)" />
                                <polyline
                                    fill="none"
                                    stroke="#22c55e"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    :points="linePath"
                                    style="vector-effect: non-scaling-stroke" />
                                <path
                                    v-for="(val, i) in trendData"
                                    :key="i"
                                    :d="`M ${(i / (trendData.length - 1)) * 100},${100 - val} L ${(i / (trendData.length - 1)) * 100},${100 - val}`"
                                    stroke="#22c55e"
                                    stroke-width="6"
                                    stroke-linecap="round"
                                    class="cursor-crosshair transition-all duration-200 hover:stroke-green-400"
                                    style="vector-effect: non-scaling-stroke" />
                            </svg>
                        </div>
                    </div>
                </motion.div>
                <motion.div
                    class="order-1 lg:order-2"
                    :initial="{ opacity: 0, x: 40 }"
                    :while-in-view="{ opacity: 1, x: 0 }"
                    :in-view-options="{ once: true }"
                    :transition="{ duration: 0.8, ease: [0.16, 1, 0.3, 1] }">
                    <h2 class="mb-6 text-4xl leading-[1.05] font-bold tracking-tight md:text-5xl lg:text-6xl">
                        Your weight,
                        <span class="text-green-500">mapped.</span>
                    </h2>
                    <p class="max-w-md text-lg leading-relaxed text-muted-foreground/50">
                        Whether you're building better habits or staying mindful of what you eat, Nourishr gives you
                        clear, helpful insights to support your everyday health.
                    </p>
                </motion.div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <FeatureCard v-for="(f, i) in features" :key="f.title" v-bind="f" :index="i" />
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { motion } from "motion-v"

// State
const calories = ref<number>(0);
const mousePos = ref({ x: 0, y: 0 });
const ringRef = ref<HTMLElement | null>(null);

// Constants
const size = 340;
const r = size / 2 - 4;

// 1. Calorie Ticker Logic
let calorieInterval: ReturnType<typeof setInterval>;

// 2. Mouse Tracking Logic
const handleMouse = (e: MouseEvent) => {
    if (!ringRef.value) return;
    const rect = ringRef.value.getBoundingClientRect();
    const cx = rect.left + rect.width / 2;
    const cy = rect.top + rect.height / 2;

    mousePos.value = {
        x: ((e.clientX - cx) / rect.width) * 12,
        y: ((e.clientY - cy) / rect.height) * 12,
    };
};

onMounted(() => {
    // Start Calorie Interval
    calorieInterval = setInterval(() => {
        if (calories.value >= 2080) {
            calories.value = 2080;
            return;
        }
        calories.value += Math.floor(Math.random() * 23 + 5);
    }, 60);

    // Start Mouse Listener
    window.addEventListener('mousemove', handleMouse, { passive: true });
});

onUnmounted(() => {
    clearInterval(calorieInterval);
    window.removeEventListener('mousemove', handleMouse);
});
</script>

<template>
    <div ref="ringRef" class="relative flex items-center justify-center"
        aria-description="An animated ring visualization showing a calorie count ticking upward to 2080">
        <motion.svg :width="size" :height="size" :viewBox="`0 0 ${size} ${size}`"
            class="drop-shadow-[0_0_30px_rgba(0,255,65,0.15)]" :style="{
                animation: 'pulse-ring 4s ease-in-out infinite',
            }">
            <defs>
                <linearGradient id="ringGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#7bf1a8" stop-opacity="0.8" />
                    <stop offset="50%" stop-color="#7bf1a8" stop-opacity="0.3" />
                    <stop offset="100%" stop-color="#7bf1a8" stop-opacity="0.8" />
                </linearGradient>
            </defs>
            <ellipse :cx="size / 2 + mousePos.x" :cy="size / 2 + mousePos.y" :rx="r"
                :ry="r - Math.abs(mousePos.y) * 0.5" fill="none" stroke="url(#ringGrad)" stroke-width="1.5"
                class="transition-all duration-700 ease-out" />

            <ellipse :cx="size / 2 - mousePos.x * 0.3" :cy="size / 2 - mousePos.y * 0.3" :rx="r - 20"
                :ry="r - 20 - Math.abs(mousePos.x) * 0.3" fill="none" stroke="#7bf1a8" stroke-width="0.5"
                stroke-opacity="0.2" class="transition-all duration-1000 ease-out" />
        </motion.svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
            <span class="text-xs uppercase tracking-[0.3em] text-muted-foreground/50 mb-1">
                Daily Intake
            </span>
            <motion.span class="text-5xl md:text-6xl font-light tabular-nums" :key="calories"
                :initial="{ opacity: 0.7 }" :animate="{ opacity: 1 }">
                {{ calories.toLocaleString() }}
            </motion.span>
            <span class="text-xs uppercase tracking-[0.3em] text-green-500 mt-1">
                kcal
            </span>
        </div>
    </div>
</template>

<style scoped>
@keyframes pulse-ring {

    0%,
    100% {
        transform: scale(1);
        opacity: 1;
    }

    50% {
        transform: scale(1.02);
        opacity: 0.9;
    }
}
</style>
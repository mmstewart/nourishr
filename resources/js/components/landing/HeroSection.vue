<script setup lang="ts">
import { motion } from 'motion-v';
import MetabolicRing from './MetabolicRing.vue';

const floatingData = ['2,130', '170 lbs', '142g', '56%', '1,890'];
</script>

<template>
    <section id="hero" class="relative flex min-h-screen items-center justify-center overflow-hidden">
        <div class="pointer-events-none absolute inset-0 overflow-hidden select-none" aria-hidden="true">
            <span
                v-for="(val, i) in floatingData"
                :key="i"
                class="animate-ghost absolute font-light"
                :style="{
                    fontSize: `${3 + (i % 3) * 2}rem`,
                    left: `${14 + i * 14}%`,
                    top: `${26 + (i % 4) * 16}%`,
                    animationDuration: `${3 + i * 0.3}s`,
                    animationDelay: `${i * 0.2}s`,
                }">
                {{ val }}
            </span>
        </div>
        <motion.h1
            class="absolute top-20 left-6 leading-none font-bold select-none md:left-12"
            :style="{ fontSize: 'clamp(3rem, 11vw, 9rem)' }"
            :initial="{ opacity: 0, y: 30 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 1, ease: [0.16, 1, 0.3, 1] }">
            SIMPLE
        </motion.h1>
        <motion.h1
            class="absolute right-6 bottom-8 leading-none font-bold select-none md:right-12"
            :style="{ fontSize: 'clamp(3rem, 11vw, 9rem)' }"
            :initial="{ opacity: 0, y: -30 }"
            :animate="{ opacity: 1, y: 0 }"
            :transition="{ duration: 1, delay: 0.2, ease: [0.16, 1, 0.3, 1] }">
            <span class="text-green-500">SMARTER</span>
        </motion.h1>
        <motion.div
            :initial="{ opacity: 0, scale: 0.8 }"
            :animate="{ opacity: 1, scale: 1 }"
            :transition="{ duration: 1.2, delay: 0.4, ease: [0.16, 1, 0.3, 1] }">
            <MetabolicRing />
        </motion.div>
        <motion.p
            class="absolute bottom-28 left-6 max-w-sm text-lg leading-relaxed md:bottom-32 md:left-12"
            :initial="{ opacity: 0 }"
            :animate="{ opacity: 1 }"
            :transition="{ duration: 1, delay: 0.8 }">
            Track your calories, nutrition & weight.
            <br />
            <span class="text-base text-muted-foreground/40">Healthy eating, made effortless.</span>
        </motion.p>
    </section>
</template>

<style scoped>
@keyframes float-data-once {
    0%,
    100% {
        transform: translateY(0);
        opacity: 0;
        /* Fully invisible at start/end */
    }

    50% {
        transform: translateY(-25px);
        opacity: 0.06;
        /* Peak visibility in the middle of the float */
    }
}

.animate-ghost {
    animation-name: float-data-once;
    animation-duration: 1s;
    /* Default duration */
    animation-timing-function: ease-out;
    animation-iteration-count: 1;
    animation-fill-mode: forwards;
    /* Stay at 100% (opacity 0) when done */
    opacity: 0;
}
</style>

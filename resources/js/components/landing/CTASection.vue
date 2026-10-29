<script setup lang="ts">
import { motion } from 'motion-v';

withDefaults(
    defineProps<{
        canRegister: boolean;
        images: string[];
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <section
        id="cta"
        class="relative overflow-hidden bg-[linear-gradient(180deg,#f3f4f6_0%,#F5FFF7_100%)] py-32 md:py-48">
        <div class="pointer-events-none absolute inset-0" aria-hidden="true">
            <img
                :src="images[0]"
                alt=""
                class="absolute top-20 left-6 h-48 w-48 rounded-md object-cover opacity-[0.07] grayscale md:left-12" />
            <img
                :src="images[1]"
                alt=""
                class="absolute right-6 bottom-8 h-56 w-56 rounded-md object-cover opacity-[0.07] grayscale md:right-12" />
        </div>
        <div class="relative z-10 mx-auto max-w-2xl px-6 text-center md:px-12">
            <motion.div
                :initial="{ opacity: 0, y: 30 }"
                :while-in-view="{ opacity: 1, y: 0 }"
                :viewport="{ once: true }"
                :transition="{ duration: 0.8 }">
                <span class="mb-8 block text-[10px] tracking-[0.4em] text-green-500 uppercase">Ready to start?</span>
                <h2 class="mb-6 text-4xl leading-[1.05] font-bold tracking-tight md:text-6xl">
                    Are you ready to
                    <br />
                    <span class="text-green-500">join us?</span>
                </h2>
                <p class="mx-auto mb-10 max-w-md text-lg leading-relaxed text-muted-foreground/50">
                    Join Nourishr and start tracking your nutrition, calories, and weight — effortless, insightful, and
                    stress-free.
                </p>
            </motion.div>
        </div>
    </section>
</template>

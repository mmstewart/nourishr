<script setup lang="ts">
import { motion } from 'motion-v';

interface FeedItem {
    time: string;
    event: string;
    value: string;
    delta: string;
}

const feedItems: FeedItem[] = [
    { time: '7:15 AM', event: 'Weight logged', value: '180 lbs', delta: '-3 lbs' },
    { time: '8:45 AM', event: 'Breakfast scanned', value: '420 kcal', delta: 'Protein: 32g' },
    { time: '12:30 PM', event: 'Lunch scanned', value: '680 kcal', delta: 'Carbohydrates: 65g' },
    { time: '3:15 PM', event: 'Snack logged', value: '180 kcal', delta: 'Fat: 12g' },
    { time: '6:00 PM', event: 'Weight goal reached', value: '178 lbs', delta: '✓ Target met' },
    { time: '7:45 PM', event: 'Dinner logged', value: '750 kcal', delta: 'Protein: 45g' },
];
</script>

<template>
    <section class="relative overflow-hidden py-32 md:py-40">
        <motion.div
            class="absolute top-20 left-6 md:left-12"
            :initial="{ opacity: 0 }"
            :while-in-view="{ opacity: 1 }"
            :in-view-options="{ once: true }">
            <span class="text-[10px] tracking-[0.4em] text-green-500 uppercase">03 — Biometric Feed</span>
        </motion.div>
        <div class="mx-auto max-w-4xl px-6 md:px-12">
            <motion.div
                class="relative mb-16 pl-12 md:pl-20"
                :initial="{ opacity: 0, y: 30 }"
                :while-in-view="{ opacity: 1, y: 0 }"
                :in-view-options="{ once: true }"
                :transition="{ duration: 0.8 }">
                <h2 class="mb-4 text-center text-4xl leading-tight font-bold tracking-tight md:text-5xl">
                    Your daily
                    <span class="text-green-500">success stream</span>
                </h2>
                <p class="text-center text-lg leading-relaxed text-muted-foreground/50">
                    Every action is recorded. Every metric flows into your personal biological timeline.
                </p>
            </motion.div>
            <div class="relative">
                <div
                    class="absolute top-0 bottom-0 left-4 w-px bg-linear-to-b from-green-500/30 via-green-500/10 to-transparent md:left-8" />
                <div class="space-y-4">
                    <motion.div
                        v-for="(item, i) in feedItems"
                        :key="i"
                        class="relative pl-12 md:pl-20"
                        :initial="{ opacity: 0, x: -20 }"
                        :while-in-view="{ opacity: 1, x: 0 }"
                        :in-view-options="{ once: true, margin: '-30px' }"
                        :transition="{ duration: 0.5, delay: i * 0.08 }">
                        <div
                            class="absolute top-10 left-[13.5px] h-1.75 w-1.75 rounded-full bg-green-500 md:left-[29.5px]" />
                        <div
                            class="rounded-md border bg-white/60 p-5 backdrop-blur-sm transition-all duration-300 hover:border-green-500">
                            <div class="mb-1 flex flex-wrap items-center justify-between gap-2">
                                <span class="text-sm/6 font-semibold capitalize">
                                    {{ item.event }}
                                </span>
                                <span class="text-sm/6">
                                    {{ item.value }}
                                </span>
                            </div>
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <span class="truncate text-xs/5 text-muted-foreground">
                                    {{ item.time }}
                                </span>
                                <span class="text-xs/5 text-muted-foreground">
                                    {{ item.delta }}
                                </span>
                            </div>
                        </div>
                    </motion.div>
                </div>
            </div>
        </div>
    </section>
</template>

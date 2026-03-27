<script setup lang="ts">
import { motion } from 'motion-v';
import { login, register } from '@/routes';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';

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
    <section id="cta"
        class="relative py-32 md:py-48 overflow-hidden bg-[linear-gradient(180deg,##f3f4f6_0%,#F5FFF7_100%)]">
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <img :src="images[0]" alt=""
                class="absolute top-20 left-6 md:left-12 w-48 h-48 object-cover rounded-md opacity-[0.07] grayscale" />
            <img :src="images[1]" alt=""
                class="absolute bottom-8 right-6 md:right-12 w-56 h-56 object-cover rounded-md opacity-[0.07] grayscale" />
        </div>
        <div class="max-w-2xl mx-auto px-6 md:px-12 text-center relative z-10">
            <motion.div :initial="{ opacity: 0, y: 30 }" :while-in-view="{ opacity: 1, y: 0 }"
                :viewport="{ once: true }" :transition="{ duration: 0.8 }">
                <span class="text-[10px] text-green-500 uppercase tracking-[0.4em] mb-8 block">
                    Ready to start?
                </span>
                <h2 class="font-bold text-4xl md:text-6xl leading-[1.05] tracking-tight mb-6">
                    Are you ready to
                    <br />
                    <span class="text-green-500">join us?</span>
                </h2>
                <p class="text-lg text-muted-foreground/50 leading-relaxed mb-10 max-w-md mx-auto">
                    Join Nourishr and start tracking your nutrition, calories, and weight — effortless, insightful, and
                    stress-free.
                </p>
                <div class="flex flex-col sm:flex-row gap-2.5 justify-center">
                    <Button variant="secondary" size="lg" as-child>
                        <Link :href="login()">
                            Login
                        </Link>
                    </Button>
                    <Button size="lg" v-if="canRegister" as-child>
                        <Link :href="register()">
                            Register
                        </Link>
                    </Button>
                </div>
            </motion.div>
        </div>
    </section>
</template>
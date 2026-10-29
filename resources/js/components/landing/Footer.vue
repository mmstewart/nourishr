<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import AppLogo from './../AppLogo.vue';

interface FooterLinks {
    label: string;
    href: string;
}

const footerLinks: FooterLinks[] = [
    { label: 'Features', href: '#features' },
    { label: 'Science', href: '#science' },
    { label: 'Privacy', href: '/privacy' },
    { label: 'Contact', href: '/contact' },
];

const navigate = (href: string) => {
    if (href.startsWith('#')) {
        const el = document.querySelector(href);

        if (el) el.scrollIntoView({ behavior: 'smooth' });
    } else {
        window.location.href = href;
    }
};
</script>

<template>
    <footer class="relative overflow-hidden bg-green-500">
        <div class="relative z-10 mx-auto max-w-7xl px-6 py-20 md:px-12 md:py-28">
            <div class="grid items-end gap-12 md:grid-cols-2">
                <motion.div
                    :initial="{ opacity: 0, y: 20 }"
                    :while-in-view="{ opacity: 1, y: 0 }"
                    :in-view-options="{ once: true }"
                    :transition="{ duration: 0.6 }">
                    <Link
                        @click="navigate('#hero')"
                        class="group mb-5 flex cursor-pointer items-center gap-1 focus:outline-none">
                        <AppLogo />
                    </Link>
                    <p class="max-w-sm text-sm leading-relaxed text-gray-800/85">
                        Simple nutrition, smarter choices. Nourishr makes healthy eating simple and sustainable. No
                        strict diets or guesswork: just clear insights for everyday health.
                    </p>
                </motion.div>
                <motion.div
                    class="flex flex-col gap-6 md:items-end"
                    :initial="{ opacity: 0, y: 20 }"
                    :while-in-view="{ opacity: 1, y: 0 }"
                    :in-view-options="{ once: true }"
                    :transition="{ duration: 0.6, delay: 0.1 }">
                    <nav class="flex gap-8">
                        <template v-for="link in footerLinks" :key="link.label">
                            <Link
                                v-if="!link.href.startsWith('#')"
                                :href="link.href"
                                class="font-space text-xs tracking-[0.2em] text-gray-800/85 uppercase transition-colors hover:text-gray-900">
                                {{ link.label }}
                            </Link>
                            <a
                                v-else
                                :href="link.href"
                                @click.prevent="navigate(link.href)"
                                class="font-space cursor-pointer text-xs tracking-[0.2em] text-gray-800/85 uppercase transition-colors hover:text-gray-900">
                                {{ link.label }}
                            </a>
                        </template>
                    </nav>
                    <p class="text-xs text-gray-800/85">
                        © {{ new Date().getFullYear() }} Nourishr. All rights reserved.
                    </p>
                </motion.div>
            </div>
        </div>
        <div
            class="pointer-events-none absolute inset-0 flex items-center justify-center overflow-hidden select-none"
            aria-hidden="true">
            <span class="font-space font-bold text-green-100/10" :style="{ fontSize: 'clamp(8rem, 25vw, 20rem)' }">
                NOURISHR
            </span>
        </div>
    </footer>
</template>

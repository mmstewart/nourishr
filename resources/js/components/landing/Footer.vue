<script setup lang="ts">
import { motion } from 'motion-v';
import AppLogo from './../AppLogo.vue';
import { Link } from '@inertiajs/vue3';

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
        const el = document.querySelector(href)

        if (el) el.scrollIntoView({ behavior: 'smooth' })
    } else {
        window.location.href = href
    }
}
</script>

<template>
    <footer class="relative bg-green-500 overflow-hidden">
        <div class="max-w-7xl mx-auto px-6 md:px-12 py-20 md:py-28 relative z-10">
            <div class="grid md:grid-cols-2 gap-12 items-end">
                <motion.div :initial="{ opacity: 0, y: 20 }" :while-in-view="{ opacity: 1, y: 0 }"
                    :viewport="{ once: true }" :transition="{ duration: 0.6 }">
                    <Link @click="navigate('#hero')"
                        class="flex items-center gap-1 mb-5 group cursor-pointer focus:outline-none">
                        <AppLogo />
                    </Link>
                    <p class="text-sm text-gray-800/85 leading-relaxed max-w-sm">
                        Simple nutrition, smarter choices. Nourishr makes healthy eating simple and sustainable. No
                        strict diets or guesswork: just clear insights for everyday health.
                    </p>
                </motion.div>
                <motion.div class="flex flex-col md:items-end gap-6" :initial="{ opacity: 0, y: 20 }"
                    :while-in-view="{ opacity: 1, y: 0 }" :viewport="{ once: true }"
                    :transition="{ duration: 0.6, delay: 0.1 }">
                    <nav class="flex gap-8">
                        <template v-for="link in footerLinks" :key="link.label">
                            <Link v-if="!link.href.startsWith('#')" :href="link.href"
                                class="text-xs font-space uppercase tracking-[0.2em] text-gray-800/85 hover:text-gray-900 transition-colors">
                                {{ link.label }}
                            </Link>
                            <a v-else :href="link.href" @click.prevent="navigate(link.href)" class="text-xs font-space uppercase tracking-[0.2em] text-gray-800/85 hover:text-gray-900
                            transition-colors cursor-pointer">
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
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none select-none overflow-hidden"
            aria-hidden="true">
            <span class="font-space font-bold text-green-100/10" :style="{ fontSize: 'clamp(8rem, 25vw, 20rem)' }">
                NOURISHR
            </span>
        </div>
    </footer>
</template>
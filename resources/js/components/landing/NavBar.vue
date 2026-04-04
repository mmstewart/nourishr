<script setup lang="ts">
import { motion } from "motion-v";
import { login, register } from '@/routes';
import AppLogo from './../AppLogo.vue';
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';

interface NavItem {
    label: string;
    href: string;
}

const navItems: NavItem[] = [
    { label: 'Home', href: '#hero' },
    { label: 'Features', href: '#features' },
    { label: 'Science', href: '#science' },
    { label: 'Access', href: '#cta' },
];

const scrollTo = (href: string) => {
    const el = document.querySelector(href);

    if (el) {
        el.scrollIntoView({ behavior: 'smooth' });
    }
};

withDefaults(
    defineProps<{
        canRegister: boolean;
    }>(),
    {
        canRegister: true,
    },
);
</script>

<template>
    <motion.header
        class="fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-6 md:px-12 h-16 backdrop-blur-md border-b"
        :initial="{ opacity: 0, y: -10 }" :animate="{ opacity: 1, y: 0 }"
        :transition="{ duration: 0.6, ease: [0.16, 1, 0.3, 1] }">
        <Link @click="scrollTo('#hero')" class="flex items-center gap-1 group cursor-pointer focus:outline-none z-10">
            <AppLogo />
        </Link>
        <div class="hidden md:flex absolute left-1/2 -translate-x-1/2 items-center">
            <nav class="flex items-center gap-8" aria-label="Main navigation">
                <a v-for="item in navItems" :key="item.label" :href="item.href" @click.prevent="scrollTo(item.href)"
                    class="text-[11px] font-space cursor-pointer uppercase tracking-[0.25em] text-muted-foreground
                hover:text-neutral-900 transition-colors duration-200 focus:outline-none">
                    {{ item.label }}
                </a>
            </nav>
        </div>
        <div class="flex items-center gap-2.5 z-10">
            <Button variant="outline" size="lg" as-child>
                <Link :href="login()">
                    Login
                </Link>
            </Button>
            <Button size="lg" v-if="canRegister" class="bg-green-500 hover:bg-green-400 dark:text-white" as-child>
                <Link :href="register()">
                    Register
                </Link>
            </Button>
        </div>
    </motion.header>
</template>
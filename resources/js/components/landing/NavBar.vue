<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { login, register } from '@/routes';
import { Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import AppLogo from './../AppLogo.vue';

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
        isLoggedIn: boolean;
    }>(),
    {
        canRegister: true,
        isLoggedIn: false,
    },
);
</script>

<template>
    <motion.header
        class="fixed top-0 right-0 left-0 z-50 flex h-16 items-center justify-between border-b px-6 backdrop-blur-md md:px-12"
        :initial="{ opacity: 0, y: -10 }"
        :animate="{ opacity: 1, y: 0 }"
        :transition="{ duration: 0.6, ease: [0.16, 1, 0.3, 1] }">
        <a @click="scrollTo('#hero')" class="group z-10 flex cursor-pointer items-center gap-1 focus:outline-none">
            <AppLogo />
        </a>
        <div class="absolute left-1/2 hidden -translate-x-1/2 items-center md:flex">
            <nav class="flex items-center gap-8" aria-label="Main navigation">
                <a
                    v-for="item in navItems"
                    :key="item.label"
                    :href="item.href"
                    @click.prevent="scrollTo(item.href)"
                    class="font-space cursor-pointer text-[11px] tracking-[0.25em] text-muted-foreground uppercase transition-colors duration-200 hover:text-neutral-900 focus:outline-none">
                    {{ item.label }}
                </a>
            </nav>
        </div>
        <div class="z-10 flex items-center gap-2.5">
            <template v-if="!$page.props.auth.user">
                <Button variant="outline" size="lg" as-child>
                    <Link :href="login()">Login</Link>
                </Button>
                <Button size="lg" v-if="canRegister" class="bg-green-500 hover:bg-green-400 dark:text-white" as-child>
                    <Link :href="register()">Register</Link>
                </Button>
            </template>
            <template v-else-if="!$page.props.auth.user.has_weight_goal">
                <Button size="lg" class="bg-green-500 hover:bg-green-400 dark:text-white" as-child>
                    <a href="/setup">Continue Setup</a>
                </Button>
            </template>
            <template v-else>
                <Button size="lg" class="bg-green-500 hover:bg-green-400 dark:text-white" as-child>
                    <a href="/dashboard">Dashboard</a>
                </Button>
            </template>
        </div>
    </motion.header>
</template>

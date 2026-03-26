<script setup lang="ts">
import { ref } from 'vue';
import AppLogo from './../AppLogo.vue';
import { Button } from '@/components/ui/button';

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

const hoveredIndex = ref<number | null>(null);

const scrollTo = (href: string) => {
    const el = document.querySelector(href);
    if (el) el.scrollIntoView({ behavior: 'smooth' });
};
</script>

<template>
    <div class="fixed top-6 left-6 right-6 md:left-12 md:right-12 z-40 flex justify-between items-center mx-auto">
        <AppLogo class="w-7 h-7" />
        <div class="flex gap-4">
            <Button size="lg">
                Login
            </Button>
            <Button variant="outline" size="lg">
                Register
            </Button>
        </div>
    </div>

    <nav class="fixed right-6 top-1/2 -translate-y-1/2 z-40 hidden lg:flex flex-col items-center gap-3"
        aria-label="Page navigation">
        <div v-for="(item, i) in navItems" :key="item.label" class="relative flex items-center"
            @mouseenter="hoveredIndex = i" @mouseleave="hoveredIndex = null">
            <Transition enter-active-class="transition duration-200 ease-out" enter-from-class="opacity-0 translate-x-2"
                enter-to-class="opacity-100 translate-x-0" leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100 translate-x-0" leave-to-class="opacity-0 translate-x-2">
                <span v-if="hoveredIndex === i"
                    class="absolute right-8 text-xs font-space font-medium text-carbon whitespace-nowrap tracking-wider uppercase">
                    {{ item.label }}
                </span>
            </Transition>

            <button @click="scrollTo(item.href)"
                class="w-0.75 rounded-full bg-carbon/20 hover:bg-metabolic transition-all duration-300 focus:outline-2 focus:outline-metabolic"
                :style="{ height: `${18 + (i % 2 === 0 ? 12 : 6)}px` }" :aria-label="item.label" />
        </div>
    </nav>
</template>
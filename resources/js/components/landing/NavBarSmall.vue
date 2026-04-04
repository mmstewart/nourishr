<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogo from './../AppLogo.vue';
</script>

<template>
    <header class="flex items-center justify-between px-6 md:px-12 h-16 border-b">
        <Link href="/" class="flex items-center gap-1 group cursor-pointer focus:outline-none z-10">
            <AppLogo />
        </Link>
    </header>
</template>
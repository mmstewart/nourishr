<script setup lang="ts">
import AppLogo from './../AppLogo.vue';

import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-vue-next'
</script>

<template>
    <header class="flex items-center justify-between px-6 md:px-12 h-16 border-b">
        <a href="/" class="flex items-center gap-1 group cursor-pointer focus:outline-none z-10">
            <AppLogo />
        </a>
        <Button variant="link" size="lg" as-child>
            <a href="/">
                <ArrowLeft class="size-4" />
                Back
            </a>
        </Button>
    </header>
</template>
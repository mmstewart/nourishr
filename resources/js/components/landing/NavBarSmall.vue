<script setup lang="ts">
import AppLogo from './../AppLogo.vue';

import { Button } from '@/components/ui/button';
import { ArrowLeft } from 'lucide-vue-next';
</script>

<template>
    <header class="flex h-16 items-center justify-between border-b px-6 md:px-12">
        <a href="/" class="group z-10 flex cursor-pointer items-center gap-1 focus:outline-none">
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

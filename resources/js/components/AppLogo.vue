<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div class="flex aspect-square items-center justify-center rounded-md">
        <AppLogoIcon class="size-8 fill-current group-data-[collapsible=icon]:size-6" />
    </div>
    <div class="ml-1.5 grid flex-1 text-left text-md group-data-[collapsible=icon]:hidden">
        <span class="truncate leading-tight font-semibold">Nourishr</span>
    </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';

const progress = ref<number>(0);

const handleScroll = () => {
    const total = document.documentElement.scrollHeight - window.innerHeight;
    const current = window.scrollY;

    // Prevent division by zero and ensure percentage stays between 0-100
    progress.value = total > 0 ? (current / total) * 100 : 0;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll, { passive: true });
    // Initial check in case the page is already scrolled on load
    handleScroll();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div class="fixed top-0 left-0 w-full h-px z-50 bg-transparent">
        <div class="h-full bg-green-500 transition-all duration-150 ease-out" :style="{ width: `${progress}%` }" />
    </div>
</template>
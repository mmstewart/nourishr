<script setup lang="ts">
import ScrollProgress from '@/components/landing/ScrollProgress.vue';
import NavBar from '@/components/landing/NavBar.vue';
import HeroSection from '@/components/landing/HeroSection.vue';
import NutritionSection from '@/components/landing/NutritionSection.vue';
import WeightSection from '@/components/landing/WeightSection.vue';
import BiometricFeed from '@/components/landing/BiometricFeed.vue';
import CTASection from '@/components/landing/CTASection.vue';
import Footer from '@/components/landing/Footer.vue';

defineProps<{
    canRegister: boolean;
}>()
</script>

<template>
    <div className="min-h-screen bg-gray-100">
        <ScrollProgress />
        <NavBar :canRegister="canRegister" />
        <HeroSection />
        <NutritionSection :image="''" />
        <WeightSection :image="''" />
        <BiometricFeed />
        <CTASection :canRegister="canRegister" :images="[]" />
        <Footer />
    </div>
</template>

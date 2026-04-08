<script setup lang="ts">
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { CircleCheck } from 'lucide-vue-next'

// import ScrollProgress from '@/components/landing/ScrollProgress.vue';
import NavBar from '@/components/landing/NavBar.vue';
import HeroSection from '@/components/landing/HeroSection.vue';
import NutritionSection from '@/components/landing/NutritionSection.vue';
import WeightSection from '@/components/landing/WeightSection.vue';
import BiometricFeed from '@/components/landing/BiometricFeed.vue';
import CTASection from '@/components/landing/CTASection.vue';
import Footer from '@/components/landing/Footer.vue';

const images = {
    quinoa: "/quinoa.png",
    avocado: "/avocado.png",
    nutrition: "/nutrition.png",
    pen: "/pen.png",
};

defineProps<{
    canRegister: boolean;
    isLoggedIn: boolean;
}>()

const page = usePage();

const showSuccessMessage = ref(false)

watch(() => page.props.flash.success, (newMessage) => {
    if (newMessage) {
        showSuccessMessage.value = true

        setTimeout(() => {
            showSuccessMessage.value = false
        }, 5000)
    }
}, { immediate: true })
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <div v-if="showSuccessMessage" class="fixed top-20 right-6 md:right-12 z-50 w-full max-w-md">
            <Alert>
                <CircleCheck class="stroke-green-500" />
                <AlertTitle class="text-green-500">
                    Thank you for the feedback!
                </AlertTitle>
                <AlertDescription>
                    {{ page.props.flash.success }}
                </AlertDescription>
            </Alert>
        </div>
        <!-- on screen  progress indicator that the transparent navbar covers -->
        <!-- <ScrollProgress /> -->
        <NavBar :isLoggedIn="isLoggedIn" :canRegister="canRegister" />
        <HeroSection />
        <NutritionSection :image="images.nutrition" />
        <WeightSection :image="images.pen" />
        <BiometricFeed />
        <CTASection :canRegister="canRegister" :images="[images.quinoa, images.avocado]" />
        <Footer />
    </div>
</template>

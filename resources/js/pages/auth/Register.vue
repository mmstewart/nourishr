<script setup lang="ts">
import SignupForm from '@/components/SignupForm.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-svh w-full items-center justify-center bg-gray-100 p-6 md:p-10">
        <div class="w-full max-w-sm">
            <Head title="Register" />
            <SignupForm />
        </div>
    </div>
</template>

<script setup lang="ts">
import LoginForm from '@/components/LoginForm.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-svh w-full items-center justify-center bg-gray-100 p-6 md:p-10">
        <div class="w-full max-w-sm">
            <Head title="Login" />
            <LoginForm />
        </div>
    </div>
</template>

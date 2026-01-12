<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LoginForm from '@/components/LoginForm.vue';
</script>

<template>
    <div class="flex min-h-svh w-full items-center justify-center p-6 md:p-10">
        <div class="w-full max-w-sm">

            <Head title="Login" />
            <LoginForm />
        </div>
    </div>
</template>

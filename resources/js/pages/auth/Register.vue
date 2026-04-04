<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SignupForm from "@/components/SignupForm.vue"
</script>

<template>
    <div class="bg-gray-100 flex min-h-svh w-full items-center justify-center p-6 md:p-10">
        <div class="w-full max-w-sm">

            <Head title="Register" />
            <SignupForm />
        </div>
    </div>
</template>

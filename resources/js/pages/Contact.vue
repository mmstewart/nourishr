<script setup lang="ts">
import AppContact from './contact/app/AppContact.vue';
import AuthContact from './contact/auth/AuthContact.vue';

defineProps<{
    isLoggedIn: boolean;
}>()
</script>

<template>
    <div v-if="isLoggedIn">
        <AuthContact />
    </div>
    <div v-else>
        <AppContact />
    </div>
</template>
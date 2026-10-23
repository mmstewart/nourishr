<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import AppContact from './contact/app/AppContact.vue';
import AuthContact from './contact/auth/AuthContact.vue';

const page = usePage();

const isLoggedIn = computed(() => !!page.props.auth.user);
</script>

<template>
    <AuthContact v-if="isLoggedIn" />
    <AppContact v-else />
</template>

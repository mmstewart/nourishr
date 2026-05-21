<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 40 40" :class="className" v-bind="$attrs">
        <path
            fill="currentColor"
            d="M20 38c-9.9 0-18-8.1-18-18S10.1 2 20 2c4.4 0 8.4 1.6 11.5 4.2l-2 2.6C27.1 6.8 23.7 5.5 20 5.5c-8 0-14.5 6.5-14.5 14.5s6.5 14.5 14.5 14.5 14.5-6.5 14.5-14.5c0-1.7-.3-3.3-.8-4.8l2.9-1c.6 1.8.9 3.8.9 5.8 0 9.9-8.1 18-18 18z" />
        <circle cx="33.3" cy="10.9" r="3" fill="#B4E3C5" />
    </svg>
</template>

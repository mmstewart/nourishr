<script setup lang="ts">
import type { HTMLAttributes } from 'vue'
import type { PopoverTriggerProps } from "reka-ui"
import { PopoverTrigger } from "reka-ui"
import { cn } from '@/lib/utils'

const props = defineProps<PopoverTriggerProps & { class?: HTMLAttributes['class'] }>()
</script>

<template>
  <PopoverTrigger data-slot="popover-trigger" v-bind="props"
    :class="cn('focus:outline-none focus:border-ring focus:ring-ring/50 focus:ring-[3px]', props.class)">
    <slot />
  </PopoverTrigger>
</template>
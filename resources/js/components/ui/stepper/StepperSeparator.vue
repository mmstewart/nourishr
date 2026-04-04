<script lang="ts" setup>
import type { StepperSeparatorProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"

import { StepperSeparator, useForwardProps } from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<StepperSeparatorProps & { class?: HTMLAttributes["class"] }>()

const delegatedProps = reactiveOmit(props, "class")

const forwarded = useForwardProps(delegatedProps)
</script>

<template>
  <StepperSeparator v-bind="forwarded" :class="cn(
    'bg-muted',
    // Disabled
    'group-data-disabled:bg-gray-300 group-data-disabled:opacity-50',
    // Completed
    'group-data-[state=completed]:bg-accent-foreground',
    props.class,
  )" />
</template>

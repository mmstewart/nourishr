<script setup lang="ts">
import { type HTMLAttributes } from 'vue';

import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import { Field, FieldDescription, FieldGroup, FieldSeparator } from '@/components/ui/field';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { Icon } from '@iconify/vue';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <div :class="cn('flex flex-col gap-6', props.class)">
        <FieldGroup>
            <div class="flex flex-col items-center gap-2 text-center">
                <a href="/" class="flex flex-col items-center gap-2 font-medium">
                    <div class="flex size-8 items-center justify-center rounded-md">
                        <AppLogoIcon class="size-6" />
                    </div>
                    <span class="sr-only">Nourishr</span>
                </a>
                <h1 class="text-xl font-bold">Welcome to Nourishr</h1>
                <FieldDescription>
                    Already have an account?
                    <a href="/login">Sign in</a>
                </FieldDescription>
            </div>
            <Field>
                <Button class="bg-green-500 hover:bg-green-400 dark:text-white" as-child>
                    <a href="/setup">Create Account</a>
                </Button>
            </Field>
            <FieldSeparator class="bg-gray-100">Or</FieldSeparator>
            <FieldGroup class="grid gap-4">
                <Button variant="outline" type="button">
                    <a href="/auth/google/redirect" class="flex items-center gap-x-2">
                        <Icon icon="logos:google-icon" class="h-5 w-5" />
                        Continue with Google
                    </a>
                </Button>
            </FieldGroup>
        </FieldGroup>
        <FieldDescription class="flex justify-center space-x-4 text-center">
            <a href="/terms-of-use">Terms of Use</a>
            <Separator orientation="vertical" class="h-auto!" />
            <a href="/privacy-policy">Privacy Policy</a>
        </FieldDescription>
    </div>
</template>

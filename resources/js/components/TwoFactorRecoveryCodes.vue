<script setup lang="ts">
import { nextTick, onMounted, ref, useTemplateRef } from 'vue';
import { Form } from '@inertiajs/vue3';
import { regenerateRecoveryCodes } from '@/routes/two-factor';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';

import AlertError from '@/components/AlertError.vue';
import { Button } from '@/components/ui/button';
import { RefreshCw } from 'lucide-vue-next';


const { recoveryCodesList, fetchRecoveryCodes, errors } = useTwoFactorAuth();
const isRecoveryCodesVisible = ref<boolean>(false);
const recoveryCodeSectionRef = useTemplateRef('recoveryCodeSectionRef');

const toggleRecoveryCodesVisibility = async () => {
    if (!isRecoveryCodesVisible.value && !recoveryCodesList.value.length) {
        await fetchRecoveryCodes();
    }

    isRecoveryCodesVisible.value = !isRecoveryCodesVisible.value;

    if (isRecoveryCodesVisible.value) {
        await nextTick();
        recoveryCodeSectionRef.value?.scrollIntoView({ behavior: 'smooth' });
    }
};

onMounted(async () => {
    if (!recoveryCodesList.value.length) {
        await fetchRecoveryCodes();
    }
});
</script>

<template>
    <div class="rounded-lg border p-4 bg-linear-to-tl from-primary/5 to-default">
        <div class="relative space-y-0.5">
            <div class="flex items-center gap-2">
                <p class="font-medium">Two-Factor Authentication Recovery Codes</p>
            </div>
            <p class="text-sm">
                Recovery codes let you regain access if you lose your two-factor authentication
                device. Store them in a secure password manager.
            </p>
        </div>
        <div class="mt-4 flex flex-col select-none sm:flex-row sm:items-center sm:justify-between">
            <Button @click="toggleRecoveryCodesVisibility" class="w-fit cursor-pointer">
                {{ isRecoveryCodesVisible ? 'Hide' : 'View' }} Recovery Codes
            </Button>
            <Form v-if="isRecoveryCodesVisible && recoveryCodesList.length" v-bind="regenerateRecoveryCodes.form()"
                method="post" :options="{ preserveScroll: true }" @success="fetchRecoveryCodes"
                #default="{ processing }">
                <Button variant="secondary" :disabled="processing" type="submit" class="cursor-pointer">
                    <RefreshCw /> Regenerate Codes
                </Button>
            </Form>
        </div>
        <div :class="[
            'relative overflow-hidden transition-all duration-300',
            isRecoveryCodesVisible
                ? 'h-auto opacity-100'
                : 'h-0 opacity-0',
        ]">
            <div v-if="errors?.length" class="mt-6">
                <AlertError :errors="errors" />
            </div>
            <div v-else class="mt-3 space-y-3">
                <div ref="recoveryCodeSectionRef" class="grid gap-1 rounded-lg bg-muted p-4 font-mono text-sm">
                    <div v-if="!recoveryCodesList.length" class="space-y-2">
                        <div v-for="n in 8" :key="n" class="h-4 animate-pulse rounded bg-muted-foreground/20"></div>
                    </div>
                    <div v-else v-for="(code, index) in recoveryCodesList" :key="index">
                        {{ code }}
                    </div>
                </div>
                <p class="text-xs text-muted-foreground select-none">
                    Each recovery code can be used once to access your
                    account and will be removed after use. If you need more,
                    click
                    <span class="font-bold">Regenerate Codes</span> above.
                </p>
            </div>
        </div>
    </div>
</template>

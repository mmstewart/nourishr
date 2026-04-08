<script setup lang="ts">
import { computed, nextTick, ref, useTemplateRef, watch } from 'vue';
import { Form } from '@inertiajs/vue3';
import { confirm } from '@/routes/two-factor';
import { useClipboard } from '@vueuse/core';
import { REGEXP_ONLY_DIGITS } from 'vue-input-otp'

import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AlertError from '@/components/AlertError.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp'
import {
    Field,
    FieldSeparator,
} from "@/components/ui/field";
import { InputGroup, InputGroupAddon, InputGroupButton, InputGroupInput } from '@/components/ui/input-group';
import { Check, Copy, ScanLine } from 'lucide-vue-next';

interface Props {
    requiresConfirmation: boolean;
    twoFactorEnabled: boolean;
}

const props = defineProps<Props>();
const isOpen = defineModel<boolean>('isOpen');

const { copy, copied } = useClipboard();
const { qrCodeSvg, manualSetupKey, clearSetupData, fetchSetupData, errors } =
    useTwoFactorAuth();

const showVerificationStep = ref(false);
const code = ref('');
const codeValue = computed<string>(() => code.value);

const pinInputContainerRef = useTemplateRef('pinInputContainerRef');

const modalConfig = computed<{
    title: string;
    description: string;
    buttonText: string;
}>(() => {
    if (props.twoFactorEnabled) {
        return {
            title: 'Two-Factor Authentication Enabled',
            description:
                'Two-factor authentication is now enabled. Scan the QR code or enter the setup key in your authenticator app',
            buttonText: 'Close',
        };
    }

    if (showVerificationStep.value) {
        return {
            title: 'Enter verification code',
            description: 'We sent a 6-digit code to your authenticator app',
            buttonText: 'Continue',
        };
    }

    return {
        title: 'Enable Two-Factor Authentication',
        description:
            'To finish enabling two-factor authentication, scan the QR code or enter the setup key in your authenticator app',
        buttonText: 'Continue',
    };
});

const handleModalNextStep = () => {
    if (props.requiresConfirmation) {
        showVerificationStep.value = true;

        nextTick(() => {
            pinInputContainerRef.value?.querySelector('input')?.focus();
        });

        return;
    }

    clearSetupData();
    isOpen.value = false;
};

const resetModalState = () => {
    if (props.twoFactorEnabled) {
        clearSetupData();
    }

    // Wrap this in a tiny timeout so the UI doesn't 
    // snap back to the QR code during the close animation
    setTimeout(() => {
        showVerificationStep.value = false;
        code.value = '';
    }, 300);
};

watch(isOpen, async (val) => {
    if (!val) {
        // Only reset to the first step if we aren't currently "Enabled"
        // This prevents the "flash" of the QR code on close.
        if (!props.twoFactorEnabled) {
            resetModalState();
        }
        return;
    }

    if (!qrCodeSvg.value) {
        await fetchSetupData();
    }
});
</script>

<template>
    <Dialog :open="isOpen" @update:open="isOpen = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader class="flex items-center justify-center">
                <div class="mb-3 w-auto rounded-full border border-border bg-card p-0.5 shadow-sm">
                    <div class="relative overflow-hidden rounded-full border border-border bg-muted p-2.5">
                        <div class="absolute inset-0 grid grid-cols-5 opacity-50">
                            <div v-for="i in 5" :key="`col-${i}`" class="border-r border-border last:border-r-0" />
                        </div>
                        <div class="absolute inset-0 grid grid-rows-5 opacity-50">
                            <div v-for="i in 5" :key="`row-${i}`" class="border-b border-border last:border-b-0" />
                        </div>
                        <ScanLine class="relative z-20 size-6 text-foreground" />
                    </div>
                </div>
                <DialogTitle>{{ modalConfig.title }}</DialogTitle>
                <DialogDescription class="text-center">
                    {{ modalConfig.description }}
                </DialogDescription>
            </DialogHeader>
            <div class="relative flex w-auto flex-col items-center justify-center space-y-5">
                <template v-if="!showVerificationStep">
                    <AlertError v-if="errors?.length" :errors="errors" />
                    <template v-else>
                        <div class="relative mx-auto flex max-w-md items-center overflow-hidden">
                            <div
                                class="relative mx-auto aspect-square w-64 overflow-hidden rounded-lg border border-border">
                                <div v-if="!qrCodeSvg"
                                    class="absolute inset-0 z-10 flex aspect-square h-auto w-full animate-pulse items-center justify-center bg-background">
                                    <Spinner class="size-6" />
                                </div>
                                <div v-else class="relative z-10 overflow-hidden p-5">
                                    <div v-html="qrCodeSvg"
                                        class="aspect-square w-full justify-center rounded-lg bg-white p-2 [&_svg]:size-full" />
                                </div>
                            </div>
                        </div>
                        <Button @click="handleModalNextStep"
                            class="w-full bg-green-500 hover:bg-green-400 dark:text-white">
                            {{ modalConfig.buttonText }}
                        </Button>
                        <Field>
                            <FieldSeparator class="bg-background my-1">Or enter the code manually</FieldSeparator>
                        </Field>
                        <div class="w-full">
                            <div v-if="!manualSetupKey">
                                <InputGroup data-disabled>
                                    <InputGroupInput placeholder="Processing..." disabled />
                                    <InputGroupAddon>
                                        <Spinner />
                                    </InputGroupAddon>
                                </InputGroup>
                            </div>
                            <template v-else>
                                <InputGroup>
                                    <InputGroupInput v-model="manualSetupKey" readonly />
                                    <InputGroupAddon align="inline-end">
                                        <InputGroupButton aria-label="Copy" title="Copy" size="icon-xs"
                                            @click="copy(manualSetupKey || '')">
                                            <Check v-if="copied" class="w-4 text-green-500" />
                                            <Copy v-else class="w-4" />
                                        </InputGroupButton>
                                    </InputGroupAddon>
                                </InputGroup>
                            </template>
                        </div>
                    </template>
                </template>
                <template v-else>
                    <Form v-bind="confirm.form()" reset-on-error @finish="code = ''" @success="isOpen = false"
                        v-slot="{ errors, processing }">
                        <input type="hidden" name="code" :value="codeValue" />
                        <div ref="pinInputContainerRef" class="relative w-full space-y-3">
                            <div class="flex w-full flex-col items-center justify-center space-y-3 py-2">
                                <InputOTP v-model="code" :maxlength="6" :pattern="REGEXP_ONLY_DIGITS" id="otp">
                                    <InputOTPGroup
                                        class="gap-2.5 *:data-[slot=input-otp-slot]:rounded-md *:data-[slot=input-otp-slot]:border">
                                        <InputOTPSlot v-for="(id, index) in 6" :key="id" :index="index"
                                            :disabled="processing" />
                                    </InputOTPGroup>
                                </InputOTP>
                                <InputError :message="errors?.confirmTwoFactorAuthentication?.code" />
                            </div>
                            <div class="flex w-full items-center space-x-5">
                                <Button @click="showVerificationStep = false" variant="outline" :disabled="processing"
                                    type="button" class="w-auto flex-1">
                                    Back
                                </Button>
                                <Button :disabled="processing || codeValue.length < 6" type="submit"
                                    class="w-auto flex-1 bg-green-500 hover:bg-green-400 dark:text-white">
                                    Confirm
                                </Button>
                            </div>
                        </div>
                    </Form>
                </template>
            </div>
        </DialogContent>
    </Dialog>
</template>

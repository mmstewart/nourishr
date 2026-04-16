<script setup lang="ts">
import { computed, ref } from 'vue';
import { Form, Head } from '@inertiajs/vue3';
import { store } from '@/routes/two-factor/login';
import { REGEXP_ONLY_DIGITS } from 'vue-input-otp'

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    InputOTP,
    InputOTPGroup,
    InputOTPSlot,
} from '@/components/ui/input-otp'

import AuthLayout from '@/layouts/AuthLayout.vue';

interface AuthConfigContent {
    title: string;
    description: string;
    toggleText: string;
}

const authConfigContent = computed<AuthConfigContent>(() => {
    if (showRecoveryInput.value) {
        return {
            title: 'Recovery Code',
            description:
                'Please confirm access to your account by entering one of your emergency recovery codes.',
            toggleText: 'login using an authentication code',
        };
    }

    return {
        title: 'Authentication Code',
        description:
            'Enter the authentication code provided by your authenticator application.',
        toggleText: 'login using a recovery code',
    };
});

const showRecoveryInput = ref<boolean>(false);

const toggleRecoveryMode = (clearErrors: () => void): void => {
    showRecoveryInput.value = !showRecoveryInput.value;
    clearErrors();
    code.value = '';
};

const code = ref('');
const codeValue = computed<string>(() => code.value);
</script>

<template>
    <AuthLayout :title="authConfigContent.title" :description="authConfigContent.description" class="bg-gray-100">

        <Head title="Two-Factor Authentication" />
        <div class="space-y-6">
            <template v-if="!showRecoveryInput">
                <Form v-bind="store.form()" class="space-y-4" reset-on-error @error="code = ''"
                    #default="{ errors, processing, clearErrors }">
                    <input type="hidden" name="code" :value="codeValue" />
                    <div class="flex flex-col items-center justify-center space-y-3 text-center">
                        <InputOTP v-model="code" :maxlength="6" :pattern="REGEXP_ONLY_DIGITS" id="otp">
                            <InputOTPGroup
                                class="gap-2.5 *:data-[slot=input-otp-slot]:rounded-md *:data-[slot=input-otp-slot]:border">
                                <InputOTPSlot v-for="(id, index) in 6" :key="id" :index="index"
                                    :disabled="processing" />
                            </InputOTPGroup>
                        </InputOTP>
                        <InputError :message="errors.code" />
                    </div>
                    <Button :disabled="processing" type="submit"
                        class="w-full bg-green-500 hover:bg-green-400 dark:text-white">
                        Continue
                    </Button>
                    <div class="text-center text-sm text-muted-foreground">
                        <span>or you can </span>
                        <button type="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            @click="() => toggleRecoveryMode(clearErrors)">
                            {{ authConfigContent.toggleText }}
                        </button>
                    </div>
                </Form>
            </template>
            <template v-else>
                <Form v-bind="store.form()" class="space-y-4" reset-on-error
                    #default="{ errors, processing, clearErrors }">
                    <Input name="recovery_code" type="text" :autofocus="showRecoveryInput" required />
                    <InputError :message="errors.recovery_code" />
                    <Button :disabled="processing" type="submit"
                        class="w-full bg-green-500 hover:bg-green-400 dark:text-white">
                        Continue
                    </Button>
                    <div class="text-center text-sm text-muted-foreground">
                        <span>or you can </span>
                        <button type="button"
                            class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                            @click="() => toggleRecoveryMode(clearErrors)">
                            {{ authConfigContent.toggleText }}
                        </button>
                    </div>
                </Form>
            </template>
        </div>
    </AuthLayout>
</template>

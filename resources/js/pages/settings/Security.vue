<script setup lang="ts">
import { FieldDescription } from '@/components/ui/field';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { disable, enable } from '@/routes/two-factor';
import { BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { onUnmounted, ref } from 'vue';
import DeleteUser from '@/components/DeleteUser.vue';

interface Props {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
}

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Security settings',
        href: '/settings/security',
    },
];

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => {
    clearTwoFactorAuthData();
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">

        <Head title="Security settings" />
        <SettingsLayout>
            <div class="space-y-6">
                <div class="flex flex-col items-start text-left">
                    <span class="sr-only">Two-Factor Authentication</span>
                    <h3 class="text-base font-medium mb-0.5">
                        Security settings
                    </h3>
                    <FieldDescription>
                        Secure your login with two-factor authentication and manage your account status
                    </FieldDescription>
                </div>
                <div v-if="!twoFactorEnabled" class="flex flex-col items-start justify-start space-y-4">
                    <div class="space-y-4 rounded-lg border p-4 bg-linear-to-tl from-primary/5 to-default">
                        <div class="relative space-y-0.5">
                            <div class="flex items-center gap-2">
                                <p class="font-medium">Two-Factor Authentication</p>
                                <Badge>Disabled</Badge>
                            </div>
                            <p class="text-sm">
                                When you enable two-factor authentication, you will be
                                prompted for a secure pin during login. This pin can be
                                retrieved from a TOTP-supported application on your
                                phone.
                            </p>
                        </div>
                        <div>
                            <Button v-if="hasSetupData" @click="showSetupModal = true"
                                class="bg-green-500 hover:bg-green-400 dark:text-white">
                                Continue Setup
                            </Button>
                            <Form v-else v-bind="enable.form()" @success="showSetupModal = true"
                                #default="{ processing }">
                                <Button :disabled="processing" type="submit"
                                    class="bg-green-500 hover:bg-green-400 dark:text-white">
                                    Enable 2FA
                                </Button>
                            </Form>
                        </div>
                    </div>
                </div>
                <div v-else class="flex flex-col items-start justify-start space-y-4">
                    <div class="space-y-4 rounded-lg border p-4 bg-linear-to-tl from-primary/5 to-default">
                        <div class="relative space-y-0.5">
                            <div class="flex items-center gap-2">
                                <p class="font-medium">Two-Factor Authentication</p>
                                <Badge variant="default" class="bg-green-500">Enabled</Badge>
                            </div>
                            <p class="text-sm">
                                With two-factor authentication enabled, you will be
                                prompted for a secure, random pin during login, which
                                you can retrieve from the TOTP-supported application on
                                your phone.
                            </p>
                        </div>
                        <div class="relative inline">
                            <Form v-bind="disable.form()" #default="{ processing }">
                                <Button variant="destructive" type="submit" :disabled="processing">
                                    Disable 2FA
                                </Button>
                            </Form>
                        </div>
                    </div>
                    <TwoFactorRecoveryCodes />
                </div>
                <TwoFactorSetupModal v-model:isOpen="showSetupModal" :requiresConfirmation="requiresConfirmation"
                    :twoFactorEnabled="twoFactorEnabled" />
                <DeleteUser />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

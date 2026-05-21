<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref, watch, type HTMLAttributes } from 'vue';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { FieldLabel } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';

defineProps<{
    class?: HTMLAttributes['class'];
}>();

const open = ref(false);
const isClosing = ref(false);

const form = useForm({
    password: '',
});

const touchedPassword = ref(false);

watch(open, (newVal) => {
    if (!newVal) {
        isClosing.value = true;

        touchedPassword.value = false;

        form.reset('password');
        form.clearErrors();

        nextTick(() => {
            isClosing.value = false;
        });
    }
});

function handleBlur() {
    if (!isClosing.value) touchedPassword.value = true;
}

function submit() {
    touchedPassword.value = true;

    if (!form.password) return;

    form.delete('/settings/security');
}
</script>

<template>
    <div class="space-y-6">
        <div class="to-default space-y-4 rounded-md border bg-linear-to-tl from-red-500/5 p-4">
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">Account</p>
                <p class="text-sm">
                    No longer want to use our service? You can delete your account here. This action is not reversible.
                    All information related to this account will be deleted permanently.
                </p>
            </div>
            <Dialog v-model:open="open">
                <DialogTrigger as-child>
                    <Button variant="destructive" data-test="delete-user-button" class="cursor-pointer">
                        Delete account
                    </Button>
                </DialogTrigger>
                <DialogContent @close-button-mousedown="isClosing = true">
                    <form @submit.prevent="submit" class="space-y-6">
                        <DialogHeader class="space-y-3">
                            <DialogTitle>Are you sure you want to delete your account?</DialogTitle>
                            <DialogDescription>
                                Once your account is deleted, all of its resources and data will also be permanently
                                deleted. Please enter your password to confirm you would like to permanently delete your
                                account.
                            </DialogDescription>
                        </DialogHeader>
                        <div class="grid gap-2">
                            <FieldLabel for="password">Password</FieldLabel>
                            <Input
                                v-model="form.password"
                                @blur="handleBlur"
                                id="password"
                                name="password"
                                type="password"
                                :class="{
                                    'border-red-500': (!form.password && touchedPassword) || form.errors.password,
                                }"
                                autocomplete="new-password" />
                            <div
                                v-if="(!form.password && touchedPassword) || form.errors.password"
                                class="text-sm text-red-500">
                                {{ form.errors.password || 'Password is required.' }}
                            </div>
                        </div>
                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button
                                    @mousedown="isClosing = true"
                                    @click="form.reset()"
                                    variant="secondary"
                                    class="cursor-pointer">
                                    Cancel
                                </Button>
                            </DialogClose>
                            <Button
                                :disabled="form.processing || !form.password"
                                variant="destructive"
                                type="submit"
                                data-test="confirm-delete-user-button"
                                class="cursor-pointer">
                                <Spinner v-if="form.processing" class="mr-2" />
                                Delete account
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    </div>
</template>

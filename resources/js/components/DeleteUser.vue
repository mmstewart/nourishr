<script setup lang="ts">
import type { HTMLAttributes } from "vue"
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'

import { Button } from '@/components/ui/button';
import {
    FieldLabel,
} from '@/components/ui/field'
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
import { Input } from '@/components/ui/input';

defineProps<{
    class?: HTMLAttributes["class"]
}>()

const form = useForm({
    password: '',
});

const touchedPassword = ref(false)
const attemptedSubmit = ref(false)

function markTouched(field: string) {
    if (field === 'password') touchedPassword.value = true
}

const passwordError = computed(() => {
    if (!form.password && (touchedPassword.value || attemptedSubmit.value)) return 'Password is required.'
    return ''
})

const hasClientErrors = computed(() => {
    return !!passwordError.value
})

const allFieldsFilled = computed(() => {
    return !!form.password
})

function submit() {
    attemptedSubmit.value = true
    touchedPassword.value = true

    if (hasClientErrors.value) {
        return
    }

    form.delete('/settings/password', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('password');
        }
    })
}
</script>

<template>
    <div class="space-y-6">
        <div class="space-y-4 rounded-lg border p-4 bg-linear-to-tl from-red-500/5 to-default">
            <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
                <p class="font-medium">Account</p>
                <p class="text-sm">
                    No longer want to use our service? You can delete your account here. This action is not reversible.
                    All information related to this account will be deleted permanently.
                </p>
            </div>
            <Dialog>
                <DialogTrigger as-child>
                    <Button variant="destructive" data-test="delete-user-button">Delete account</Button>
                </DialogTrigger>
                <DialogContent>
                    <form action="/settings/profile" method="delete" @submit.prevent="submit" class="space-y-6">
                        <DialogHeader class="space-y-3">
                            <DialogTitle>Are you sure you want to delete your
                                account?</DialogTitle>
                            <DialogDescription>
                                Once your account is deleted, all of its
                                resources and data will also be permanently
                                deleted. Please enter your password to confirm
                                you would like to permanently delete your
                                account.
                            </DialogDescription>
                        </DialogHeader>
                        <div class="grid gap-2">
                            <FieldLabel for="password">
                                Password
                            </FieldLabel>
                            <Input v-model="form.password" @blur="markTouched('password')" id="password" name="password"
                                type="password" :class="{ 'border-red-500': passwordError || form.errors.password }"
                                autocomplete="new-password" />
                            <div v-if="passwordError || form.errors.password" class="text-red-500 text-sm">
                                {{ passwordError || form.errors.password }}
                            </div>
                        </div>
                        <DialogFooter class="gap-2">
                            <DialogClose as-child>
                                <Button variant="secondary" @click="
                                    () => {
                                        form.clearErrors();
                                        form.reset();
                                    }
                                ">
                                    Cancel
                                </Button>
                            </DialogClose>
                            <Button :disabled="form.processing || !allFieldsFilled" variant="destructive" type="submit"
                                data-test="confirm-delete-user-button">
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

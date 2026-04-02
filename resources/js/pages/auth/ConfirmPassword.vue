<script setup lang="ts">
import { ref, computed, type HTMLAttributes } from 'vue'
import { useForm } from '@inertiajs/vue3'

import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import {
    Field,
    FieldDescription,
    FieldGroup,
    FieldLabel,
} from '@/components/ui/field'
import { Input } from '@/components/ui/input';
import { Spinner } from '@/components/ui/spinner';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    class?: HTMLAttributes["class"];
}>()

const form = useForm({
    password: '',
})

// Touch tracking so we don't show errors before the user interacts
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

    form.post('/user/confirm-password', {
        onSuccess: () => {
            form.reset('password');
        }
    })
}
</script>

<template>
    <div class="flex min-h-svh w-full items-center justify-center p-6 md:p-10">
        <div class="w-full max-w-sm">

            <Head title="Confirm your password" />
            <div :class="cn('flex flex-col gap-6', props.class)">
                <form action="/user/confirm-password" method="put" @submit.prevent="submit">
                    <FieldGroup>
                        <div class="flex flex-col items-center gap-2 text-center">
                            <span class="sr-only">Confirm your password</span>
                            <h1 class="text-xl font-bold">
                                Confirm your password
                            </h1>
                            <FieldDescription>
                                This is a secure area of the application. Please confirm your password before
                                continuing
                            </FieldDescription>
                        </div>
                        <Field>
                            <FieldLabel for="password">
                                Password
                            </FieldLabel>
                            <Input v-model="form.password" @blur="markTouched('password')" id="password" name="password"
                                type="password" :class="{ 'border-red-500': passwordError || form.errors.password }" />
                            <div v-if="passwordError || form.errors.password" class="text-red-500 text-sm">
                                {{ passwordError || form.errors.password }}
                            </div>
                        </Field>
                        <Field>
                            <Button :disabled="form.processing || !allFieldsFilled" type="submit"
                                class="bg-green-500 hover:bg-green-400">
                                <Spinner v-if="form.processing" class="mr-2" />
                                Confirm Password
                            </Button>
                        </Field>
                    </FieldGroup>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import { Field, FieldDescription, FieldGroup, FieldLabel } from '@/components/ui/field';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const props = defineProps<{
    question?: string | null;
    answer?: string | null;
    remainingToday: number;
    dailyLimit: number;
}>();

const outOfQuestions = computed(() => props.remainingToday <= 0);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Coach',
        href: '/coach',
    },
];

const MAX_LENGTH = 500;

const examples = [
    'What are some high-protein breakfasts under 400 calories?',
    'How much fiber should I aim for each day?',
    'What are healthy snacks I can bring to work?',
];

const page = usePage();
const errorMessage = computed(() => page.props.flash.error);

const form = useForm({
    question: '',
});

const remaining = computed(() => MAX_LENGTH - form.question.length);

function submit() {
    if (!form.question.trim() || outOfQuestions.value) return;

    form.post('/coach', {
        preserveScroll: true,
        onSuccess: () => form.reset(),
        onError: (errors) => {
            if (!errors.question) {
                form.setError('question', "Slow down! You're asking too fast.");
            }
        },
    });
}

function useExample(example: string) {
    form.question = example;
}
</script>

<template>
    <Head title="Coach" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex-1 md:max-w-2xl">
            <section class="max-w-xl space-y-6 p-4">
                <form @submit.prevent="submit">
                    <FieldGroup>
                        <div class="flex flex-col items-start text-left">
                            <h3 class="mb-0.5 text-base font-medium">Nutrition Coach</h3>
                            <FieldDescription>
                                Ask a question about food, nutrition, or your goals. The coach gives general
                                information, not medical advice.
                            </FieldDescription>
                        </div>

                        <Field>
                            <FieldLabel for="question">Your question</FieldLabel>
                            <Textarea
                                v-model="form.question"
                                id="question"
                                name="question"
                                :maxlength="MAX_LENGTH"
                                :aria-invalid="!!form.errors.question"
                                aria-describedby="question-help"
                                :class="['min-h-28', { 'border-red-500': form.errors.question }]"
                                @keydown.meta.enter.prevent="submit"
                                @keydown.ctrl.enter.prevent="submit" />
                            <div id="question-help" class="text-xs text-muted-foreground">
                                {{ remaining }} characters left
                            </div>
                            <div v-if="form.errors.question" class="text-sm text-red-500" role="alert">
                                {{ form.errors.question }}
                            </div>
                        </Field>

                        <div v-if="!props.answer && !form.question" class="flex flex-wrap gap-2">
                            <button
                                v-for="example in examples"
                                :key="example"
                                type="button"
                                class="cursor-pointer rounded-full border px-3 py-1 text-left text-xs text-muted-foreground hover:bg-muted"
                                @click="useExample(example)">
                                {{ example }}
                            </button>
                        </div>

                        <Field class="flex items-center gap-4">
                            <Button
                                :disabled="form.processing || !form.question.trim() || outOfQuestions"
                                type="submit"
                                class="inline-flex w-auto! cursor-pointer items-start self-start bg-green-500 hover:bg-green-400 dark:text-white">
                                <Spinner v-if="form.processing" class="mr-2" />
                                {{ form.processing ? 'Thinking...' : 'Ask' }}
                            </Button>
                            <span class="text-xs text-muted-foreground">
                                {{ props.remainingToday }} of {{ props.dailyLimit }} questions left today
                            </span>
                        </Field>
                    </FieldGroup>
                </form>

                <div v-if="errorMessage" class="rounded-md border border-red-300 p-3 text-sm text-red-600" role="alert">
                    {{ errorMessage }}
                </div>

                <div v-if="props.answer" class="space-y-3 rounded-lg border p-4" aria-live="polite">
                    <p v-if="props.question" class="text-sm font-medium">{{ props.question }}</p>
                    <p class="text-sm whitespace-pre-line text-muted-foreground">{{ props.answer }}</p>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

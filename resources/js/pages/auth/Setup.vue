<script setup lang="ts">
import AccountStep from './setup-steps/AccountStep.vue';
import ProfileStep from './setup-steps/ProfileStep.vue';
import WeightGoalStep from './setup-steps/WeightGoalStep.vue';

const props = defineProps<{
    name?: string | null;
    email?: string | null;
    step: string;
    countries?: Record<string, string>;
    activityLevels?: Array<{ id: number; name: string }>;
    weeklyGoals?: Array<{ id: number; name: string }>;
}>();

const phaseOrder = ['account', 'profile', 'weightGoal'];

const isCompleted = (currentIterationStep: string) => {
    const currentIndex = phaseOrder.indexOf(props.step);
    const iterationIndex = phaseOrder.indexOf(currentIterationStep);

    return iterationIndex < currentIndex;
};
</script>

<template>
    <div class="fixed top-0 left-0 z-50 w-full border-b bg-white px-6 py-4">
        <div class="mx-auto max-w-xl">
            <div class="flex items-center gap-4">
                <div v-for="s in ['account', 'profile', 'weightGoal']" :key="s" class="group flex-1">
                    <div
                        :class="[
                            'h-1.5 w-full rounded-full transition-all duration-700',
                            step === s ? 'bg-green-500 shadow-xs shadow-green-500/50' : '',
                            isCompleted(s) ? 'bg-green-600/50' : '',
                            !isCompleted(s) && step !== s ? 'bg-gray-200' : '',
                        ]"></div>
                    <span
                        :class="[
                            'mt-1.5 block text-center text-[10px] font-bold tracking-widest uppercase transition-colors',
                            step === s ? 'text-green-500' : 'text-muted-foreground/40',
                        ]">
                        {{ s === 'weightGoal' ? 'weight goals' : s }}
                    </span>
                </div>
            </div>
        </div>
    </div>
    <AccountStep v-if="step === 'account'" />
    <ProfileStep :countries="countries" v-else-if="step === 'profile'" />
    <WeightGoalStep :activityLevels="activityLevels" :weeklyGoals="weeklyGoals" v-else-if="step === 'weightGoal'" />
</template>

<script setup lang="ts">
import AccountStep from './AccountStep.vue';
import ProfileStep from './ProfileStep.vue';
// import WeightGoalStep from './WeightGoalStep.vue';

defineProps<{
    name?: string | null;
    email?: string | null;
    step: string;
    countries?: Record<string, string>;
}>();
</script>

<template>
    <!-- todo -->
    <!-- <div class="max-w-md mx-auto mt-12">
        <div class="flex gap-2 mb-8">
            <div v-for="s in ['account', 'profile', 'goals']" :key="s"
                :class="['h-1 flex-1 rounded', step === s ? 'bg-blue-600' : 'bg-gray-00']">
            </div>
        </div>
    </div> -->
    <AccountStep v-if="step === 'account'" />
    <ProfileStep :countries="countries" v-else-if="step === 'profile'" />
</template>
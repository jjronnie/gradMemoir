<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        size?: 'card' | 'post' | 'profile';
    }>(),
    {
        size: 'card',
    },
);

const classes = computed(() => {
    if (props.size === 'post') {
        return 'aspect-square';
    }

    if (props.size === 'profile') {
        return 'aspect-[16/9]';
    }

    return 'aspect-[3/4]';
});
</script>

<template>
    <div
        class="animate-pulse rounded-xl bg-muted"
        :class="classes"
        aria-hidden="true"
    />
</template>

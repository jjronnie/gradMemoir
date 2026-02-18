<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

type Props = {
    class?: HTMLAttributes['class'];
};

const props = defineProps<Props>();
</script>

<template>
    <svg
        viewBox="0 0 24 24"
        aria-hidden="true"
        :class="props.class"
    >
        <circle cx="12" cy="12" r="10" fill="currentColor" />
        <path
            d="M8 12.5 10.7 15 16 9.5"
            fill="none"
            stroke="white"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
        />
    </svg>
</template>

<script setup lang="ts">
import type { PrimitiveProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { Button } from '@/components/ui/button';
import type { ButtonVariants } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';

interface Props extends PrimitiveProps {
    loading?: boolean;
    loadingText?: string;
    disabled?: boolean;
    variant?: ButtonVariants['variant'];
    size?: ButtonVariants['size'];
    type?: HTMLButtonElement['type'];
    class?: HTMLAttributes['class'];
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    loadingText: '',
    disabled: false,
    type: 'button',
    as: 'button',
});
</script>

<template>
    <Button
        :as="props.as"
        :as-child="props.asChild"
        :type="props.type"
        :variant="props.variant"
        :size="props.size"
        :disabled="props.disabled || props.loading"
        :class="props.class"
    >
        <Spinner v-if="props.loading" />
        <span v-if="props.loading && props.loadingText !== ''">
            {{ props.loadingText }}
        </span>
        <slot v-else-if="!props.loading || props.loadingText === ''" />
    </Button>
</template>

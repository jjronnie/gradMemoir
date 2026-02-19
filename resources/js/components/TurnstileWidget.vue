<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

type TurnstileRenderOptions = {
    sitekey: string;
    callback?: (token: string) => void;
    'expired-callback'?: () => void;
    'error-callback'?: () => void;
    theme?: 'light' | 'dark' | 'auto';
};

declare global {
    interface Window {
        turnstile?: {
            render: (
                container: HTMLElement | string,
                options: TurnstileRenderOptions,
            ) => string;
            reset: (widgetId: string) => void;
        };
    }
}

const emit = defineEmits<{
    (event: 'verified', token: string): void;
    (event: 'expired'): void;
    (event: 'widget-mounted', id: string): void;
}>();

const page = usePage();
const container = ref<HTMLElement | null>(null);
const turnstileEnabled = computed(() =>
    Boolean(page.props.turnstileEnabled),
);
const siteKey = computed(
    () => (page.props.turnstileSiteKey as string | undefined) ?? '',
);

const waitForTurnstile = async (): Promise<void> => {
    let attempts = 0;

    while (window.turnstile === undefined && attempts < 40) {
        await new Promise((resolve) => setTimeout(resolve, 50));
        attempts += 1;
    }
};

onMounted(async () => {
    if (
        ! turnstileEnabled.value ||
        siteKey.value === '' ||
        container.value === null
    ) {
        return;
    }

    await waitForTurnstile();

    if (window.turnstile === undefined || container.value === null) {
        return;
    }

    const widgetId = window.turnstile.render(container.value, {
        sitekey: siteKey.value,
        callback: (token: string) => {
            emit('verified', token);
        },
        'expired-callback': () => {
            emit('expired');
        },
        'error-callback': () => {
            emit('expired');
        },
        theme: 'auto',
    });

    emit('widget-mounted', widgetId);
});
</script>

<template>
    <div class="flex w-full justify-center overflow-x-auto">
        <div ref="container" />
    </div>
</template>

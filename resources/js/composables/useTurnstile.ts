import { ref } from 'vue';

declare global {
    interface Window {
        turnstile?: {
            reset: (widgetId: string) => void;
        };
    }
}

/**
 * Shared helpers for Turnstile tokens.
 */
export function useTurnstile() {
    const token = ref('');
    const widgetId = ref<string | null>(null);

    const setToken = (value: string): void => {
        token.value = value;
    };

    const setWidgetId = (value: string): void => {
        widgetId.value = value;
    };

    const reset = (): void => {
        if (widgetId.value !== null && window.turnstile !== undefined) {
            window.turnstile.reset(widgetId.value);
        }

        token.value = '';
    };

    return {
        token,
        widgetId,
        setToken,
        setWidgetId,
        reset,
    };
}

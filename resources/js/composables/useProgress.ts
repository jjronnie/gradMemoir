import { ref } from 'vue';

const isActive = ref(false);
const percent = ref(0);

/**
 * Global progress bar state helper.
 */
export function useProgress() {
    const start = (): void => {
        isActive.value = true;
        percent.value = 10;
    };

    const setPercent = (value: number): void => {
        percent.value = Math.max(0, Math.min(100, value));
    };

    const finish = (): void => {
        percent.value = 100;
        setTimeout(() => {
            isActive.value = false;
            percent.value = 0;
        }, 250);
    };

    return {
        isActive,
        percent,
        start,
        setPercent,
        finish,
    };
}

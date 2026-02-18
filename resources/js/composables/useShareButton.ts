import { computed, onMounted, onUnmounted, ref } from 'vue';

/**
 * Tracks scroll position for floating share controls.
 */
export function useShareButton(threshold = 200) {
    const scrollY = ref(0);

    const onScroll = (): void => {
        scrollY.value = window.scrollY;
    };

    onMounted(() => {
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    });

    onUnmounted(() => {
        window.removeEventListener('scroll', onScroll);
    });

    const isVisible = computed(() => scrollY.value > threshold);

    return {
        isVisible,
        scrollY,
    };
}

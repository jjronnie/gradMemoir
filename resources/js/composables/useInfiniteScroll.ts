import { onMounted, onUnmounted, ref, type Ref } from 'vue';

/**
 * Observes a sentinel element and calls loadMore when visible.
 */
export function useInfiniteScroll(
    target: Ref<HTMLElement | null>,
    loadMore: () => void | Promise<void>,
) {
    const observer = ref<IntersectionObserver | null>(null);

    onMounted(() => {
        observer.value = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    void loadMore();
                }
            });
        });

        if (target.value !== null) {
            observer.value.observe(target.value);
        }
    });

    onUnmounted(() => {
        observer.value?.disconnect();
        observer.value = null;
    });
}

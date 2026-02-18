<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { onBeforeUnmount, ref, watch } from 'vue';

type ToastType = 'success' | 'error';

type ToastItem = {
    id: number;
    type: ToastType;
    message: string;
    remainingMs: number;
    totalMs: number;
    paused: boolean;
    timerId: ReturnType<typeof setInterval> | null;
};

const page = usePage();
const toasts = ref<ToastItem[]>([]);
let toastSequence = 0;

const removeToast = (id: number): void => {
    const toast = toasts.value.find((item) => item.id === id);

    if (toast?.timerId !== null) {
        clearInterval(toast.timerId);
    }

    toasts.value = toasts.value.filter((item) => item.id !== id);
};

const setPaused = (id: number, paused: boolean): void => {
    const toast = toasts.value.find((item) => item.id === id);

    if (toast !== undefined) {
        toast.paused = paused;
    }
};

const enqueueToast = (type: ToastType, message: string): void => {
    if (message.trim() === '') {
        return;
    }

    const existing = toasts.value.find(
        (item) => item.message === message && item.type === type,
    );

    if (existing !== undefined) {
        existing.remainingMs = existing.totalMs;
        return;
    }

    const toast: ToastItem = {
        id: ++toastSequence,
        type,
        message,
        totalMs: 10000,
        remainingMs: 10000,
        paused: false,
        timerId: null,
    };

    toast.timerId = setInterval(() => {
        if (toast.paused) {
            return;
        }

        toast.remainingMs -= 100;

        if (toast.remainingMs <= 0) {
            removeToast(toast.id);
        }
    }, 100);

    toasts.value.unshift(toast);
};

watch(
    () => page.props.flash,
    (flash) => {
        const success = (flash?.success as string | null | undefined) ?? '';
        const error = (flash?.error as string | null | undefined) ?? '';

        if (success !== '') {
            enqueueToast('success', success);
        }

        if (error !== '') {
            enqueueToast('error', error);
        }
    },
    { deep: true, immediate: true },
);

onBeforeUnmount(() => {
    for (const toast of toasts.value) {
        if (toast.timerId !== null) {
            clearInterval(toast.timerId);
        }
    }
});
</script>

<template>
    <div
        v-if="toasts.length > 0"
        class="pointer-events-none fixed top-4 left-1/2 z-[80] w-[min(92vw,30rem)] -translate-x-1/2 space-y-3"
    >
        <div
            v-for="toast in toasts"
            :key="toast.id"
            class="pointer-events-auto overflow-hidden rounded-xl border border-white/20 bg-background/50 shadow-xl backdrop-blur-xl"
            :class="
                toast.type === 'success'
                    ? 'text-emerald-700 dark:text-emerald-300'
                    : 'text-destructive'
            "
            @mouseenter="setPaused(toast.id, true)"
            @mouseleave="setPaused(toast.id, false)"
            @touchstart="setPaused(toast.id, true)"
            @touchend="setPaused(toast.id, false)"
            @click="setPaused(toast.id, !toast.paused)"
        >
            <div class="flex items-start gap-3 px-4 py-3">
                <i
                    :class="
                        toast.type === 'success'
                            ? 'fa-solid fa-circle-check mt-0.5'
                            : 'fa-solid fa-circle-exclamation mt-0.5'
                    "
                />
                <div class="text-sm font-medium">
                    {{ toast.message }}
                </div>
            </div>
            <div class="h-1 w-full bg-white/10">
                <div
                    class="h-full transition-[width] duration-100"
                    :class="
                        toast.type === 'success'
                            ? 'bg-emerald-500/80'
                            : 'bg-destructive/80'
                    "
                    :style="{
                        width: `${Math.max((toast.remainingMs / toast.totalMs) * 100, 0)}%`,
                    }"
                />
            </div>
        </div>
    </div>
</template>

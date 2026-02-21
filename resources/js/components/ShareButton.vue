<script setup lang="ts">
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { useShareButton } from '@/composables/useShareButton';

const props = defineProps<{
    url: string;
    title: string;
    inline?: boolean;
}>();

const { isVisible } = useShareButton(200);
const expanded = ref(false);
const copied = ref(false);
const showControl = computed(() => props.inline === true || isVisible.value);

const toggle = (): void => {
    expanded.value = !expanded.value;
};

const copyLink = async (): Promise<void> => {
    await navigator.clipboard.writeText(props.url);
    copied.value = true;

    setTimeout(() => {
        copied.value = false;
    }, 1200);
};
</script>

<template>
    <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-x-3 opacity-0"
        leave-active-class="transition duration-150 ease-in"
        leave-to-class="translate-x-3 opacity-0"
    >
        <div
            v-if="showControl"
            :class="
                props.inline === true
                    ? 'relative z-40'
                    : 'fixed top-1/2 right-4 -translate-y-1/2 space-y-2'
            "
        >
            <Button
                size="icon"
                class="rounded-full"
                :class="{ 'shadow-lg': props.inline !== true }"
                aria-label="Toggle share options"
                @click="toggle"
            >
                <i :class="expanded ? 'fa-solid fa-xmark' : 'fa-solid fa-share'" />
            </Button>

            <div
                v-if="expanded"
                class="w-48 space-y-2 rounded-xl border bg-card p-3 text-sm shadow-xl"
                :class="
                    props.inline === true
                        ? 'absolute top-full right-0 mt-2'
                        : ''
                "
            >
                <Button
                    variant="secondary"
                    class="w-full justify-start"
                    aria-label="Copy profile link"
                    @click="copyLink"
                >
                    {{ copied ? 'Copied!' : 'Copy Link' }}
                </Button>
                <a
                    class="block rounded-md px-2 py-1.5 hover:bg-accent"
                    :href="`https://wa.me/?text=${encodeURIComponent(`${title} ${url}`)}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    >Share to WhatsApp</a
                >
                <a
                    class="block rounded-md px-2 py-1.5 hover:bg-accent"
                    :href="`https://x.com/intent/tweet?text=${encodeURIComponent(title)}&url=${encodeURIComponent(url)}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    >Share to X</a
                >
                <a
                    class="block rounded-md px-2 py-1.5 hover:bg-accent"
                    :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`"
                    target="_blank"
                    rel="noopener noreferrer"
                    >Share to Facebook</a
                >
            </div>
        </div>
    </transition>
</template>

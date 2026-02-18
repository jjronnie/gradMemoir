<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: string;
        name?: string;
        disabled?: boolean;
        checkAvailability?: boolean;
    }>(),
    {
        name: 'username',
        disabled: false,
        checkAvailability: true,
    },
);

const emit = defineEmits<{
    (event: 'update:modelValue', value: string): void;
    (event: 'availability', value: boolean | null): void;
}>();

const page = usePage();
const pending = ref(false);
const available = ref<boolean | null>(null);
let timeoutId: ReturnType<typeof setTimeout> | null = null;

const usernameError = computed(() => {
    return (page.props.errors?.[props.name] as string | undefined) ?? null;
});

const check = async (value: string): Promise<void> => {
    if (!props.checkAvailability) {
        return;
    }

    if (value.length < 3) {
        available.value = null;
        emit('availability', null);

        return;
    }

    pending.value = true;

    try {
        const response = await fetch('/api/check-username', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN':
                    document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') ?? '',
            },
            body: JSON.stringify({ username: value }),
        });

        const payload = (await response.json()) as {
            data?: { available?: boolean };
        };

        available.value = payload.data?.available ?? false;
        emit('availability', available.value);
    } catch {
        available.value = null;
        emit('availability', null);
    } finally {
        pending.value = false;
    }
};

watch(
    () => props.modelValue,
    (value) => {
        if (timeoutId !== null) {
            clearTimeout(timeoutId);
        }

        timeoutId = setTimeout(() => {
            void check(value);
        }, 400);
    },
);

onBeforeUnmount(() => {
    if (timeoutId !== null) {
        clearTimeout(timeoutId);
    }
});
</script>

<template>
    <div class="grid gap-2">
        <div class="relative">
            <span class="absolute top-1/2 left-3 -translate-y-1/2 text-muted-foreground"
                >@</span
            >
            <Input
                :name="name"
                :disabled="disabled"
                :model-value="modelValue"
                class="pl-7"
                placeholder="username"
                @update:model-value="emit('update:modelValue', ($event as string).replace(/^@/, ''))"
            />
        </div>
        <div class="text-xs text-muted-foreground" v-if="checkAvailability">
            <span v-if="pending">Checking availability...</span>
            <span v-else-if="available === true" class="text-green-600"
                >Username is available.</span
            >
            <span v-else-if="available === false" class="text-destructive"
                >Username is not available.</span
            >
        </div>
        <InputError :message="usernameError" />
    </div>
</template>

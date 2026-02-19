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
const usernamePattern = /^[a-z0-9_]+$/;
let requestVersion = 0;
let timeoutId: ReturnType<typeof setTimeout> | null = null;

const usernameError = computed(() => {
    return (page.props.errors?.[props.name] as string | undefined) ?? null;
});

const sanitizeUsername = (value: string): string => {
    return value
        .replace(/^@+/, '')
        .toLowerCase()
        .replace(/\s+/g, '_')
        .replace(/[^a-z0-9_]/g, '')
        .replace(/_+/g, '_')
        .replace(/^_+|_+$/g, '')
        .slice(0, 30);
};

const validateUsername = (value: string): string | null => {
    if (value === '') {
        return null;
    }

    if (value.length < 3) {
        return 'Username must be at least 3 characters.';
    }

    if (!usernamePattern.test(value)) {
        return 'Username may only contain lowercase letters, numbers, and underscores.';
    }

    return null;
};

const inlineError = computed(() => validateUsername(props.modelValue));

const resetAvailability = (): void => {
    available.value = null;
    emit('availability', null);
};

const check = async (value: string): Promise<void> => {
    if (!props.checkAvailability) {
        return;
    }

    if (validateUsername(value) !== null) {
        pending.value = false;
        resetAvailability();

        return;
    }

    const currentRequest = ++requestVersion;
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

        if (currentRequest !== requestVersion) {
            return;
        }

        if (!response.ok) {
            resetAvailability();

            return;
        }

        const payload = (await response.json()) as {
            data?: { available?: boolean };
        };

        available.value =
            typeof payload.data?.available === 'boolean'
                ? payload.data.available
                : null;
        emit('availability', available.value);
    } catch {
        if (currentRequest !== requestVersion) {
            return;
        }

        resetAvailability();
    } finally {
        if (currentRequest === requestVersion) {
            pending.value = false;
        }
    }
};

watch(
    () => props.modelValue,
    (value) => {
        if (timeoutId !== null) {
            clearTimeout(timeoutId);
        }

        const sanitized = sanitizeUsername(value);

        if (sanitized !== value) {
            emit('update:modelValue', sanitized);

            return;
        }

        requestVersion++;
        pending.value = false;
        resetAvailability();

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

const onUsernameInput = (value: string): void => {
    emit('update:modelValue', sanitizeUsername(value));
};
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
                @update:model-value="onUsernameInput($event as string)"
            />
        </div>
        <div class="text-xs text-muted-foreground" v-if="checkAvailability">
            <span v-if="inlineError" class="text-destructive">{{ inlineError }}</span>
            <span v-else-if="pending">Checking availability...</span>
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

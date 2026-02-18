<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: string;
        name?: string;
        maxWords?: number;
        rows?: number;
        placeholder?: string;
    }>(),
    {
        name: 'bio',
        maxWords: 100,
        rows: 4,
        placeholder: '',
    },
);

const emit = defineEmits<{
    (event: 'update:modelValue', value: string): void;
}>();

const page = usePage();

const wordCount = computed(() => {
    const value = props.modelValue.trim();

    return value === '' ? 0 : value.split(/\s+/).length;
});

const warningClass = computed(() => {
    if (wordCount.value > props.maxWords) {
        return 'text-destructive';
    }

    if (wordCount.value >= props.maxWords - 10) {
        return 'text-amber-600';
    }

    return 'text-muted-foreground';
});

const error = computed(() => {
    return (page.props.errors?.[props.name] as string | undefined) ?? null;
});
</script>

<template>
    <div class="grid gap-2">
        <textarea
            :name="name"
            :rows="rows"
            :value="modelValue"
            :placeholder="placeholder"
            class="flex min-h-24 w-full rounded-md border border-input bg-background px-3 py-2 text-sm shadow-xs outline-none ring-ring/50 transition-[color,box-shadow] placeholder:text-muted-foreground focus-visible:ring-[3px]"
            @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
        />
        <div class="text-right text-xs" :class="warningClass">
            {{ wordCount }} / {{ maxWords }} words
        </div>
        <InputError :message="error" />
    </div>
</template>

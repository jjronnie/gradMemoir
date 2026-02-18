<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        modelValue: File | null;
        currentAvatar?: string | null;
        name?: string;
    }>(),
    {
        currentAvatar: null,
        name: 'avatar',
    },
);

const emit = defineEmits<{
    (event: 'update:modelValue', value: File | null): void;
}>();

const page = usePage();
const preview = ref<string | null>(props.currentAvatar);

watch(
    () => props.currentAvatar,
    (value) => {
        if (props.modelValue === null) {
            preview.value = value ?? null;
        }
    },
);

const error = computed(() => {
    return (page.props.errors?.[props.name] as string | undefined) ?? null;
});

const onFileChange = (event: Event): void => {
    const input = event.target as HTMLInputElement;
    const file = input.files?.[0] ?? null;
    emit('update:modelValue', file);

    if (file === null) {
        preview.value = props.currentAvatar ?? null;

        return;
    }

    const reader = new FileReader();
    reader.onload = () => {
        preview.value = reader.result as string;
    };
    reader.readAsDataURL(file);
};

const clear = (): void => {
    emit('update:modelValue', null);
    preview.value = props.currentAvatar ?? null;
};
</script>

<template>
    <div class="space-y-3">
        <div
            class="h-28 w-28 overflow-hidden rounded-full border border-border bg-muted"
        >
            <img
                v-if="preview"
                :src="preview"
                alt="Avatar preview"
                class="h-full w-full object-cover"
            />
            <div v-else class="flex h-full w-full items-center justify-center text-xs text-muted-foreground">
                No image
            </div>
        </div>

        <Input
            :name="name"
            type="file"
            accept=".jpg,.jpeg,.png,.webp,.gif,.avif"
            @change="onFileChange"
        />

        <Button type="button" variant="outline" size="sm" @click="clear">
            Clear
        </Button>

        <InputError :message="error" />
    </div>
</template>

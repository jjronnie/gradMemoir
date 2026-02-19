<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { usePage } from '@inertiajs/vue3';
import { Camera, ImagePlus, UserCircle2 } from 'lucide-vue-next';
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
const fileInput = ref<HTMLInputElement | null>(null);

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

const openPicker = (): void => {
    fileInput.value?.click();
};

const clear = (): void => {
    emit('update:modelValue', null);
    preview.value = props.currentAvatar ?? null;

    if (fileInput.value !== null) {
        fileInput.value.value = '';
    }
};
</script>

<template>
    <div class="space-y-4">
        <input
            ref="fileInput"
            :name="name"
            type="file"
            accept=".jpg,.jpeg,.png,.webp,.gif,.avif"
            class="hidden"
            @change="onFileChange"
        />

        <button
            type="button"
            class="group relative w-full overflow-hidden rounded-xl border border-dashed border-border bg-muted/40 p-4 text-left transition hover:border-primary"
            @click="openPicker"
        >
            <div class="flex items-center gap-4">
                <div
                    class="h-24 w-24 overflow-hidden rounded-full border border-border bg-background"
                >
                    <img
                        v-if="preview"
                        :src="preview"
                        alt="Avatar preview"
                        class="h-full w-full object-cover"
                    />
                    <div
                        v-else
                        class="flex h-full w-full items-center justify-center text-muted-foreground"
                    >
                        <UserCircle2 class="h-12 w-12" />
                    </div>
                </div>
                <div class="space-y-1">
                    <p class="font-medium">
                        {{ preview ? 'Change profile photo' : 'Choose profile photo' }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        JPG, PNG, WEBP, GIF, or AVIF up to 20MB
                    </p>
                    <p class="text-xs text-muted-foreground">
                        Tap to open picker and preview instantly
                    </p>
                </div>
            </div>
            <span
                class="pointer-events-none absolute right-3 bottom-3 inline-flex items-center gap-1 rounded-md border border-border bg-background px-2 py-1 text-xs text-muted-foreground transition group-hover:text-foreground"
            >
                <Camera class="h-3.5 w-3.5" />
                Pick
            </span>
        </button>

        <div class="flex items-center gap-2">
            <Button type="button" variant="outline" size="sm" @click="openPicker">
                <ImagePlus class="mr-1 h-4 w-4" />
                {{ preview ? 'Replace' : 'Upload' }}
            </Button>
            <Button type="button" variant="ghost" size="sm" @click="clear">
                Clear
            </Button>
        </div>

        <InputError :message="error" />
    </div>
</template>

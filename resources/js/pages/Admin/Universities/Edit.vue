<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    university: {
        id: number;
        name: string;
        location: string;
    };
}>();

const form = useForm({
    name: props.university.name,
    location: props.university.location,
    logo: null as File | null,
});

const submit = (): void => {
    form.put(`/admin/universities/${props.university.id}`, {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Edit University" />

    <AppLayout>
        <div class="mx-auto max-w-2xl space-y-4 p-4">
            <h1 class="text-2xl font-semibold">Edit University</h1>
            <form class="space-y-4 rounded-xl border border-border bg-card p-4" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input id="name" v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="grid gap-2">
                    <Label for="location">Location</Label>
                    <Input id="location" v-model="form.location" />
                    <InputError :message="form.errors.location" />
                </div>
                <div class="grid gap-2">
                    <Label for="logo">Logo</Label>
                    <Input
                        id="logo"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp,.gif,.avif"
                        @change="form.logo = (($event.target as HTMLInputElement).files?.[0] ?? null)"
                    />
                    <InputError :message="form.errors.logo" />
                </div>
                <LoadingButton
                    type="submit"
                    :loading="form.processing"
                    loading-text="Updating..."
                >
                    Update
                </LoadingButton>
            </form>
        </div>
    </AppLayout>
</template>

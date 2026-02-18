<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    universities: Array<{ id: number; name: string }>;
    admins: Array<{ id: number; name: string; email: string }>;
}>();

const form = useForm({
    university_id: '',
    name: '',
    short_name: '',
    nickname: '',
    year: '',
    admin_id: '',
});

const slugPreview = computed(() => {
    if (form.short_name === '' || form.year === '') {
        return '';
    }

    return `${form.short_name}-class-of-${form.year}`
        .toLowerCase()
        .replace(/\s+/g, '-');
});

const submit = (): void => {
    form.post('/admin/courses');
};
</script>

<template>
    <Head title="Create Course" />

    <AppLayout>
        <div class="mx-auto max-w-2xl space-y-4 p-4">
            <h1 class="text-2xl font-semibold">Create Course</h1>
            <form class="space-y-4 rounded-xl border border-border bg-card p-4" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="university_id">University</Label>
                    <select
                        id="university_id"
                        v-model="form.university_id"
                        class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                    >
                        <option value="">Select university</option>
                        <option
                            v-for="university in universities"
                            :key="university.id"
                            :value="university.id"
                        >
                            {{ university.name }}
                        </option>
                    </select>
                    <InputError :message="form.errors.university_id" />
                </div>
                <div class="grid gap-2">
                    <Label for="name">Course Name</Label>
                    <Input id="name" v-model="form.name" />
                    <InputError :message="form.errors.name" />
                </div>
                <div class="grid gap-2 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="short_name">Short Name</Label>
                        <Input id="short_name" v-model="form.short_name" />
                        <InputError :message="form.errors.short_name" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="nickname">Nickname (optional)</Label>
                        <Input id="nickname" v-model="form.nickname" placeholder="e.g. Computer Science" />
                        <InputError :message="form.errors.nickname" />
                    </div>
                </div>
                <div class="grid gap-2">
                    <div class="grid gap-2">
                        <Label for="year">Year</Label>
                        <Input id="year" v-model="form.year" />
                        <InputError :message="form.errors.year" />
                    </div>
                </div>
                <div class="rounded-md border border-border bg-muted p-3 text-xs">
                    Slug preview: {{ slugPreview || 'short-name-class-of-year' }}
                </div>
                <div class="grid gap-2">
                    <Label for="admin_id">Assigned Admin (optional)</Label>
                    <select
                        id="admin_id"
                        v-model="form.admin_id"
                        class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                    >
                        <option value="">No admin assigned</option>
                        <option
                            v-for="admin in admins"
                            :key="admin.id"
                            :value="admin.id"
                        >
                            {{ admin.name }} ({{ admin.email }})
                        </option>
                    </select>
                    <InputError :message="form.errors.admin_id" />
                </div>
                <LoadingButton
                    type="submit"
                    :loading="form.processing"
                    loading-text="Saving..."
                >
                    Save
                </LoadingButton>
            </form>
        </div>
    </AppLayout>
</template>

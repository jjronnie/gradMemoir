<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import InputError from '@/components/InputError.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';

type CourseYearItem = {
    id: number;
    year: string;
    slug: string;
    admin_id: number | null;
    admin?: { id: number; name: string; email: string } | null;
};

const props = defineProps<{
    course: {
        id: number;
        university_id: number;
        name: string;
        short_name: string;
        nickname: string | null;
        course_years: CourseYearItem[];
    };
    universities: Array<{ id: number; name: string }>;
    admins: Array<{ id: number; name: string; email: string }>;
}>();

const form = useForm({
    university_id: props.course.university_id,
    name: props.course.name,
    short_name: props.course.short_name,
    nickname: props.course.nickname ?? '',
});

const createCohortForm = useForm({
    year: '',
    admin_id: '',
});

const cohortAdminSelections = reactive<Record<number, number | ''>>(
    props.course.course_years.reduce(
        (carry, cohort) => ({
            ...carry,
            [cohort.id]: cohort.admin_id ?? '',
        }),
        {} as Record<number, number | ''>,
    ),
);

const updatingCohortId = ref<number | null>(null);

const submit = (): void => {
    form.put(`/admin/courses/${props.course.id}`);
};

const createCohort = (): void => {
    createCohortForm
        .transform((data) => ({
            ...data,
            admin_id: data.admin_id === '' ? null : data.admin_id,
        }))
        .post(`/admin/courses/${props.course.id}/course-years`, {
        preserveScroll: true,
        onSuccess: () => {
            createCohortForm.reset();
        },
    });
};

const saveCohortAdmin = (courseYearId: number): void => {
    updatingCohortId.value = courseYearId;

    router.put(
        `/admin/course-years/${courseYearId}/admin`,
        {
            admin_id:
                cohortAdminSelections[courseYearId] === ''
                    ? null
                    : cohortAdminSelections[courseYearId],
        },
        {
            preserveScroll: true,
            onFinish: () => {
                updatingCohortId.value = null;
            },
        },
    );
};
</script>

<template>
    <Head title="Edit Program" />

    <AppLayout>
        <div class="mx-auto max-w-4xl space-y-4 p-4">
            <h1 class="text-2xl font-semibold">Edit Program</h1>
            <form class="space-y-4 rounded-xl border border-border bg-card p-4" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="university_id">University</Label>
                    <select
                        id="university_id"
                        v-model="form.university_id"
                        class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                    >
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
                    <Label for="name">Program Name</Label>
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
                <LoadingButton
                    type="submit"
                    :loading="form.processing"
                    loading-text="Updating..."
                >
                    Update Program
                </LoadingButton>
            </form>

            <section class="space-y-4 rounded-xl border border-border bg-card p-4">
                <h2 class="text-lg font-semibold">Cohorts</h2>

                <form class="grid gap-3 rounded-lg border border-border/60 p-3 md:grid-cols-[1fr_1fr_auto]" @submit.prevent="createCohort">
                    <div class="grid gap-2">
                        <Label for="year">Year</Label>
                        <Input id="year" v-model="createCohortForm.year" placeholder="2026" maxlength="4" />
                        <InputError :message="createCohortForm.errors.year" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="cohort_admin_id">Assign Admin (optional)</Label>
                        <select
                            id="cohort_admin_id"
                            v-model="createCohortForm.admin_id"
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
                        <InputError :message="createCohortForm.errors.admin_id" />
                    </div>
                    <div class="self-end">
                        <LoadingButton
                            type="submit"
                            :loading="createCohortForm.processing"
                            loading-text="Creating..."
                        >
                            Add Cohort
                        </LoadingButton>
                    </div>
                </form>

                <div class="rounded-lg border border-border/60">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-border text-left">
                                <th class="px-3 py-2">Year</th>
                                <th class="px-3 py-2">Slug</th>
                                <th class="px-3 py-2">Admin</th>
                                <th class="px-3 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="courseYear in course.course_years"
                                :key="courseYear.id"
                                class="border-b border-border/50"
                            >
                                <td class="px-3 py-2">Class of {{ courseYear.year }}</td>
                                <td class="px-3 py-2 font-mono text-xs">
                                    {{ courseYear.slug }}
                                </td>
                                <td class="px-3 py-2">
                                    <select
                                        v-model="cohortAdminSelections[courseYear.id]"
                                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
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
                                </td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <LoadingButton
                                            type="button"
                                            size="sm"
                                            :loading="updatingCohortId === courseYear.id"
                                            loading-text="Saving..."
                                            @click="saveCohortAdmin(courseYear.id)"
                                        >
                                            Save
                                        </LoadingButton>
                                        <a
                                            :href="`/${courseYear.slug}`"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex h-8 items-center rounded-md border border-border px-3 text-xs hover:bg-accent"
                                        >
                                            View
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="course.course_years.length === 0">
                                <td colspan="4" class="px-3 py-4 text-center text-muted-foreground">
                                    No cohorts added yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

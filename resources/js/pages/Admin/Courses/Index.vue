<script setup lang="ts">
import ConfirmActionModal from '@/components/ConfirmActionModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

type Course = {
    id: number;
    name: string;
    short_name: string;
    shortcode: string;
    course_years_count: number;
    university?: { name: string };
};

const props = defineProps<{
    courses: {
        data: Course[];
    };
    search: string;
}>();

const search = ref(props.search);
const confirmOpen = ref(false);
const pendingCourseId = ref<number | null>(null);
const deleting = ref(false);

const shortNameSlug = (shortName: string): string =>
    shortName
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');

const onSearch = (): void => {
    router.get(
        '/admin/courses',
        { search: search.value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const requestDelete = (courseId: number): void => {
    pendingCourseId.value = courseId;
    confirmOpen.value = true;
};

const deleteCourse = (): void => {
    if (pendingCourseId.value === null) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/courses/${pendingCourseId.value}`, {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            confirmOpen.value = false;
            pendingCourseId.value = null;
        },
    });
};
</script>

<template>
    <Head title="Programs" />

    <AppLayout>
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Programs</h1>
                <Link href="/admin/courses/create">
                    <Button>Create Program</Button>
                </Link>
            </div>
            <Input
                v-model="search"
                placeholder="Search programs..."
                @input="onSearch"
            />
            <div class="rounded-xl border border-border bg-card p-4">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[960px] text-sm">
                        <thead>
                            <tr class="border-b border-border text-left">
                                <th class="px-2 py-2">Program</th>
                                <th class="px-2 py-2">University</th>
                                <th class="px-2 py-2">Shortcode</th>
                                <th class="px-2 py-2">Cohorts</th>
                                <th class="px-2 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="course in courses.data"
                                :key="course.id"
                                class="border-b border-border/50"
                            >
                                <td class="px-2 py-2">
                                    {{ course.name }} ({{ course.short_name }})
                                </td>
                                <td class="px-2 py-2">
                                    {{ course.university?.name }}
                                </td>
                                <td class="px-2 py-2">{{ course.shortcode }}</td>
                                <td class="px-2 py-2">{{ course.course_years_count }}</td>
                                <td class="px-2 py-2">
                                    <Link
                                        :href="`/admin/courses/${course.id}/edit`"
                                    >
                                        <Button variant="outline" size="sm"
                                            >Edit</Button
                                        >
                                    </Link>
                                    <a
                                        :href="`/course/${shortNameSlug(course.short_name)}`"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="ml-2 inline-flex h-8 items-center rounded-md border border-border px-3 text-xs hover:bg-accent"
                                    >
                                        View
                                    </a>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        class="ml-2"
                                        @click="requestDelete(course.id)"
                                    >
                                        Delete
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <ConfirmActionModal
            :open="confirmOpen"
            title="Delete Program"
            description="Are you sure you want to delete this program? Users assigned to its cohorts will have membership cleared."
            confirm-text="Delete"
            :processing="deleting"
            @update:open="confirmOpen = $event"
            @confirm="deleteCourse"
        />
    </AppLayout>
</template>

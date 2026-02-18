<script setup lang="ts">
import ShareButton from '@/components/ShareButton.vue';
import ShimmerCard from '@/components/ShimmerCard.vue';
import { Input } from '@/components/ui/input';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { Course } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

type StudentCard = {
    id: number;
    name: string;
    username: string;
    profile_url?: string;
    media?: Array<{
        original_url?: string;
        conversions?: Record<string, string>;
    }>;
};

type Paginated<T> = {
    data: T[];
    next_page_url: string | null;
    prev_page_url: string | null;
    current_page: number;
};

const props = defineProps<{
    course: Course & { university: { name: string } };
    students: Paginated<StudentCard>;
    search: string;
}>();
const currentUrl = ref('');
const searchTerm = ref(props.search);
let timeoutId: ReturnType<typeof setTimeout> | null = null;

const onSearch = (): void => {
    if (timeoutId !== null) {
        clearTimeout(timeoutId);
    }

    timeoutId = setTimeout(() => {
        router.get(
            `/courses/${props.course.slug}`,
            { search: searchTerm.value },
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
    }, 350);
};

onMounted(() => {
    currentUrl.value = window.location.href;
});
</script>

<template>
    <Head :title="course.name" />

    <PublicLayout>
        <section class="py-8">
            <h1 class="text-3xl font-semibold">{{ course.name }}</h1>
            <p class="mt-2 text-sm text-muted-foreground">
                {{ course.university?.name }} · Class of {{ course.year }}
            </p>
            <p
                v-if="course.nickname"
                class="mt-1 text-sm text-muted-foreground"
            >
                AKA: {{ course.nickname }}
            </p>
            <div class="mt-4 max-w-md">
                <Input
                    v-model="searchTerm"
                    placeholder="Search by name or username..."
                    @input="onSearch"
                />
            </div>
        </section>

        <section
            class="grid grid-cols-2 gap-4 pb-10 sm:grid-cols-3 lg:grid-cols-4"
        >
            <Link
                v-for="student in students.data"
                :key="student.id"
                :href="`/@${student.username}`"
                class="overflow-hidden border border-border bg-card"
            >
                <img
                    :src="
                        student.media?.[0]?.conversions?.full ??
                        student.media?.[0]?.conversions?.thumb ??
                        student.media?.[0]?.original_url ??
                        ''
                    "
                    :alt="student.name"
                    class="aspect-[3/4] w-full object-cover"
                    loading="lazy"
                />
                <div class="p-3">
                    <p
                        class="truncate text-center text-sm font-semibold capitalize"
                    >
                        {{ student.name }}
                    </p>
                </div>
            </Link>

            <ShimmerCard v-if="students.data.length === 0" size="card" />
        </section>

        <section class="flex items-center justify-between pb-10">
            <Link
                v-if="students.prev_page_url"
                :href="students.prev_page_url"
                class="rounded-md border border-border px-3 py-1.5 text-sm"
                >Previous</Link
            >
            <div v-else />
            <Link
                v-if="students.next_page_url"
                :href="students.next_page_url"
                class="rounded-md border border-border px-3 py-1.5 text-sm"
                >Next</Link
            >
        </section>

        <ShareButton
            :url="currentUrl"
            :title="`${course.name} - ${$page.props.appName}`"
        />
    </PublicLayout>
</template>

<script setup lang="ts">
import ShareButton from '@/components/ShareButton.vue';
import { Input } from '@/components/ui/input';
import PublicLayout from '@/layouts/PublicLayout.vue';
import type { CourseYear } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, nextTick, onMounted, ref } from 'vue';

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
    courseYear: CourseYear & {
        course: {
            id: number;
            name: string;
            short_name: string;
            nickname?: string | null;
            university?: { name: string };
        };
    };
    students: Paginated<StudentCard>;
    search: string;
}>();

const currentUrl = ref('');
const searchTerm = ref(props.search);
const isSearchOpen = ref(props.search.trim() !== '');
const classHeading = computed(
    () => `${props.courseYear.course.short_name.toUpperCase()} Class of ${props.courseYear.year}`,
);
const searchInputId = `course-student-search-${props.courseYear.id}`;
let timeoutId: ReturnType<typeof setTimeout> | null = null;

const studentPhotoUrl = (student: StudentCard): string | null => {
    const url = student.media?.[0]?.conversions?.full
        ?? student.media?.[0]?.conversions?.thumb
        ?? student.media?.[0]?.original_url
        ?? null;

    if (url === null || url.trim() === '') {
        return null;
    }

    return url;
};

const onSearch = (): void => {
    if (timeoutId !== null) {
        clearTimeout(timeoutId);
    }

    timeoutId = setTimeout(() => {
        router.get(
            `/${props.courseYear.slug}`,
            { search: searchTerm.value },
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
    }, 350);
};

const openSearch = async (): Promise<void> => {
    isSearchOpen.value = true;

    await nextTick();

    const searchInput = document.getElementById(searchInputId) as HTMLInputElement | null;
    searchInput?.focus();
};

onMounted(() => {
    currentUrl.value = window.location.href;
});
</script>

<template>
    <Head :title="`${courseYear.course.name} - ${$page.props.appName}`" />

    <PublicLayout>
        <section class="py-10">
            <div class="mx-auto w-[90%] space-y-2">
                <div class="h-px bg-border" />
                <div class="h-px bg-border/70" />
            </div>

            <h1
                class="mx-auto mt-6 max-w-4xl text-center text-4xl font-semibold tracking-wide text-foreground sm:text-5xl"
            >
                {{ classHeading }}
            </h1>

            <div class="mx-auto mt-6 h-px w-[90%] bg-border" />

            <div
                class="mx-auto mt-6 grid w-[90%] grid-cols-1 gap-2 text-center text-sm text-muted-foreground md:grid-cols-3"
            >
                <p class="font-medium">{{ courseYear.course.university?.name }}</p>
                <p class="font-medium">{{ courseYear.course.name }}</p>
                <p class="font-medium">{{ courseYear.year }}</p>
            </div>

            <p
                v-if="courseYear.course.nickname"
                class="mt-4 text-center text-sm text-muted-foreground"
            >
                AKA: {{ courseYear.course.nickname }}
            </p>
            <div class="mx-auto mt-5 flex w-[90%] items-center gap-3">
                <button
                    type="button"
                    class="inline-flex h-10 w-10 items-center justify-center rounded-md border border-border bg-background text-foreground transition hover:bg-accent"
                    aria-label="Open student search"
                    @click="openSearch"
                >
                    <i class="fa-solid fa-magnifying-glass text-sm" />
                </button>

                <div v-if="isSearchOpen" class="w-full max-w-md">
                    <Input
                        :id="searchInputId"
                        v-model="searchTerm"
                        placeholder="Search by name or username..."
                        @input="onSearch"
                    />
                </div>
            </div>
        </section>

        <section class="grid grid-cols-2 gap-x-5 gap-y-8 py-10 sm:grid-cols-3 lg:grid-cols-4">
            <Link
                v-for="student in students.data"
                :key="student.id"
                :href="`/@${student.username}`"
                class="group block"
            >
                <div class="overflow-hidden border border-border bg-card">
                    <img
                        v-if="studentPhotoUrl(student)"
                        :src="studentPhotoUrl(student) ?? ''"
                        :alt="student.name"
                        class="aspect-[3/4] w-full object-cover transition-transform duration-300 group-hover:scale-[1.03]"
                        loading="lazy"
                    />
                    <div
                        v-else
                        class="flex aspect-[3/4] w-full items-center justify-center bg-muted text-muted-foreground"
                    >
                        <i class="fa-solid fa-user text-4xl" />
                    </div>
                </div>
                <p class="mt-3 truncate text-center text-sm font-semibold uppercase">
                    {{ student.name }}
                </p>
            </Link>

            <p
                v-if="students.data.length === 0"
                class="col-span-full text-center text-sm text-muted-foreground"
            >
                No results
            </p>
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
            :title="`${courseYear.course.name} Class of ${courseYear.year} - ${$page.props.appName}`"
        />
    </PublicLayout>
</template>

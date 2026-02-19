<script setup lang="ts">
import AvatarUpload from '@/components/AvatarUpload.vue';
import InputError from '@/components/InputError.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Course, CourseYear, University } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps<{
    universities: University[];
    courses: Course[];
    courseYears: CourseYear[];
    selectedUniversityId: number | null;
    selectedCourseId: number | null;
    selectedCourseYearId: number | null;
    search: string;
}>();

const currentStep = ref(1);
const search = ref(props.search ?? '');
const courseSearch = ref('');
const localErrors = ref<Record<string, string | null>>({
    university_id: null,
    course_id: null,
    course_year_id: null,
});

const form = useForm({
    university_id: props.selectedUniversityId,
    course_id: props.selectedCourseId,
    course_year_id: props.selectedCourseYearId,
    avatar: null as File | null,
});

const filteredUniversities = computed(() => {
    if (search.value.trim() === '') {
        return props.universities;
    }

    return props.universities.filter((university) =>
        university.name.toLowerCase().includes(search.value.toLowerCase()),
    );
});

const filteredCourses = computed(() => {
    if (courseSearch.value.trim() === '') {
        return props.courses;
    }

    const term = courseSearch.value.toLowerCase();

    return props.courses.filter((course) =>
        `${course.name} ${course.short_name} ${course.nickname ?? ''}`
            .toLowerCase()
            .includes(term),
    );
});

const selectedCourseYearLabel = computed(() => {
    const match = props.courseYears.find(
        (courseYear) => courseYear.id === form.course_year_id,
    );

    return match === undefined ? null : `Class of ${match.year}`;
});

const selectUniversity = (universityId: number): void => {
    form.university_id = universityId;
    form.course_id = null;
    form.course_year_id = null;
    localErrors.value.university_id = null;
    localErrors.value.course_id = null;
    localErrors.value.course_year_id = null;

    router.get(
        '/onboarding',
        { university_id: universityId },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: [
                'courses',
                'courseYears',
                'selectedUniversityId',
                'selectedCourseId',
                'selectedCourseYearId',
            ],
        },
    );
};

const selectCourse = (courseId: number): void => {
    form.course_id = courseId;
    form.course_year_id = null;
    localErrors.value.course_id = null;
    localErrors.value.course_year_id = null;

    router.get(
        '/onboarding',
        {
            university_id: form.university_id,
            course_id: courseId,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['courseYears', 'selectedCourseId', 'selectedCourseYearId'],
        },
    );
};

const validateCurrentStep = (): boolean => {
    if (currentStep.value === 1) {
        if (form.university_id === null) {
            localErrors.value.university_id = 'Please select a university.';
            return false;
        }

        localErrors.value.university_id = null;
        return true;
    }

    if (currentStep.value === 2) {
        if (form.course_id === null) {
            localErrors.value.course_id = 'Please select a course.';
            return false;
        }

        localErrors.value.course_id = null;
        return true;
    }

    if (currentStep.value === 3) {
        if (form.course_year_id === null) {
            localErrors.value.course_year_id = 'Please select a cohort.';
            return false;
        }

        localErrors.value.course_year_id = null;
        return true;
    }

    return true;
};

const next = (): void => {
    if (!validateCurrentStep()) {
        return;
    }

    if (currentStep.value < 4) {
        currentStep.value += 1;
    }
};

const back = (): void => {
    if (currentStep.value > 1) {
        currentStep.value -= 1;
    }
};

const submit = (): void => {
    if (!validateCurrentStep()) {
        return;
    }

    form.post('/onboarding', {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Onboarding" />

    <AppLayout>
        <div class="mx-auto w-full max-w-3xl space-y-6 p-4">
            <div class="space-y-2">
                <h1 class="text-2xl font-semibold">Complete your profile</h1>
                <p class="text-sm text-muted-foreground">
                    Step {{ currentStep }} of 4
                </p>
                <div class="grid grid-cols-4 gap-2">
                    <div
                        v-for="step in 4"
                        :key="step"
                        class="h-1.5 rounded-full"
                        :class="
                            step <= currentStep ? 'bg-primary' : 'bg-muted'
                        "
                    />
                </div>
            </div>

            <section
                v-if="currentStep === 1"
                class="space-y-4 rounded-xl border border-border bg-card p-4"
            >
                <Label for="university-search">Select University</Label>
                <Input
                    id="university-search"
                    v-model="search"
                    placeholder="Search university..."
                />
                <div class="grid gap-3 md:grid-cols-2">
                    <button
                        v-for="university in filteredUniversities"
                        :key="university.id"
                        type="button"
                        class="rounded-lg border p-4 text-left hover:border-primary"
                        :class="
                            form.university_id === university.id
                                ? 'border-primary'
                                : 'border-border'
                        "
                        @click="selectUniversity(university.id)"
                    >
                        <p class="font-semibold">{{ university.name }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ university.location }}
                        </p>
                    </button>
                </div>
                <InputError :message="form.errors.university_id" />
                <InputError :message="localErrors.university_id" />
            </section>

            <section
                v-if="currentStep === 2"
                class="space-y-4 rounded-xl border border-border bg-card p-4"
            >
                <h2 class="font-semibold">Select Program</h2>
                <Input
                    v-model="courseSearch"
                    placeholder="Search program..."
                />
                <div class="grid gap-3 md:grid-cols-2">
                    <button
                        v-for="course in filteredCourses"
                        :key="course.id"
                        type="button"
                        class="rounded-lg border p-4 text-left hover:border-primary"
                        :class="
                            form.course_id === course.id
                                ? 'border-primary'
                                : 'border-border'
                        "
                        @click="selectCourse(course.id)"
                    >
                        <p class="font-semibold">{{ course.name }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ course.short_name }}
                        </p>
                    </button>
                </div>
                <InputError :message="form.errors.course_id" />
                <InputError :message="localErrors.course_id" />
            </section>

            <section
                v-if="currentStep === 3"
                class="space-y-4 rounded-xl border border-border bg-card p-4"
            >
                <h2 class="font-semibold">Select Cohort</h2>
                <div v-if="courseYears.length > 0" class="space-y-3">
                    <div class="grid gap-3 md:grid-cols-2">
                        <button
                            v-for="courseYear in courseYears"
                            :key="courseYear.id"
                            type="button"
                            class="rounded-lg border p-4 text-left hover:border-primary"
                            :class="
                                form.course_year_id === courseYear.id
                                    ? 'border-primary'
                                    : 'border-border'
                            "
                            @click="
                                form.course_year_id = courseYear.id;
                                localErrors.course_year_id = null;
                            "
                        >
                            <p class="font-semibold">
                                Class of {{ courseYear.year }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ courseYear.slug }}
                            </p>
                        </button>
                    </div>
                </div>
                <p
                    v-if="selectedCourseYearLabel"
                    class="text-sm text-muted-foreground"
                >
                    Selected: {{ selectedCourseYearLabel }}
                </p>
                <InputError :message="form.errors.course_year_id" />
                <InputError :message="localErrors.course_year_id" />
            </section>

            <section
                v-if="currentStep === 4"
                class="space-y-4 rounded-xl border border-border bg-card p-4"
            >
                <h2 class="font-semibold">Upload Profile Photo (Optional)</h2>
                <AvatarUpload v-model="form.avatar" />
            </section>

            <div class="flex items-center justify-between">
                <LoadingButton
                    type="button"
                    variant="outline"
                    :disabled="currentStep === 1"
                    :loading="form.processing"
                    loading-text="Please wait..."
                    @click="back"
                >
                    Back
                </LoadingButton>
                <LoadingButton
                    v-if="currentStep < 4"
                    type="button"
                    :loading="form.processing"
                    loading-text="Validating..."
                    @click="next"
                >
                    Next
                </LoadingButton>
                <LoadingButton
                    v-else
                    :loading="form.processing"
                    loading-text="Finishing..."
                    @click="submit"
                >
                    Finish
                </LoadingButton>
            </div>
        </div>
    </AppLayout>
</template>

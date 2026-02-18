<script setup lang="ts">
import AvatarUpload from '@/components/AvatarUpload.vue';
import InputError from '@/components/InputError.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import UsernameInput from '@/components/UsernameInput.vue';
import WordCountTextarea from '@/components/WordCountTextarea.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { Course, University } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps<{
    universities: University[];
    courses: Course[];
    selectedUniversityId: number | null;
    search: string;
}>();

const page = usePage();
const currentStep = ref(1);
const search = ref(props.search ?? '');
const courseSearch = ref('');
const usernameAvailable = ref<boolean | null>(null);
const localErrors = ref<Record<string, string | null>>({
    university_id: null,
    course_id: null,
    username: null,
    bio: null,
});

const form = useForm({
    university_id: props.selectedUniversityId,
    course_id: null as number | null,
    username: (page.props.auth.user?.username as string | undefined) ?? '',
    bio: '',
    profession: '',
    location: '',
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
        `${course.name} ${course.short_name} ${course.year}`
            .toLowerCase()
            .includes(term),
    );
});

const bioWordCount = computed(() => {
    const value = form.bio.trim();

    return value === '' ? 0 : value.split(/\s+/).length;
});

const normalizedUsername = computed(() => form.username.trim().toLowerCase());
const usernameRegex = /^[a-z0-9_]{3,30}$/;

watch(
    () => form.username,
    () => {
        localErrors.value.username = null;
    },
);

const selectUniversity = (universityId: number): void => {
    form.university_id = universityId;
    form.course_id = null;
    localErrors.value.university_id = null;
    localErrors.value.course_id = null;

    router.get(
        '/onboarding',
        { university_id: universityId },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['courses', 'selectedUniversityId'],
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
        if (!usernameRegex.test(normalizedUsername.value)) {
            localErrors.value.username =
                'Username must be 3-30 characters and contain only lowercase letters, numbers, or underscores.';
            return false;
        }

        if (usernameAvailable.value !== true) {
            localErrors.value.username =
                'Please choose an available username before continuing.';
            return false;
        }

        localErrors.value.username = null;
        return true;
    }

    if (currentStep.value === 5) {
        if (bioWordCount.value > 100) {
            localErrors.value.bio = 'Bio may not exceed 100 words.';
            return false;
        }

        localErrors.value.bio = null;
        return true;
    }

    return true;
};

const next = (): void => {
    if (!validateCurrentStep()) {
        return;
    }

    if (currentStep.value < 5) {
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
                    Step {{ currentStep }} of 5
                </p>
                <div class="grid grid-cols-5 gap-2">
                    <div
                        v-for="step in 5"
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
                <h2 class="font-semibold">Select Course</h2>
                <Input
                    v-model="courseSearch"
                    placeholder="Search course..."
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
                        @click="
                            form.course_id = course.id;
                            localErrors.course_id = null;
                        "
                    >
                        <p class="font-semibold">{{ course.name }}</p>
                        <p class="text-sm text-muted-foreground">
                            {{ course.short_name }} · {{ course.year }}
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
                <h2 class="font-semibold">Choose Username</h2>
                <UsernameInput
                    v-model="form.username"
                    @availability="usernameAvailable = $event"
                />
                <InputError :message="localErrors.username" />
            </section>

            <section
                v-if="currentStep === 4"
                class="space-y-4 rounded-xl border border-border bg-card p-4"
            >
                <h2 class="font-semibold">Upload Profile Photo (Optional)</h2>
                <AvatarUpload v-model="form.avatar" />
            </section>

            <section
                v-if="currentStep === 5"
                class="space-y-4 rounded-xl border border-border bg-card p-4"
            >
                <h2 class="font-semibold">Complete Profile</h2>
                <WordCountTextarea
                    v-model="form.bio"
                    name="bio"
                    placeholder="Tell your class about yourself..."
                />
                <InputError :message="localErrors.bio" />

                <div class="grid gap-4 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="profession">Profession</Label>
                        <Input
                            id="profession"
                            v-model="form.profession"
                            placeholder="e.g. Photographer"
                        />
                        <InputError :message="form.errors.profession" />
                    </div>
                    <div class="grid gap-2">
                        <Label for="location">Location</Label>
                        <Input
                            id="location"
                            v-model="form.location"
                            placeholder="e.g. Lagos, Nigeria"
                        />
                        <InputError :message="form.errors.location" />
                    </div>
                </div>
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
                    v-if="currentStep < 5"
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

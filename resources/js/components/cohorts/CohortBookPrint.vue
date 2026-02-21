<script setup lang="ts">
import { computed, ref } from 'vue';

type UniversityMedia = {
    original_url?: string;
    conversions?: Record<string, string>;
};

type UniversityDetails = {
    id?: number;
    name: string;
    media?: UniversityMedia[];
};

type CourseDetails = {
    id: number;
    name: string;
    short_name: string;
};

type CohortDetails = {
    id: number;
    year?: string | number | null;
    name?: string | null;
    slug?: string;
};

type StudentCard = {
    id: number;
    name: string;
    username: string;
    nickname?: string | null;
    quote?: string | null;
    profession?: string | null;
    location?: string | null;
    department?: string | null;
    media?: Array<{
        original_url?: string;
        conversions?: Record<string, string>;
    }>;
};

const props = defineProps<{
    university: UniversityDetails | null;
    course: CourseDetails;
    cohort: CohortDetails;
    students: StudentCard[];
    totalStudents: number;
}>();

const quotes = [
    'Dreams are earned one quiet, consistent day at a time.',
    'The work was hard, and that is exactly why it matters.',
    'The future belongs to those who finish what they start.',
    'Discipline writes the story that talent begins.',
    'Small progress, repeated daily, becomes legacy.',
    'Courage is showing up when no one is watching.',
    'Growth begins where comfort ends.',
    'Stay humble, stay curious, and keep building.',
    'Great journeys are made of ordinary days done well.',
    'Every challenge became a chapter in this success.',
    'Keep the faith, trust the process, finish strong.',
    'What once felt impossible is now your proof.',
    'Your effort today becomes someone else inspiration tomorrow.',
    'Excellence is a habit before it is a headline.',
    'The best stories are written by those who persist.',
    'We arrived with hope, and we leave with purpose.',
];

const studentsPerPage = 3;
const placeholderProfilePhoto = '/images/default-profile.svg';
const universityLogoFailed = ref(false);
const brokenStudentImageIds = ref<Set<number>>(new Set());

const cohortYear = computed(() => {
    const directYear = String(props.cohort.year ?? '').trim();

    if (/^\d{4}$/.test(directYear)) {
        return directYear;
    }

    const fallbackSource = [props.cohort.name, props.cohort.slug].find(
        (value): value is string => typeof value === 'string' && value.trim() !== '',
    );
    const parsedYear = fallbackSource?.match(/(19|20)\d{2}/)?.[0];

    if (typeof parsedYear === 'string') {
        return parsedYear;
    }

    return String(new Date().getFullYear());
});

const universityName = computed(() => props.university?.name ?? 'University');
const classLabel = computed(() => `${props.course.short_name.toUpperCase()} Class of ${cohortYear.value}`);

const universityInitials = computed(() =>
    universityName.value
        .split(/\s+/)
        .filter((segment) => segment.trim() !== '')
        .slice(0, 3)
        .map((segment) => segment[0]?.toUpperCase() ?? '')
        .join(''),
);

const universityLogoUrl = computed(() => {
    if (universityLogoFailed.value) {
        return null;
    }

    const media = props.university?.media?.[0];
    const url = media?.conversions?.full ?? media?.conversions?.thumb ?? media?.original_url ?? null;

    if (url === null || url.trim() === '') {
        return null;
    }

    return url;
});

const sortedStudents = computed(() =>
    [...props.students].sort((a, b) =>
        a.name.localeCompare(b.name, undefined, {
            sensitivity: 'base',
        }),
    ),
);

const studentPages = computed(() => {
    const chunks: StudentCard[][] = [];

    for (let index = 0; index < sortedStudents.value.length; index += studentsPerPage) {
        chunks.push(sortedStudents.value.slice(index, index + studentsPerPage));
    }

    return chunks;
});

const insidePageCount = computed(() => studentPages.value.length + 3);

const graduatesCount = computed(() => Math.max(props.totalStudents, sortedStudents.value.length));

const departmentsCount = computed<number | null>(() => {
    const values = sortedStudents.value
        .map((student) => student.department?.trim().toLowerCase() ?? '')
        .filter((value) => value !== '');

    const unique = new Set(values);

    return unique.size > 0 ? unique.size : null;
});

const countriesCount = computed<number | null>(() => {
    const values = sortedStudents.value
        .map((student) => {
            const location = student.location?.trim() ?? '';

            if (location === '') {
                return '';
            }

            const parts = location.split(',').map((part) => part.trim()).filter((part) => part !== '');

            return parts.length > 0 ? parts[parts.length - 1].toLowerCase() : '';
        })
        .filter((value) => value !== '');

    const unique = new Set(values);

    return unique.size > 0 ? unique.size : null;
});

const quoteIndex = computed(() => {
    const seed = `${props.course.id}-${props.cohort.id}`;
    let hash = 0;

    for (let index = 0; index < seed.length; index += 1) {
        hash = (hash * 31 + seed.charCodeAt(index)) >>> 0;
    }

    return hash % quotes.length;
});

const selectedQuote = computed(() => quotes[quoteIndex.value]);

const introStats = computed(() => {
    const stats: Array<{ label: string; value: string }> = [
        { label: 'graduates', value: String(graduatesCount.value) },
        { label: 'nights of grinding', value: '1095' },
        { label: 'lectures', value: '300+' },
    ];

    if (departmentsCount.value !== null) {
        stats.splice(1, 0, {
            label: 'departments',
            value: String(departmentsCount.value),
        });
    }

    if (countriesCount.value !== null) {
        stats.push({
            label: 'countries',
            value: String(countriesCount.value),
        });
    }

    return stats;
});

const studentPhotoUrl = (student: StudentCard): string | null => {
    if (brokenStudentImageIds.value.has(student.id)) {
        return null;
    }

    const url = student.media?.[0]?.conversions?.full
        ?? student.media?.[0]?.conversions?.thumb
        ?? student.media?.[0]?.original_url
        ?? null;

    if (url === null || url.trim() === '') {
        return null;
    }

    return url;
};

const onStudentImageError = (studentId: number): void => {
    if (brokenStudentImageIds.value.has(studentId)) {
        return;
    }

    brokenStudentImageIds.value = new Set([...brokenStudentImageIds.value, studentId]);
};

const sanitizeStudentQuote = (quote: string | null | undefined): string | null => {
    const cleanQuote = (quote ?? '').trim();

    if (cleanQuote === '') {
        return null;
    }

    const words = cleanQuote.split(/\s+/).filter((word) => word !== '');

    if (words.length > 8) {
        return words.slice(0, 8).join(' ');
    }

    return cleanQuote;
};
</script>

<template>
    <div class="cohort-print-root">
        <article class="print-page print-cover-page">
            <div class="print-cover-frame print-cover-frame-outer">
                <div class="print-cover-frame print-cover-frame-inner">
                    <div class="print-cover-logo-wrap">
                        <img
                            v-if="universityLogoUrl"
                            :src="universityLogoUrl"
                            :alt="universityName"
                            class="print-cover-logo"
                            loading="eager"
                            referrerpolicy="no-referrer"
                            @error="universityLogoFailed = true"
                        />
                        <div v-else class="print-cover-logo-fallback">
                            {{ universityInitials }}
                        </div>
                    </div>

                    <p class="print-cover-university">{{ universityName }}</p>
                    <p class="print-cover-course">{{ course.name }}</p>
                    <h1 class="print-cover-class">Class of {{ cohortYear }}</h1>
                    <p class="print-cover-kicker">DIGITAL YEARBOOK</p>
                </div>
            </div>
        </article>

        <article class="print-page print-intro-page">
            <p class="print-running-header">{{ classLabel }}</p>
            <div class="print-intro-layout">
                <div class="print-intro-context">
                    <p class="print-intro-university">{{ universityName }}</p>
                    <p class="print-intro-class">Class of {{ cohortYear }}</p>
                </div>

                <p class="print-intro-mark">&quot;</p>
                <p class="print-intro-quote">{{ selectedQuote }}</p>
                <p class="print-intro-signature">- THE CLASS OF {{ cohortYear }}</p>

                <div class="print-intro-stats">
                    <div v-for="stat in introStats" :key="stat.label" class="print-intro-stat">
                        <p class="print-intro-stat-value">{{ stat.value }}</p>
                        <p class="print-intro-stat-label">{{ stat.label }}</p>
                    </div>
                </div>
            </div>
            <p class="print-page-number">Page 1 of {{ insidePageCount }}</p>
        </article>

        <article class="print-page print-intro-page">
            <p class="print-running-header">{{ classLabel }}</p>
            <div class="print-intro-layout print-intro-right-layout">
                <p class="print-intro-heading-sm">MEET THE GRADUATES</p>
                <h2 class="print-intro-heading-lg">Our Stars</h2>
                <p class="print-intro-course-name">{{ course.name }}</p>
                <p class="print-intro-copy">
                    The following pages celebrate the brilliant minds and spirited souls
                    who make up the Class of {{ cohortYear }}.
                </p>
            </div>
            <p class="print-page-number">Page 2 of {{ insidePageCount }}</p>
        </article>

        <article
            v-for="(studentPage, pageIndex) in studentPages"
            :key="`students-page-${pageIndex}`"
            class="print-page print-students-page"
        >
            <p class="print-running-header">{{ classLabel }}</p>
            <div class="print-student-list">
                <div
                    v-for="student in studentPage"
                    :key="student.id"
                    class="print-student-card"
                >
                    <img
                        v-if="studentPhotoUrl(student)"
                        :src="studentPhotoUrl(student) ?? ''"
                        :alt="student.name"
                        class="print-student-photo"
                        loading="eager"
                        referrerpolicy="no-referrer"
                        @error="onStudentImageError(student.id)"
                    />
                    <img
                        v-else
                        :src="placeholderProfilePhoto"
                        :alt="`${student.name} profile placeholder`"
                        class="print-student-photo"
                        loading="eager"
                        referrerpolicy="no-referrer"
                    />

                    <div class="print-student-content">
                        <p class="print-student-name">{{ student.name }}</p>
                        <p
                            v-if="student.profession && student.profession.trim() !== ''"
                            class="print-student-profession"
                        >
                            {{ student.profession }}
                        </p>
                        <p
                            v-if="student.nickname && student.nickname.trim() !== ''"
                            class="print-student-nickname"
                        >
                            &quot;{{ student.nickname }}&quot;
                        </p>
                        <p
                            v-if="sanitizeStudentQuote(student.quote)"
                            class="print-student-quote"
                        >
                            &quot;{{ sanitizeStudentQuote(student.quote) }}&quot;
                        </p>
                    </div>
                </div>
            </div>
            <p class="print-page-number">Page {{ pageIndex + 3 }} of {{ insidePageCount }}</p>
        </article>

        <article class="print-page print-back-page">
            <div class="print-back-inner">
                <h2 class="print-back-title">The End</h2>
                <p class="print-back-subtitle">Made with love by JRonnie</p>
                <p class="print-back-email">jronnie@thetechtower.com</p>
                <p class="print-back-link">jronnie.thetechtower.com</p>
            </div>
            <p class="print-page-number">Page {{ insidePageCount }} of {{ insidePageCount }}</p>
        </article>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap');

.cohort-print-root {
    display: grid;
    gap: 1.2rem;
}

.print-page {
    position: relative;
    width: 100%;
    min-height: 277mm;
    padding: 16mm 14mm;
    background: #f7f4ee;
    color: #1f2937;
    box-shadow: 0 8px 26px rgba(15, 23, 42, 0.18);
    font-family: 'Cormorant Garamond', serif;
}

.print-cover-page,
.print-back-page {
    background: radial-gradient(circle at 20% 10%, #122752, #08132b 60%, #050b1a 100%);
    color: #e8cc89;
}

.print-running-header {
    text-align: center;
    font-family: 'Cinzel', serif;
    font-size: 0.72rem;
    letter-spacing: 0.2em;
    color: #5c6e96;
    text-transform: uppercase;
}

.print-page-number {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 6mm;
    text-align: center;
    font-family: 'Cinzel', serif;
    font-size: 0.52rem;
    letter-spacing: 0.08em;
    color: #556a96;
}

.print-cover-frame {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
}

.print-cover-frame-outer {
    border: 2px solid rgba(209, 171, 88, 0.7);
    padding: 5mm;
}

.print-cover-frame-inner {
    border: 1px solid rgba(214, 176, 93, 0.54);
    gap: 0.85rem;
}

.print-cover-logo-wrap {
    margin-top: 3mm;
}

.print-cover-logo,
.print-cover-logo-fallback {
    width: 24mm;
    height: 24mm;
    object-fit: cover;
    margin: 0 auto;
}

.print-cover-logo-fallback {
    display: grid;
    place-items: center;
    font-family: 'Cinzel', serif;
    letter-spacing: 0.12em;
}

.print-cover-university {
    color: #f5e3b4;
    font-size: 1.1rem;
    letter-spacing: 0.06em;
}

.print-cover-course {
    color: #f5e3b4;
    font-size: 0.95rem;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}

.print-cover-class {
    font-family: 'Cinzel', serif;
    font-size: 2.3rem;
    color: #f7f0da;
    line-height: 1.1;
}

.print-cover-kicker {
    font-family: 'Cinzel', serif;
    letter-spacing: 0.3em;
    font-size: 0.73rem;
}

.print-intro-layout {
    min-height: calc(100% - 28mm);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    gap: 0.8rem;
}

.print-intro-context {
    margin-bottom: 0.25rem;
}

.print-intro-university {
    color: #5c6e96;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-size: 0.76rem;
    font-family: 'Cinzel', serif;
}

.print-intro-class {
    color: #5c6e96;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-size: 0.72rem;
    font-family: 'Cinzel', serif;
}

.print-intro-mark {
    font-size: 1.5rem;
    color: #cfad64;
    line-height: 1;
}

.print-intro-quote {
    color: #5f6f8f;
    font-size: 1.7rem;
    line-height: 1.3;
    max-width: 128mm;
    font-style: italic;
}

.print-intro-signature {
    margin-top: 0.35rem;
    color: #5c6e96;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    font-size: 0.78rem;
    font-family: 'Cinzel', serif;
}

.print-intro-stats {
    margin-top: 0.8rem;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.8rem;
    width: 100%;
    max-width: 145mm;
}

.print-intro-stat {
    text-align: center;
}

.print-intro-stat-value {
    color: #a2782a;
    font-family: 'Cinzel', serif;
    font-size: 1.6rem;
    line-height: 1;
}

.print-intro-stat-label {
    margin-top: 0.2rem;
    color: #5c6e96;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-size: 0.66rem;
    font-family: 'Cinzel', serif;
}

.print-intro-right-layout {
    gap: 0.45rem;
}

.print-intro-heading-sm {
    color: #a2782a;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    font-size: 0.8rem;
    font-family: 'Cinzel', serif;
}

.print-intro-heading-lg {
    color: #a2782a;
    font-family: 'Cinzel', serif;
    font-size: 3rem;
    line-height: 1;
}

.print-intro-course-name {
    color: #5c6e96;
    font-size: 1.1rem;
    font-style: italic;
}

.print-intro-copy {
    margin-top: 0.5rem;
    color: #5f6f8f;
    font-size: 1.3rem;
    line-height: 1.4;
    max-width: 130mm;
    font-style: italic;
}

.print-students-page {
    display: flex;
    flex-direction: column;
}

.print-student-list {
    min-height: calc(100% - 27mm);
    display: grid;
    align-content: center;
    gap: 5mm;
    margin-top: 6mm;
}

.print-student-card {
    display: grid;
    grid-template-columns: 34mm 1fr;
    gap: 4mm;
    align-items: center;
}

.print-student-photo {
    width: 34mm;
    height: 42mm;
    object-fit: cover;
}

.print-student-name {
    color: #15223f;
    font-size: 1.4rem;
    font-weight: 600;
    line-height: 1.08;
    overflow-wrap: anywhere;
}

.print-student-profession {
    margin-top: 0.1rem;
    color: #6d7ea2;
    font-size: 0.92rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    font-family: 'Cinzel', serif;
}

.print-student-nickname {
    margin-top: 0.14rem;
    color: #a2782a;
    font-size: 1rem;
    font-style: italic;
}

.print-student-quote {
    margin-top: 0.24rem;
    color: #6d7ea2;
    font-size: 0.8rem;
    line-height: 1.2;
    font-style: italic;
}

.print-back-inner {
    min-height: calc(100% - 20mm);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    gap: 0.35rem;
}

.print-back-title {
    font-family: 'Cinzel', serif;
    font-size: 2.2rem;
    color: #f7f0da;
}

.print-back-subtitle {
    color: #f7f0da;
    font-size: 0.95rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-family: 'Cinzel', serif;
}

.print-back-email {
    color: rgba(248, 236, 205, 0.72);
    font-size: 0.78rem;
    letter-spacing: 0.08em;
}

.print-back-link {
    color: #e8cc89;
    font-size: 0.74rem;
    letter-spacing: 0.08em;
}

@media print {
    .cohort-print-root {
        gap: 0;
    }

    .print-page {
        width: 190mm;
        min-height: 277mm;
        margin: 0 auto;
        box-shadow: none;
        break-after: page;
        page-break-after: always;
    }

    .print-page:last-child {
        break-after: auto;
        page-break-after: auto;
    }
}
</style>

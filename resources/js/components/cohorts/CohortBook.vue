<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { PageFlip } from 'page-flip';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

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
    course?: {
        name?: string | null;
        short_name?: string | null;
    } | null;
    media?: Array<{
        original_url?: string;
        conversions?: Record<string, string>;
    }>;
};

type BookPageModel = {
    id: string;
    kind: 'cover' | 'intro-left' | 'intro-right' | 'students' | 'back';
    density?: 'hard' | 'soft';
    students?: StudentCard[];
    pageNumber: number | null;
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

const placeholderProfilePhoto = '/images/default-profile.svg';
const studentsPerPage = 3;

const stageRef = ref<HTMLElement | null>(null);
const bookRef = ref<HTMLElement | null>(null);
const hostKey = ref(0);
const pageFlip = ref<PageFlip | null>(null);
const isMobile = ref(false);
const isFlipReady = ref(false);
const isInitializing = ref(false);
const hasInitializedOnce = ref(false);
const isFlipAnimating = ref(false);
const pendingResizeUpdate = ref(false);
const pendingRebuild = ref(false);
const observedStageSize = ref({ width: 0, height: 0 });
const universityLogoFailed = ref(false);
const brokenStudentImageIds = ref<Set<number>>(new Set());

const resizeObserver = ref<ResizeObserver | null>(null);
let resizeTimeoutId: ReturnType<typeof setTimeout> | null = null;
let rebuildTimeoutId: ReturnType<typeof setTimeout> | null = null;

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

const coverDisplayName = computed(() => {
    const shortName = props.course.short_name?.trim();

    if (shortName !== '') {
        return shortName.toUpperCase();
    }

    return universityInitials.value;
});

const sortedStudents = computed(() =>
    [...props.students].sort((a, b) =>
        a.name.localeCompare(b.name, undefined, {
            sensitivity: 'base',
        }),
    ),
);

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

const hasCompleteStudentDataset = computed(() => props.totalStudents <= sortedStudents.value.length);

const graduatesCount = computed(() => Math.max(props.totalStudents, sortedStudents.value.length));

const departmentsCount = computed<number | null>(() => {
    if (!hasCompleteStudentDataset.value) {
        return null;
    }

    const values = sortedStudents.value
        .map((student) => student.department?.trim().toLowerCase() ?? '')
        .filter((value) => value !== '');

    const unique = new Set(values);

    return unique.size > 0 ? unique.size : null;
});

const countriesCount = computed<number | null>(() => {
    if (!hasCompleteStudentDataset.value) {
        return null;
    }

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

const studentPages = computed(() => {
    const chunks: StudentCard[][] = [];

    for (let index = 0; index < sortedStudents.value.length; index += studentsPerPage) {
        chunks.push(sortedStudents.value.slice(index, index + studentsPerPage));
    }

    return chunks;
});

const classLabel = computed(() => `${props.course.short_name.toUpperCase()} Class of ${cohortYear.value}`);
const insidePageCount = computed(() => studentPages.value.length + 3);

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

const bookShellStyle = computed<Record<string, string>>(() =>
    isMobile.value
        ? {
            width: 'min(370px, calc(100vw - 1rem))',
            height: 'min(560px, 78vh)',
        }
        : {
            width: 'min(920px, calc(100vw - 2rem))',
            height: 'min(680px, 82vh)',
        },
);

const bookPages = computed<BookPageModel[]>(() => {
    const pages: BookPageModel[] = [
        {
            id: 'cover-front',
            kind: 'cover',
            density: 'hard',
            pageNumber: null,
        },
        {
            id: 'intro-left',
            kind: 'intro-left',
            pageNumber: 1,
        },
        {
            id: 'intro-right',
            kind: 'intro-right',
            pageNumber: 2,
        },
    ];

    studentPages.value.forEach((students, index) => {
        pages.push({
            id: `students-${index}`,
            kind: 'students',
            students,
            pageNumber: index + 3,
        });
    });

    pages.push({
        id: 'cover-back',
        kind: 'back',
        density: 'hard',
        pageNumber: insidePageCount.value,
    });

    return pages;
});

const bookPagesSignature = computed(() => {
    return `${bookPages.value.map((page) => page.id).join('|')}::${sortedStudents.value
        .map((student) => `${student.id}-${student.name}-${student.nickname ?? ''}-${student.quote ?? ''}`)
        .join('|')}`;
});

const updateIsMobile = (): void => {
    isMobile.value = window.matchMedia('(max-width: 767px)').matches;
};

const syncStageSize = (): boolean => {
    if (stageRef.value === null) {
        return false;
    }

    const width = Math.round(stageRef.value.clientWidth);
    const height = Math.round(stageRef.value.clientHeight);
    const changed = width !== observedStageSize.value.width || height !== observedStageSize.value.height;

    if (!changed) {
        return false;
    }

    observedStageSize.value = { width, height };

    return true;
};

const waitForStableLayout = async (): Promise<void> => {
    await nextTick();

    await new Promise<void>((resolve) => {
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                resolve();
            });
        });
    });
};

const collectPageElements = (): HTMLElement[] => {
    if (bookRef.value === null) {
        return [];
    }

    return Array.from(bookRef.value.querySelectorAll<HTMLElement>('.page'));
};

const destroyFlip = (): void => {
    if (pageFlip.value === null) {
        return;
    }

    pageFlip.value.off('flip');
    pageFlip.value.off('init');
    pageFlip.value.off('update');
    pageFlip.value.off('changeOrientation');
    pageFlip.value.off('changeState');
    pageFlip.value.destroy();
    pageFlip.value = null;
    isFlipAnimating.value = false;
};

/**
 * Initialize only after Vue finished rendering and the layout settled.
 */
const initializeFlip = async (startPage = 0): Promise<void> => {
    if (isInitializing.value) {
        return;
    }

    isInitializing.value = true;
    isFlipReady.value = false;

    await waitForStableLayout();

    const currentBook = bookRef.value;
    if (currentBook === null) {
        isInitializing.value = false;

        return;
    }

    const safeStartPage = Math.max(0, Math.min(startPage, bookPages.value.length - 1));

    const instance = new PageFlip(currentBook, {
        width: 460,
        height: 660,
        size: 'stretch',
        minWidth: 220,
        maxWidth: 460,
        minHeight: 380,
        maxHeight: 660,
        showCover: true,
        mobileScrollSupport: true,
        usePortrait: true,
        flippingTime: 900,
        startZIndex: 10,
        maxShadowOpacity: 0.35,
        drawShadow: true,
        disableFlipByClick: false,
        startPage: safeStartPage,
    });

    instance.on('changeState', (event) => {
        const state = String(event.data);
        isFlipAnimating.value = state !== 'read';

        if (state !== 'read') {
            return;
        }

        if (pendingResizeUpdate.value) {
            pendingResizeUpdate.value = false;
            scheduleResizeUpdate();
        }

        if (pendingRebuild.value) {
            pendingRebuild.value = false;
            scheduleRebuild();
        }
    });

    instance.on('init', async () => {
        await waitForStableLayout();

        const activePage = instance.getCurrentPageIndex();
        if (activePage !== safeStartPage) {
            instance.turnToPage(safeStartPage);
        }

        syncStageSize();
        isFlipReady.value = true;
        isInitializing.value = false;
    });

    const pages = collectPageElements();

    pageFlip.value = instance;
    instance.loadFromHTML(pages);

    hasInitializedOnce.value = true;
};

/**
 * Rebuild only when the page set changes, preserving the user's current page.
 */
const rebuildFlipPreservingPage = async (): Promise<void> => {
    const preservedPageIndex = pageFlip.value?.getCurrentPageIndex() ?? 0;

    destroyFlip();
    hostKey.value += 1;

    await waitForStableLayout();

    await initializeFlip(preservedPageIndex);
};

const scheduleResizeUpdate = (): void => {
    if (isFlipAnimating.value) {
        pendingResizeUpdate.value = true;

        return;
    }

    if (resizeTimeoutId !== null) {
        clearTimeout(resizeTimeoutId);
    }

    resizeTimeoutId = setTimeout(async () => {
        if (pageFlip.value === null || isInitializing.value) {
            return;
        }

        await waitForStableLayout();

        pageFlip.value.update();
    }, 140);
};

const scheduleRebuild = (): void => {
    if (!hasInitializedOnce.value) {
        return;
    }

    if (isFlipAnimating.value) {
        pendingRebuild.value = true;

        return;
    }

    if (rebuildTimeoutId !== null) {
        clearTimeout(rebuildTimeoutId);
    }

    rebuildTimeoutId = setTimeout(async () => {
        await rebuildFlipPreservingPage();
    }, 90);
};

const onWindowResize = (): void => {
    updateIsMobile();

    if (syncStageSize()) {
        scheduleResizeUpdate();
    }
};

const onKeydown = (event: KeyboardEvent): void => {
    if (event.key === 'ArrowLeft') {
        event.preventDefault();
        pageFlip.value?.flipPrev('top');
    }

    if (event.key === 'ArrowRight') {
        event.preventDefault();
        pageFlip.value?.flipNext('top');
    }
};

onMounted(async () => {
    updateIsMobile();

    await initializeFlip(0);
    syncStageSize();

    if (typeof ResizeObserver !== 'undefined' && stageRef.value !== null) {
        resizeObserver.value = new ResizeObserver(() => {
            if (syncStageSize()) {
                scheduleResizeUpdate();
            }
        });

        resizeObserver.value.observe(stageRef.value);
    }

    window.addEventListener('resize', onWindowResize, { passive: true });
    window.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener('resize', onWindowResize);
    window.removeEventListener('keydown', onKeydown);

    if (resizeObserver.value !== null) {
        resizeObserver.value.disconnect();
        resizeObserver.value = null;
    }

    if (resizeTimeoutId !== null) {
        clearTimeout(resizeTimeoutId);
    }

    if (rebuildTimeoutId !== null) {
        clearTimeout(rebuildTimeoutId);
    }

    destroyFlip();
});

watch(
    bookPagesSignature,
    () => {
        scheduleRebuild();
    },
    { flush: 'post' },
);
</script>

<template>
    <section class="mx-auto w-full max-w-[980px] px-1 sm:px-2">
        <div ref="stageRef" class="book-stage mx-auto" :style="bookShellStyle">
            <div class="book-spine-shadow" aria-hidden="true" />

            <div v-if="!isFlipReady" class="book-skeleton" aria-hidden="true" />

            <div
                :key="hostKey"
                ref="bookRef"
                class="cohort-book-host"
                :class="{ 'book-host-hidden': !isFlipReady }"
            >
                <div
                    v-for="page in bookPages"
                    :key="page.id"
                    :data-density="page.density ?? 'soft'"
                    :class="['page', 'book-page', `page-${page.kind}`]"
                >
                    <template v-if="page.kind === 'cover'">
                        <div class="cover-frame cover-frame-outer">
                            <div class="cover-frame cover-frame-inner cover-centered">
                                <div class="cover-logo-wrap">
                                    <img
                                        v-if="universityLogoUrl"
                                        :src="universityLogoUrl"
                                        :alt="universityName"
                                        class="cover-logo"
                                        loading="lazy"
                                        referrerpolicy="no-referrer"
                                        @error="universityLogoFailed = true"
                                    />
                                    <div v-else class="cover-logo-fallback">
                                        {{ universityInitials }}
                                    </div>
                                    <p class="cover-university-name">{{ universityName }}</p>
                                </div>

                                <h3 class="cover-title">{{ coverDisplayName }}</h3>
                                <p class="cover-subtitle">Class of {{ cohortYear }}</p>
                                <p class="cover-kicker">DIGITAL YEARBOOK</p>
                                <p class="cover-quote">{{ selectedQuote }}</p>
                            </div>
                        </div>
                    </template>

                    <template v-else-if="page.kind === 'intro-left'">
                        <p class="book-running-header">{{ classLabel }}</p>

                        <div class="intro-layout">
                            <div class="intro-context">
                                <p class="intro-context-university">{{ universityName }}</p>
                                <p class="intro-context-class">Class of {{ cohortYear }}</p>
                            </div>
                            <div class="intro-quote-wrap">
                                <p class="intro-quote-mark">"</p>
                                <p class="intro-quote">{{ selectedQuote }}</p>
                                <p class="intro-divider" />
                                <p class="intro-signature">- THE CLASS OF {{ cohortYear }}</p>
                            </div>

                            <div class="intro-stats">
                                <div v-for="stat in introStats" :key="stat.label" class="intro-stat">
                                    <p class="intro-stat-value">{{ stat.value }}</p>
                                    <p class="intro-stat-label">{{ stat.label }}</p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template v-else-if="page.kind === 'intro-right'">
                        <p class="book-running-header">{{ classLabel }}</p>

                        <div class="intro-layout intro-right-layout">
                            <p class="intro-heading-sm">MEET THE GRADUATES</p>
                            <h3 class="intro-heading-lg">Our Stars</h3>
                            <p class="intro-course-name">{{ course.name }}</p>
                            <p class="intro-divider" />
                            <p class="intro-copy">
                                The following pages celebrate the brilliant minds and spirited souls
                                who make up the Class of {{ cohortYear }}.
                            </p>
                        </div>
                    </template>

                    <template v-else-if="page.kind === 'students'">
                        <p class="book-running-header">{{ classLabel }}</p>

                        <div class="student-list">
                            <Link
                                v-for="student in page.students"
                                :key="student.id"
                                :href="`/@${student.username}`"
                                class="student-entry"
                            >
                                <img
                                    v-if="studentPhotoUrl(student)"
                                    :src="studentPhotoUrl(student) ?? ''"
                                    :alt="student.name"
                                    class="student-photo"
                                    loading="lazy"
                                    referrerpolicy="no-referrer"
                                    @error="onStudentImageError(student.id)"
                                />
                                <img
                                    v-else
                                    :src="placeholderProfilePhoto"
                                    :alt="`${student.name} profile placeholder`"
                                    class="student-photo"
                                    loading="lazy"
                                    referrerpolicy="no-referrer"
                                />

                                <div class="student-content">
                                    <p class="student-name">{{ student.name }}</p>
                                    <p
                                        v-if="student.nickname && student.nickname.trim() !== ''"
                                        class="student-nickname"
                                    >
                                        "{{ student.nickname }}"
                                    </p>
                                    <p
                                        v-if="sanitizeStudentQuote(student.quote)"
                                        class="student-quote"
                                    >
                                        "{{ sanitizeStudentQuote(student.quote) }}"
                                    </p>
                                </div>
                            </Link>
                        </div>
                    </template>

                    <template v-else>
                        <div class="cover-frame cover-frame-outer">
                            <div class="cover-frame cover-frame-inner cover-centered">
                                <h3 class="cover-title">The End</h3>
                                <p class="back-signoff">Made with love by JRonnie</p>
                                <p class="back-email">jronnie@thetechtower.com</p>
                                <a
                                    href="https://jronnie.thetechtower.com"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="back-link"
                                >
                                    jronnie.thetechtower.com
                                </a>
                            </div>
                        </div>
                    </template>

                    <p
                        v-if="page.pageNumber !== null"
                        class="book-page-number"
                    >
                        Page {{ page.pageNumber }} of {{ insidePageCount }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap');

.book-stage {
    position: relative;
    isolation: isolate;
}

.book-spine-shadow {
    pointer-events: none;
}

.book-spine-shadow {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 50%;
    width: 18px;
    transform: translateX(-50%);
    z-index: 25;
    background: linear-gradient(
        to right,
        rgba(0, 0, 0, 0.17) 0%,
        rgba(0, 0, 0, 0.03) 26%,
        rgba(255, 255, 255, 0.3) 50%,
        rgba(0, 0, 0, 0.03) 74%,
        rgba(0, 0, 0, 0.17) 100%
    );
}

.book-skeleton {
    position: absolute;
    inset: 0;
    z-index: 30;
    border-radius: 2px;
    background: linear-gradient(120deg, #f3efe7 0%, #fff 50%, #f3efe7 100%);
    background-size: 220% 100%;
    animation: shimmer 1.2s linear infinite;
}

.cohort-book-host {
    position: relative;
    z-index: 5;
    width: 100%;
    height: 100%;
}

.book-host-hidden {
    opacity: 0;
}

:deep(.stf__parent),
:deep(.stf__wrapper),
:deep(.stf__block) {
    overflow: hidden;
}

:deep(.stf__parent) {
    background: transparent !important;
    position: relative;
}

:deep(.stf__parent)::before,
:deep(.stf__parent)::after {
    content: '';
    position: absolute;
    top: 14px;
    bottom: 14px;
    width: 14px;
    z-index: 2;
    pointer-events: none;
    background: repeating-linear-gradient(
        to bottom,
        #eee9e1 0px,
        #eee9e1 2px,
        #d8d1c7 2px,
        #d8d1c7 3px
    );
    box-shadow: inset 0 0 0 1px rgba(117, 104, 84, 0.16);
    opacity: 0.95;
}

:deep(.stf__parent)::before {
    left: -10px;
    transform: rotate(-1.2deg) skewY(-3deg);
    border-radius: 2px 0 0 2px;
}

:deep(.stf__parent)::after {
    right: -10px;
    transform: rotate(1.2deg) skewY(3deg);
    border-radius: 0 2px 2px 0;
}

.page {
    transition: none !important;
    backface-visibility: hidden;
    -webkit-backface-visibility: hidden;
}

.book-page {
    position: relative;
    width: 100%;
    height: 100%;
    padding: 2.8rem 1.9rem 2.6rem;
    background: #f7f4ee;
    color: #1f2937;
    box-shadow: inset 0 0 0 1px rgba(182, 145, 84, 0.22);
    overflow: hidden;
    font-family: 'Cormorant Garamond', serif;
}

.book-running-header {
    position: absolute;
    top: 0.8rem;
    left: 0;
    width: 100%;
    text-align: center;
    font-size: 0.72rem;
    letter-spacing: 0.22em;
    color: #5c6e96;
    font-family: 'Cinzel', serif;
    text-transform: uppercase;
}

.book-page-number {
    position: absolute;
    bottom: 0.55rem;
    left: 0;
    width: 100%;
    text-align: center;
    font-size: 0.52rem;
    letter-spacing: 0.05em;
    color: #556a96;
    font-family: 'Cinzel', serif;
}

.page-cover,
.page-back {
    background: radial-gradient(circle at 20% 10%, #122752, #08132b 60%, #050b1a 100%);
    color: #e8cc89;
}

.cover-frame {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
}

.cover-centered {
    justify-content: center;
    gap: 1rem;
}

.cover-frame-outer {
    border: 2px solid rgba(209, 171, 88, 0.7);
    padding: 0.48rem;
}

.cover-frame-inner {
    border: 1px solid rgba(214, 176, 93, 0.54);
    padding: 1.05rem 1rem;
}

.cover-logo-wrap {
    text-align: center;
}

.cover-logo,
.cover-logo-fallback {
    width: 4rem;
    height: 4rem;
    object-fit: cover;
    margin: 0 auto;
}

.cover-logo-fallback {
    display: grid;
    place-items: center;
    font-size: 1rem;
    letter-spacing: 0.14em;
    font-weight: 600;
}

.cover-university-name {
    margin-top: 0.45rem;
    color: #f5e3b4;
    font-size: 0.94rem;
    letter-spacing: 0.08em;
}

.cover-kicker {
    letter-spacing: 0.34em;
    font-size: 0.68rem;
    font-family: 'Cinzel', serif;
}

.cover-title {
    font-family: 'Cinzel', serif;
    font-size: clamp(2.45rem, 4.7vw, 3.7rem);
    line-height: 1.1;
    font-weight: 700;
}

.cover-subtitle {
    color: #f7f0da;
    font-family: 'Cormorant Garamond', serif;
    font-size: clamp(1.85rem, 2.8vw, 2.3rem);
    font-weight: 600;
}

.cover-quote {
    color: rgba(248, 236, 205, 0.72);
    font-size: 1rem;
    font-style: italic;
}

.back-signoff {
    color: #f7f0da;
    font-size: 0.95rem;
    letter-spacing: 0.08em;
    font-family: 'Cinzel', serif;
    text-transform: uppercase;
}

.back-email {
    color: rgba(248, 236, 205, 0.72);
    font-size: 0.78rem;
    letter-spacing: 0.08em;
}

.intro-layout {
    min-height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.intro-context {
    text-align: center;
    margin-bottom: 0.6rem;
}

.intro-context-university {
    color: #5d719c;
    font-size: 0.95rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-family: 'Cinzel', serif;
}

.intro-context-class {
    margin-top: 0.1rem;
    color: #7384aa;
    font-size: 0.9rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    font-family: 'Cinzel', serif;
}

.intro-quote-wrap {
    text-align: center;
}

.intro-quote-mark {
    color: rgba(172, 133, 61, 0.6);
    font-size: 1.95rem;
}

.intro-quote {
    margin-top: 0.3rem;
    font-size: clamp(1.55rem, 2.15vw, 2rem);
    font-style: italic;
    color: #4f6189;
    line-height: 1.45;
}

.intro-divider {
    margin: 1.2rem auto 0;
    width: 4.3rem;
    border-top: 1px solid rgba(172, 133, 61, 0.55);
}

.intro-signature {
    margin-top: 1rem;
    font-size: 0.98rem;
    letter-spacing: 0.16em;
    color: #5d719c;
    font-family: 'Cinzel', serif;
}

.intro-stats {
    margin-top: 2rem;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 0.9rem;
}

.intro-stat {
    text-align: center;
}

.intro-stat-value {
    color: #a2782a;
    font-size: 2rem;
    font-weight: 600;
    line-height: 1;
}

.intro-stat-label {
    margin-top: 0.2rem;
    color: #5a6f98;
    font-size: 0.72rem;
    letter-spacing: 0.14em;
    text-transform: uppercase;
    font-family: 'Cinzel', serif;
}

.intro-right-layout {
    align-items: center;
    text-align: center;
}

.intro-heading-sm {
    color: #a2782a;
    letter-spacing: 0.34em;
    font-size: 0.8rem;
    font-family: 'Cinzel', serif;
}

.intro-heading-lg {
    margin-top: 0.72rem;
    font-size: clamp(3rem, 4.2vw, 4rem);
    font-weight: 600;
    color: #9c7426;
    line-height: 1;
}

.intro-course-name {
    margin-top: 0.55rem;
    font-size: 1.06rem;
    color: #5f739e;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    font-family: 'Cinzel', serif;
}

.intro-copy {
    margin: 1.3rem auto 0;
    max-width: 21rem;
    color: #4b628d;
    font-size: 1.18rem;
    line-height: 1.45;
    font-style: italic;
}

.student-list {
    height: 100%;
    display: grid;
    gap: 1rem;
    align-content: center;
}

.student-entry {
    display: grid;
    grid-template-columns: 108px minmax(0, 1fr);
    gap: 1rem;
    padding: 0.15rem;
}

.student-photo {
    width: 108px;
    height: 132px;
    object-fit: cover;
    border-radius: 0.15rem;
}

.student-content {
    min-width: 0;
    align-self: center;
}

.student-name {
    color: #15223f;
    font-size: 1.38rem;
    font-weight: 600;
    line-height: 1.08;
    overflow-wrap: anywhere;
}

.student-nickname {
    margin-top: 0.14rem;
    color: #a2782a;
    font-size: 0.92rem;
    font-style: italic;
}

.student-quote {
    margin-top: 0.24rem;
    color: #6d7ea2;
    font-size: 0.72rem;
    font-style: italic;
    line-height: 1.2;
}

.back-link {
    color: #e9cc89;
    font-size: 0.74rem;
    letter-spacing: 0.08em;
    text-decoration: underline;
    text-underline-offset: 2px;
}

@keyframes shimmer {
    0% {
        background-position: 200% 0;
    }

    100% {
        background-position: -200% 0;
    }
}

@media (max-width: 767px) {
    .book-spine-shadow {
        display: none;
    }

    :deep(.stf__parent)::before,
    :deep(.stf__parent)::after {
        content: none;
    }

    .book-page {
        padding: 2.2rem 0.95rem 2rem;
    }

    .cover-frame-inner {
        padding: 0.85rem 0.7rem;
    }

    .cover-title {
        font-size: 2.22rem;
    }

    .cover-subtitle {
        font-size: 1.74rem;
    }

    .intro-quote {
        font-size: 1.28rem;
    }

    .intro-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .intro-heading-lg {
        font-size: 2.4rem;
    }

    .intro-copy {
        font-size: 1.03rem;
    }

    .student-entry {
        grid-template-columns: 78px minmax(0, 1fr);
        gap: 0.65rem;
    }

    .student-photo {
        width: 78px;
        height: 102px;
    }

    .student-name {
        font-size: 1.02rem;
    }

    .student-nickname,
    .student-quote {
        font-size: 0.66rem;
    }
}
</style>

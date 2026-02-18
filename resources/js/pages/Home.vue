<script setup lang="ts">
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';

type GalleryPhoto = {
    thumb: string;
    full: string;
};

const props = defineProps<{
    galleryPhotos: GalleryPhoto[];
}>();

const rowPatterns = [
    [
        'lg:col-span-2 lg:aspect-[4/5]',
        'lg:col-span-1 lg:aspect-[5/6]',
        'lg:col-span-2 lg:aspect-[3/4]',
        'lg:col-span-2 lg:aspect-[3/4]',
        'lg:col-span-1 lg:aspect-[5/6]',
        'lg:col-span-2 lg:aspect-[4/5]',
        'lg:col-span-2 lg:aspect-[4/5]',
    ],
    [
        'lg:col-span-2 lg:aspect-[3/4]',
        'lg:col-span-2 lg:aspect-[4/5]',
        'lg:col-span-1 lg:aspect-[5/6]',
        'lg:col-span-1 lg:aspect-[5/6]',
        'lg:col-span-2 lg:aspect-[3/4]',
        'lg:col-span-2 lg:aspect-[4/5]',
        'lg:col-span-2 lg:aspect-[4/5]',
    ],
    [
        'lg:col-span-2 lg:aspect-[4/5]',
        'lg:col-span-1 lg:aspect-[5/6]',
        'lg:col-span-2 lg:aspect-[3/4]',
        'lg:col-span-1 lg:aspect-[5/6]',
        'lg:col-span-2 lg:aspect-[4/5]',
        'lg:col-span-2 lg:aspect-[3/4]',
        'lg:col-span-2 lg:aspect-[4/5]',
    ],
] as const;

const limitedGalleryPhotos = computed(() => props.galleryPhotos.slice(0, 21));

const galleryRows = computed(() => {
    const rows: Array<GalleryPhoto[]> = [];

    for (let i = 0; i < limitedGalleryPhotos.value.length; i += 7) {
        rows.push(limitedGalleryPhotos.value.slice(i, i + 7));
    }

    return rows.slice(0, 3);
});

const tileClass = (rowIndex: number, tileIndex: number): string => {
    const patternClass =
        rowPatterns[rowIndex]?.[tileIndex] ?? 'lg:col-span-2 lg:aspect-[4/5]';

    return `col-span-1 aspect-square lg:aspect-auto ${patternClass}`;
};
</script>

<template>
    <Head :title="`${$page.props.appName} - Lets keep it here`" />

    <PublicLayout>
        <section class="py-14 text-center">
            <p
                class="text-xs tracking-[0.22em] text-muted-foreground uppercase"
            >
                Let's keep it here
            </p>

            <h1 class="mt-4 text-4xl font-semibold tracking-wide md:text-6xl">
                {{ $page.props.appName }}
            </h1>
            <p
                class="mx-auto mt-5 max-w-3xl text-sm leading-7 text-muted-foreground sm:text-base"
            >
                A class memoir platform where students preserve graduation
                photos, profiles, and stories with their course community in one
                archive.
            </p>
        </section>

        <section class="pb-14">
            <div class="space-y-3">
                <div
                    v-for="(row, rowIndex) in galleryRows"
                    :key="`gallery-row-${rowIndex}`"
                    class="grid grid-cols-7 gap-1 sm:gap-2 lg:grid-cols-12 lg:gap-3"
                >
                    <article
                        v-for="(photo, tileIndex) in row"
                        :key="`gallery-photo-${rowIndex}-${tileIndex}`"
                        :class="tileClass(rowIndex, tileIndex)"
                        class="home-photo-tile overflow-hidden border border-border bg-card"
                    >
                        <img
                            :src="photo.full || photo.thumb"
                            alt="Featured gallery photo"
                            class="h-full w-full object-cover transition-transform duration-500 hover:scale-[1.05]"
                            loading="lazy"
                        />
                    </article>
                </div>
            </div>

            <div
                v-if="limitedGalleryPhotos.length === 0"
                class="rounded-xl border border-dashed border-border p-8 text-center text-sm text-muted-foreground"
            >
                Photos will appear here once users publish their memories.
            </div>
        </section>

        <section class="grid gap-4 pb-12 md:grid-cols-3">
            <article class="rounded-xl border border-border bg-card p-4">
                <h3 class="font-semibold">1. Sign Up</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Create your account and verify your email.
                </p>
            </article>
            <article class="rounded-xl border border-border bg-card p-4">
                <h3 class="font-semibold">2. Join Your Course</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Pick your university and graduation class.
                </p>
            </article>
            <article class="rounded-xl border border-border bg-card p-4">
                <h3 class="font-semibold">3. Archive Forever</h3>
                <p class="mt-2 text-sm text-muted-foreground">
                    Share your profile and memories with your class.
                </p>
            </article>
        </section>
    </PublicLayout>
</template>

<style scoped>
.home-photo-tile {
    position: relative;
    isolation: isolate;
    animation: floatTile 7s ease-in-out infinite;
    transform-origin: center;
    overflow: hidden;
}

.home-photo-tile:nth-child(2n) {
    animation-duration: 8.5s;
}

.home-photo-tile:nth-child(3n) {
    animation-duration: 9.5s;
    animation-delay: 0.6s;
}

.home-photo-tile::before {
    content: '';
    position: absolute;
    inset: 0;
    z-index: 1;
    background: linear-gradient(
        120deg,
        rgb(255 255 255 / 0) 0%,
        rgb(255 255 255 / 22%) 44%,
        rgb(255 255 255 / 0) 68%
    );
    transform: translateX(-130%);
    animation: slotSweep 8.2s ease-in-out infinite;
}

.home-photo-tile::after {
    content: '';
    position: absolute;
    inset: 0;
    z-index: 1;
    background: rgb(0 0 0 / 14%);
    opacity: 0.08;
    animation: slotShade 6.5s ease-in-out infinite;
}

.home-photo-tile:nth-child(2n)::before {
    animation-delay: 0.7s;
}

.home-photo-tile:nth-child(3n)::before {
    animation-delay: 1.3s;
}

.home-photo-tile:nth-child(2n)::after {
    animation-delay: 0.8s;
}

.home-photo-tile:nth-child(3n)::after {
    animation-delay: 1.4s;
}

@keyframes floatTile {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}

@keyframes slotShade {
    0%,
    100% {
        opacity: 0.08;
    }
    18% {
        opacity: 0.25;
    }
    45% {
        opacity: 0.06;
    }
    68% {
        opacity: 0.32;
    }
}

@keyframes slotSweep {
    0%,
    56%,
    100% {
        transform: translateX(-130%);
        opacity: 0;
    }
    64% {
        opacity: 0.9;
    }
    80% {
        transform: translateX(130%);
        opacity: 0;
    }
}
</style>

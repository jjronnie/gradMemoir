<script setup lang="ts">
import PublicLayout from "@/layouts/PublicLayout.vue";
import { Head } from "@inertiajs/vue3";
import { computed } from "vue";

type GalleryPhoto = {
  thumb: string;
  full: string;
};

const props = defineProps<{
  galleryPhotos: GalleryPhoto[];
}>();

const rowPatterns = [
  [
    "lg:col-span-2 lg:aspect-[4/5]",
    "lg:col-span-1 lg:aspect-[5/6]",
    "lg:col-span-2 lg:aspect-[3/4]",
    "lg:col-span-2 lg:aspect-[3/4]",
    "lg:col-span-1 lg:aspect-[5/6]",
    "lg:col-span-2 lg:aspect-[4/5]",
    "lg:col-span-2 lg:aspect-[4/5]",
  ],
  [
    "lg:col-span-2 lg:aspect-[3/4]",
    "lg:col-span-2 lg:aspect-[4/5]",
    "lg:col-span-1 lg:aspect-[5/6]",
    "lg:col-span-1 lg:aspect-[5/6]",
    "lg:col-span-2 lg:aspect-[3/4]",
    "lg:col-span-2 lg:aspect-[4/5]",
    "lg:col-span-2 lg:aspect-[4/5]",
  ],
  [
    "lg:col-span-2 lg:aspect-[4/5]",
    "lg:col-span-1 lg:aspect-[5/6]",
    "lg:col-span-2 lg:aspect-[3/4]",
    "lg:col-span-1 lg:aspect-[5/6]",
    "lg:col-span-2 lg:aspect-[4/5]",
    "lg:col-span-2 lg:aspect-[3/4]",
    "lg:col-span-2 lg:aspect-[4/5]",
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
    rowPatterns[rowIndex]?.[tileIndex] ?? "lg:col-span-2 lg:aspect-[4/5]";

  return `col-span-6 sm:col-span-4 md:col-span-3 aspect-[3/4] ${patternClass}`;
};
</script>

<template>
  <Head :title="`${$page.props.appName} - Lets keep it here`" />

  <PublicLayout>
    <section class="py-14 text-center">
      <p class="text-xs tracking-[0.22em] text-muted-foreground uppercase">
        Let's keep it here
      </p>

      <h1 class="mt-4 text-4xl font-semibold tracking-wide md:text-6xl">
        {{ $page.props.appName }}
      </h1>
      <p
        class="mx-auto mt-5 max-w-3xl text-sm leading-7 text-muted-foreground sm:text-base"
      >
        A class memoir platform where students preserve graduation photos, profiles, and
        stories with their course community in one archive.
      </p>
    </section>

    <section class="pb-14">
      <div class="space-y-3">
        <div
          v-for="(row, rowIndex) in galleryRows"
          :key="`gallery-row-${rowIndex}`"
          class="grid grid-cols-6 gap-3 sm:grid-cols-12"
        >
          <article
            v-for="(photo, tileIndex) in row"
            :key="`gallery-photo-${rowIndex}-${tileIndex}`"
            :class="tileClass(rowIndex, tileIndex)"
            class="overflow-hidden border border-border bg-card"
          >
            <img
              :src="photo.full || photo.thumb"
              alt="Featured gallery photo"
              class="h-full w-full object-cover"
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

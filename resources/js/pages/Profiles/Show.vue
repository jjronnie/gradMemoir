<script setup lang="ts">
import PhotoCarousel from '@/components/PhotoCarousel.vue';
import ShareButton from '@/components/ShareButton.vue';
import VerifiedBadgeIcon from '@/components/VerifiedBadgeIcon.vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

type MediaItem = {
    original_url?: string;
    conversions?: Record<string, string>;
};

type PostItem = {
    id: number;
    body: string | null;
    published_at: string | null;
    created_at: string | null;
    media?: MediaItem[];
};

type Profile = {
    id: number;
    name: string;
    username: string;
    is_verified?: boolean;
    profession?: string | null;
    location?: string | null;
    bio?: string | null;
    phone?: string | null;
    instagram_username?: string | null;
    website?: string | null;
    tiktok_username?: string | null;
    x_username?: string | null;
    facebook_username?: string | null;
    email_public?: string | null;
    created_at?: string | null;
    media?: MediaItem[];
    university?: {
        name?: string | null;
        slug?: string | null;
        media?: MediaItem[];
    } | null;
    course?: {
        name?: string | null;
        short_name?: string | null;
        year?: string | null;
        slug?: string | null;
    } | null;
};

const props = defineProps<{
    profile: Profile;
    posts: {
        data: PostItem[];
        next_page_url: string | null;
        prev_page_url: string | null;
    };
}>();

type SocialLink = {
    key: string;
    href: string;
    iconClass: string;
    label: string;
};

type DetailRowKey = 'university' | 'course' | 'location' | 'joined' | 'bio';

const isCarouselOpen = ref(false);
const isProfileImageOpen = ref(false);
const selectedPhotos = ref<
    Array<{
        id: string;
        thumb: string;
        full: string;
        description: string | null;
        postedDiff: string | null;
        postedAt: string | null;
        downloadName: string;
    }>
>([]);
const selectedPhotoIndex = ref(0);
const currentUrl = ref('');

const profileImage = computed(() => {
    return (
        props.profile.media?.[0]?.conversions?.full ??
        props.profile.media?.[0]?.original_url ??
        null
    );
});

const profileInitial = computed(
    () => props.profile.name.trim().charAt(0).toUpperCase() || 'U',
);

const courseLabel = computed(() => {
    if (!props.profile.course?.short_name || !props.profile.course?.year) {
        return null;
    }

    return `${props.profile.course.short_name.toUpperCase()} Class of ${props.profile.course.year}`;
});

const hasUniversity = computed(() =>
    Boolean(props.profile.university?.name && props.profile.university?.slug),
);
const hasCourse = computed(() =>
    Boolean(props.profile.course?.slug && courseLabel.value),
);
const hasLocation = computed(() => Boolean(props.profile.location));
const hasJoined = computed(() => Boolean(joinedLabel.value));
const hasBio = computed(() => Boolean(props.profile.bio));

const detailRows = computed<DetailRowKey[]>(() => {
    const rows: DetailRowKey[] = [];

    if (hasUniversity.value) {
        rows.push('university');
    }

    if (hasCourse.value) {
        rows.push('course');
    }

    if (hasLocation.value) {
        rows.push('location');
    }

    if (hasJoined.value) {
        rows.push('joined');
    }

    if (hasBio.value) {
        rows.push('bio');
    }

    return rows;
});

const socialLinks = computed<SocialLink[]>(() => {
    const links: SocialLink[] = [];

    if (props.profile.phone) {
        links.push({
            key: 'whatsapp',
            href: `https://wa.me/${props.profile.phone}`,
            iconClass: 'fa-brands fa-whatsapp',
            label: 'WhatsApp',
        });
    }

    if (props.profile.instagram_username) {
        links.push({
            key: 'instagram',
            href: `https://instagram.com/${props.profile.instagram_username}`,
            iconClass: 'fa-brands fa-instagram',
            label: 'Instagram',
        });
    }

    if (props.profile.tiktok_username) {
        links.push({
            key: 'tiktok',
            href: `https://tiktok.com/@${props.profile.tiktok_username}`,
            iconClass: 'fa-brands fa-tiktok',
            label: 'TikTok',
        });
    }

    if (props.profile.x_username) {
        links.push({
            key: 'x',
            href: `https://x.com/${props.profile.x_username.replace(/^@/, '')}`,
            iconClass: 'fa-brands fa-x-twitter',
            label: 'X',
        });
    }

    if (props.profile.facebook_username) {
        links.push({
            key: 'facebook',
            href: `https://facebook.com/${props.profile.facebook_username}`,
            iconClass: 'fa-brands fa-facebook',
            label: 'Facebook',
        });
    }

    if (props.profile.email_public) {
        links.push({
            key: 'email',
            href: `mailto:${props.profile.email_public}`,
            iconClass: 'fa-solid fa-envelope',
            label: 'Email',
        });
    }

    if (props.profile.website) {
        links.push({
            key: 'website',
            href: `https://${props.profile.website}`,
            iconClass: 'fa-solid fa-globe',
            label: 'Website',
        });
    }

    return links;
});

const joinedLabel = computed(() => {
    if (!props.profile.created_at) {
        return null;
    }

    const joinedDate = new Date(props.profile.created_at);

    if (Number.isNaN(joinedDate.getTime())) {
        return null;
    }

    return joinedDate.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'long',
    });
});

const humanDiff = (dateString: string | null): string | null => {
    if (dateString === null || dateString === '') {
        return null;
    }

    const date = new Date(dateString);

    if (Number.isNaN(date.getTime())) {
        return null;
    }

    const now = Date.now();
    const seconds = Math.round((date.getTime() - now) / 1000);
    const formatter = new Intl.RelativeTimeFormat(undefined, {
        numeric: 'auto',
    });

    const ranges: Array<{
        unit: Intl.RelativeTimeFormatUnit;
        seconds: number;
    }> = [
        { unit: 'year', seconds: 31536000 },
        { unit: 'month', seconds: 2592000 },
        { unit: 'week', seconds: 604800 },
        { unit: 'day', seconds: 86400 },
        { unit: 'hour', seconds: 3600 },
        { unit: 'minute', seconds: 60 },
    ];

    for (const range of ranges) {
        if (Math.abs(seconds) >= range.seconds) {
            return formatter.format(
                Math.round(seconds / range.seconds),
                range.unit,
            );
        }
    }

    return formatter.format(seconds, 'second');
};

const profilePhotos = computed(() => {
    return props.posts.data.flatMap((post) => {
        const postedAt = post.published_at ?? post.created_at;
        const postedDiff = humanDiff(postedAt);

        return (post.media ?? []).map((media, index) => ({
            id: `${post.id}-${index}`,
            thumb:
                media.conversions?.thumb ??
                media.conversions?.full ??
                media.original_url ??
                '',
            full:
                media.conversions?.full ??
                media.conversions?.thumb ??
                media.original_url ??
                '',
            description: post.body,
            postedDiff,
            postedAt,
            downloadName: `@${props.profile.username}-photo-${post.id}-${index + 1}.webp`,
        }));
    });
});

const openCarousel = (photoUrl: string): void => {
    selectedPhotos.value = profilePhotos.value.filter(
        (photo) => photo.full !== '',
    );

    const initialIndex = selectedPhotos.value.findIndex(
        (photo) => photo.full === photoUrl,
    );

    selectedPhotoIndex.value = initialIndex >= 0 ? initialIndex : 0;

    isCarouselOpen.value = true;
};

onMounted(() => {
    currentUrl.value = window.location.href;
});
</script>

<template>
    <Head :title="`${profile.name} | ${$page.props.appName}`" />

    <PublicLayout>
        <section class="mx-auto mt-6 w-[90%]">
            <div
                class="flex flex-col gap-7 lg:flex-row lg:items-start lg:gap-10"
            >
                <div
                    class="mx-auto w-full max-w-sm lg:mx-0 lg:w-72 lg:max-w-none"
                >
                    <button
                        type="button"
                        class="h-80 w-full overflow-hidden rounded-3xl border border-border bg-muted sm:h-96 lg:h-[30rem]"
                        @click="isProfileImageOpen = true"
                    >
                        <img
                            v-if="profileImage"
                            :src="profileImage"
                            :alt="profile.name"
                            class="h-full w-full object-cover"
                            loading="lazy"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center text-5xl font-semibold text-muted-foreground"
                        >
                            {{ profileInitial }}
                        </div>
                    </button>
                </div>

                <div class="flex-1">
                    <div class="text-center lg:text-left">
                        <div class="inline-flex items-center gap-2">
                            <h1 class="text-3xl font-semibold sm:text-4xl">
                                {{ profile.name }}
                            </h1>
                            <VerifiedBadgeIcon
                                v-if="profile.is_verified"
                                class="size-5 shrink-0 text-primary"
                            />
                        </div>
                        <p class="mt-1 text-base text-muted-foreground">
                            @{{ profile.username }}
                        </p>
                    </div>

                    <div
                        v-if="detailRows.length > 0 || socialLinks.length > 0"
                        class="my-5 h-px bg-border"
                    />

                    <div
                        v-if="detailRows.length > 0"
                        class="space-y-3 text-base text-muted-foreground"
                    >
                        <template v-for="(row, index) in detailRows" :key="row">
                            <div v-if="index > 0" class="h-px bg-border" />

                            <Link
                                v-if="row === 'university'"
                                :href="`/universities/${profile.university?.slug}`"
                                class="flex items-center gap-2 hover:text-foreground"
                            >
                                <img
                                    v-if="
                                        profile.university?.media?.[0]
                                            ?.conversions?.thumb ||
                                        profile.university?.media?.[0]
                                            ?.original_url
                                    "
                                    :src="
                                        profile.university?.media?.[0]
                                            ?.conversions?.full ??
                                        profile.university?.media?.[0]
                                            ?.conversions?.thumb ??
                                        profile.university?.media?.[0]
                                            ?.original_url ??
                                        ''
                                    "
                                    :alt="
                                        profile.university?.name ??
                                        'University logo'
                                    "
                                    class="h-4 w-4 object-cover"
                                />
                                <span>{{ profile.university?.name }}</span>
                            </Link>

                            <Link
                                v-else-if="row === 'course'"
                                :href="`/courses/${profile.course?.slug}`"
                                class="flex items-center gap-2 hover:text-foreground"
                            >
                                <i class="fa-solid fa-graduation-cap" />
                                <span>{{ courseLabel }}</span>
                            </Link>

                            <p
                                v-else-if="row === 'location'"
                                class="flex items-center gap-2"
                            >
                                <i class="fa-solid fa-location-dot" />
                                <span>{{ profile.location }}</span>
                            </p>

                            <p
                                v-else-if="row === 'joined'"
                                class="flex items-center gap-2"
                            >
                                <i class="fa-regular fa-calendar" />
                                <span>Joined {{ joinedLabel }}</span>
                            </p>

                            <p
                                v-else
                                class="flex items-start gap-2 leading-relaxed"
                            >
                                <i class="fa-regular fa-note-sticky pt-0.5" />
                                <span>{{ profile.bio }}</span>
                            </p>
                        </template>
                    </div>

                    <div
                        v-if="socialLinks.length > 0 && detailRows.length > 0"
                        class="mt-5 h-px bg-border"
                    />

                    <div v-if="socialLinks.length > 0" class="mt-5">
                        <div
                            class="grid grid-cols-4 gap-3 lg:flex lg:flex-nowrap lg:justify-start lg:gap-4"
                        >
                            <a
                                v-for="link in socialLinks"
                                :key="link.key"
                                :href="link.href"
                                target="_blank"
                                rel="noopener noreferrer"
                                :aria-label="link.label"
                                class="inline-flex h-12 w-12 items-center justify-center rounded-full border border-border bg-background text-lg hover:bg-accent"
                            >
                                <i :class="link.iconClass" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mt-8 border-t border-border pt-8 pb-10">
            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4">
                <button
                    v-for="photo in profilePhotos"
                    :key="photo.id"
                    type="button"
                    class="group overflow-hidden border border-border bg-card text-left"
                    @click="openCarousel(photo.full)"
                >
                    <img
                        :src="photo.thumb"
                        alt="Post image"
                        class="aspect-[3/4] w-full object-cover transition-transform duration-200 group-hover:scale-[1.03]"
                        loading="lazy"
                    />
                </button>
            </div>

            <div
                v-if="profilePhotos.length === 0"
                class="rounded-xl border border-dashed border-border p-6 text-center text-sm text-muted-foreground"
            >
                No photos published yet.
            </div>
        </section>

        <ShareButton :url="currentUrl" :title="profile.name" />
        <PhotoCarousel
            v-model="isCarouselOpen"
            :photos="selectedPhotos"
            :initial-index="selectedPhotoIndex"
        />
        <PhotoCarousel
            v-if="profileImage"
            v-model="isProfileImageOpen"
            :photos="[profileImage]"
            title="Profile Photo"
        />
    </PublicLayout>
</template>

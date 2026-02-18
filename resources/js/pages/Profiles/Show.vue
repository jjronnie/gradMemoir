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

const truncatedLocation = computed(() => {
    const location = props.profile.location?.trim() ?? '';

    if (location === '') {
        return null;
    }

    return location.length > 15 ? `${location.slice(0, 15)}...` : location;
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
        <section
            class="relative mt-6 overflow-hidden rounded-3xl border border-border bg-card"
        >
            <div
                class="absolute inset-0 bg-gradient-to-br from-primary/10 via-transparent to-transparent"
            />

            <div class="relative p-5 sm:p-8">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-end">
                    <button
                        type="button"
                        class="h-28 w-28 shrink-0 overflow-hidden rounded-full border border-border bg-muted sm:h-32 sm:w-32"
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
                            class="flex h-full w-full items-center justify-center text-3xl font-semibold text-muted-foreground"
                        >
                            {{ profileInitial }}
                        </div>
                    </button>

                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <h1
                                class="truncate text-3xl font-semibold sm:text-4xl"
                            >
                                {{ profile.name }}
                            </h1>
                            <VerifiedBadgeIcon
                                v-if="profile.is_verified"
                                class="size-5 shrink-0 text-primary"
                            />
                        </div>
                        <p class="mt-1 text-muted-foreground">
                            @{{ profile.username }}
                        </p>
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                            <span v-if="profile.profession">{{
                                profile.profession
                            }}</span>
                            <span
                                v-if="truncatedLocation"
                                class="inline-flex items-center gap-1 text-muted-foreground"
                            >
                                <i class="fa-solid fa-location-dot" />
                                {{ truncatedLocation }}
                            </span>
                        </div>

                        <div class="mt-2 space-y-2 text-sm text-muted-foreground">
                            <Link
                                v-if="
                                    profile.university?.name &&
                                    profile.university?.slug
                                "
                                :href="`/universities/${profile.university.slug}`"
                                class="flex items-center gap-1 text-muted-foreground hover:text-muted-foreground"
                            >
                                <img
                                    v-if="
                                        profile.university.media?.[0]
                                            ?.conversions?.thumb ||
                                        profile.university.media?.[0]
                                            ?.original_url
                                    "
                                    :src="
                                        profile.university.media?.[0]
                                            ?.conversions?.full ??
                                        profile.university.media?.[0]
                                            ?.conversions?.thumb ??
                                        profile.university.media?.[0]
                                            ?.original_url ??
                                        ''
                                    "
                                    :alt="
                                        profile.university.name ??
                                        'University logo'
                                    "
                                    class="h-4 w-4 object-cover"
                                />
                                {{ profile.university.name }}
                            </Link>
                            <Link
                                v-if="
                                    profile.course?.short_name &&
                                    profile.course?.year &&
                                    profile.course?.slug
                                "
                                :href="`/courses/${profile.course.slug}`"
                                class="flex items-center gap-1 text-muted-foreground hover:text-muted-foreground"
                            >
                                <i class="fa-solid fa-graduation-cap" />
                                {{ profile.course.short_name }} Class of
                                {{ profile.course.year }}
                            </Link>
                            <span
                                v-if="joinedLabel"
                                class="flex items-center gap-1 text-muted-foreground"
                            >
                                <i class="fa-regular fa-calendar" />
                                Joined {{ joinedLabel }}
                            </span>
                        </div>
                    </div>
                </div>

                <p
                    v-if="profile.bio"
                    class="mt-6 max-w-3xl text-sm leading-relaxed text-muted-foreground"
                >
                    {{ profile.bio }}
                </p>

                <div class="mt-6 flex flex-wrap gap-2">
                    <a
                        v-for="link in socialLinks"
                        :key="link.key"
                        :href="link.href"
                        target="_blank"
                        rel="noopener noreferrer"
                        :aria-label="link.label"
                        class="inline-flex items-center gap-2 rounded-full border border-border bg-background px-3 py-1.5 text-sm hover:bg-accent"
                    >
                        <i :class="link.iconClass" />
                        <span>{{ link.label }}</span>
                    </a>
                </div>
            </div>
        </section>

        <section class="mt-6 pb-10">
            <h2 class="mb-3 text-lg font-semibold">Photos</h2>

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

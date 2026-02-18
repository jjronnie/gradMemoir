<script setup lang="ts">
import ConfirmActionModal from '@/components/ConfirmActionModal.vue';
import InputError from '@/components/InputError.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

type MediaPayload = {
    original_url?: string;
    conversions?: Record<string, string>;
};

type FeaturedProfile = {
    id: number;
    sort_order: number;
    user?: {
        id: number;
        name: string;
        username: string;
        email: string;
        media?: MediaPayload[];
    };
};

type SearchResultUser = {
    id: number;
    name: string;
    username: string;
    email: string;
    media?: MediaPayload[];
    featured_profile?: {
        id: number;
    } | null;
};

const props = defineProps<{
    featuredProfiles: {
        data: FeaturedProfile[];
    };
    search: string;
    searchResults: SearchResultUser[];
}>();

const form = useForm({
    user_id: '',
});

const searchQuery = ref(props.search);
const deletingId = ref<number | null>(null);
const addingUserId = ref<number | null>(null);
const confirmOpen = ref(false);
const confirmTitle = ref('');
const confirmDescription = ref('');
const pendingAction = ref<
    | { type: 'add'; user: SearchResultUser }
    | { type: 'remove'; id: number }
    | null
>(null);

let searchTimeout: ReturnType<typeof setTimeout> | null = null;

const applySearch = (): void => {
    if (searchTimeout !== null) {
        clearTimeout(searchTimeout);
    }

    searchTimeout = setTimeout(() => {
        router.get(
            '/admin/featured-profiles',
            { search: searchQuery.value },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    }, 300);
};

const profilePhotoUrl = (media?: MediaPayload[]): string | null => {
    return (
        media?.[0]?.conversions?.thumb ??
        media?.[0]?.conversions?.full ??
        media?.[0]?.original_url ??
        null
    );
};

const requestAddProfile = (user: SearchResultUser): void => {
    pendingAction.value = { type: 'add', user };
    confirmTitle.value = 'Add Featured Profile';
    confirmDescription.value = `Add ${user.name} to featured profiles?`;
    confirmOpen.value = true;
};

const requestRemoveProfile = (featuredProfileId: number): void => {
    pendingAction.value = { type: 'remove', id: featuredProfileId };
    confirmTitle.value = 'Remove Featured Profile';
    confirmDescription.value = 'Remove this profile from the featured list?';
    confirmOpen.value = true;
};

const executeConfirmAction = (): void => {
    if (pendingAction.value === null) {
        return;
    }

    if (pendingAction.value.type === 'add') {
        const user = pendingAction.value.user;

        form.clearErrors();
        form.user_id = String(user.id);
        addingUserId.value = user.id;

        form.post('/admin/featured-profiles', {
            preserveScroll: true,
            onFinish: () => {
                addingUserId.value = null;
                confirmOpen.value = false;
                pendingAction.value = null;
            },
        });

        return;
    }

    deletingId.value = pendingAction.value.id;

    router.delete(`/admin/featured-profiles/${pendingAction.value.id}`, {
        preserveScroll: true,
        onFinish: () => {
            deletingId.value = null;
            confirmOpen.value = false;
            pendingAction.value = null;
        },
    });
};
</script>

<template>
    <Head title="Featured Profiles" />

    <AppLayout>
        <div class="space-y-4 p-4">
            <h1 class="text-2xl font-semibold">Featured Profiles</h1>

            <div class="rounded-xl border border-border bg-card p-4">
                <label class="mb-2 block text-sm font-medium"
                    >Search users to add/remove</label
                >
                <Input
                    v-model="searchQuery"
                    placeholder="Search by name, username, or email..."
                    @input="applySearch"
                />
                <InputError :message="form.errors.user_id" class="mt-2" />

                <div
                    v-if="searchQuery.trim() !== ''"
                    class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3"
                >
                    <article
                        v-for="user in searchResults"
                        :key="user.id"
                        class="rounded-lg border border-border p-3"
                    >
                        <div class="flex items-start gap-3">
                            <img
                                v-if="profilePhotoUrl(user.media)"
                                :src="profilePhotoUrl(user.media) ?? ''"
                                :alt="user.name"
                                class="h-12 w-12 rounded object-cover"
                                loading="lazy"
                            />
                            <div
                                v-else
                                class="flex h-12 w-12 items-center justify-center rounded bg-muted text-xs text-muted-foreground"
                            >
                                N/A
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">
                                    {{ user.name }}
                                </p>
                                <p
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    @{{ user.username }}
                                </p>
                                <p
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ user.email }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <a
                                :href="`/@${user.username}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex h-8 items-center rounded-md border border-border px-3 text-xs hover:bg-accent"
                            >
                                Show
                            </a>
                            <LoadingButton
                                v-if="!user.featured_profile"
                                type="button"
                                size="sm"
                                :loading="addingUserId === user.id"
                                loading-text="Adding..."
                                @click="requestAddProfile(user)"
                            >
                                Add
                            </LoadingButton>
                            <LoadingButton
                                v-else
                                type="button"
                                size="sm"
                                variant="destructive"
                                :loading="
                                    deletingId === user.featured_profile.id
                                "
                                loading-text="Removing..."
                                @click="
                                    requestRemoveProfile(
                                        user.featured_profile.id,
                                    )
                                "
                            >
                                Remove
                            </LoadingButton>
                        </div>
                    </article>

                    <p
                        v-if="searchResults.length === 0"
                        class="text-sm text-muted-foreground"
                    >
                        No matching users found.
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-border bg-card p-4">
                <p
                    v-if="featuredProfiles.data.length > 20"
                    class="mb-3 text-xs text-amber-600"
                >
                    Warning: more than 20 featured profiles may reduce homepage
                    quality.
                </p>

                <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    <article
                        v-for="profile in featuredProfiles.data"
                        :key="profile.id"
                        class="rounded-lg border border-border p-3"
                    >
                        <div class="flex items-start gap-3">
                            <img
                                v-if="profilePhotoUrl(profile.user?.media)"
                                :src="
                                    profilePhotoUrl(profile.user?.media) ?? ''
                                "
                                :alt="profile.user?.name ?? 'Profile photo'"
                                class="h-12 w-12 rounded object-cover"
                                loading="lazy"
                            />
                            <div
                                v-else
                                class="flex h-12 w-12 items-center justify-center rounded bg-muted text-xs text-muted-foreground"
                            >
                                N/A
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold">
                                    {{ profile.user?.name }}
                                </p>
                                <p
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    @{{ profile.user?.username }}
                                </p>
                                <p
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ profile.user?.email }}
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Sort order: {{ profile.sort_order }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <a
                                v-if="profile.user?.username"
                                :href="`/@${profile.user.username}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex h-8 items-center rounded-md border border-border px-3 text-xs hover:bg-accent"
                            >
                                Show
                            </a>
                            <LoadingButton
                                type="button"
                                size="sm"
                                variant="destructive"
                                :loading="deletingId === profile.id"
                                loading-text="Removing..."
                                @click="requestRemoveProfile(profile.id)"
                            >
                                Remove
                            </LoadingButton>
                        </div>
                    </article>

                    <p
                        v-if="featuredProfiles.data.length === 0"
                        class="text-sm text-muted-foreground"
                    >
                        No featured profiles yet.
                    </p>
                </div>
            </div>
        </div>

        <ConfirmActionModal
            :open="confirmOpen"
            :title="confirmTitle"
            :description="confirmDescription"
            :confirm-text="pendingAction?.type === 'add' ? 'Add' : 'Remove'"
            :confirm-variant="
                pendingAction?.type === 'add' ? 'default' : 'destructive'
            "
            :processing="addingUserId !== null || deletingId !== null"
            @update:open="confirmOpen = $event"
            @confirm="executeConfirmAction"
        />
    </AppLayout>
</template>

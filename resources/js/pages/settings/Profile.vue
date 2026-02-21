<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AvatarUpload from '@/components/AvatarUpload.vue';
import DeleteUser from '@/components/DeleteUser.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import UsernameInput from '@/components/UsernameInput.vue';
import WordCountTextarea from '@/components/WordCountTextarea.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/profile';

type Props = {
    canUpdateUsernameAt?: string | null;
};

const props = defineProps<Props>();

const page = usePage();
const user = page.props.auth.user;

const form = useForm({
    name: user?.name ?? '',
    nickname: (user?.nickname as string | undefined) ?? '',
    email: user?.email ?? '',
    username: user?.username ?? '',
    bio: (user?.bio as string | undefined) ?? '',
    profession: (user?.profession as string | undefined) ?? '',
    quote: (user?.quote as string | undefined) ?? '',
    location: (user?.location as string | undefined) ?? '',
    phone: (user?.phone as string | undefined) ?? '',
    facebook_username: (user?.facebook_username as string | undefined) ?? '',
    x_username: (user?.x_username as string | undefined) ?? '',
    tiktok_username: (user?.tiktok_username as string | undefined) ?? '',
    instagram_username:
        (user?.instagram_username as string | undefined) ?? '',
    website: (user?.website as string | undefined) ?? '',
    email_public: (user?.email_public as string | undefined) ?? '',
});

const avatarForm = useForm({
    avatar: null as File | null,
});

const quoteWordCount = computed(() => {
    const trimmedQuote = form.quote.trim();

    if (trimmedQuote === '') {
        return 0;
    }

    return trimmedQuote.split(/\s+/).filter((word) => word !== '').length;
});

const hasQuoteWordLimitError = computed(() => quoteWordCount.value > 8);

const submitProfile = (): void => {
    if (hasQuoteWordLimitError.value) {
        form.setError('quote', 'Quote may not exceed 8 words.');

        return;
    }

    form.clearErrors('quote');
    form.put('/settings/profile');
};

const submitAvatar = (): void => {
    avatarForm.put('/settings/avatar', {
        forceFormData: true,
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="[{ title: 'Profile settings', href: edit().url }]">
        <Head title="Profile settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <Heading
                    variant="small"
                    title="Profile information"
                    description="Update your public and account profile details."
                />

                <form class="space-y-6" @submit.prevent="submitProfile">
                    <div class="grid gap-2">
                        <Label for="name">Name</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            placeholder="e.g. Jane Doe"
                        />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            v-model="form.email"
                            placeholder="you@example.com"
                        />
                        <InputError :message="form.errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Username</Label>
                        <UsernameInput v-model="form.username" />
                        <p
                            v-if="props.canUpdateUsernameAt"
                            class="text-xs text-muted-foreground"
                        >
                            Next change available on
                            {{ new Date(props.canUpdateUsernameAt).toLocaleDateString() }}
                        </p>
                    </div>

                    <WordCountTextarea
                        v-model="form.bio"
                        name="bio"
                        placeholder="Tell your class about yourself"
                    />

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="nickname">Nickname</Label>
                            <Input
                                id="nickname"
                                v-model="form.nickname"
                                maxlength="80"
                                placeholder='e.g. "The Visionary"'
                            />
                            <InputError :message="form.errors.nickname" />
                        </div>
                        <div class="grid gap-2">
                            <Label for="profession">Profession</Label>
                            <Input
                                id="profession"
                                v-model="form.profession"
                                placeholder="e.g. Photographer"
                            />
                            <InputError :message="form.errors.profession" />
                        </div>
                        <div class="grid gap-2 md:col-span-2">
                            <Label for="quote">Short Quote (max 8 words)</Label>
                            <Input
                                id="quote"
                                v-model="form.quote"
                                maxlength="120"
                                placeholder="e.g. Rise, build, and leave impact."
                                @input="form.clearErrors('quote')"
                            />
                            <div class="flex items-center justify-between">
                                <InputError :message="form.errors.quote" />
                                <p
                                    class="text-xs"
                                    :class="
                                        hasQuoteWordLimitError
                                            ? 'text-destructive'
                                            : 'text-muted-foreground'
                                    "
                                >
                                    {{ quoteWordCount }}/8 words
                                </p>
                            </div>
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

                    <div class="grid gap-2">
                        <Label for="phone">WhatsApp Number</Label>
                        <Input
                            id="phone"
                            v-model="form.phone"
                            placeholder="+1 555 123 4567"
                        />
                        <InputError :message="form.errors.phone" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="website">Website (without https://)</Label>
                        <Input id="website" v-model="form.website" placeholder="example.com" />
                        <InputError :message="form.errors.website" />
                    </div>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="grid gap-2">
                            <Label for="facebook_username">Facebook</Label>
                            <Input
                                id="facebook_username"
                                v-model="form.facebook_username"
                                placeholder="facebook_username"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label for="x_username">X</Label>
                            <Input
                                id="x_username"
                                v-model="form.x_username"
                                placeholder="x_username"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label for="tiktok_username">TikTok</Label>
                            <Input
                                id="tiktok_username"
                                v-model="form.tiktok_username"
                                placeholder="tiktok_username"
                            />
                        </div>
                        <div class="grid gap-2">
                            <Label for="instagram_username">Instagram</Label>
                            <Input
                                id="instagram_username"
                                v-model="form.instagram_username"
                                placeholder="instagram_username"
                            />
                        </div>
                    </div>

                    <div class="grid gap-2">
                        <Label for="email_public">Public Email</Label>
                        <Input
                            id="email_public"
                            type="email"
                            v-model="form.email_public"
                            placeholder="public-contact@example.com"
                        />
                        <InputError :message="form.errors.email_public" />
                    </div>

                    <LoadingButton
                        type="submit"
                        :loading="form.processing"
                        loading-text="Saving..."
                    >
                        Save Profile
                    </LoadingButton>
                </form>
            </div>

            <div class="space-y-4">
                <Heading
                    variant="small"
                    title="Avatar"
                    description="Upload and replace your profile picture."
                />
                <form class="space-y-4" @submit.prevent="submitAvatar">
                    <AvatarUpload
                        v-model="avatarForm.avatar"
                        :current-avatar="(user?.avatar as string | undefined) ?? null"
                    />
                    <LoadingButton
                        type="submit"
                        :loading="avatarForm.processing"
                        loading-text="Uploading..."
                    >
                        Update Avatar
                    </LoadingButton>
                </form>
            </div>

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>

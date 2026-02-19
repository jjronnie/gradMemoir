<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import ConfirmActionModal from '@/components/ConfirmActionModal.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { getInitials } from '@/composables/useInitials';
import { logout } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import { Menu } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
};

type NavLink = {
    title: string;
    href: string;
    iconClass: string;
    visible: boolean;
};

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);
const { isCurrentUrl, whenCurrentUrl } = useCurrentUrl();
const logoutModalOpen = ref(false);
const logoutProcessing = ref(false);

const activeItemStyles =
    'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100';

const authenticatedLinks = computed<NavLink[]>(() => {
    const courseSlug = (auth.value.user?.course_slug as string | null) ?? null;
    const profileUrl = (auth.value.user?.profile_url as string | null) ?? null;
    const canAddPhoto =
        ((auth.value.user?.photo_slots_remaining as number | undefined) ?? 0) >
        0;

    return [
        {
            title: 'Home',
            href: '/',
            iconClass: 'fa-solid fa-house',
            visible: true,
        },
        {
            title: 'My Course',
            href: courseSlug === null ? '/dashboard' : `/courses/${courseSlug}`,
            iconClass: 'fa-solid fa-graduation-cap',
            visible: true,
        },
        {
            title: 'Add Photo',
            href: '/posts/create',
            iconClass: 'fa-solid fa-circle-plus',
            visible: canAddPhoto,
        },
        {
            title: 'Profile',
            href: profileUrl ?? '/dashboard',
            iconClass: 'fa-solid fa-user',
            visible: true,
        },
        {
            title: 'More',
            href: '/more',
            iconClass: 'fa-solid fa-ellipsis',
            visible: true,
        },
    ];
});

const desktopLinks = computed<NavLink[]>(() => authenticatedLinks.value);

const mobileLinks = computed<NavLink[]>(() => [
    ...authenticatedLinks.value,
    {
        title: 'Manage Course',
        href: '/course-admin',
        iconClass: 'fa-solid fa-users',
        visible: auth.value.user?.roles?.includes('admin') ?? false,
    },
    {
        title: 'Settings',
        href: '/settings/profile',
        iconClass: 'fa-solid fa-gear',
        visible: true,
    },
    {
        title: 'How It Works',
        href: '/how-it-works',
        iconClass: 'fa-solid fa-circle-info',
        visible: true,
    },
    {
        title: 'Terms',
        href: '/terms',
        iconClass: 'fa-solid fa-file-lines',
        visible: true,
    },
]);

const requestLogout = (): void => {
    logoutModalOpen.value = true;
};

const confirmLogout = (): void => {
    if (logoutProcessing.value) {
        return;
    }

    logoutProcessing.value = true;

    router.post(logout.url(), {}, {
        onSuccess: () => {
            router.flushAll();
        },
        onFinish: () => {
            logoutProcessing.value = false;
            logoutModalOpen.value = false;
        },
    });
};
</script>

<template>
    <div>
        <div class="border-b border-sidebar-border/80">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="mr-2 h-9 w-9"
                            >
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetTitle class="sr-only"
                                >Navigation Menu</SheetTitle
                            >
                            <SheetHeader class="flex justify-start text-left">
                                <AppLogoIcon class="size-6" />
                            </SheetHeader>
                            <div class="mt-5 space-y-1">
                                <Link
                                    v-for="item in mobileLinks.filter((entry) => entry.visible)"
                                    :key="item.title"
                                    :href="item.href"
                                    class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                    :class="
                                        whenCurrentUrl(item.href, activeItemStyles)
                                    "
                                >
                                    <i :class="item.iconClass" />
                                    {{ item.title }}
                                </Link>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link href="/dashboard" class="flex items-center gap-x-2">
                    <AppLogo />
                </Link>

                <div class="hidden h-full lg:flex lg:flex-1 lg:items-center">
                    <nav class="ml-8 flex items-center gap-2">
                        <Link
                            v-for="item in desktopLinks.filter((entry) => entry.visible)"
                            :key="item.title"
                            :href="item.href"
                            class="inline-flex h-9 items-center gap-2 rounded-md px-3 text-sm font-medium hover:bg-accent"
                            :class="
                                isCurrentUrl(item.href)
                                    ? 'bg-accent text-accent-foreground'
                                    : 'text-muted-foreground'
                            "
                        >
                            <i :class="item.iconClass" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </nav>
                </div>

                <div class="ml-auto flex items-center">
                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                            >
                                <Avatar
                                    class="size-8 overflow-hidden rounded-full"
                                >
                                    <AvatarImage
                                        v-if="auth.user.avatar"
                                        :src="auth.user.avatar"
                                        :alt="auth.user.name"
                                    />
                                    <AvatarFallback
                                        class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ getInitials(auth.user?.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent
                                :user="auth.user"
                                @request-logout="requestLogout"
                            />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <div
            v-if="props.breadcrumbs.length > 1"
            class="flex w-full border-b border-sidebar-border/70"
        >
            <div
                class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl"
            >
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>

        <ConfirmActionModal
            :open="logoutModalOpen"
            title="Log out"
            description="Are you sure you want to log out of your account?"
            confirm-text="Log out"
            confirm-variant="destructive"
            :processing="logoutProcessing"
            @update:open="logoutModalOpen = $event"
            @confirm="confirmLogout"
        />
    </div>
</template>

<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import BottomNav from '@/components/BottomNav.vue';
import ConfirmActionModal from '@/components/ConfirmActionModal.vue';
import FlashMessages from '@/components/FlashMessages.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { useAppearance } from '@/composables/useAppearance';
import { getInitials } from '@/composables/useInitials';
import { logout } from '@/routes';
import type { AppPageProps } from '@/types';

const { resolvedAppearance, updateAppearance } = useAppearance();
const page = usePage<AppPageProps>();
const appName = computed(
    () =>
        (page.props.appName as string | undefined) ??
        (page.props.name as string | undefined) ??
        'App',
);
const currentYear = new Date().getFullYear();
const authUser = computed(() => page.props.auth.user);
const logoutModalOpen = ref(false);
const logoutProcessing = ref(false);

const toggleTheme = (): void => {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
};

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
    <div class="min-h-screen bg-background text-foreground">
        <header
            class="sticky top-0 z-30 border-b border-border/70 bg-background/95 backdrop-blur"
        >
            <div
                class="mx-auto flex w-full max-w-6xl items-center justify-between px-4 py-3"
            >
                <Link href="/" class="flex items-center gap-2">
                    <AppLogo />
                </Link>
                <div class="flex items-center gap-2">
                    <nav class="hidden items-center gap-1 md:flex">
                        <Link
                            href="/universities"
                            class="rounded-md px-3 py-1.5 text-sm hover:bg-accent"
                        >
                            Explore
                        </Link>
                        <template v-if="authUser">
                            <Link
                                href="/dashboard"
                                class="rounded-md px-3 py-1.5 text-sm hover:bg-accent"
                            >
                                Dashboard
                            </Link>
                        </template>
                        <template v-else>
                            <Link
                                href="/login"
                                class="rounded-md px-3 py-1.5 text-sm hover:bg-accent"
                            >
                                Login
                            </Link>
                            <Link
                                href="/register"
                                class="rounded-md px-3 py-1.5 text-sm hover:bg-accent"
                            >
                                Register
                            </Link>
                        </template>
                    </nav>
                    <Button
                        variant="outline"
                        size="icon"
                        aria-label="Toggle appearance"
                        @click="toggleTheme"
                    >
                        <i
                            :class="
                                resolvedAppearance === 'dark'
                                    ? 'fa-solid fa-sun'
                                    : 'fa-solid fa-moon'
                            "
                        />
                    </Button>
                    <DropdownMenu v-if="authUser">
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
                                        v-if="authUser.avatar"
                                        :src="authUser.avatar"
                                        :alt="authUser.name"
                                    />
                                    <AvatarFallback
                                        class="rounded-full bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ getInitials(authUser.name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent
                                :user="authUser"
                                @request-logout="requestLogout"
                            />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-6xl px-4 pb-24">
            <FlashMessages class="pt-4" />
            <slot />
        </main>

        <footer class="border-t border-border bg-muted/30 pb-24 lg:pb-8">
            <div class="mx-auto w-full max-w-6xl px-4 py-12">
                <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
                    <div class="space-y-4 lg:col-span-2">
                        <Link href="/" class="inline-flex items-center gap-2">
                            <AppLogo />
                        </Link>
                        <p class="max-w-xl text-sm text-muted-foreground">
                            {{ appName }} preserves university memories through
                            lasting, searchable class archives.
                        </p>
                    </div>

                    <div>
                        <h3
                            class="mb-3 text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase"
                        >
                            Explore
                        </h3>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <Link href="/" class="hover:underline"
                                    >Home</Link
                                >
                            </li>
                            <li>
                                <Link href="/universities" class="hover:underline"
                                    >Universities</Link
                                >
                            </li>
                            <li>
                                <Link href="/how-it-works" class="hover:underline"
                                    >How It Works</Link
                                >
                            </li>
                        </ul>
                    </div>

                    <div>
                        <h3
                            class="mb-3 text-xs font-semibold tracking-[0.18em] text-muted-foreground uppercase"
                        >
                            Legal
                        </h3>
                        <ul class="space-y-2 text-sm">
                            <li>
                                <Link href="/terms" class="hover:underline"
                                    >Terms & Conditions</Link
                                >
                            </li>
                            <li>
                                <Link href="/more" class="hover:underline"
                                    >More</Link
                                >
                            </li>
                            <li>
                                <a
                                    href="https://techtowerinc.com"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="hover:underline"
                                >
                                    Developer
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div
                    class="mt-10 border-t border-border pt-5 text-xs text-muted-foreground"
                >
                    <p>
                        &copy; {{ currentYear }} {{ appName }}. All rights
                        reserved.
                    </p>
                </div>
            </div>
        </footer>

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

        <BottomNav />
    </div>
</template>

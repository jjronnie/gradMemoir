<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import BottomNav from '@/components/BottomNav.vue';
import FlashMessages from '@/components/FlashMessages.vue';
import { Button } from '@/components/ui/button';
import { useAppearance } from '@/composables/useAppearance';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const { resolvedAppearance, updateAppearance } = useAppearance();
const page = usePage();
const appName = computed(
    () =>
        (page.props.appName as string | undefined) ??
        (page.props.name as string | undefined) ??
        'App',
);
const currentYear = new Date().getFullYear();
const authUser = computed(() => page.props.auth.user);

const toggleTheme = (): void => {
    updateAppearance(resolvedAppearance.value === 'dark' ? 'light' : 'dark');
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

        <BottomNav />
    </div>
</template>

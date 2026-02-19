<script setup lang="ts">
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import type { AppPageProps } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type MenuItem = {
    label: string;
    href: string;
};

type NavItem = {
    label: string;
    iconClass: string;
    visible: boolean;
    active: boolean;
    type: 'link' | 'menu';
    href?: string;
    menuItems?: MenuItem[];
    emphasized?: boolean;
};

const page = usePage<AppPageProps>();

const currentUrl = computed(() => page.url);
const user = computed(() => page.props.auth.user);
const isSuperadmin = computed(() => user.value?.roles?.includes('superadmin'));
const canAddPhoto = computed(() => {
    if (user.value === null || isSuperadmin.value) {
        return false;
    }

    return ((user.value.photo_slots_remaining as number | undefined) ?? 0) > 0;
});

const superadminItems = computed<NavItem[]>(() => [
    {
        label: 'Dashboard',
        type: 'link',
        href: '/admin/dashboard',
        iconClass: 'fa-solid fa-gauge',
        visible: true,
        active: currentUrl.value.startsWith('/admin/dashboard'),
    },
    {
        label: 'Universities',
        type: 'link',
        href: '/admin/universities',
        iconClass: 'fa-solid fa-building-columns',
        visible: true,
        active: currentUrl.value.startsWith('/admin/universities'),
    },
    {
        label: 'Courses',
        type: 'link',
        href: '/admin/courses',
        iconClass: 'fa-solid fa-graduation-cap',
        visible: true,
        active: currentUrl.value.startsWith('/admin/courses'),
    },
    {
        label: 'Users',
        type: 'link',
        href: '/admin/users',
        iconClass: 'fa-solid fa-users',
        visible: true,
        active: currentUrl.value.startsWith('/admin/users'),
    },
    {
        label: 'Flags',
        type: 'link',
        href: '/admin/flags',
        iconClass: 'fa-solid fa-flag',
        visible: true,
        active: currentUrl.value.startsWith('/admin/flags'),
    },
]);

const studentOrAdminItems = computed<NavItem[]>(() => {
    const courseYearUrl = (user.value?.course_year_url as string | undefined) ?? '';
    const profileUrl = (user.value?.profile_url as string | undefined) ?? '#';

    return [
        {
            label: 'Dashboard',
            type: 'link',
            href: '/dashboard',
            iconClass: 'fa-solid fa-gauge',
            visible: true,
            active: currentUrl.value.startsWith('/dashboard'),
        },
        {
            label: 'My Course',
            type: 'link',
            href: courseYearUrl !== '' ? courseYearUrl : '/dashboard',
            iconClass: 'fa-solid fa-graduation-cap',
            visible: true,
            active: currentUrl.value.startsWith('/course/'),
        },
        {
            label: 'Add Photo',
            type: 'link',
            href: '/posts/create',
            iconClass: 'fa-solid fa-plus',
            visible: canAddPhoto.value,
            active: currentUrl.value.startsWith('/posts/create'),
            emphasized: true,
        },
        {
            label: 'Profile',
            type: 'link',
            href: profileUrl,
            iconClass: 'fa-solid fa-user',
            visible: true,
            active: profileUrl !== '#' && currentUrl.value === profileUrl,
        },
        {
            label: 'More',
            type: 'link',
            href: '/more',
            iconClass: 'fa-solid fa-ellipsis',
            visible: !(user.value?.roles?.includes('admin') ?? false),
            active: currentUrl.value.startsWith('/more'),
        },
        {
            label: 'Manage',
            type: 'link',
            href: '/course-admin',
            iconClass: 'fa-solid fa-users',
            visible: user.value?.roles?.includes('admin') ?? false,
            active: currentUrl.value.startsWith('/course-admin'),
        },
    ];
});

const guestItems = computed<NavItem[]>(() => [
    {
        label: 'Home',
        type: 'link',
        href: '/',
        iconClass: 'fa-solid fa-house',
        visible: true,
        active: currentUrl.value === '/',
    },
    {
        label: 'Universities',
        type: 'link',
        href: '/universities',
        iconClass: 'fa-solid fa-building-columns',
        visible: true,
        active: currentUrl.value.startsWith('/universities'),
    },
    {
        label: 'Login',
        type: 'menu',
        iconClass: 'fa-solid fa-right-to-bracket',
        visible: true,
        active: currentUrl.value === '/login' || currentUrl.value === '/register',
        menuItems: [
            {
                label: 'Login',
                href: '/login',
            },
            {
                label: 'Register',
                href: '/register',
            },
        ],
    },
    {
        label: 'More',
        type: 'link',
        href: '/more',
        iconClass: 'fa-solid fa-ellipsis',
        visible: true,
        active: currentUrl.value.startsWith('/more'),
    },
]);

const navItems = computed(() => {
    if (isSuperadmin.value) {
        return superadminItems.value;
    }

    return user.value === null ? guestItems.value : studentOrAdminItems.value;
});

const visibleItems = computed(() =>
    navItems.value.filter((entry) => entry.visible),
);

const navGridStyle = computed(() => ({
    gridTemplateColumns: `repeat(${Math.max(visibleItems.value.length, 1)}, minmax(0, 1fr))`,
}));
</script>

<template>
    <nav
        v-if="visibleItems.length > 0"
        class="fixed right-0 bottom-0 left-0 z-40 border-t border-border/80 bg-background/95 pb-[max(env(safe-area-inset-bottom),0.5rem)] backdrop-blur lg:hidden"
        aria-label="Bottom navigation"
    >
        <div class="mx-auto grid max-w-xl gap-1 px-2 pt-2" :style="navGridStyle">
            <template v-for="item in visibleItems" :key="item.label">
                <Link
                    v-if="item.type === 'link'"
                    :href="item.href!"
                    class="flex items-center justify-center rounded-md px-2 py-1.5"
                    :class="[
                        item.active ? 'text-foreground' : 'text-muted-foreground',
                        item.emphasized ? 'relative -mt-5' : '',
                    ]"
                    :aria-label="item.label"
                >
                    <span
                        class="flex h-8 w-8 items-center justify-center rounded-full"
                        :class="
                            item.emphasized
                                ? 'border border-border bg-primary text-primary-foreground shadow-lg'
                                : item.active
                                  ? 'bg-accent text-accent-foreground'
                                  : 'hover:bg-accent/70'
                        "
                    >
                        <i :class="item.iconClass" />
                    </span>
                    <span class="sr-only">{{ item.label }}</span>
                </Link>

                <DropdownMenu v-else>
                    <DropdownMenuTrigger :as-child="true">
                        <button
                            type="button"
                            class="flex items-center justify-center rounded-md px-2 py-1.5"
                            :class="
                                item.active
                                    ? 'text-foreground'
                                    : 'text-muted-foreground'
                            "
                            :aria-label="item.label"
                        >
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-full"
                                :class="
                                    item.active
                                        ? 'bg-accent text-accent-foreground'
                                        : 'hover:bg-accent/70'
                                "
                            >
                                <i :class="item.iconClass" />
                            </span>
                            <span class="sr-only">{{ item.label }}</span>
                        </button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        align="center"
                        side="top"
                        :side-offset="10"
                        class="w-40"
                    >
                        <DropdownMenuItem
                            v-for="menuItem in item.menuItems ?? []"
                            :key="menuItem.href"
                            :as-child="true"
                        >
                            <Link :href="menuItem.href" class="w-full">
                                {{ menuItem.label }}
                            </Link>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </template>
        </div>
    </nav>
</template>

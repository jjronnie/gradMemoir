<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookMarked,
    Flag,
    FolderKanban,
    GraduationCap,
    LayoutGrid,
    Sparkles,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isSuperadmin = computed(() => user.value?.roles?.includes('superadmin'));

const mainNavItems = computed<NavItem[]>(() => {
    if (isSuperadmin.value) {
        return [
            {
                title: 'Dashboard',
                href: '/admin/dashboard',
                icon: LayoutGrid,
            },
            {
                title: 'Universities',
                href: '/admin/universities',
                icon: BookMarked,
            },
            {
                title: 'Courses',
                href: '/admin/courses',
                icon: GraduationCap,
            },
            {
                title: 'Users',
                href: '/admin/users',
                icon: Users,
            },
            {
                title: 'Featured Profiles',
                href: '/admin/featured-profiles',
                icon: Sparkles,
            },
            {
                title: 'Applications',
                href: '/admin/applications',
                icon: FolderKanban,
            },
            {
                title: 'Flags',
                href: '/admin/flags',
                icon: Flag,
            },
        ];
    }

    return [
        {
            title: 'Dashboard',
            href: dashboard(),
            icon: LayoutGrid,
        },
    ];
});

const footerNavItems: NavItem[] = [
    {
        title: 'Developer',
        href: 'https://techtowerinc.com',
        icon: Sparkles,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="isSuperadmin ? '/admin/dashboard' : dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

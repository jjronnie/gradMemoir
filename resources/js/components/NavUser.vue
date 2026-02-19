<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { ChevronsUpDown } from 'lucide-vue-next';
import { ref } from 'vue';
import ConfirmActionModal from '@/components/ConfirmActionModal.vue';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import UserInfo from '@/components/UserInfo.vue';
import { logout } from '@/routes';
import UserMenuContent from './UserMenuContent.vue';

const page = usePage();
const user = page.props.auth.user;
const { isMobile, state } = useSidebar();
const logoutModalOpen = ref(false);
const logoutProcessing = ref(false);

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
        <SidebarMenu>
            <SidebarMenuItem>
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <SidebarMenuButton
                            size="lg"
                            class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                            data-test="sidebar-menu-button"
                        >
                            <UserInfo :user="user" />
                            <ChevronsUpDown class="ml-auto size-4" />
                        </SidebarMenuButton>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        class="w-(--reka-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                        :side="
                            isMobile
                                ? 'bottom'
                                : state === 'collapsed'
                                  ? 'left'
                                  : 'bottom'
                        "
                        align="end"
                        :side-offset="4"
                    >
                        <UserMenuContent
                            :user="user"
                            @request-logout="requestLogout"
                        />
                    </DropdownMenuContent>
                </DropdownMenu>
            </SidebarMenuItem>
        </SidebarMenu>

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

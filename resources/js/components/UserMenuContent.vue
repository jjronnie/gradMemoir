<script setup lang="ts">
import ConfirmActionModal from '@/components/ConfirmActionModal.vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { User } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { GraduationCap, Images, LogOut, Settings } from 'lucide-vue-next';
import { computed, ref } from 'vue';

type Props = {
    user: User;
};

const props = defineProps<Props>();
const logoutModalOpen = ref(false);
const logoutProcessing = ref(false);
const courseHref = computed(() =>
    props.user.course_slug ? `/courses/${props.user.course_slug}` : '/dashboard',
);

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
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" :href="edit()" prefetch>
                <Settings class="mr-2 h-4 w-4" />
                Settings
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" href="/posts">
                <Images class="mr-2 h-4 w-4" />
                Posts
            </Link>
        </DropdownMenuItem>
        <DropdownMenuItem :as-child="true">
            <Link class="block w-full cursor-pointer" :href="courseHref">
                <GraduationCap class="mr-2 h-4 w-4" />
                My Course
            </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <button
            type="button"
            class="flex w-full cursor-pointer items-center"
            data-test="logout-button"
            @click="requestLogout"
        >
            <LogOut class="mr-2 h-4 w-4" />
            Log out
        </button>
    </DropdownMenuItem>

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
</template>

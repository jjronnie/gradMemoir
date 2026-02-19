<script setup lang="ts">
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import UserInfo from '@/components/UserInfo.vue';
import { edit } from '@/routes/profile';
import type { User } from '@/types';
import { Link } from '@inertiajs/vue3';
import { GraduationCap, Images, LogOut, Settings } from 'lucide-vue-next';
import { computed } from 'vue';

type Props = {
    user: User;
};

const props = defineProps<Props>();
const emit = defineEmits<{
    (event: 'request-logout'): void;
}>();

const courseHref = computed(() =>
    props.user.course_year_url ? props.user.course_year_url : '/dashboard',
);

const requestLogout = (): void => {
    emit('request-logout');
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
</template>

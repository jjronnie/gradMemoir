<script setup lang="ts">
import ConfirmActionModal from '@/components/ConfirmActionModal.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';

type UserRow = {
    id: number;
    name: string;
    email: string;
    username: string;
    role: 'student' | 'admin' | 'superadmin';
    status: 'active' | 'suspended' | 'banned';
    created_at?: string;
};

const props = defineProps<{
    users: {
        data: UserRow[];
    };
    filters: {
        search: string;
        role: string;
        status: string;
    };
}>();

const filters = reactive({
    search: props.filters.search,
    role: props.filters.role,
    status: props.filters.status,
});
const confirmOpen = ref(false);
const pendingUserId = ref<number | null>(null);
const deleting = ref(false);

const applyFilters = (): void => {
    router.get('/admin/users', filters, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const requestDelete = (userId: number): void => {
    pendingUserId.value = userId;
    confirmOpen.value = true;
};

const deleteUser = (): void => {
    if (pendingUserId.value === null) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/users/${pendingUserId.value}`, {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            confirmOpen.value = false;
            pendingUserId.value = null;
        },
    });
};

const statusBadgeClass = (status: UserRow['status']): string => {
    if (status === 'active') {
        return 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400';
    }

    if (status === 'suspended') {
        return 'bg-amber-500/10 text-amber-600 dark:text-amber-400';
    }

    return 'bg-destructive/10 text-destructive';
};
</script>

<template>
    <Head title="Users" />

    <AppLayout>
        <div class="space-y-4 p-4">
            <h1 class="text-2xl font-semibold">Users</h1>

            <div class="grid gap-3 md:grid-cols-3">
                <Input
                    v-model="filters.search"
                    placeholder="Search users..."
                    @input="applyFilters"
                />
                <select
                    v-model="filters.role"
                    class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                    @change="applyFilters"
                >
                    <option value="">All roles</option>
                    <option value="student">Student</option>
                    <option value="admin">Admin</option>
                    <option value="superadmin">Superadmin</option>
                </select>
                <select
                    v-model="filters.status"
                    class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                    @change="applyFilters"
                >
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="banned">Banned</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>

            <div class="rounded-xl border border-border bg-card p-4">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-sm">
                        <thead>
                            <tr class="border-b border-border text-left">
                                <th class="px-2 py-2">Name</th>
                                <th class="px-2 py-2">Email</th>
                                <th class="px-2 py-2">Username</th>
                                <th class="px-2 py-2">Role</th>
                                <th class="px-2 py-2">Status</th>
                                <th class="px-2 py-2">Created</th>
                                <th class="px-2 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="user in users.data"
                                :key="user.id"
                                class="border-b border-border/60"
                            >
                                <td class="px-2 py-2">{{ user.name }}</td>
                                <td class="px-2 py-2">{{ user.email }}</td>
                                <td class="px-2 py-2">@{{ user.username }}</td>
                                <td class="px-2 py-2">
                                    <span
                                        class="rounded-full border border-border px-2 py-0.5 text-xs"
                                    >
                                        {{ user.role }}
                                    </span>
                                </td>
                                <td class="px-2 py-2">
                                    <span
                                        class="rounded-full px-2 py-0.5 text-xs"
                                        :class="statusBadgeClass(user.status)"
                                    >
                                        {{ user.status }}
                                    </span>
                                </td>
                                <td class="px-2 py-2">
                                    {{
                                        user.created_at
                                            ? new Date(
                                                  user.created_at,
                                              ).toLocaleDateString()
                                            : 'N/A'
                                    }}
                                </td>
                                <td class="px-2 py-2">
                                    <div class="flex items-center gap-2">
                                        <Link
                                            :href="`/admin/users/${user.id}/edit`"
                                        >
                                            <Button variant="outline" size="sm"
                                                >Edit</Button
                                            >
                                        </Link>
                                        <a
                                            :href="`/@${user.username}`"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex h-8 items-center rounded-md border border-border px-3 text-xs hover:bg-accent"
                                        >
                                            Profile
                                        </a>
                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            @click="requestDelete(user.id)"
                                        >
                                            Delete
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="users.data.length === 0">
                                <td
                                    colspan="7"
                                    class="px-2 py-6 text-center text-sm text-muted-foreground"
                                >
                                    No users found for the current filters.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <ConfirmActionModal
            :open="confirmOpen"
            title="Delete User"
            description="Are you sure you want to delete this user account? This action cannot be undone."
            confirm-text="Delete"
            :processing="deleting"
            @update:open="confirmOpen = $event"
            @confirm="deleteUser"
        />
    </AppLayout>
</template>

<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

type UserPayload = {
    id: number;
    name: string;
    email: string;
    username: string;
    website?: string | null;
    is_verified?: boolean;
    email_verified_at?: string | null;
    role: 'student' | 'admin' | 'superadmin';
    status: 'active' | 'suspended' | 'banned';
    university?: { name?: string | null } | null;
    course?: { name?: string | null } | null;
};

const props = defineProps<{
    user: UserPayload;
    currentUserId?: number | null;
}>();

const detailsForm = useForm({
    name: props.user.name,
    email: props.user.email,
    username: props.user.username,
    website: props.user.website ?? '',
    is_verified: Boolean(props.user.is_verified),
    email_verified_at: props.user.email_verified_at
        ? String(props.user.email_verified_at).slice(0, 16)
        : '',
});

const statusForm = useForm({
    status: props.user.status,
});

const roleForm = useForm({
    role: props.user.role,
});

const canEditRoleAndStatus = computed(
    () => props.currentUserId === null || props.currentUserId !== props.user.id,
);

const saveDetails = (): void => {
    detailsForm.put(`/admin/users/${props.user.id}`, {
        preserveScroll: true,
    });
};

const saveStatus = (): void => {
    if (!canEditRoleAndStatus.value) {
        return;
    }

    statusForm.put(`/admin/users/${props.user.id}/status`, {
        preserveScroll: true,
    });
};

const saveRole = (): void => {
    if (!canEditRoleAndStatus.value) {
        return;
    }

    roleForm.put(`/admin/users/${props.user.id}/role`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Edit ${user.name}`" />

    <AppLayout>
        <div class="mx-auto w-full max-w-4xl space-y-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold">Edit User</h1>
                    <p class="mt-1 text-sm text-muted-foreground">
                        @{{ user.username }}
                    </p>
                </div>
                <Link href="/admin/users">
                    <Button variant="outline">Back to Users</Button>
                </Link>
            </div>

            <div class="rounded-xl border border-border bg-card p-4 text-sm">
                <p>
                    <span class="text-muted-foreground">University:</span>
                    {{ user.university?.name ?? 'Not set' }}
                </p>
                <p>
                    <span class="text-muted-foreground">Course:</span>
                    {{ user.course?.name ?? 'Not set' }}
                </p>
                <p>
                    <span class="text-muted-foreground">Public profile:</span>
                    <a
                        :href="`/@${user.username}`"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="underline underline-offset-4"
                    >
                        View profile
                    </a>
                </p>
            </div>

            <div class="rounded-xl border border-border bg-card p-4">
                <h2 class="mb-3 text-base font-semibold">User Details</h2>
                <div class="grid gap-3">
                    <div>
                        <Input v-model="detailsForm.name" placeholder="Full name" />
                        <InputError :message="detailsForm.errors.name" />
                    </div>
                    <div>
                        <Input
                            v-model="detailsForm.email"
                            type="email"
                            placeholder="Email"
                        />
                        <InputError :message="detailsForm.errors.email" />
                    </div>
                    <div>
                        <Input v-model="detailsForm.username" placeholder="username" />
                        <InputError :message="detailsForm.errors.username" />
                    </div>
                    <div>
                        <Input
                            v-model="detailsForm.website"
                            placeholder="example.com"
                        />
                        <InputError :message="detailsForm.errors.website" />
                    </div>
                    <label class="flex items-center gap-2 text-xs">
                        <input
                            v-model="detailsForm.is_verified"
                            type="checkbox"
                        />
                        Verified profile
                    </label>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-muted-foreground">
                            Email verified at
                        </label>
                        <Input
                            v-model="detailsForm.email_verified_at"
                            type="datetime-local"
                        />
                        <InputError :message="detailsForm.errors.email_verified_at" />
                    </div>
                    <LoadingButton
                        type="button"
                        :loading="detailsForm.processing"
                        loading-text="Saving..."
                        @click="saveDetails"
                    >
                        Save Details
                    </LoadingButton>
                </div>
            </div>

            <p
                v-if="!canEditRoleAndStatus"
                class="rounded-md border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-xs text-amber-600 dark:text-amber-300"
            >
                You cannot modify your own role or account status.
            </p>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="rounded-xl border border-border bg-card p-4">
                    <h2 class="mb-3 text-base font-semibold">Account Status</h2>
                    <select
                        v-model="statusForm.status"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        :disabled="!canEditRoleAndStatus"
                    >
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="banned">Banned</option>
                    </select>
                    <InputError :message="statusForm.errors.status" />
                    <LoadingButton
                        type="button"
                        class="mt-3"
                        :loading="statusForm.processing"
                        loading-text="Saving..."
                        :disabled="!canEditRoleAndStatus"
                        @click="saveStatus"
                    >
                        Save Status
                    </LoadingButton>
                </div>

                <div class="rounded-xl border border-border bg-card p-4">
                    <h2 class="mb-3 text-base font-semibold">Role</h2>
                    <select
                        v-model="roleForm.role"
                        class="w-full rounded-md border border-input bg-background px-3 py-2 text-sm"
                        :disabled="!canEditRoleAndStatus"
                    >
                        <option value="student">Student</option>
                        <option value="admin">Admin</option>
                        <option value="superadmin">Superadmin</option>
                    </select>
                    <InputError :message="roleForm.errors.role" />
                    <LoadingButton
                        type="button"
                        class="mt-3"
                        :loading="roleForm.processing"
                        loading-text="Saving..."
                        :disabled="!canEditRoleAndStatus"
                        @click="saveRole"
                    >
                        Save Role
                    </LoadingButton>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

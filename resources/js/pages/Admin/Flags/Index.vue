<script setup lang="ts">
import ConfirmActionModal from '@/components/ConfirmActionModal.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

type Flag = {
    id: number;
    reason: string;
    reviewed: boolean;
    created_at?: string;
    user?: {
        id?: number;
        name: string;
        username: string;
        email?: string;
        status?: string;
    };
    flagged_by?: { name: string; email?: string };
};

const props = defineProps<{
    flags: {
        data: Flag[];
    };
}>();

const selectedFlag = ref<Flag | null>(null);
const isDialogOpen = ref(false);
const processingId = ref<number | null>(null);
const deletingId = ref<number | null>(null);
const confirmDeleteOpen = ref(false);
const pendingDeleteId = ref<number | null>(null);

const openDialog = (flag: Flag): void => {
    selectedFlag.value = flag;
    isDialogOpen.value = true;
};

const updateReviewed = (id: number, reviewed: boolean): void => {
    processingId.value = id;

    router.put(
        `/admin/flags/${id}`,
        { reviewed },
        {
            preserveScroll: true,
            onFinish: () => {
                processingId.value = null;
            },
        },
    );
};

const requestDeleteFlag = (id: number): void => {
    pendingDeleteId.value = id;
    confirmDeleteOpen.value = true;
};

const removeFlag = (): void => {
    if (pendingDeleteId.value === null) {
        return;
    }

    deletingId.value = pendingDeleteId.value;

    router.delete(`/admin/flags/${pendingDeleteId.value}`, {
        preserveScroll: true,
        onFinish: () => {
            deletingId.value = null;
            pendingDeleteId.value = null;
            confirmDeleteOpen.value = false;
            isDialogOpen.value = false;
        },
    });
};
</script>

<template>
    <Head title="Flags" />

    <AppLayout>
        <div class="space-y-4 p-4">
            <h1 class="text-2xl font-semibold">User Flags</h1>
            <div class="rounded-xl border border-border bg-card p-4">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[920px] text-sm">
                        <thead>
                            <tr class="border-b border-border text-left">
                                <th class="px-2 py-2">User</th>
                                <th class="px-2 py-2">Flagged By</th>
                                <th class="px-2 py-2">Reason</th>
                                <th class="px-2 py-2">Reviewed</th>
                                <th class="px-2 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="flag in flags.data"
                                :key="flag.id"
                                class="border-b border-border/60"
                            >
                                <td class="px-2 py-2">
                                    {{ flag.user?.name }} (@{{
                                        flag.user?.username
                                    }})
                                </td>
                                <td class="px-2 py-2">
                                    {{ flag.flagged_by?.name }}
                                </td>
                                <td class="px-2 py-2">{{ flag.reason }}</td>
                                <td class="px-2 py-2">
                                    {{ flag.reviewed ? 'Yes' : 'No' }}
                                </td>
                                <td class="px-2 py-2">
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        @click="openDialog(flag)"
                                    >
                                        View
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Dialog v-model:open="isDialogOpen">
                <DialogContent class="max-h-[88vh] max-w-xl overflow-y-auto">
                    <DialogHeader>
                        <DialogTitle>Flag Details</DialogTitle>
                        <DialogDescription>
                            Review this moderation flag.
                        </DialogDescription>
                    </DialogHeader>

                    <div v-if="selectedFlag" class="space-y-2 text-sm">
                        <p>
                            <span class="text-muted-foreground"
                                >Flagged user:</span
                            >
                            {{ selectedFlag.user?.name }} (@{{
                                selectedFlag.user?.username
                            }})
                        </p>
                        <p>
                            <span class="text-muted-foreground"
                                >User email:</span
                            >
                            {{ selectedFlag.user?.email ?? 'N/A' }}
                        </p>
                        <p>
                            <span class="text-muted-foreground"
                                >User status:</span
                            >
                            {{ selectedFlag.user?.status ?? 'N/A' }}
                        </p>
                        <p>
                            <span class="text-muted-foreground"
                                >Flagged by:</span
                            >
                            {{ selectedFlag.flagged_by?.name }}
                        </p>
                        <p>
                            <span class="text-muted-foreground">Reason:</span>
                            {{ selectedFlag.reason }}
                        </p>
                        <p>
                            <span class="text-muted-foreground">Reviewed:</span>
                            {{ selectedFlag.reviewed ? 'Yes' : 'No' }}
                        </p>
                    </div>

                    <div v-if="selectedFlag" class="mt-4 flex flex-wrap gap-2">
                        <LoadingButton
                            type="button"
                            size="sm"
                            variant="outline"
                            :loading="processingId === selectedFlag.id"
                            loading-text="Saving..."
                            @click="
                                updateReviewed(
                                    selectedFlag.id,
                                    !selectedFlag.reviewed,
                                )
                            "
                        >
                            Toggle Reviewed
                        </LoadingButton>
                        <LoadingButton
                            type="button"
                            size="sm"
                            variant="destructive"
                            :loading="deletingId === selectedFlag.id"
                            loading-text="Deleting..."
                            @click="requestDeleteFlag(selectedFlag.id)"
                        >
                            Delete
                        </LoadingButton>
                        <a
                            v-if="selectedFlag.user?.username"
                            :href="`/@${selectedFlag.user.username}`"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-8 items-center rounded-md border border-border px-3 text-xs hover:bg-accent"
                        >
                            View Public Profile
                        </a>
                    </div>
                </DialogContent>
            </Dialog>

            <ConfirmActionModal
                :open="confirmDeleteOpen"
                title="Delete Flag"
                description="Are you sure you want to delete this moderation flag?"
                confirm-text="Delete"
                :processing="deletingId !== null"
                @update:open="confirmDeleteOpen = $event"
                @confirm="removeFlag"
            />
        </div>
    </AppLayout>
</template>

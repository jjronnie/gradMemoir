<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
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

type Application = {
    id: number;
    applicant_name: string;
    email: string;
    phone: string;
    university_name: string;
    course_name: string;
    year: string;
    notes?: string | null;
    status: string;
    created_at?: string;
};

const props = defineProps<{
    applications: {
        data: Application[];
    };
}>();

const selectedApplication = ref<Application | null>(null);
const isDialogOpen = ref(false);
const processingId = ref<number | null>(null);
const deletingId = ref<number | null>(null);
const confirmDeleteOpen = ref(false);
const pendingDeleteId = ref<number | null>(null);

const openDialog = (application: Application): void => {
    selectedApplication.value = application;
    isDialogOpen.value = true;
};

const markReviewed = (id: number): void => {
    processingId.value = id;

    router.put(
        `/admin/applications/${id}`,
        { status: 'reviewed' },
        {
            preserveScroll: true,
            onFinish: () => {
                processingId.value = null;
            },
        },
    );
};

const requestDeleteApplication = (id: number): void => {
    pendingDeleteId.value = id;
    confirmDeleteOpen.value = true;
};

const removeApplication = (): void => {
    if (pendingDeleteId.value === null) {
        return;
    }

    deletingId.value = pendingDeleteId.value;

    router.delete(`/admin/applications/${pendingDeleteId.value}`, {
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
    <Head title="Applications" />

    <AppLayout>
        <div class="space-y-4 p-4">
            <h1 class="text-2xl font-semibold">Course Applications</h1>
            <div class="rounded-xl border border-border bg-card p-4">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-sm">
                        <thead>
                            <tr class="border-b border-border text-left">
                                <th class="px-2 py-2">Applicant</th>
                                <th class="px-2 py-2">University</th>
                                <th class="px-2 py-2">Course</th>
                                <th class="px-2 py-2">Year</th>
                                <th class="px-2 py-2">Status</th>
                                <th class="px-2 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="application in applications.data"
                                :key="application.id"
                                class="border-b border-border/60"
                            >
                                <td class="px-2 py-2">
                                    {{ application.applicant_name }}
                                </td>
                                <td class="px-2 py-2">
                                    {{ application.university_name }}
                                </td>
                                <td class="px-2 py-2">
                                    {{ application.course_name }}
                                </td>
                                <td class="px-2 py-2">
                                    {{ application.year }}
                                </td>
                                <td class="px-2 py-2">
                                    {{ application.status }}
                                </td>
                                <td class="px-2 py-2">
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant="outline"
                                        @click="openDialog(application)"
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
                        <DialogTitle>Application Details</DialogTitle>
                        <DialogDescription>
                            Review and moderate this request.
                        </DialogDescription>
                    </DialogHeader>

                    <div v-if="selectedApplication" class="space-y-2 text-sm">
                        <p>
                            <span class="text-muted-foreground"
                                >Applicant:</span
                            >
                            {{ selectedApplication.applicant_name }}
                        </p>
                        <p>
                            <span class="text-muted-foreground">Email:</span>
                            {{ selectedApplication.email }}
                        </p>
                        <p>
                            <span class="text-muted-foreground">Phone:</span>
                            {{ selectedApplication.phone }}
                        </p>
                        <p>
                            <span class="text-muted-foreground"
                                >University:</span
                            >
                            {{ selectedApplication.university_name }}
                        </p>
                        <p>
                            <span class="text-muted-foreground">Course:</span>
                            {{ selectedApplication.course_name }}
                        </p>
                        <p>
                            <span class="text-muted-foreground">Year:</span>
                            {{ selectedApplication.year }}
                        </p>
                        <p>
                            <span class="text-muted-foreground">Status:</span>
                            {{ selectedApplication.status }}
                        </p>
                        <p v-if="selectedApplication.notes">
                            <span class="text-muted-foreground">Notes:</span>
                            {{ selectedApplication.notes }}
                        </p>
                    </div>

                    <div
                        v-if="selectedApplication"
                        class="mt-4 flex flex-wrap gap-2"
                    >
                        <LoadingButton
                            type="button"
                            size="sm"
                            variant="outline"
                            :loading="processingId === selectedApplication.id"
                            loading-text="Saving..."
                            @click="markReviewed(selectedApplication.id)"
                        >
                            Mark Reviewed
                        </LoadingButton>
                        <LoadingButton
                            type="button"
                            size="sm"
                            variant="destructive"
                            :loading="deletingId === selectedApplication.id"
                            loading-text="Deleting..."
                            @click="
                                requestDeleteApplication(selectedApplication.id)
                            "
                        >
                            Delete
                        </LoadingButton>
                    </div>
                </DialogContent>
            </Dialog>

            <ConfirmActionModal
                :open="confirmDeleteOpen"
                title="Delete Application"
                description="Are you sure you want to delete this application?"
                confirm-text="Delete"
                :processing="deletingId !== null"
                @update:open="confirmDeleteOpen = $event"
                @confirm="removeApplication"
            />
        </div>
    </AppLayout>
</template>

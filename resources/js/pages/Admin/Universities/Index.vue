<script setup lang="ts">
import ConfirmActionModal from '@/components/ConfirmActionModal.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

type University = {
    id: number;
    name: string;
    slug: string;
    location: string;
    courses_count: number;
    media?: Array<{
        conversions?: Record<string, string>;
        original_url?: string;
    }>;
};

defineProps<{
    universities: {
        data: University[];
    };
}>();

const confirmOpen = ref(false);
const pendingId = ref<number | null>(null);
const deleting = ref(false);

const requestRemove = (id: number): void => {
    pendingId.value = id;
    confirmOpen.value = true;
};

const remove = (): void => {
    if (pendingId.value === null) {
        return;
    }

    deleting.value = true;

    router.delete(`/admin/universities/${pendingId.value}`, {
        preserveScroll: true,
        onFinish: () => {
            deleting.value = false;
            confirmOpen.value = false;
            pendingId.value = null;
        },
    });
};
</script>

<template>
    <Head title="Universities" />

    <AppLayout>
        <div class="space-y-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold">Universities</h1>
                <Link href="/admin/universities/create">
                    <Button>Create University</Button>
                </Link>
            </div>

            <div class="rounded-xl border border-border bg-card p-4">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[980px] text-sm">
                        <thead>
                            <tr class="border-b border-border text-left">
                                <th class="px-2 py-2">Logo</th>
                                <th class="px-2 py-2">Name</th>
                                <th class="px-2 py-2">Location</th>
                                <th class="px-2 py-2">Slug</th>
                                <th class="px-2 py-2">Courses</th>
                                <th class="px-2 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="university in universities.data"
                                :key="university.id"
                                class="border-b border-border/60"
                            >
                                <td class="px-2 py-2">
                                    <img
                                        :src="
                                            university.media?.[0]?.conversions
                                                ?.full ??
                                            university.media?.[0]?.conversions
                                                ?.thumb ??
                                            university.media?.[0]
                                                ?.original_url ??
                                            ''
                                        "
                                        class="h-8 w-8 rounded object-cover"
                                    />
                                </td>
                                <td class="px-2 py-2">{{ university.name }}</td>
                                <td class="px-2 py-2">
                                    {{ university.location }}
                                </td>
                                <td class="px-2 py-2">{{ university.slug }}</td>
                                <td class="px-2 py-2">
                                    {{ university.courses_count }}
                                </td>
                                <td class="flex gap-2 px-2 py-2">
                                    <Link
                                        :href="`/admin/universities/${university.id}/edit`"
                                    >
                                        <Button variant="outline" size="sm"
                                            >Edit</Button
                                        >
                                    </Link>
                                    <a
                                        :href="`/universities/${university.slug}`"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex h-8 items-center rounded-md border border-border px-3 text-xs hover:bg-accent"
                                    >
                                        View
                                    </a>
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        @click="requestRemove(university.id)"
                                    >
                                        Delete
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <ConfirmActionModal
            :open="confirmOpen"
            title="Delete University"
            description="Are you sure you want to delete this university? This action cannot be undone."
            confirm-text="Delete"
            :processing="deleting"
            @update:open="confirmOpen = $event"
            @confirm="remove"
        />
    </AppLayout>
</template>

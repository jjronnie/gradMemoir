<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

type Member = {
    id: number;
    name: string;
    username: string;
    email: string;
    status: string;
    created_at: string;
    university?: { name: string };
    course?: { name: string };
    course_year?: { year: string };
    media?: Array<{ conversions?: Record<string, string>; original_url?: string }>;
};

const props = defineProps<{
    courseYear: {
        id: number;
        year: string;
        slug: string;
        course: {
            id: number;
            name: string;
            short_name: string;
            university?: { name: string };
        };
    };
    members: {
        data: Member[];
        next_page_url: string | null;
        prev_page_url: string | null;
    };
    search: string;
}>();

const search = ref(props.search);
const selectedMember = ref<Member | null>(null);
const form = useForm({
    reason: '',
});

const onSearch = (): void => {
    router.get(
        '/course-admin',
        { search: search.value },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const submitFlag = (): void => {
    if (selectedMember.value === null) {
        return;
    }

    form.post(`/users/${selectedMember.value.id}/flag`, {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Manage Cohort" />

    <AppLayout>
        <div class="mx-auto w-full max-w-5xl space-y-4 p-4">
            <h1 class="text-2xl font-semibold">
                Manage {{ courseYear.course.name }} Class of {{ courseYear.year }}
            </h1>
            <p class="text-sm text-muted-foreground">
                {{ courseYear.course.university?.name }}
            </p>

            <Input
                v-model="search"
                placeholder="Search by name, username, or email..."
                @input="onSearch"
            />

            <div class="rounded-xl border border-border bg-card">
                <div class="overflow-x-auto">
                    <table class="min-w-[940px] w-full text-sm">
                        <thead>
                            <tr class="border-b border-border text-left">
                                <th class="px-4 py-3">Name</th>
                                <th class="px-4 py-3">Username</th>
                                <th class="px-4 py-3">Email</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Joined</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="member in members.data"
                                :key="member.id"
                                class="border-b border-border/60"
                            >
                                <td class="px-4 py-3">{{ member.name }}</td>
                                <td class="px-4 py-3">@{{ member.username }}</td>
                                <td class="px-4 py-3">{{ member.email }}</td>
                                <td class="px-4 py-3">{{ member.status }}</td>
                                <td class="px-4 py-3">
                                    {{ new Date(member.created_at).toLocaleDateString() }}
                                </td>
                                <td class="px-4 py-3">
                                    <Dialog>
                                        <DialogTrigger as-child>
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                @click="selectedMember = member"
                                            >
                                                <i class="fa-regular fa-eye" />
                                            </Button>
                                        </DialogTrigger>
                                        <DialogContent class="max-h-[88vh] max-w-lg overflow-y-auto">
                                            <DialogHeader>
                                                <DialogTitle>Member Details</DialogTitle>
                                                <DialogDescription>
                                                    Review profile and optionally flag this user.
                                                </DialogDescription>
                                            </DialogHeader>

                                            <div
                                                v-if="selectedMember"
                                                class="space-y-2 text-sm"
                                            >
                                                <p><strong>Name:</strong> {{ selectedMember.name }}</p>
                                                <p>
                                                    <strong>Username:</strong>
                                                    @{{ selectedMember.username }}
                                                </p>
                                                <p><strong>Email:</strong> {{ selectedMember.email }}</p>
                                                <p>
                                                    <strong>University:</strong>
                                                    {{ selectedMember.university?.name }}
                                                </p>
                                                <p><strong>Course:</strong> {{ selectedMember.course?.name }}</p>
                                                <p>
                                                    <strong>Cohort:</strong>
                                                    Class of {{ selectedMember.course_year?.year ?? '—' }}
                                                </p>
                                            </div>

                                            <div class="space-y-2">
                                                <Input
                                                    v-model="form.reason"
                                                    placeholder="Reason for flag (min 10 characters)"
                                                />
                                                <InputError :message="form.errors.reason" />
                                                <LoadingButton
                                                    type="button"
                                                    :loading="form.processing"
                                                    loading-text="Submitting..."
                                                    @click="submitFlag"
                                                >
                                                    Flag User
                                                </LoadingButton>
                                            </div>
                                        </DialogContent>
                                    </Dialog>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

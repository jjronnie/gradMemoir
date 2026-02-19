<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import LoadingButton from '@/components/LoadingButton.vue';
import TurnstileWidget from '@/components/TurnstileWidget.vue';
import { useTurnstile } from '@/composables/useTurnstile';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const turnstileEnabled = computed(() =>
    Boolean(page.props.turnstileEnabled),
);
const turnstile = useTurnstile();

const form = useForm({
    applicant_name: '',
    email: '',
    phone: '',
    university_name: '',
    course_name: '',
    year: '',
    notes: '',
    'cf-turnstile-response': '',
});

const submit = (): void => {
    form['cf-turnstile-response'] = turnstile.token.value;

    form.post('/apply', {
        onFinish: () => {
            if (turnstileEnabled.value) {
                turnstile.reset();
                form['cf-turnstile-response'] = '';
            }
        },
    });
};
</script>

<template>
    <Head title="Apply" />

    <PublicLayout>
        <div class="mx-auto max-w-2xl py-10">
            <h1 class="text-3xl font-semibold">Apply to Add My Course</h1>
            <p class="mt-2 text-sm text-muted-foreground">
                If your university or course is not listed, submit this form.
            </p>

            <form class="mt-8 space-y-5" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="applicant_name">Your Name</Label>
                    <Input
                        id="applicant_name"
                        v-model="form.applicant_name"
                        placeholder="e.g. Jane Doe"
                    />
                    <InputError :message="form.errors.applicant_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Your Email</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        placeholder="you@example.com"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="phone">WhatsApp Phone Number</Label>
                    <Input
                        id="phone"
                        v-model="form.phone"
                        placeholder="+1 555 123 4567"
                    />
                    <InputError :message="form.errors.phone" />
                </div>

                <div class="grid gap-2">
                    <Label for="university_name">University Name</Label>
                    <Input
                        id="university_name"
                        v-model="form.university_name"
                        placeholder="e.g. University of Lagos"
                    />
                    <InputError :message="form.errors.university_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="course_name">Course Name</Label>
                    <Input
                        id="course_name"
                        v-model="form.course_name"
                        placeholder="e.g. Computer Science"
                    />
                    <InputError :message="form.errors.course_name" />
                </div>

                <div class="grid gap-2">
                    <Label for="year">Year</Label>
                    <Input
                        id="year"
                        v-model="form.year"
                        placeholder="e.g. 2026"
                    />
                    <InputError :message="form.errors.year" />
                </div>

                <div class="grid gap-2">
                    <Label for="notes">Additional Notes</Label>
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="4"
                        placeholder="Share any helpful details about your request..."
                        class="rounded-md border border-input bg-background px-3 py-2 text-sm"
                    />
                    <InputError :message="form.errors.notes" />
                </div>

                <TurnstileWidget
                    v-if="turnstileEnabled"
                    @verified="turnstile.setToken($event)"
                    @expired="turnstile.reset()"
                    @widget-mounted="turnstile.setWidgetId($event)"
                />
                <InputError
                    v-if="turnstileEnabled"
                    :message="form.errors['cf-turnstile-response']"
                />

                <LoadingButton
                    type="submit"
                    :loading="form.processing"
                    loading-text="Submitting..."
                >
                    Submit Application
                </LoadingButton>
            </form>
        </div>
    </PublicLayout>
</template>

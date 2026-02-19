<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import TurnstileWidget from '@/components/TurnstileWidget.vue';
import { useTurnstile } from '@/composables/useTurnstile';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { redirect } from '@/routes/google';
import { store } from '@/routes/register';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const googleProcessing = ref(false);
const showPassword = ref(false);
const showPasswordConfirmation = ref(false);
const turnstileEnabled = computed(() => Boolean(page.props.turnstileEnabled));
const turnstile = useTurnstile();

const startGoogleSignIn = (): void => {
    if (googleProcessing.value) {
        return;
    }

    googleProcessing.value = true;
    window.location.assign(redirect.url());
};

const resetTurnstile = (): void => {
    if (turnstileEnabled.value) {
        turnstile.reset();
    }
};
</script>

<template>
    <AuthBase
        title="Create an account"
        description="Enter your details below to create your account"
    >
        <Head title="Register" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            :transform="
                (data) => ({
                    ...data,
                    'cf-turnstile-response': turnstile.token.value,
                })
            "
            @finish="resetTurnstile"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <button
                type="button"
                class="inline-flex w-full items-center justify-center gap-2 rounded-md border border-border px-4 py-2 text-sm font-medium hover:bg-accent"
                :disabled="processing || googleProcessing"
                @click="startGoogleSignIn"
            >
                <i class="fa-brands fa-google" />
                <Spinner v-if="googleProcessing" />
                <span>{{
                    googleProcessing ? 'Processing...' : 'Sign in with Google'
                }}</span>
            </button>

            <div class="flex items-center gap-3 text-xs text-muted-foreground">
                <span class="h-px flex-1 bg-border" />
                OR
                <span class="h-px flex-1 bg-border" />
            </div>

            <p class="text-center text-sm text-muted-foreground">
                Enter your details below to create your account
            </p>

            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="Full name"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password">Password</Label>
                    <div class="relative">
                        <Input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            required
                            :tabindex="3"
                            autocomplete="new-password"
                            name="password"
                            placeholder="Password"
                            class="pr-10"
                        />
                        <button
                            type="button"
                            class="absolute inset-y-0 right-0 inline-flex w-10 items-center justify-center text-muted-foreground hover:text-foreground"
                            :aria-label="
                                showPassword ? 'Hide password' : 'Show password'
                            "
                            @click="showPassword = !showPassword"
                        >
                            <i
                                :class="
                                    showPassword
                                        ? 'fa-regular fa-eye-slash'
                                        : 'fa-regular fa-eye'
                                "
                            />
                        </button>
                    </div>
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirm password</Label>
                    <div class="relative">
                        <Input
                            id="password_confirmation"
                            :type="
                                showPasswordConfirmation ? 'text' : 'password'
                            "
                            required
                            :tabindex="4"
                            autocomplete="new-password"
                            name="password_confirmation"
                            placeholder="Confirm password"
                            class="pr-10"
                        />
                        <button
                            type="button"
                            class="absolute inset-y-0 right-0 inline-flex w-10 items-center justify-center text-muted-foreground hover:text-foreground"
                            :aria-label="
                                showPasswordConfirmation
                                    ? 'Hide password confirmation'
                                    : 'Show password confirmation'
                            "
                            @click="
                                showPasswordConfirmation =
                                    !showPasswordConfirmation
                            "
                        >
                            <i
                                :class="
                                    showPasswordConfirmation
                                        ? 'fa-regular fa-eye-slash'
                                        : 'fa-regular fa-eye'
                                "
                            />
                        </button>
                    </div>
                    <InputError :message="errors.password_confirmation" />
                </div>

                <TurnstileWidget
                    v-if="turnstileEnabled"
                    @verified="turnstile.setToken($event)"
                    @expired="turnstile.reset()"
                    @widget-mounted="turnstile.setWidgetId($event)"
                />
                <InputError
                    v-if="turnstileEnabled"
                    :message="errors['cf-turnstile-response']"
                />

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    tabindex="5"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <Spinner v-if="processing" />
                    Create account
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                Already have an account?
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="6"
                    >Log in</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>

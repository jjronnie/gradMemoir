<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import TurnstileWidget from '@/components/TurnstileWidget.vue';
import { useTurnstile } from '@/composables/useTurnstile';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { redirect } from '@/routes/google';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();

const page = usePage();
const googleProcessing = ref(false);
const showPassword = ref(false);
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
        title="Log in to your account"
        description="Enter your email and password below to log in"
    >
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>
        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            :transform="
                (data) => ({
                    ...data,
                    turnstile_token: turnstile.token.value,
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
                Enter your details below to sign in
            </p>

            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email">Email address</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@example.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password">Password</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm"
                            :tabindex="5"
                        >
                            Forgot password?
                        </TextLink>
                    </div>
                    <div class="relative">
                        <Input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            name="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
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

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>Remember me</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    Log in
                </Button>
            </div>

            <TurnstileWidget
                v-if="turnstileEnabled"
                @verified="turnstile.setToken($event)"
                @expired="turnstile.reset()"
                @widget-mounted="turnstile.setWidgetId($event)"
            />
            <InputError v-if="turnstileEnabled" :message="errors.turnstile" />

            <div
                class="text-center text-sm text-muted-foreground"
                v-if="canRegister"
            >
                Don't have an account?
                <TextLink :href="register()" :tabindex="5">Sign up</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>

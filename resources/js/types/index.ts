export * from './auth';
export * from './classmemoir';
export * from './navigation';
export * from './ui';

import type { Auth } from './auth';

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    appName: string;
    auth: Auth;
    sidebarOpen: boolean;
    turnstileEnabled?: boolean;
    turnstileSiteKey?: string;
    flash?: {
        success?: string | null;
        error?: string | null;
    };
    [key: string]: unknown;
};

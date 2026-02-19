export type User = {
    id: number;
    name: string;
    email: string;
    username: string;
    website?: string | null;
    is_verified?: boolean;
    avatar?: string;
    status: 'active' | 'banned' | 'suspended';
    roles: string[];
    onboarding_completed: boolean;
    university_id: number | null;
    course_id: number | null;
    course_year_id: number | null;
    course_year_slug?: string | null;
    course_year_url?: string | null;
    profile_url?: string | null;
    photo_count?: number | null;
    photo_limit?: number | null;
    photo_slots_remaining?: number | null;
    email_verified_at: string | null;
    created_at?: string;
    updated_at?: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User | null;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};

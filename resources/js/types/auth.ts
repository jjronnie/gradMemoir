export type User = {
    id: number;
    name: string;
    nickname?: string | null;
    email: string;
    username: string;
    bio?: string | null;
    profession?: string | null;
    quote?: string | null;
    location?: string | null;
    phone?: string | null;
    facebook_username?: string | null;
    x_username?: string | null;
    tiktok_username?: string | null;
    instagram_username?: string | null;
    email_public?: string | null;
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

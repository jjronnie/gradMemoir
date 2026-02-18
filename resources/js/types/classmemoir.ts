export type University = {
    id: number;
    name: string;
    slug: string;
    location: string;
    media?: Array<{
        original_url?: string;
        preview_url?: string;
        conversions?: Record<string, string>;
    }>;
    courses_count?: number;
    active_students_count?: number;
    [key: string]: unknown;
};

export type Course = {
    id: number;
    name: string;
    short_name: string;
    nickname?: string | null;
    year: string;
    slug: string;
    shortcode: string;
    university_id: number;
    university?: University;
    active_students_count?: number;
    [key: string]: unknown;
};

export type Post = {
    id: number;
    body: string | null;
    published: boolean;
    published_at: string | null;
    media?: Array<Record<string, unknown>>;
    [key: string]: unknown;
};

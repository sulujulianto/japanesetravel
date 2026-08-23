import type { PageProps } from '@inertiajs/core';

export type Locale = 'en' | 'id';

export interface AuthenticatedUser {
    id: number;
    username: string;
    email: string;
    role: string;
}

export interface SharedPageProps extends PageProps {
    app: {
        name: string;
    };
    auth: {
        admin: AuthenticatedUser | null;
        user: AuthenticatedUser | null;
    };
    locale: Locale;
}

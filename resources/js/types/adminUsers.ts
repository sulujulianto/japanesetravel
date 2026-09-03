import type { AdminShellCopy, AdminShellRoutes } from './admin';
import type { Pagination } from './pagination';

export interface AdminUserListCopy extends AdminShellCopy {
    actions: string;
    addresses: string;
    all: string;
    applyFilters: string;
    description: string;
    detail: string;
    emptyDescription: string;
    emptyTitle: string;
    eyebrow: string;
    filtersDescription: string;
    filtersTitle: string;
    fullName: string;
    joined: string;
    next: string;
    ordersCount: string;
    previous: string;
    reset: string;
    resultsDescription: string;
    resultsTitle: string;
    search: string;
    searchPlaceholder: string;
    title: string;
    username: string;
    verification: string;
}

export interface AdminUserFilters {
    search: string;
    verification: string;
}

export interface AdminUserFilterOption {
    label: string;
    value: string;
}

export interface AdminUserListItem {
    addressCount: number;
    email: string;
    fullName: string | null;
    id: number;
    joinedAt: string;
    orderCount: number;
    url: string;
    username: string;
    verification: {
        label: string;
        verified: boolean;
    };
}

export interface AdminUsersResult {
    data: AdminUserListItem[];
    pagination: Pagination;
}

export interface AdminUserOptions {
    verificationStatuses: AdminUserFilterOption[];
}

export type AdminUserListRoutes = AdminShellRoutes;

export interface AdminUserDetailCopy extends AdminShellCopy {
    accountDescription: string;
    accountTitle: string;
    addressesDescription: string;
    addressesTitle: string;
    back: string;
    cityProvince: string;
    country: string;
    defaultAddress: string;
    email: string;
    eyebrow: string;
    fullName: string;
    joined: string;
    lastSeen: string;
    noAddresses: string;
    notProvided: string;
    orderCount: string;
    ordersDescription: string;
    ordersTitle: string;
    phone: string;
    preferredLocale: string;
    privacyNotice: string;
    profileDescription: string;
    profileTitle: string;
    recipient: string;
    spent: string;
    title: string;
    username: string;
    verification: string;
    verifiedAt: string;
    viewOrders: string;
}

export interface AdminUserAddress {
    addressLine1: string;
    addressLine2: string | null;
    city: string;
    country: string;
    countryCode: string;
    id: number;
    isDefault: boolean;
    label: string;
    postalCode: string;
    province: string;
    recipientName: string;
    recipientPhone: string;
}

export interface AdminUserDetail {
    addresses: AdminUserAddress[];
    email: string;
    id: number;
    joinedAt: string;
    lastSeenAt: string;
    orderSummary: {
        count: string;
        spent: string;
    };
    profile: {
        fullName: string | null;
        phone: string | null;
        preferredLocale: {
            label: string;
            value: string;
        } | null;
    };
    username: string;
    verification: {
        label: string;
        verified: boolean;
        verifiedAt: string;
    };
}

export interface AdminUserDetailRoutes extends AdminShellRoutes {
    ordersForUser: string;
}

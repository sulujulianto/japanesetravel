import type { AdminShellCopy, AdminShellRoutes } from './admin';
import type { Pagination } from './pagination';

export interface AdminOrderCopy extends AdminShellCopy {
    actions: string;
    all: string;
    applyFilters: string;
    customer: string;
    date: string;
    dateFrom: string;
    dateTo: string;
    description: string;
    detail: string;
    emptyDescription: string;
    emptyTitle: string;
    eyebrow: string;
    filtersDescription: string;
    filtersTitle: string;
    next: string;
    noPayment: string;
    order: string;
    payment: string;
    paymentStatus: string;
    previous: string;
    reset: string;
    resultsDescription: string;
    resultsTitle: string;
    search: string;
    searchPlaceholder: string;
    status: string;
    title: string;
    total: string;
}

export interface AdminOrderFilters {
    dateFrom: string;
    dateTo: string;
    paymentStatus: string;
    search: string;
    status: string;
}

export interface AdminOrderFilterOption {
    label: string;
    value: string;
}

export interface AdminOrderListItem {
    customer: { email: string; username: string };
    date: string;
    id: number;
    payment: { label: string; status: string } | null;
    reference: string;
    status: { label: string; value: string };
    total: string;
    url: string;
}

export type AdminOrderPagination = Pagination;

export interface AdminOrderOptions {
    orderStatuses: AdminOrderFilterOption[];
    paymentStatuses: AdminOrderFilterOption[];
}

export type AdminOrderRoutes = AdminShellRoutes;

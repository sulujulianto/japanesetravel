import type { AdminShellCopy, AdminShellRoutes } from './admin';
import type { StockStatus } from './adminSouvenirs';
import type { Pagination } from './pagination';

export interface AdminInventoryCopy extends AdminShellCopy {
    add: string;
    adjustment: string;
    amount: string;
    description: string;
    emptyDescription: string;
    emptyTitle: string;
    eyebrow: string;
    filterDescription: string;
    filterLabel: string;
    filterTitle: string;
    next: string;
    previous: string;
    price: string;
    product: string;
    remaining: string;
    reset: string;
    resultsDescription: string;
    resultsTitle: string;
    show: string;
    subtract: string;
    title: string;
}

export interface AdminInventoryFilters {
    threshold: number;
}

export interface AdminInventoryListItem {
    id: number;
    name: string;
    price: string;
    reference: string;
    adjustmentLabel: string;
    deductUrl: string;
    restockUrl: string;
    stock: number;
    stockCount: string;
    stockLabel: string;
    stockStatus: StockStatus;
}

export type AdminInventoryRoutes = AdminShellRoutes;

export interface AdminInventoryResult {
    data: AdminInventoryListItem[];
    pagination: Pagination;
}

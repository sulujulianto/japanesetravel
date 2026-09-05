import type { AdminShellCopy, AdminShellRoutes } from './admin';
import type { StockStatus } from './adminSouvenirs';
import type { Pagination } from './pagination';

export interface AdminInventoryCopy extends AdminShellCopy {
    add: string;
    adjustment: string;
    actor: string;
    amount: string;
    description: string;
    emptyDescription: string;
    emptyTitle: string;
    eyebrow: string;
    filterDescription: string;
    filterLabel: string;
    filterTitle: string;
    historyDescription: string;
    historyEmpty: string;
    historyTitle: string;
    next: string;
    order: string;
    previous: string;
    price: string;
    product: string;
    quantityChange: string;
    recordedAt: string;
    reference: string;
    remaining: string;
    reset: string;
    resultsDescription: string;
    resultsTitle: string;
    show: string;
    subtract: string;
    stockChange: string;
    title: string;
    type: string;
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

export interface AdminInventoryMovement {
    actor: string;
    createdAt: string;
    id: number;
    orderReference: string;
    productName: string;
    quantityDelta: number;
    quantityDeltaLabel: string;
    reference: string;
    stockAfter: string;
    stockBefore: string;
    type: string;
    typeLabel: string;
}

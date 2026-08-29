import type { AdminShellCopy, AdminShellRoutes } from './admin';
import type { Pagination } from './pagination';

export type StockStatus = 'available' | 'low' | 'out-of-stock';

export interface AdminSouvenirsCopy extends AdminShellCopy {
    actions: string;
    add: string;
    delete: string;
    deleting: string;
    description: string;
    edit: string;
    emptyDescription: string;
    emptyTitle: string;
    eyebrow: string;
    image: string;
    name: string;
    next: string;
    previous: string;
    price: string;
    resultsDescription: string;
    resultsTitle: string;
    stock: string;
    title: string;
}

export interface AdminSouvenirListItem {
    deleteConfirmation: string;
    deleteUrl: string;
    editUrl: string;
    id: number;
    imageUrl: string;
    name: string;
    price: string;
    reference: string;
    stock: number;
    stockCount: string;
    stockLabel: string;
    stockStatus: StockStatus;
}

export interface AdminSouvenirsRoutes extends AdminShellRoutes {
    createSouvenir: string;
}

export interface AdminSouvenirsResult {
    data: AdminSouvenirListItem[];
    pagination: Pagination;
}

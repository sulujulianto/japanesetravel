import type { AdminShellCopy, AdminShellRoutes } from './admin';
import type { Pagination } from './pagination';

export interface AdminPlacesCopy extends AdminShellCopy {
    actions: string;
    add: string;
    address: string;
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
    resultsDescription: string;
    resultsTitle: string;
    title: string;
}

export interface AdminPlaceListItem {
    address: string;
    deleteConfirmation: string;
    deleteUrl: string;
    editUrl: string;
    id: number;
    imageUrl: string;
    name: string;
    reference: string;
}

export interface AdminPlacesRoutes extends AdminShellRoutes {
    createPlace: string;
}

export interface AdminPlacesResult {
    data: AdminPlaceListItem[];
    pagination: Pagination;
}

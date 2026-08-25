export interface PaginationPage {
    active: boolean;
    page: number;
    url: string;
}

export interface Pagination {
    currentPage: number;
    from: number | null;
    lastPage: number;
    nextUrl: string | null;
    pages: PaginationPage[];
    previousUrl: string | null;
    summary: string;
    to: number | null;
    total: number;
}

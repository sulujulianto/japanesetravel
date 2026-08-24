import type { AdminShellCopy, AdminShellRoutes } from './admin';

export interface AdminOrderDetailCopy extends AdminShellCopy {
    adminNote: string;
    back: string;
    createdOn: string;
    customer: string;
    emptyItems: string;
    emptyPaymentsDescription: string;
    emptyPaymentsTitle: string;
    eyebrow: string;
    formError: string;
    internalNoteHelp: string;
    itemsDescription: string;
    itemsTitle: string;
    noPayment: string;
    notPaid: string;
    orderNote: string;
    orderTime: string;
    paymentDescription: string;
    paymentTitle: string;
    referenceUnavailable: string;
    save: string;
    saving: string;
    status: string;
    subtotal: string;
    summaryDescription: string;
    summaryTitle: string;
    title: string;
    total: string;
    updateDescription: string;
    updateTitle: string;
}

export interface AdminOrderDetailRoutes extends AdminShellRoutes {
    updateOrder: string;
}

export interface AdminOrderStatusOption {
    label: string;
    value: string;
}

export interface AdminOrderDetailItem {
    id: number;
    imageUrl: string | null;
    name: string;
    quantity: string;
    subtotal: string;
    unitPrice: string;
}

export interface AdminOrderDetailPayment {
    amount: string;
    id: number;
    paidAt: string | null;
    provider: string;
    reference: string | null;
    status: AdminOrderStatusOption;
}

export interface AdminOrderDetail {
    adminNote: string | null;
    createdAt: string;
    customer: {
        email: string;
        username: string;
    };
    id: number;
    items: AdminOrderDetailItem[];
    latestPayment: { label: string; status: string } | null;
    note: string | null;
    payments: AdminOrderDetailPayment[];
    reference: string;
    status: AdminOrderStatusOption;
    total: string;
}

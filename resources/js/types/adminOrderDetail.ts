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
    shippingDescription: string;
    shippingMissing: string;
    shippingTitle: string;
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

export interface AdminOrderShippingAddress {
    addressLine1: string;
    addressLine2: string | null;
    city: string;
    country: string;
    countryCode: string;
    label: string;
    postalCode: string;
    province: string;
    recipientName: string;
    recipientPhone: string;
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
    shippingAddress: AdminOrderShippingAddress | null;
    status: AdminOrderStatusOption;
    total: string;
}

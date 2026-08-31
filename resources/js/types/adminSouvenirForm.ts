import type { AdminShellCopy, AdminShellRoutes } from './admin';

export type AdminSouvenirFormMode = 'create' | 'edit';

export interface AdminSouvenirFormCopy extends AdminShellCopy {
    cancel: string;
    currentImage: string;
    description: string;
    descriptionEn: string;
    descriptionHelp: string;
    descriptionId: string;
    enContentDescription: string;
    enContentTitle: string;
    eyebrow: string;
    formError: string;
    idContentDescription: string;
    idContentTitle: string;
    media: string;
    mediaDescription: string;
    mediaHelp: string;
    nameEn: string;
    nameId: string;
    price: string;
    priceHelp: string;
    salesDescription: string;
    salesTitle: string;
    saving: string;
    stock: string;
    stockHelp: string;
    submit: string;
    title: string;
    uploadImage: string;
}

export interface AdminSouvenirFormInitialValues {
    currentImageAlt: string | null;
    currentImageUrl: string | null;
    descriptionEn: string;
    descriptionId: string;
    imageRequired: boolean;
    nameEn: string;
    nameId: string;
    price: string;
    stock: string;
}

export interface AdminSouvenirFormRoutes extends AdminShellRoutes {
    submitSouvenir: string;
}

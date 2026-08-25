import type { AdminShellCopy, AdminShellRoutes } from './admin';

export type AdminPlaceFormMode = 'create' | 'edit';

export interface AdminPlaceFormCopy extends AdminShellCopy {
    address: string;
    amPm: string;
    cancel: string;
    clearSchedule: string;
    closingTime: string;
    currentImage: string;
    description: string;
    descriptionEn: string;
    descriptionHelp: string;
    descriptionId: string;
    enContentDescription: string;
    enContentTitle: string;
    eyebrow: string;
    facilities: string;
    facilitiesHelp: string;
    formError: string;
    fromDay: string;
    hour: string;
    idContentDescription: string;
    idContentTitle: string;
    legacyScheduleHelp: string;
    media: string;
    mediaDescription: string;
    mediaHelp: string;
    minute: string;
    nameEn: string;
    nameId: string;
    openDays: string;
    openingTime: string;
    period: string;
    saving: string;
    scheduleHelp: string;
    selectDay: string;
    submit: string;
    title: string;
    toDay: string;
    uploadImage: string;
    visitDescription: string;
    visitTitle: string;
}

export interface AdminPlaceFormInitialValues {
    address: string;
    currentImageAlt: string | null;
    currentImageUrl: string | null;
    descriptionEn: string;
    descriptionId: string;
    facilities: string;
    hasSchedule: boolean;
    legacyScheduleLabel: string | null;
    nameEn: string;
    nameId: string;
}

export interface AdminPlaceScheduleValues {
    open_day_end: string;
    open_day_start: string;
    open_time_end_hour: string;
    open_time_end_minute: string;
    open_time_end_period: string;
    open_time_start_hour: string;
    open_time_start_minute: string;
    open_time_start_period: string;
}

export interface AdminPlaceScheduleOptions {
    days: Array<{ label: string; value: string }>;
    hours: number[];
    minutes: string[];
    periods: string[];
}

export interface AdminPlaceFormRoutes extends AdminShellRoutes {
    submitPlace: string;
}

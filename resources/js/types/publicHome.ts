import type { PublicShellCopy, PublicShellRoutes } from './public';

export interface PublicHomeCopy extends PublicShellCopy {
    allDestinations: string;
    emptyDestinations: string;
    eyebrow: string;
    featuredDescription: string;
    featuredEyebrow: string;
    featuredTitle: string;
    heroDescription: string;
    heroTitle: string;
    pageTitle: string;
    portfolioNote: string;
    primaryCta: string;
    proofItems: string[];
    quickLookDescription: string;
    quickLookTitle: string;
    rating: string;
    reviews: string;
    secondaryCta: string;
    summaryPlaces: string;
    summarySouvenirs: string;
    summaryTitle: string;
    souvenirCta: string;
    souvenirDescription: string;
    souvenirEyebrow: string;
    souvenirTitle: string;
}

export type PublicHomeRoutes = PublicShellRoutes;

export interface FeaturedPlace {
    id: number;
    name: string;
    address: string;
    excerpt: string;
    imageUrl: string;
    initial: string;
    rating: string;
    reviewCount: number;
    reviewLabel: string;
    showUrl: string;
}

export interface PublicHomeSummary {
    places: number;
    reviews: number;
    souvenirs: number;
}

export interface AdminShellCopy {
    closeMenu: string;
    dashboard: string;
    logout: string;
    lowStock: string;
    menu: string;
    navigation: string;
    orders: string;
    places: string;
    souvenirs: string;
    theme: string;
    themeDark: string;
    themeLight: string;
    themeToggle: string;
    users: string;
    useDarkTheme: string;
    useLightTheme: string;
    viewSite: string;
    workspace: string;
    workspaceDescription: string;
}

export interface AdminDashboardCopy extends AdminShellCopy {
    allStockSafe: string;
    allStockSafeDescription: string;
    chartsError: string;
    checkStock: string;
    criticalStockDescription: string;
    criticalStockTitle: string;
    customer: string;
    description: string;
    detail: string;
    eyebrow: string;
    loadingCharts: string;
    manageOrders: string;
    metricsDescription: string;
    metricsTitle: string;
    noOrdersChart: string;
    noRecentOrders: string;
    noRevenueChart: string;
    noSales: string;
    order: string;
    ordersChartDescription: string;
    ordersChartTitle: string;
    payment: string;
    recentOrdersDescription: string;
    recentOrdersTitle: string;
    remainingStock: string;
    revenueChartDescription: string;
    revenueChartTitle: string;
    sold: string;
    status: string;
    title: string;
    topSouvenirsDescription: string;
    topSouvenirsTitle: string;
    total: string;
    view: string;
    viewAllOrders: string;
}

export interface AdminShellRoutes {
    dashboard: string;
    home: string;
    localeEn: string;
    localeId: string;
    logout: string;
    lowStock: string;
    orders: string;
    places: string;
    souvenirs: string;
    users: string;
}

export interface AdminRoutes extends AdminShellRoutes {
    charts: string;
}

export interface DashboardMetric {
    description: string;
    key: string;
    label: string;
    value: string;
}

export interface LowStockItem {
    id: number;
    name: string;
    stock: number;
    stockLabel: string;
}

export interface RecentOrder {
    customer: { email: string; username: string };
    id: number;
    payment: { label: string; status: string } | null;
    status: { label: string; value: string };
    total: string;
    url: string;
}

export interface ChartSeries {
    labels: string[];
    series: number[];
}

export interface DashboardCharts {
    orders: ChartSeries;
    revenue: ChartSeries;
    topSouvenirs: Array<{ name: string; total: number }>;
}

export type NavigationKey = 'dashboard' | 'low-stock' | 'orders' | 'places' | 'souvenirs' | 'users';

export interface NavigationItem {
    href: string;
    key: NavigationKey;
    label: string;
}

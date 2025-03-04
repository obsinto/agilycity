/**
 * Dashboard Configuration
 * Arquivo: components/dashboard/config.js
 */

export const apiEndpoints = {
    filter: '/dashboard/filter',
    secretaryDetails: (id) => `/dashboard/secretary/${id}/details`
};

// Add other configuration settings as needed
export const chartColors = {
    primary: '#4F46E5',
    secondary: '#10B981',
    danger: '#EF4444',
    warning: '#F59E0B',
    info: '#3B82F6',
    light: '#F3F4F6'
};

export const defaultSettings = {
    animationDuration: 500,
    loadingText: 'Carregando dados...',
    noDataText: 'Sem dados disponíveis'
};

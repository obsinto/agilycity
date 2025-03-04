// resources/js/dashboard/utils.js

/**
 * Format currency values
 * @param {number} value - Value to format
 * @returns {string} Formatted currency string
 */
export function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

/**
 * Format percentage values
 * @param {number} value - Value to format
 * @returns {string} Formatted percentage string
 */
export function formatPercentage(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'percent',
        minimumFractionDigits: 1,
        maximumFractionDigits: 1
    }).format(value / 100);
}

/**
 * Format date values
 * @param {string|Date} date - Date to format
 * @returns {string} Formatted date string
 */
export function formatDate(date) {
    return new Intl.DateTimeFormat('pt-BR').format(new Date(date));
}

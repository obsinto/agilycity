/**
 * Utilitários para o Dashboard de Secretários
 * resources/js/components/dashboard/secretary/utils.js
 */

/**
 * Formata um valor numérico como moeda brasileira (R$)
 * @param {number} value - Valor a ser formatado
 * @returns {string} - Valor formatado (ex: "R$ 1.234,56")
 */
export function formatCurrency(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(value);
}

/**
 * Atualiza a variação mensal nos cards do dashboard
 * @param {number} currentMonth - Valor do mês atual
 * @param {number} lastMonth - Valor do mês anterior
 * @param {string} selector - Seletor do elemento HTML para atualizar
 */
export function updateMonthlyVariation(currentMonth, lastMonth, selector = '.month-variation') {
    const variation = lastMonth !== 0 ? ((currentMonth - lastMonth) / lastMonth) * 100 : 0;
    const variationElement = $(selector);

    variationElement.html(`
        <span class="${variation >= 0 ? 'text-red-500' : 'text-green-500'} flex items-center">
            ${variation >= 0 ?
        '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>' :
        '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>'
    }
            ${Math.abs(variation).toFixed(1)}% em relação ao período anterior
        </span>
    `);
}

/**
 * Exibe um indicador de carregamento
 */
export function showLoading() {
    $('.dashboard-loading').show();
}

/**
 * Esconde o indicador de carregamento
 */
export function hideLoading() {
    $('.dashboard-loading').hide();
}

/**
 * Trunca um texto se for maior que o tamanho máximo
 * @param {string} text - Texto a ser truncado
 * @param {number} maxLength - Tamanho máximo
 * @returns {string} - Texto truncado com "..." se necessário
 */
export function truncateText(text, maxLength = 25) {
    if (!text) return '';
    return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
}

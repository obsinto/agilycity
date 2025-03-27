/**
 * API integration module para o Dashboard de Líderes de Setor
 * resources/js/components/dashboard/sector/api.js
 */
import $ from 'jquery';
import {apiEndpoints} from './config';

/**
 * Busca dados do dashboard de setor com base nos filtros aplicados
 * @param {Object} filters - Objeto com filtros a serem aplicados
 * @returns {Promise} - Promise da requisição AJAX
 */
export function fetchSectorDashboardData(filters = {}) {
    // Processar o date_range para formato apropriado para a API
    let apiFilters = {...filters};

    // Se houver um date_range no formato DD/MM/YYYY - DD/MM/YYYY, convertemos para o que a API espera
    if (filters.date_range) {
        const dates = filters.date_range.split(' - ');
        if (dates.length === 2) {
            // Converter de DD/MM/YYYY para YYYY-MM-DD que a API espera
            const startParts = dates[0].split('/');
            const endParts = dates[1].split('/');

            if (startParts.length === 3 && endParts.length === 3) {
                apiFilters.start_date = `${startParts[2]}-${startParts[1]}-${startParts[0]}`;
                apiFilters.end_date = `${endParts[2]}-${endParts[1]}-${endParts[0]}`;

                // Remover o date_range original já que estamos usando start_date e end_date
                delete apiFilters.date_range;
            }
        }
    }

    // Log para depuração
    console.log('Enviando filtros para API do setor:', apiFilters);

    return $.ajax({
        url: apiEndpoints.filter,
        method: 'GET',
        data: apiFilters,
        dataType: 'json'
    });
}

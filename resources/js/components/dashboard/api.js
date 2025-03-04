/**
 * API integration module
 * Arquivo: components/dashboard/api.js
 */
import $ from 'jquery';
import {apiEndpoints} from './config';

/**
 * Busca dados do dashboard com base nos filtros aplicados
 * @param {Object} filters - Objeto com filtros a serem aplicados
 * @returns {Promise} - Promise da requisição AJAX
 */
export function fetchDashboardData(filters = {}) {
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
    console.log('Enviando filtros para API:', apiFilters);

    return $.ajax({
        url: apiEndpoints.filter,
        method: 'GET',
        data: apiFilters,
        dataType: 'json'
    });
}

/**
 * Busca detalhes de uma secretaria específica
 * @param {number} secretaryId - ID da secretaria
 * @param {Object} filters - Filtros adicionais (como período de data)
 * @returns {Promise} - Promise da requisição AJAX
 */
export function fetchSecretaryDetails(secretaryId, filters = {}) {
    // Processar o date_range da mesma forma
    let apiFilters = {...filters};

    if (filters.date_range) {
        const dates = filters.date_range.split(' - ');
        if (dates.length === 2) {
            const startParts = dates[0].split('/');
            const endParts = dates[1].split('/');

            if (startParts.length === 3 && endParts.length === 3) {
                apiFilters.start_date = `${startParts[2]}-${startParts[1]}-${startParts[0]}`;
                apiFilters.end_date = `${endParts[2]}-${endParts[1]}-${endParts[0]}`;
                delete apiFilters.date_range;
            }
        }
    }

    return $.ajax({
        url: apiEndpoints.secretaryDetails(secretaryId),
        method: 'GET',
        data: apiFilters,
        dataType: 'json'
    });
}

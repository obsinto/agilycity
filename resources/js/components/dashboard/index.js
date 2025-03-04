import $ from 'jquery';
import * as echarts from 'echarts';
import {loadDashboardData} from './dashboard';
import {initializeDateRangePicker} from './datepicker';

$(document).ready(function () {
    // Inicializa o date range picker
    initializeDateRangePicker();

    // Escuta pelo evento dateRangeChanged disparado pelo datepicker
    document.addEventListener('dateRangeChanged', function (event) {
        // Quando o daterange mudar, carregamos os dados com os filtros atualizados
        loadDashboardData(event.detail.filters);

        // Feedback visual - opcional
        $('.dashboard-filter-active').show().delay(1500).fadeOut();
    });

    // Listeners para filtros (select boxes)
    $('#secretary, #department, #expenseType').on('change', function () {
        const filters = {
            date_range: $('#dateRange').val(),
            secretary_id: $('#secretary').val(),
            department_id: $('#department').val(),
            expense_type: $('#expenseType').val()
        };
        loadDashboardData(filters);
    });

    // Limpar Filtros
    window.clearFilters = function () {
        // Importante: Para o dateRange, precisamos garantir que o plugin seja resetado também
        $('#dateRange').val('');
        // Forçar a atualização do daterangepicker
        if ($('#dateRange').data('daterangepicker')) {
            $('#dateRange').data('daterangepicker').setStartDate(moment());
            $('#dateRange').data('daterangepicker').setEndDate(moment());
        }

        $('#secretary, #department, #expenseType').val('');
        loadDashboardData();
    };

    // Setup de clicks nos gráficos interativos
    setupChartInteractions();

    // Carregamento inicial - agora incluindo o valor inicial do date range
    const initialFilters = {
        date_range: $('#dateRange').val(),
        secretary_id: $('#secretary').val(),
        department_id: $('#department').val(),
        expense_type: $('#expenseType').val()
    };
    loadDashboardData(initialFilters);
});

// Configura interações com os gráficos
function setupChartInteractions() {
    // Interação com Treemap
    const treemapDom = document.getElementById('treemap');
    if (treemapDom) {
        echarts.getInstanceByDom(treemapDom)?.on('click', function (params) {
            if (params.data && params.data.id) {
                // Verifica se é uma secretaria (tem children) ou um departamento
                const filters = {};
                if (params.data.children && params.data.children.length > 0) {
                    filters.secretary_id = params.data.id;
                    $('#secretary').val(params.data.id);
                } else {
                    filters.department_id = params.data.id;
                    $('#department').val(params.data.id);
                }

                // Importante: incluir o filtro de data atual
                filters.date_range = $('#dateRange').val();
                filters.expense_type = $('#expenseType').val();

                loadDashboardData(filters);
            }
        });

        // Duplo clique para limpar filtros
        echarts.getInstanceByDom(treemapDom)?.on('dblclick', function () {
            clearFilters();
        });
    }

    // Interação com gráfico de tipos de despesa
    const pieChartDom = document.getElementById('expenseTypePie');
    if (pieChartDom) {
        echarts.getInstanceByDom(pieChartDom)?.on('click', function (params) {
            if (params.data && params.data.name) {
                const currentExpenseType = $('#expenseType').val();

                // Se já estiver filtrado pelo mesmo tipo, remove o filtro
                if (currentExpenseType === params.data.name) {
                    $('#expenseType').val('');
                } else {
                    $('#expenseType').val(params.data.name);
                }

                loadDashboardData({
                    date_range: $('#dateRange').val(),
                    secretary_id: $('#secretary').val(),
                    department_id: $('#department').val(),
                    expense_type: $('#expenseType').val()
                });
            }
        });
    }
}

// Função para mostrar detalhes de departamento (chamada diretamente do HTML)
window.showDepartmentDetails = function (secretaryId) {
    $('#secretary').val(secretaryId);
    loadDashboardData({
        date_range: $('#dateRange').val(),
        secretary_id: secretaryId,
        department_id: $('#department').val(),
        expense_type: $('#expenseType').val()
    });
};

import $ from 'jquery';
import {fetchDashboardData} from './api';
import {renderBarChart, renderExpenseTypePie, renderGaugeChart, renderTimeline, renderTreemap} from './charts';
import {formatCurrency} from './utils';
import * as echarts from 'echarts';

// Importe a função para inicializar o range picker
import {initializeDateRangePicker} from './datepicker';

$(document).ready(function () {
    // Inicializa o date range picker
    initializeDateRangePicker();

    // Carrega dados do dashboard
    function loadDashboardData(filters = {}) {
        // Exibe algum loading caso necessário
        $('.dashboard-loading').show();

        fetchDashboardData(filters)
            .done(function (response) {
                // Atualiza cards
                $('#totalExpenses').text(formatCurrency(response.totalExpenses));
                $('#currentMonthExpenses').text(formatCurrency(response.currentMonthExpenses));

                // Atualiza variação mensal
                updateMonthlyVariation(response.currentMonthExpenses, response.lastMonthExpenses);

                // Renderiza gráficos
                renderTreemap('treemap', response.hierarchicalData);
                renderExpenseTypePie('expenseTypePie', response.expenseTypeData);
                renderTimeline('timeline', response.monthlyExpenses, response.series);

                // Gráfico de Barras
                if (response.departmentsData && response.departmentsData.length > 0) {
                    const barCategories = response.departmentsData.map(item => item.name);
                    const barValues = response.departmentsData.map(item => item.total);
                    renderBarChart('barChart', barCategories, barValues);
                }

                // Gráfico de Velocímetro (Gauge)
                const capValue = response.capValue || response.monthlyBudget;
                renderGaugeChart('gaugeChart', response.currentMonthExpenses, capValue);

                // Oculta o loading
                $('.dashboard-loading').hide();
            })
            .fail(function (err) {
                console.error("Erro ao carregar dados do dashboard", err);
                // Oculta o loading e mostra erro
                $('.dashboard-loading').hide();
                $('.dashboard-error').show().delay(3000).fadeOut();
            });
    }

    // Atualiza o texto da variação mensal
    function updateMonthlyVariation(currentMonth, lastMonth) {
        const variation = lastMonth !== 0 ? ((currentMonth - lastMonth) / lastMonth) * 100 : 0;
        const variationElement = $('.month-variation');

        variationElement.html(`
            <span class="${variation >= 0 ? 'text-red-500' : 'text-green-500'} flex items-center">
                ${variation >= 0 ? '↑' : '↓'}
                ${Math.abs(variation).toFixed(1)}% em relação ao mês anterior
            </span>
        `);
    }

    // Listeners para filtros
    $('#dateRange, #secretary, #department, #expenseType').on('change', function () {
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
        $('#dateRange, #secretary, #department, #expenseType').val('');
        loadDashboardData();
    };

    // Clique no Treemap
    const treemapDom = document.getElementById('treemap');
    if (treemapDom) {
        const treemapChart = echarts.init(treemapDom);
        treemapChart.on('click', function (params) {
            if (params.data && params.data.id) {
                const filters = {};
                if (params.data.children && params.data.children.length > 0) {
                    filters.secretary_id = params.data.id;
                } else {
                    filters.department_id = params.data.id;
                }
                loadDashboardData(filters);
            }
        });
    }

    // Carregamento inicial
    loadDashboardData();
});

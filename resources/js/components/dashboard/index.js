import $ from 'jquery';
import {fetchDashboardData} from './api';
import {renderBarChart, renderExpenseTypePie, renderGaugeChart, renderTimeline, renderTreemap} from './charts';
import {formatCurrency} from './utils';
import * as echarts from 'echarts';

// Importa a função para inicializar o date range picker
import {initializeDateRangePicker} from './datepicker';

$(document).ready(function () {
    // Inicializa o date range picker
    initializeDateRangePicker();

    // NOVO: Escuta pelo evento dateRangeChanged disparado pelo datepicker
    document.addEventListener('dateRangeChanged', function (event) {
        // Quando o daterange mudar, carregamos os dados com os filtros atualizados
        loadDashboardData(event.detail.filters);

        // Feedback visual - opcional
        $('.dashboard-filter-active').show().delay(1500).fadeOut();
    });

    // Carrega dados do dashboard
    function loadDashboardData(filters = {}) {
        // Exibe algum loading caso necessário
        $('.dashboard-loading').show();

        // Adicionar log para debug
        console.log('Carregando dashboard com filtros:', filters);

        fetchDashboardData(filters)
            .done(function (response) {
                // Atualiza cards
                $('#totalExpenses').text(formatCurrency(response.totalExpenses));
                $('#currentMonthExpenses').text(formatCurrency(response.currentMonthExpenses));

                // Atualiza variação mensal
                updateMonthlyVariation(response.currentMonthExpenses, response.lastMonthExpenses);

                // Atualiza cards de secretaria e departamento com maior gasto
                if (response.secretaries && response.secretaries.length > 0) {
                    // Ordena por total e pega o primeiro
                    const topSecretary = [...response.secretaries].sort((a, b) => b.total - a.total)[0];
                    $('#topSecretaryName').text(topSecretary.name);
                    $('#topSecretaryAmount').text(formatCurrency(topSecretary.total));
                }

                if (response.departmentsData && response.departmentsData.length > 0) {
                    // Já vem ordenado do backend
                    const topDepartment = response.departmentsData[0];
                    $('#topDepartmentName').text(topDepartment.name);
                    $('#topDepartmentAmount').text(formatCurrency(topDepartment.total));
                }

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
                ${variation >= 0 ?
            '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>' :
            '<svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>'
        }
                ${Math.abs(variation).toFixed(1)}% em relação ao mês anterior
            </span>
        `);
    }

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

    // Clique no Treemap
    const treemapDom = document.getElementById('treemap');
    if (treemapDom) {
        const treemapChart = echarts.init(treemapDom);
        treemapChart.on('click', function (params) {
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
        treemapChart.on('dblclick', function () {
            clearFilters();
        });
    }

    // Clique no gráfico de tipos de despesa
    const pieChartDom = document.getElementById('expenseTypePie');
    if (pieChartDom) {
        const pieChart = echarts.init(pieChartDom);
        pieChart.on('click', function (params) {
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

    // Clique na tabela detalhada
    window.showDepartmentDetails = function (secretaryId) {
        $('#secretary').val(secretaryId);
        loadDashboardData({
            date_range: $('#dateRange').val(),
            secretary_id: secretaryId,
            department_id: $('#department').val(),
            expense_type: $('#expenseType').val()
        });
    };

    // Carregamento inicial - agora incluindo o valor inicial do date range
    const initialFilters = {
        date_range: $('#dateRange').val(),
        secretary_id: $('#secretary').val(),
        department_id: $('#department').val(),
        expense_type: $('#expenseType').val()
    };
    loadDashboardData(initialFilters);
});

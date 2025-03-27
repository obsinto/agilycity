/**
 * Arquivo principal do Dashboard para Secretários
 * resources/js/components/dashboard/secretary/index.js
 */
import $ from 'jquery';
import * as echarts from 'echarts';
import {fetchSecretaryDashboardData} from './api';
import {renderBarChart, renderExpenseTypePie, renderTimeline, renderTreemap} from './charts';
import {formatCurrency, hideLoading, showLoading, updateMonthlyVariation} from './utils';
import {initializeDateRangePicker} from './datepicker';

// Charts instances
let treemapChart = null;
let expenseTypePieChart = null;
let timelineChart = null;
let barChart = null;

/**
 * Carrega os dados do dashboard de secretário
 * @param {Object} filters - Filtros para a API
 */
function loadSecretaryDashboardData(filters = {}) {
    // Exibe loading
    showLoading();

    console.log('Carregando dashboard de secretário com filtros:', filters);

    fetchSecretaryDashboardData(filters)
        .done(function (response) {
            // Atualiza cards principais
            $('#totalExpenses').text(formatCurrency(response.totalExpenses));
            $('#currentMonthExpenses').text(formatCurrency(response.currentMonthExpenses));

            // Atualiza variação mensal
            updateMonthlyVariation(response.currentMonthExpenses, response.lastMonthExpenses);

            // Atualiza departamento com maior gasto
            if (response.departmentsData && response.departmentsData.length > 0) {
                // Já vem ordenado do backend
                const topDepartment = response.departmentsData[0];
                $('#topDepartmentName').text(topDepartment.name);
                $('#topDepartmentAmount').text(formatCurrency(topDepartment.total));
            }

            // Renderiza/atualiza gráficos
            treemapChart = renderTreemap('treemap', response.hierarchicalData);
            expenseTypePieChart = renderExpenseTypePie('expenseTypePie', response.expenseTypeData);
            timelineChart = renderTimeline('timeline', response.monthlyExpenses, response.series);

            // Gráfico de Barras (Departamentos)
            if (response.departmentsData && response.departmentsData.length > 0) {
                const barCategories = response.departmentsData.map(item => item.name);
                const barValues = response.departmentsData.map(item => item.total);
                barChart = renderBarChart('barChart', barCategories, barValues);
            }

            // Setup interatividade dos gráficos
            setupChartInteractions();

            // Esconde loading
            hideLoading();
        })
        .fail(function (error) {
            console.error('Erro ao carregar dashboard de secretário:', error);
            hideLoading();
            // Exibe uma mensagem de erro
            alert('Ocorreu um erro ao carregar os dados. Por favor, tente novamente.');
        });
}

/**
 * Configura interações com os gráficos
 */
function setupChartInteractions() {
    // Interação com Treemap
    const treemapDom = document.getElementById('treemap');
    if (treemapDom) {
        const chart = echarts.getInstanceByDom(treemapDom);
        if (chart) {
            chart.off('click');
            chart.on('click', function (params) {
                if (params.data && params.data.id) {
                    // Filtrar pelo departamento
                    $('#department').val(params.data.id);

                    // Atualizar os dados com o novo filtro
                    loadSecretaryDashboardData({
                        date_range: $('#dateRange').val(),
                        department_id: params.data.id,
                        expense_type: $('#expenseType').val()
                    });
                }
            });

            // Duplo clique para limpar filtros
            chart.off('dblclick');
            chart.on('dblclick', function () {
                clearFilters();
            });
        }
    }

    // Interação com gráfico de tipos de despesa
    const pieChartDom = document.getElementById('expenseTypePie');
    if (pieChartDom) {
        const chart = echarts.getInstanceByDom(pieChartDom);
        if (chart) {
            chart.off('click');
            chart.on('click', function (params) {
                if (params.data && params.data.name) {
                    const currentExpenseType = $('#expenseType').val();

                    // Se já estiver filtrado pelo mesmo tipo, remove o filtro
                    if (currentExpenseType === params.data.name) {
                        $('#expenseType').val('');
                    } else {
                        $('#expenseType').val(params.data.name);
                    }

                    // Atualiza os dados com o novo filtro
                    loadSecretaryDashboardData({
                        date_range: $('#dateRange').val(),
                        department_id: $('#department').val(),
                        expense_type: $('#expenseType').val()
                    });
                }
            });
        }
    }

    // Interação com gráfico de barras
    const barChartDom = document.getElementById('barChart');
    if (barChartDom) {
        const chart = echarts.getInstanceByDom(barChartDom);
        if (chart) {
            chart.off('click');
            chart.on('click', function (params) {
                if (params.name) {
                    // Encontra o ID do departamento pelo nome
                    const departmentId = $('#department option').filter(function () {
                        return $(this).text() === params.name;
                    }).val();

                    if (departmentId) {
                        $('#department').val(departmentId);

                        // Atualiza os dados com o novo filtro
                        loadSecretaryDashboardData({
                            date_range: $('#dateRange').val(),
                            department_id: departmentId,
                            expense_type: $('#expenseType').val()
                        });
                    }
                }
            });
        }
    }
}

/**
 * Inicializa o dashboard quando o DOM estiver pronto
 */
$(document).ready(function () {
    // Adiciona elemento de loading se não existir
    if ($('.dashboard-loading').length === 0) {
        $('body').append(`
            <div class="dashboard-loading fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50" style="display: none;">
                <div class="bg-white p-4 rounded-lg shadow-lg flex items-center">
                    <svg class="animate-spin h-6 w-6 text-blue-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Carregando dados...</span>
                </div>
            </div>
        `);
    }

    // Inicializa o DateRangePicker
    initializeDateRangePicker('dateRange', function (start, end) {
        // Callback quando a data muda
        loadSecretaryDashboardData({
            date_range: `${start.format('DD/MM/YYYY')} - ${end.format('DD/MM/YYYY')}`,
            department_id: $('#department').val(),
            expense_type: $('#expenseType').val()
        });
    });

    // Escuta mudanças nos seletores de departamento e tipo de despesa
    $('#department, #expenseType').on('change', function () {
        const filters = {
            date_range: $('#dateRange').val(),
            department_id: $('#department').val(),
            expense_type: $('#expenseType').val()
        };

        loadSecretaryDashboardData(filters);
    });

    // Função global para limpar filtros (chamada diretamente do HTML)
    window.clearFilters = function () {
        // Resetar o DateRangePicker para o mês atual
        const startDate = moment().startOf('month');
        const endDate = moment().endOf('month');

        const picker = $('#dateRange').data('daterangepicker');
        if (picker) {
            picker.setStartDate(startDate);
            picker.setEndDate(endDate);
            $('#dateRange').val(startDate.format('DD/MM/YYYY') + ' - ' + endDate.format('DD/MM/YYYY'));
        }

        // Limpar filtros de departamento e tipo de despesa
        $('#department').val('');
        $('#expenseType').val('');

        // Recarregar dados
        loadSecretaryDashboardData({
            date_range: `${startDate.format('DD/MM/YYYY')} - ${endDate.format('DD/MM/YYYY')}`
        });
    };

    // Função para mostrar detalhes de um departamento específico
    window.showDepartmentDetails = function (departmentId) {
        $('#department').val(departmentId);

        // Atualiza os dados com o novo filtro
        loadSecretaryDashboardData({
            date_range: $('#dateRange').val(),
            department_id: departmentId,
            expense_type: $('#expenseType').val()
        });
    };

    // Carregamento inicial dos dados
    const initialFilters = {
        date_range: $('#dateRange').val(),
        department_id: $('#department').val(),
        expense_type: $('#expenseType').val()
    };

    loadSecretaryDashboardData(initialFilters);
});

// Tornar a função acessível globalmente
window.loadSecretaryDashboardData = loadSecretaryDashboardData;

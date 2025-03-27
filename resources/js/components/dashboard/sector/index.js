/**
 * Arquivo principal do Dashboard para Líderes de Setor
 * resources/js/components/dashboard/sector/index.js
 */
import $ from 'jquery';
import * as echarts from 'echarts';
import {fetchSectorDashboardData} from './api';
import {renderExpenseTypePie, renderTimeline} from './charts';
import {formatCurrency, hideLoading, showLoading, updateMonthlyVariation} from './utils';
import {initializeDateRangePicker} from './datepicker';

// Charts instances
let timelineChart = null;
let expenseTypePieChart = null;

/**
 * Carrega os dados do dashboard de setor
 * @param {Object} filters - Filtros para a API
 */
function loadSectorDashboardData(filters = {}) {
    // Exibe loading
    showLoading();

    console.log('Carregando dashboard de setor com filtros:', filters);

    fetchSectorDashboardData(filters)
        .done(function (response) {
            // Atualiza cards principais
            $('#totalExpenses').text(formatCurrency(response.totalExpenses));
            $('#currentMonthExpenses').text(formatCurrency(response.currentMonthExpenses));
            $('#budgetCap').text(formatCurrency(response.budgetCap));

            // Atualiza variação mensal
            updateMonthlyVariation(response.currentMonthExpenses, response.lastMonthExpenses);

            // Atualiza indicador de uso do orçamento
            updateBudgetUsage(response.currentMonthExpenses, response.budgetCap);

            // Renderiza/atualiza gráficos
            timelineChart = renderTimeline('timeline', response.monthlyExpenses);
            expenseTypePieChart = renderExpenseTypePie('expenseTypePie', response.expenseTypeData);

            // Setup interatividade do gráfico de pizza
            setupPieChartInteractions();

            // Esconde loading
            hideLoading();
        })
        .fail(function (error) {
            console.error('Erro ao carregar dashboard de setor:', error);
            hideLoading();
            // Exibe uma mensagem de erro
            alert('Ocorreu um erro ao carregar os dados. Por favor, tente novamente.');
        });
}

/**
 * Atualiza o indicador de uso do orçamento
 */
function updateBudgetUsage(currentExpenses, budgetCap) {
    // Calcula porcentagem de uso do teto
    const usagePercent = budgetCap > 0
        ? Math.min(Math.round((currentExpenses / budgetCap) * 100), 100)
        : 0;

    // Atualiza a barra de progresso
    const barColor = usagePercent > 80 ? 'red' : (usagePercent > 60 ? 'yellow' : 'green');
    const progressBar = $('#budgetCap').siblings('div').find('div');

    progressBar.removeClass('bg-red-500 bg-yellow-500 bg-green-500')
        .addClass(`bg-${barColor}-500`)
        .css('width', `${usagePercent}%`);

    progressBar.siblings('span').text(`${usagePercent}%`);
}

/**
 * Configura interações com o gráfico de pizza
 */
function setupPieChartInteractions() {
    const pieChartDom = document.getElementById('expenseTypePie');
    if (!pieChartDom) return;

    const chart = echarts.getInstanceByDom(pieChartDom);
    if (!chart) return;

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
            const dateRange = $('#dateRange').data('daterangepicker');
            const filters = {
                date_range: $('#dateRange').val(),
                expense_type: $('#expenseType').val()
            };

            loadSectorDashboardData(filters);
        }
    });
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
        loadSectorDashboardData({
            date_range: `${start.format('DD/MM/YYYY')} - ${end.format('DD/MM/YYYY')}`,
            expense_type: $('#expenseType').val()
        });
    });

    // Escuta mudanças no seletor de tipo de despesa
    $('#expenseType').on('change', function () {
        const filters = {
            date_range: $('#dateRange').val(),
            expense_type: $(this).val()
        };

        loadSectorDashboardData(filters);
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

        // Limpar filtro de tipo de despesa
        $('#expenseType').val('');

        // Recarregar dados
        loadSectorDashboardData({
            date_range: `${startDate.format('DD/MM/YYYY')} - ${endDate.format('DD/MM/YYYY')}`
        });
    };

    // Carregamento inicial dos dados
    const initialFilters = {
        date_range: $('#dateRange').val(),
        expense_type: $('#expenseType').val()
    };

    loadSectorDashboardData(initialFilters);
});

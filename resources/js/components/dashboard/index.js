import $ from 'jquery';
import * as echarts from 'echarts';
import {loadDashboardData} from './dashboard';
import {initializeDateRangePicker} from './datepicker';

// Determinar o tipo de usuário/dashboard (prefeito ou secretário)
const userRole = $('body').data('user-role') || 'unknown';
const isSecretary = userRole === 'secretary';
const currentSecretaryId = isSecretary ? $('body').data('secretary-id') : null;

// Função para filtrar departamentos com base na secretaria selecionada
function filterDepartments() {
    // Se for secretário, usamos sempre o ID da secretaria do usuário
    const secretaryId = isSecretary ? currentSecretaryId : $('#secretary').val();

    // Se nenhuma secretaria estiver selecionada, mostrar todos os departamentos
    if (!secretaryId) {
        $('#department option').show();
        return;
    }

    // Esconder todos os departamentos primeiro
    $('#department option').hide();

    // Mostrar apenas a opção "Todos"
    $('#department option[value=""]').show();

    // Mostrar apenas departamentos da secretaria selecionada
    $(`#department option[data-secretary="${secretaryId}"]`).show();

    // Se o departamento atualmente selecionado não pertence à secretaria,
    // resetar a seleção para "Todos"
    const currentDepartment = $('#department').val();
    const belongsToSecretary = $(`#department option[value="${currentDepartment}"][data-secretary="${secretaryId}"]`).length > 0;

    if (currentDepartment && !belongsToSecretary) {
        $('#department').val('');
    }
}

// Função para obter os filtros atuais, considerando as restrições baseadas no papel do usuário
function getCurrentFilters() {
    const filters = {
        date_range: $('#dateRange').val(),
        department_id: $('#department').val(),
        expense_type: $('#expenseType').val()
    };

    // Se for secretário, sempre usamos o ID fixo da secretaria
    if (isSecretary && currentSecretaryId) {
        filters.secretary_id = currentSecretaryId;
    } else {
        // Se for prefeito, usamos o valor selecionado no filtro
        filters.secretary_id = $('#secretary').val();
    }

    return filters;
}

$(document).ready(function () {
    // Aplicar atributos ao corpo que identificam o tipo de usuário
    if (userRole) {
        $('body').attr('data-user-role', userRole);
    }

    // Se for secretário, ocultar o filtro de secretaria (já que é fixo)
    if (isSecretary) {
        $('.secretary-filter-container').hide();
    }

    // Inicializa o date range picker
    initializeDateRangePicker();

    // Escuta pelo evento dateRangeChanged disparado pelo datepicker
    document.addEventListener('dateRangeChanged', function (event) {
        // Combina os filtros do evento com os filtros fixos baseados no papel
        const combinedFilters = {...event.detail.filters};

        // Se for secretário, sempre sobrescreve o secretary_id
        if (isSecretary && currentSecretaryId) {
            combinedFilters.secretary_id = currentSecretaryId;
        }

        // Quando o daterange mudar, carregamos os dados com os filtros atualizados
        loadDashboardData(combinedFilters);

        // Feedback visual - opcional
        $('.dashboard-filter-active').show().delay(1500).fadeOut();
    });

    // Aplicar o filtro de departamentos quando a secretaria é alterada
    $('#secretary').on('change', filterDepartments);

    // Inicializar o filtro de departamentos no carregamento
    filterDepartments();

    // Listeners para filtros (select boxes)
    $('#secretary, #department, #expenseType').on('change', function () {
        loadDashboardData(getCurrentFilters());
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

        // O prefeito pode limpar todos os filtros
        if (!isSecretary) {
            $('#secretary').val('');
        }

        $('#department, #expenseType').val('');

        // Mostrar todas as opções de departamento após limpar
        $('#department option').show();

        loadDashboardData(getCurrentFilters());
    };

    // Setup de clicks nos gráficos interativos
    setupChartInteractions();

    // Carregamento inicial com os filtros iniciais
    loadDashboardData(getCurrentFilters());
});

// Configura interações com os gráficos
function setupChartInteractions() {
    // Interação com Treemap
    const treemapDom = document.getElementById('treemap');
    if (treemapDom) {
        echarts.getInstanceByDom(treemapDom)?.on('click', function (params) {
            if (params.data && params.data.id) {
                // Se for secretário, não permitimos trocar de secretaria
                if (isSecretary && params.data.children && params.data.children.length > 0) {
                    return; // Não faz nada ao clicar em outra secretaria
                }

                // Verifica se é uma secretaria (tem children) ou um departamento
                if (params.data.children && params.data.children.length > 0) {
                    $('#secretary').val(params.data.id).trigger('change');
                } else {
                    $('#department').val(params.data.id);
                }

                loadDashboardData(getCurrentFilters());
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

                loadDashboardData(getCurrentFilters());
            }
        });
    }
}

// Função para mostrar detalhes de departamento (chamada diretamente do HTML)
window.showDepartmentDetails = function (secretaryId) {
    // Se for secretário, ignora o parâmetro e usa o ID da secretaria atual
    const idToUse = isSecretary ? currentSecretaryId : secretaryId;

    if (!isSecretary) {
        $('#secretary').val(idToUse).trigger('change');
    }

    const filters = getCurrentFilters();
    loadDashboardData(filters);
};

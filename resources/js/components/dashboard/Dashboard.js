import $ from 'jquery';
import {fetchDashboardData} from './api';
import {renderBarChart, renderExpenseTypePie, renderGaugeChart, renderTimeline, renderTreemap} from './charts';
import {formatCurrency} from './utils';

// Função principal para carregar dados do dashboard
function loadDashboardData(filters = {}) {
    // Exibe loading
    $('.dashboard-loading').show();

    // Log para debug
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

            // ===== CONFIGURAÇÃO DO GAUGE CHART =====

            // Verifica se existe um teto definido, caso contrário usa o orçamento mensal padrão
            let capValue = response.capValue;

            // Verificações de segurança para garantir um valor válido para o gauge
            if (!capValue || capValue <= 0) {
                capValue = response.monthlyBudget || 30000; // Fallback para valor padrão
            }

            console.log('Configurando gauge:', {
                currentMonthExpenses: response.currentMonthExpenses,
                capValue: capValue,
                percentual: ((response.currentMonthExpenses / capValue) * 100).toFixed(1) + '%'
            });

            // Renderiza o gauge com os valores corretos
            renderGaugeChart('gaugeChart', response.currentMonthExpenses, capValue);

            // Oculta o loading
            $('.dashboard-loading').hide();

            // Atualiza elementos de informação sobre limites de gastos, se existirem
            if ($('#spendingCapInfo').length) {
                $('#spendingCapInfo').html(`
                    <div class="text-sm text-gray-600">
                        <p>Teto de Gastos: ${formatCurrency(capValue)}</p>
                        <p>Gastos Atuais: ${formatCurrency(response.currentMonthExpenses)}</p>
                        <p>Margem: ${formatCurrency(capValue - response.currentMonthExpenses)}</p>
                    </div>
                `);
            }
        })
        .fail(function (err) {
            console.error("Erro ao carregar dados do dashboard", err);
            // Oculta o loading e mostra erro
            $('.dashboard-loading').hide();
            $('.dashboard-error').show().delay(3000).fadeOut();
        });
}

// Função para atualizar a variação mensal
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

// Exporta as funções para uso no index.js
export {loadDashboardData, updateMonthlyVariation};

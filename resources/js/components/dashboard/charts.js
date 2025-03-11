// Renderiza o gráfico de linha para a evolução mensal
// resources/js/dashboard/charts.js
import * as echarts from 'echarts';

/**
 * Renderiza um gráfico de barras.
 * @param {string} selector - O ID do elemento onde o gráfico será renderizado.
 * @param {Array} categories - Array com as categorias (ex.: nomes dos departamentos).
 * @param {Array} values - Array com os valores correspondentes a cada categoria.
 */
export function renderBarChart(selector, categories, values) {
    const chartDom = document.getElementById(selector);
    if (!chartDom) return;

    const myChart = echarts.init(chartDom);

    const option = {
        tooltip: {
            trigger: 'axis'
        },
        xAxis: {
            type: 'category',
            data: categories,
            axisLabel: {
                interval: 0,
                rotate: 30
            }
        },
        yAxis: {
            type: 'value',
            name: 'Valor (R$)'
        },
        series: [{
            data: values,
            type: 'bar',
            barWidth: '50%',
            itemStyle: {
                color: '#4CAF50'
            }
        }]
    };

    myChart.setOption(option);
}

/**
 * Renderiza um gráfico do tipo velocímetro (gauge).
 import * as echarts from 'echarts';
 import { formatCurrency } from './utils';

 /**
 * Renderiza um gráfico de gauge para visualizar as despesas atuais em relação ao teto de gastos
 /**
 * Formata um valor numérico como moeda (fallback caso utils.js não esteja disponível)
 * @param {number} value - Valor a ser formatado
 * @return {string} - Valor formatado como moeda
 */
function formatCurrencyInternal(value) {
    if (typeof window.formatCurrency === 'function') {
        return window.formatCurrency(value);
    }

    // Implementação de fallback
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
        minimumFractionDigits: 2
    }).format(value);
}

/**
 * Renderiza um gráfico de gauge para visualizar as despesas atuais em relação ao teto de gastos
 *
 * @param {string} selector - ID do elemento DOM onde renderizar o gráfico
 * @param {number} value - Valor atual de despesas do mês
 * @param {number} capValue - Valor do teto de gastos
 */
export function renderGaugeChart(selector, value, capValue, capSource = 'none') {
    // capSource pode ser:
    //  - 'specific':   teto específico para o filtro atual
    //  - 'macro':      teto genérico (macro) da secretaria
    //  - 'none':       não há teto algum definido

    const chartDom = document.getElementById(selector);
    if (!chartDom) return;

    // Garante que "value" seja número; se não for, vira 0
    value = parseFloat(value) || 0;

    // Se não houver teto (capSource = 'none'), podemos definir capValue como 0
    // e tratar depois no gauge. Caso contrário, usa o que vier (teto específico ou macro).
    let useGauge = true; // Flag para indicar se vamos desenhar o gauge "normal"
    if (capSource === 'none') {
        capValue = 0;
        useGauge = false;
    } else {
        capValue = parseFloat(capValue) || 0;
        // Se por alguma razão ainda for 0, tratamos como 'none'
        if (capValue <= 0) {
            useGauge = false;
        }
    }

    // Limpa qualquer gráfico existente
    echarts.dispose(chartDom);
    const myChart = echarts.init(chartDom);

    // Se tivermos um teto válido (> 0), calculamos o percentual
    let percentage = 0;
    if (useGauge) {
        percentage = (value / capValue) * 100;
    }

    // Preparação do label e tooltip
    let gaugeLabel = '';
    let tooltipText = '';

    if (!useGauge) {
        // Caso "nenhum teto": mostramos mensagem de "N/A" ou equivalente
        gaugeLabel = 'Nenhum\nteto\ndefinido';
        tooltipText = 'Nenhum teto definido para o filtro atual';
    } else {
        // Se temos teto, montamos o label normal + alguma indicação da origem (capSource)
        gaugeLabel = `${percentage.toFixed(0)}%\n${formatCurrencyInternal(value)}`;

        // Exemplo: se for macro, avisamos no tooltip que é “teto geral”
        if (capSource === 'macro') {
            tooltipText = `Usando teto geral da Secretaria: ${formatCurrencyInternal(capValue)}<br/>
                           Gasto Atual: ${formatCurrencyInternal(value)}<br/>
                           Utilizado: ${percentage.toFixed(1)}%`;
        } else {
            // Se for específico
            tooltipText = `Orçamento Mensal<br/>
                           Teto: ${formatCurrencyInternal(capValue)}<br/>
                           Gasto Atual: ${formatCurrencyInternal(value)}<br/>
                           Utilizado: ${percentage.toFixed(1)}%`;
        }
    }

    const option = {
        tooltip: {
            formatter: function () {
                return tooltipText;
            }
        },
        series: [
            {
                name: 'Orçamento',
                type: 'gauge',
                min: 0,
                max: 100,
                splitNumber: 10,
                radius: '90%',
                axisLabel: {
                    formatter: '{value}%',
                    distance: -40,
                    color: '#999',
                    fontSize: 10
                },
                detail: {
                    // Se não há teto, exibimos "Nenhum teto definido"
                    // Se há teto, exibimos "XX% / valor"
                    formatter: function () {
                        return gaugeLabel;
                    },
                    offsetCenter: [0, '60%'],
                    style: {
                        fontSize: 14,
                        fontWeight: 'bold',
                        lineHeight: 20
                    }
                },
                data: [{
                    // Se não há teto, deixamos pointer em 0
                    value: useGauge ? percentage : 0,
                    name: 'Gastos'
                }],
                title: {
                    fontSize: 12,
                    offsetCenter: [0, '80%']
                },
                axisLine: {
                    lineStyle: {
                        width: 30,
                        color: [
                            [0.3, '#67e0e3'],  // 0-30%: verde-água
                            [0.7, '#37a2da'],  // 30-70%: azul
                            [1, '#fd666d']     // 70-100%: vermelho
                        ]
                    }
                },
                pointer: {
                    show: useGauge, // Se não há teto, podemos até esconder o ponteiro
                    itemStyle: {
                        color: 'auto'
                    }
                }
            }
        ]
    };

    myChart.setOption(option);

    // Responsividade
    window.addEventListener('resize', function () {
        myChart.resize();
    });

    return myChart;
}

// Outras funções já existentes: renderTreemap, renderExpenseTypePie, renderTimeline, etc.


// Renderiza o Treemap
export function renderTreemap(selector, data) {
    const chartDom = document.getElementById(selector);
    if (!chartDom) return;
    const myChart = echarts.init(chartDom);
    const option = {
        tooltip: {
            formatter: "{b}: {c}"
        },
        series: [{
            type: 'treemap',
            data: data // data deve estar no formato esperado pelo ECharts
        }]
    };
    myChart.setOption(option);
}

// Renderiza o gráfico de pizza para os tipos de despesa
export function renderExpenseTypePie(selector, data) {
    const chartDom = document.getElementById(selector);
    if (!chartDom) return;

    const myChart = echarts.init(chartDom);

    // Calcula o total para poder computar as porcentagens
    const total = data.reduce((sum, item) => sum + item.value, 0);

    const option = {
        tooltip: {
            trigger: 'item',
            formatter: '{b}: {c} ({d}%)' // Exibe nome, valor e porcentagem na tooltip
        },
        // Configuração da legenda posicionada à esquerda
        legend: {
            orient: 'vertical',
            left: 'left',
            data: data.map(item => item.name),
            // Formata cada item da legenda para mostrar o nome e a porcentagem
            formatter: function (name) {
                const item = data.find(d => d.name === name);
                const percent = total > 0 ? ((item.value / total) * 100).toFixed(1) : 0;
                return `${name} ${percent}%`;
            }
        },
        series: [{
            type: 'pie',
            radius: '50%',
            center: ['50%', '50%'],
            data: data, // data deve estar no formato: [{ name: 'Tipo A', value: 123 }, ...]
            // Exibe os rótulos fora da fatia com o nome e a porcentagem
            label: {
                position: 'outside',
                formatter: '{b}: {d}%' // {b} = nome, {d} = porcentagem calculada automaticamente pelo ECharts
            },
            emphasis: {
                itemStyle: {
                    shadowBlur: 10,
                    shadowOffsetX: 0,
                    shadowColor: 'rgba(0, 0, 0, 0.5)'
                }
            }
        }]
    };
    myChart.setOption(option);
}

export function renderTimeline(selector, monthlyData, seriesData) {
    // Verifica se os dados necessários foram passados e são arrays
    if (!Array.isArray(monthlyData)) {
        console.error("monthlyData não é um array ou está indefinido:", monthlyData);
        return;
    }
    if (!Array.isArray(seriesData)) {
        console.error("seriesData não é um array ou está indefinido:", seriesData);
        return;
    }

    // Seleciona o elemento do DOM onde o gráfico será renderizado
    const chartDom = document.getElementById(selector);
    if (!chartDom) {
        console.error(`Elemento com ID '${selector}' não foi encontrado.`);
        return;
    }

    const myChart = echarts.init(chartDom);

    // Cria as opções do gráfico
    const option = {
        tooltip: {trigger: 'axis'},
        legend: {
            // Cria a legenda com os nomes de cada série (ex.: "Agricultura", "Bem-Estar Social", "Educação")
            data: seriesData.map(series => series.name)
        },
        xAxis: {
            type: 'category',
            // Usa os valores da propriedade 'month' para definir as categorias do eixo X
            data: monthlyData.map(item => item.month)
        },
        yAxis: {type: 'value'},
        series: seriesData.map(series => ({
            name: series.name,
            type: 'line',
            smooth: true,
            // Usa os valores do array 'data' para definir os pontos da linha
            data: series.data
        }))
    };

    // Opcional: Verifica no console os valores que serão usados no gráfico
    console.log("Legend data:", seriesData.map(series => series.name));
    console.log("X Axis data:", monthlyData.map(item => item.month));
    console.log("Series:", seriesData.map(series => ({name: series.name, data: series.data})));

    // Renderiza o gráfico
    myChart.setOption(option);
}



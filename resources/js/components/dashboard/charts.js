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
 * @param {string} selector - O ID do elemento onde o gráfico será renderizado.
 * @param {number} value - O valor atual para exibir (ex.: a porcentagem de realização orçamentária).
 * @param {number} maxValue - O valor máximo da escala (padrão 100).
 */
export function renderGaugeChart(selector, value, maxValue = 100) {
    const chartDom = document.getElementById(selector);
    if (!chartDom) return;

    const myChart = echarts.init(chartDom);

    const option = {
        tooltip: {
            formatter: '{a} <br/>{b} : {c}%'
        },
        series: [
            {
                name: 'Orçamento',
                type: 'gauge',
                detail: {
                    formatter: function (val) {
                        if (!maxValue) return '0%';
                        return ((val / maxValue) * 100).toFixed(0) + '%';
                    }
                },
                data: [{value: value, name: 'Gastos'}],
                axisLine: {
                    lineStyle: {
                        width: 10,
                        color: [
                            [0.3, '#67e0e3'], // 0-30%: verde-água
                            [0.7, '#37a2da'], // 30-70%: azul
                            [1, '#fd666d']    // 70-100%: vermelho
                        ]
                    }
                }
            }
        ]
    };

    myChart.setOption(option);
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



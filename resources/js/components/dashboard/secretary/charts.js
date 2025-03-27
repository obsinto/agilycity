/**
 * Módulo de gráficos para o Dashboard de Secretários
 * resources/js/components/dashboard/secretary/charts.js
 */
import * as echarts from 'echarts';
import {chartColors} from './config';

/**
 * Renderiza o gráfico Treemap de distribuição por departamento
 * @param {string} elementId - ID do elemento HTML
 * @param {Array} data - Dados hierárquicos para o treemap
 * @returns {echarts.ECharts} Instância do gráfico
 */
export function renderTreemap(elementId, data) {
    const chartDom = document.getElementById(elementId);
    if (!chartDom) return null;

    let chart = echarts.getInstanceByDom(chartDom);
    if (!chart) {
        chart = echarts.init(chartDom);
    }

    const option = {
        tooltip: {
            formatter: function (info) {
                const value = info.value;
                const treePathInfo = info.treePathInfo;
                const treePath = [];

                for (let i = 1; i < treePathInfo.length; i++) {
                    treePath.push(treePathInfo[i].name);
                }

                return [
                    '<div class="tooltip-title">' + treePath.join('/') + '</div>',
                    'Total: R$ ' + value.toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })
                ].join('');
            }
        },
        series: [{
            name: 'Distribuição de Gastos',
            type: 'treemap',
            visibleMin: 300,
            label: {
                show: true,
                formatter: '{b}: {c}'
            },
            itemStyle: {
                borderColor: '#fff'
            },
            levels: [
                {
                    itemStyle: {
                        borderColor: '#777',
                        borderWidth: 0,
                        gapWidth: 1
                    },
                    upperLabel: {
                        show: false
                    }
                },
                {
                    itemStyle: {
                        borderColor: '#555',
                        borderWidth: 5,
                        gapWidth: 1
                    },
                    emphasis: {
                        itemStyle: {
                            borderColor: '#ddd'
                        }
                    }
                },
                {
                    colorSaturation: [0.35, 0.5],
                    itemStyle: {
                        borderWidth: 5,
                        gapWidth: 1,
                        borderColorSaturation: 0.6
                    }
                }
            ],
            data: data
        }]
    };

    chart.setOption(option);

    window.addEventListener('resize', function () {
        chart.resize();
    });

    return chart;
}

/**
 * Renderiza o gráfico de pizza para distribuição por tipo de despesa
 * @param {string} elementId - ID do elemento HTML
 * @param {Array} data - Dados para o gráfico de pizza
 * @returns {echarts.ECharts} Instância do gráfico
 */
export function renderExpenseTypePie(elementId, data) {
    const chartDom = document.getElementById(elementId);
    if (!chartDom) return null;

    let chart = echarts.getInstanceByDom(chartDom);
    if (!chart) {
        chart = echarts.init(chartDom);
    }

    const option = {
        tooltip: {
            trigger: 'item',
            formatter: function (params) {
                return `${params.name}<br/>R$ ${params.value.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })} (${params.percent.toFixed(1)}%)`;
            }
        },
        legend: {
            orient: 'vertical',
            right: 10,
            top: 'center',
            type: 'scroll'
        },
        series: [{
            name: 'Tipos de Despesa',
            type: 'pie',
            radius: ['40%', '70%'],
            avoidLabelOverlap: true,
            itemStyle: {
                borderRadius: 10,
                borderColor: '#fff',
                borderWidth: 2
            },
            label: {
                show: false
            },
            emphasis: {
                label: {
                    show: true,
                    fontSize: '16',
                    fontWeight: 'bold'
                }
            },
            labelLine: {
                show: false
            },
            data: data
        }]
    };

    chart.setOption(option);

    window.addEventListener('resize', function () {
        chart.resize();
    });

    return chart;
}

/**
 * Renderiza o gráfico de linha para evolução mensal
 * @param {string} elementId - ID do elemento HTML
 * @param {Array} monthlyData - Dados mensais para o eixo X
 * @param {Array} seriesData - Dados das séries para o gráfico
 * @returns {echarts.ECharts} Instância do gráfico
 */
export function renderTimeline(elementId, monthlyData, seriesData = null) {
    const chartDom = document.getElementById(elementId);
    if (!chartDom) return null;

    let chart = echarts.getInstanceByDom(chartDom);
    if (!chart) {
        chart = echarts.init(chartDom);
    }

    // Extrai meses para o eixo X
    const months = monthlyData.map(item => item.month);

    // Se não houver dados de série, crie uma série padrão
    let series = seriesData || [{
        name: 'Total de Gastos',
        type: 'line',
        smooth: true,
        data: monthlyData.map(item => item.total),
        itemStyle: {
            color: chartColors[0]
        },
        areaStyle: {
            color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                {offset: 0, color: chartColors[0] + 'AA'},
                {offset: 1, color: chartColors[0] + '11'}
            ])
        }
    }];

    // Atribua cores às séries se houver mais de uma
    if (seriesData && seriesData.length > 0) {
        series = seriesData.map((s, index) => {
            const colorIndex = index % chartColors.length;
            return {
                ...s,
                itemStyle: {
                    color: chartColors[colorIndex]
                },
                areaStyle: s.areaStyle || {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        {offset: 0, color: chartColors[colorIndex] + 'AA'},
                        {offset: 1, color: chartColors[colorIndex] + '11'}
                    ])
                }
            };
        });
    }

    const option = {
        tooltip: {
            trigger: 'axis',
            formatter: function (params) {
                let content = params[0].name + '<br/>';
                params.forEach(param => {
                    content += `${param.seriesName}: R$ ${param.value.toLocaleString('pt-BR', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })}<br/>`;
                });
                return content;
            }
        },
        legend: {
            data: series.map(s => s.name),
            bottom: 0
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '10%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: months,
            axisLabel: {
                rotate: 45
            }
        },
        yAxis: {
            type: 'value',
            axisLabel: {
                formatter: function (value) {
                    return 'R$ ' + value.toLocaleString('pt-BR', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                }
            }
        },
        series: series
    };

    chart.setOption(option);

    window.addEventListener('resize', function () {
        chart.resize();
    });

    return chart;
}

/**
 * Renderiza o gráfico de barras para despesas por departamento
 * @param {string} elementId - ID do elemento HTML
 * @param {Array} categories - Categorias para o eixo X (nomes dos departamentos)
 * @param {Array} values - Valores para as barras
 * @returns {echarts.ECharts} Instância do gráfico
 */
export function renderBarChart(elementId, categories, values) {
    const chartDom = document.getElementById(elementId);
    if (!chartDom) return null;

    let chart = echarts.getInstanceByDom(chartDom);
    if (!chart) {
        chart = echarts.init(chartDom);
    }

    const option = {
        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'shadow'
            },
            formatter: function (params) {
                return `${params[0].name}<br/>R$ ${params[0].value.toLocaleString('pt-BR', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}`;
            }
        },
        grid: {
            left: '3%',
            right: '4%',
            bottom: '3%',
            containLabel: true
        },
        xAxis: {
            type: 'category',
            data: categories,
            axisLabel: {
                interval: 0,
                rotate: 45,
                textStyle: {
                    fontSize: 10
                }
            }
        },
        yAxis: {
            type: 'value',
            axisLabel: {
                formatter: function (value) {
                    return 'R$ ' + value.toLocaleString('pt-BR', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                }
            }
        },
        series: [{
            name: 'Gastos',
            type: 'bar',
            data: values,
            itemStyle: {
                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                    {offset: 0, color: chartColors[0]},
                    {offset: 1, color: chartColors[1]}
                ])
            },
            emphasis: {
                itemStyle: {
                    color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                        {offset: 0, color: chartColors[2]},
                        {offset: 1, color: chartColors[0]}
                    ])
                }
            }
        }]
    };

    chart.setOption(option);

    window.addEventListener('resize', function () {
        chart.resize();
    });

    return chart;
}

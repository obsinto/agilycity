/**
 * Módulo de gráficos para o Dashboard de Líderes de Setor
 * resources/js/components/dashboard/sector/charts.js
 */
import * as echarts from 'echarts';
import {chartColors} from './config';

/**
 * Inicializa o gráfico de linha para evolução mensal
 * @param {string} elementId - ID do elemento HTML para o gráfico
 * @param {Array} data - Dados de gastos mensais
 * @returns {echarts.ECharts} Instância do gráfico
 */
export function renderTimeline(elementId, data) {
    const chartDom = document.getElementById(elementId);
    if (!chartDom) return null;

    let chart = echarts.getInstanceByDom(chartDom);
    if (!chart) {
        chart = echarts.init(chartDom);
    }

    // Extrai meses e valores dos dados
    const months = data.map(item => item.month);
    const values = data.map(item => item.total);

    const option = {
        tooltip: {
            trigger: 'axis',
            formatter: function (params) {
                const param = params[0];
                return `${param.name}<br/>${param.seriesName}: R$ ${param.value.toLocaleString('pt-BR', {
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
        series: [{
            name: 'Total de Gastos',
            data: values,
            type: 'line',
            smooth: true,
            lineStyle: {
                width: 3
            },
            itemStyle: {
                color: chartColors[0]
            },
            areaStyle: {
                color: new echarts.graphic.LinearGradient(0, 0, 0, 1, [
                    {offset: 0, color: chartColors[0] + 'AA'},
                    {offset: 1, color: chartColors[0] + '11'}
                ])
            }
        }]
    };

    chart.setOption(option);

    // Evento de redimensionamento
    window.addEventListener('resize', function () {
        chart.resize();
    });

    return chart;
}

/**
 * Inicializa o gráfico de pizza para tipos de despesa
 * @param {string} elementId - ID do elemento HTML para o gráfico
 * @param {Array} data - Dados de tipos de despesa
 * @returns {echarts.ECharts} Instância do gráfico
 */
export function renderExpenseTypePie(elementId, data) {
    const chartDom = document.getElementById(elementId);
    if (!chartDom) return null;

    let chart = echarts.getInstanceByDom(chartDom);
    if (!chart) {
        chart = echarts.init(chartDom);
    }

    // Formatar dados para o gráfico
    const pieData = data.map(item => ({
        name: item.name,
        value: item.value
    }));

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
            data: pieData
        }]
    };

    chart.setOption(option);

    // Evento de redimensionamento
    window.addEventListener('resize', function () {
        chart.resize();
    });

    return chart;
}

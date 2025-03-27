/**
 * DateRangePicker para o Dashboard de Líderes de Setor
 * resources/js/components/dashboard/sector/datepicker.js
 */
import $ from 'jquery';
import moment from 'moment';
import 'moment/locale/pt-br';
import 'daterangepicker';

/**
 * Inicializa o Date Range Picker com configurações específicas
 * @param {string} elementId - ID do elemento HTML para o datepicker
 * @param {Function} callback - Função de callback quando a data é alterada
 */
export function initializeDateRangePicker(elementId = 'dateRange', callback = null) {
    moment.locale('pt-br');

    const startDate = moment().startOf('month');
    const endDate = moment().endOf('month');

    $(`#${elementId}`).daterangepicker({
        startDate: startDate,
        endDate: endDate,
        ranges: {
            'Mês Atual': [moment().startOf('month'), moment().endOf('month')],
            'Mês Anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Últimos 3 Meses': [moment().subtract(2, 'month').startOf('month'), moment().endOf('month')],
            'Este Ano': [moment().startOf('year'), moment().endOf('year')]
        },
        locale: {
            format: 'DD/MM/YYYY',
            applyLabel: 'Aplicar',
            cancelLabel: 'Cancelar',
            fromLabel: 'De',
            toLabel: 'Até',
            customRangeLabel: 'Período Personalizado',
            daysOfWeek: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
            monthNames: [
                'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
            ],
            firstDay: 1
        }
    }, function (start, end) {
        if (callback && typeof callback === 'function') {
            callback(start, end);
        } else {
            // Disparar um evento padronizado que pode ser interceptado
            const event = new CustomEvent('dateRangeChanged', {
                detail: {
                    startDate: start,
                    endDate: end,
                    filters: {
                        date_range: `${start.format('DD/MM/YYYY')} - ${end.format('DD/MM/YYYY')}`
                    }
                }
            });
            document.dispatchEvent(event);
        }
    });

    // Formatação inicial
    $(`#${elementId}`).val(startDate.format('DD/MM/YYYY') + ' - ' + endDate.format('DD/MM/YYYY'));
}

/**
 * Atualiza as datas do DateRangePicker
 * @param {string} elementId - ID do elemento HTML do datepicker
 * @param {moment} startDate - Data inicial
 * @param {moment} endDate - Data final
 */
export function updateDateRangePicker(elementId = 'dateRange', startDate, endDate) {
    const picker = $(`#${elementId}`).data('daterangepicker');
    if (picker) {
        picker.setStartDate(startDate);
        picker.setEndDate(endDate);
        $(`#${elementId}`).val(startDate.format('DD/MM/YYYY') + ' - ' + endDate.format('DD/MM/YYYY'));
    }
}

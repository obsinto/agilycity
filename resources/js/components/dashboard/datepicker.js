import $ from 'jquery';
import moment from 'moment';
import 'moment/locale/pt-br';
import 'daterangepicker';

export function initializeDateRangePicker() {
    const dateRangeInput = $('#dateRange');

    if (!dateRangeInput.length) {
        console.warn("Elemento #dateRange não encontrado.");
        return;
    }

    // Configura locale do moment
    moment.locale('pt-br');

    try {
        dateRangeInput.daterangepicker({
            autoUpdateInput: true,
            showDropdowns: true,
            minYear: 2000,
            maxYear: parseInt(moment().format('YYYY'), 10),
            opens: 'right',
            drops: 'down',
            autoApply: false,
            timePicker: false,
            alwaysShowCalendars: true,
            showCustomRangeLabel: true,
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
            locale: {
                format: 'DD/MM/YYYY',
                separator: ' - ',
                applyLabel: 'Aplicar',
                cancelLabel: 'Limpar',
                fromLabel: 'De',
                toLabel: 'Até',
                customRangeLabel: 'Personalizado',
                weekLabel: 'S',
                daysOfWeek: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
                monthNames: [
                    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
                ],
                firstDay: 0
            },
            ranges: {
                'Hoje': [moment(), moment()],
                'Ontem': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Últimos 7 dias': [moment().subtract(6, 'days'), moment()],
                'Últimos 30 dias': [moment().subtract(29, 'days'), moment()],
                'Este mês': [moment().startOf('month'), moment().endOf('month')],
                'Mês Passado': [
                    moment().subtract(1, 'month').startOf('month'),
                    moment().subtract(1, 'month').endOf('month')
                ],
                'Este Ano': [moment().startOf('year'), moment().endOf('year')],
                'Ano Passado': [
                    moment().subtract(1, 'year').startOf('year'),
                    moment().subtract(1, 'year').endOf('year')
                ]
            }
        });

        // Evento ao clicar em "Aplicar"
        dateRangeInput.on('apply.daterangepicker', function (ev, picker) {
            const startDate = picker.startDate.format('DD/MM/YYYY');
            const endDate = picker.endDate.format('DD/MM/YYYY');
            $(this).val(`${startDate} - ${endDate}`).trigger('change');
        });

        // Evento ao clicar em "Limpar"
        dateRangeInput.on('cancel.daterangepicker', function () {
            $(this).val('').trigger('change');
        });

        // Se quiser setar o valor inicial manualmente
        if (!dateRangeInput.val()) {
            const start = moment().startOf('month').format('DD/MM/YYYY');
            const end = moment().endOf('month').format('DD/MM/YYYY');
            dateRangeInput.val(`${start} - ${end}`);
        }
    } catch (error) {
        console.error('Erro ao inicializar daterangepicker:', error);
    }
}

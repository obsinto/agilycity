/**
 * DateRangePicker - Inicialização e configuração
 * Arquivo: components/dashboard/datepicker.js
 */

/**
 * Inicializa o DateRangePicker com opções predefinidas
 */
export function initializeDateRangePicker() {
    $('#dateRange').daterangepicker({
        autoUpdateInput: false,
        locale: {
            format: 'DD/MM/YYYY',
            separator: ' - ',
            applyLabel: 'Aplicar',
            cancelLabel: 'Cancelar',
            fromLabel: 'De',
            toLabel: 'Até',
            customRangeLabel: 'Personalizado',
            weekLabel: 'S',
            daysOfWeek: ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'],
            monthNames: [
                'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
                'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
            ],
            firstDay: 1
        },
        ranges: {
            'Hoje': [moment(), moment()],
            'Ontem': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Últimos 7 dias': [moment().subtract(6, 'days'), moment()],
            'Últimos 30 dias': [moment().subtract(29, 'days'), moment()],
            'Este mês': [moment().startOf('month'), moment().endOf('month')],
            'Mês passado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Este trimestre': [moment().startOf('quarter'), moment().endOf('quarter')],
            'Trimestre passado': [moment().subtract(1, 'quarter').startOf('quarter'), moment().subtract(1, 'quarter').endOf('quarter')],
            'Este ano': [moment().startOf('year'), moment().endOf('year')],
            'Ano passado': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
            'Últimos 12 meses': [moment().subtract(1, 'year'), moment()]
        },
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),
        opens: 'right',
        showDropdowns: true,
        alwaysShowCalendars: true,
        linkedCalendars: false
    });

    // Eventos apply e cancel
    $('#dateRange').on('apply.daterangepicker', function (ev, picker) {
        $(this).val(picker.startDate.format('DD/MM/YYYY') + ' - ' + picker.endDate.format('DD/MM/YYYY'));

        // IMPORTANTE: Atualizar o dashboard usando a função principal loadDashboardData
        const filters = {
            date_range: $(this).val(),
            secretary_id: $('#secretary').val(),
            department_id: $('#department').val(),
            expense_type: $('#expenseType').val()
        };

        // Disparar evento que o dashboard principal irá escutar
        document.dispatchEvent(new CustomEvent('dateRangeChanged', {
            detail: {
                filters: filters,
                startDate: picker.startDate,
                endDate: picker.endDate
            }
        }));
    });

    $('#dateRange').on('cancel.daterangepicker', function () {
        $(this).val('');
    });

    // Definir valor inicial (este mês)
    const startOfMonth = moment().startOf('month').format('DD/MM/YYYY');
    const endOfMonth = moment().endOf('month').format('DD/MM/YYYY');
    $('#dateRange').val(`${startOfMonth} - ${endOfMonth}`);
}

/**
 * Função auxiliar para recuperar o período atual
 */
export function getCurrentDateRange() {
    const rangeValue = $('#dateRange').val();
    if (!rangeValue) return null;

    const dates = rangeValue.split(' - ');
    return {
        startDate: moment(dates[0], 'DD/MM/YYYY'),
        endDate: moment(dates[1], 'DD/MM/YYYY')
    };
}

/**
 * Função para definir programaticamente um intervalo de datas
 */
export function setDateRange(startDate, endDate) {
    const picker = $('#dateRange').data('daterangepicker');
    if (picker) {
        picker.setStartDate(startDate);
        picker.setEndDate(endDate);
        $('#dateRange').val(startDate.format('DD/MM/YYYY') + ' - ' + endDate.format('DD/MM/YYYY'));

        // IMPORTANTE: Usar a mesma estrutura de evento para garantir consistência
        document.dispatchEvent(new CustomEvent('dateRangeChanged', {
            detail: {
                filters: {
                    date_range: $('#dateRange').val(),
                    secretary_id: $('#secretary').val(),
                    department_id: $('#department').val(),
                    expense_type: $('#expenseType').val()
                },
                startDate: startDate,
                endDate: endDate
            }
        }));
    }
}

/**
 * Função auxiliar para formatar números
 */
export function formatNumber(number, decimals = 2) {
    return new Intl.NumberFormat('pt-BR', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(number);
}

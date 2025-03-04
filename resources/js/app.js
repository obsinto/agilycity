import './bootstrap';
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';
import './components/Dashboard'; // Se precisar
// Importando bibliotecas necessárias
import $ from 'jquery';
import moment from 'moment';
import 'moment/locale/pt-br';
import ApexCharts from 'apexcharts';
import * as echarts from 'echarts';
import 'daterangepicker';
import 'daterangepicker/daterangepicker.css';

// Expondo jQuery globalmente
window.$ = window.jQuery = $;

// Expondo outras bibliotecas globalmente
window.ApexCharts = ApexCharts;
window.echarts = echarts;
window.moment = moment;

// Configurando Alpine.js
window.Alpine = Alpine;
Alpine.plugin(focus);
Alpine.plugin(collapse);
Alpine.start();

// Configurando moment.js para português
moment.locale('pt-br');

// Configuração do CSRF token para requisições AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Se precisar de configurações globais do DateRangePicker, adicione-as aqui

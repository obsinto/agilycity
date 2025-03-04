// app.js - Abordagem simplificada e moderna

// Importações de bibliotecas principais
import './bootstrap';
import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';
import $ from 'jquery';
import moment from 'moment';
import 'moment/locale/pt-br';
import ApexCharts from 'apexcharts';
import * as echarts from 'echarts';
import 'daterangepicker/daterangepicker.css';

// Inicialização da aplicação
async function initializeApp() {
    // Configurar bibliotecas globais
    window.$ = window.jQuery = $;
    window.moment = moment;
    window.ApexCharts = ApexCharts;
    window.echarts = echarts;
    window.Alpine = Alpine;

    // Configurações iniciais
    moment.locale('pt-br');
    Alpine.plugin(focus);
    Alpine.plugin(collapse);
    Alpine.start();

    // Configurar AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    try {
        // Carregar daterangepicker de forma assíncrona
        await import('daterangepicker');

        // Carregar o index.js do dashboard, que importará os outros módulos necessários
        await import('./components/dashboard/index.js');

        console.log('Aplicação inicializada com sucesso!');
    } catch (error) {
        console.error('Erro ao inicializar a aplicação:', error);
    }
}

// Iniciar a aplicação
initializeApp();

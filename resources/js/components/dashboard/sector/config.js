/**
 * Configurações para o Dashboard de Líderes de Setor
 * resources/js/components/dashboard/sector/config.js
 */

// Endpoints de API para o dashboard de setor
export const apiEndpoints = {
    // Endpoint para buscar dados filtrados
    filter: '/dashboard/sector/filter',

    // Endpoint para detalhes do departamento específico (se necessário)
    departmentDetails: (departmentId) => `/dashboard/sector/department/${departmentId}`
};

// Cores para os gráficos
export const chartColors = [
    '#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de',
    '#3ba272', '#fc8452', '#9a60b4', '#ea7ccc'
];

/**
 * Configurações para o Dashboard de Secretários
 * resources/js/components/dashboard/secretary/config.js
 */

// Endpoints de API para o dashboard de secretário
export const apiEndpoints = {
    // Endpoint para buscar dados filtrados
    filter: '/dashboard/secretary/filter',

    // Endpoint para detalhes de departamentos específicos
    departmentDetails: (departmentId) => `/dashboard/secretary/department/${departmentId}`
};

// Cores para os gráficos
export const chartColors = [
    '#5470c6', '#91cc75', '#fac858', '#ee6666', '#73c0de',
    '#3ba272', '#fc8452', '#9a60b4', '#ea7ccc'
];

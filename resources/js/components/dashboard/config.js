// Define os endpoints para as chamadas à API
export const apiEndpoints = {
    filter: '/dashboard/filter',
    // Caso tenha outros endpoints, ex:
    getSecretaryDetails: (id) => `/dashboard/secretary/${id}/details`,
};

import $ from 'jquery';
import {apiEndpoints} from './config';

export function fetchDashboardData(filters = {}) {
    return $.ajax({
        url: apiEndpoints.filter,
        method: 'GET',
        data: filters,
        dataType: 'json',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
}

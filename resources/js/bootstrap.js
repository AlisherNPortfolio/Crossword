import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Setup CSRF token for all AJAX requests
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Import Bootstrap for JavaScript functionality
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

// Import Bootstrap icons CSS
import 'bootstrap-icons/font/bootstrap-icons.css';

// Custom event listeners
document.addEventListener('DOMContentLoaded', () => {
    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initialize Bootstrap popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Auto-dismiss alerts
    const alertList = document.querySelectorAll('.alert-dismissible');
    alertList.forEach((alert) => {
        setTimeout(() => {
            const dismissAlert = new bootstrap.Alert(alert);
            dismissAlert.close();
        }, 5000);
    });
});

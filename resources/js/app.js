import 'bootstrap';
import { OverlayScrollbars } from 'overlayscrollbars';

// AdminLTE 4
import 'admin-lte';

// Axios global
import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// OverlayScrollbars pour la sidebar AdminLTE
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.querySelector('#sidebar');
    if (sidebar) {
        OverlayScrollbars(sidebar, { scrollbars: { autoHide: 'leave' } });
    }

    // Notifications flash auto-hide
    const alerts = document.querySelectorAll('.alert-dismissible.auto-hide');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = window.bootstrap?.Alert?.getOrCreateInstance(alert);
            bsAlert?.close();
        }, 4000);
    });
});

// Confirmation de suppression
window.confirmDelete = function(formId) {
    if (confirm(window.trans?.confirm_delete || 'Confirmer la suppression ?')) {
        document.getElementById(formId).submit();
    }
};

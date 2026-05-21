/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// app.js
// Функция для показа toast уведомлений
function showToast(message, type = 'info') {
    const types = {
        'success': { bg: 'bg-success', icon: 'bi-check-circle' },
        'error': { bg: 'bg-danger', icon: 'bi-exclamation-triangle' },
        'warning': { bg: 'bg-warning', icon: 'bi-exclamation-circle' },
        'info': { bg: 'bg-info', icon: 'bi-info-circle' }
    };

    const config = types[type] || types.info;
    const toastId = 'toast-' + Date.now();

    const toastHtml = `
                        <div id="${toastId}" class="toast align-items-center text-white ${config.bg} border-0" role="alert">
                            <div class="d-flex">
                                <div class="toast-body">
                                    <i class="bi ${config.icon} me-2"></i>
                                    ${message}
                                </div>
                                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                            </div>
                        </div>
                    `;

    // Контейнер для toast
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'position-fixed top-0 end-0 p-3';
        container.style.zIndex = '1100';
        document.body.appendChild(container);
    }

    // Добавляем toast
    container.insertAdjacentHTML('beforeend', toastHtml);

    // Показываем
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: true,
        delay: 3000
    });
    toast.show();

    // Удаляем после скрытия
    toastElement.addEventListener('hidden.bs.toast', function() {
        this.remove();
    });
}

// Глобальные функции
window.showToast = showToast;

const $ = require('jquery');
// this "modifies" the jquery module: adding behavior to it
// the bootstrap module doesn't export/return anything
require('bootstrap');

// or you can include specific pieces
// require('bootstrap/js/dist/tooltip');
// require('bootstrap/js/dist/popover');

// $(document).ready(function() {
//     $('[data-toggle="popover"]').popover();
// });

// any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';
import  './styles/global.scss';
import 'bootstrap-icons/font/bootstrap-icons.css';
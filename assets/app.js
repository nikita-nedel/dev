import * as bootstrap from 'bootstrap';
import './styles/global.scss';
import 'bootstrap-icons/font/bootstrap-icons.css';

window.bootstrap = bootstrap;

/* ---------- Тосты ---------- */
function showToast(message, type = 'info') {
    let stack = document.getElementById('wb-toast-stack');
    if (!stack) {
        stack = document.createElement('div');
        stack.id = 'wb-toast-stack';
        stack.className = 'wb-toast-stack';
        stack.setAttribute('aria-live', 'polite');
        document.body.appendChild(stack);
    }

    const icon = type === 'error'
        ? 'bi-exclamation-circle'
        : type === 'success'
            ? 'bi-check-circle'
            : 'bi-info-circle';

    const toast = document.createElement('div');
    toast.className = 'wb-toast';
    toast.setAttribute('role', 'status');
    toast.innerHTML = `<i class="bi ${icon}"></i><span></span>`;
    toast.querySelector('span').textContent = message;
    stack.appendChild(toast);

    window.setTimeout(() => {
        toast.classList.add('is-leaving');
        window.setTimeout(() => toast.remove(), 220);
    }, 2800);
}

window.showToast = showToast;

document.addEventListener('DOMContentLoaded', () => {
    /* Мобильное меню */
    const menuButton = document.querySelector('[data-wb-menu]');
    const mobileNav = document.getElementById('wb-mobile-nav');
    if (menuButton && mobileNav) {
        menuButton.addEventListener('click', () => {
            const isOpen = mobileNav.classList.toggle('is-open');
            menuButton.setAttribute('aria-expanded', String(isOpen));
            menuButton.setAttribute('aria-label', isOpen ? 'Закрыть меню' : 'Открыть меню');
            menuButton.innerHTML = `<i class="bi ${isOpen ? 'bi-x-lg' : 'bi-list'}"></i>`;
        });
    }

    /* Закрытие flash-сообщений */
    document.querySelectorAll('[data-wb-flash-close]').forEach((button) => {
        button.addEventListener('click', () => button.closest('.wb-flash')?.remove());
    });

    /* Избранное */
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-wb-favorite]');
        if (!button) return;
        event.preventDefault();
        const active = button.classList.toggle('is-active');
        button.setAttribute('aria-pressed', String(active));
        const icon = button.querySelector('.bi');
        if (icon) {
            icon.classList.toggle('bi-heart', !active);
            icon.classList.toggle('bi-heart-fill', active);
        }
        showToast(active ? 'Сохранено в избранном' : 'Удалено из избранного', 'success');
    });

    /* Ссылки на избранное: отдельной страницы пока нет */
    document.addEventListener('click', (event) => {
        const link = event.target.closest('[data-wb-favorites]');
        if (!link) return;
        event.preventDefault();
        showToast('Раздел «Избранное» скоро появится');
    });

    /* Фильтр заказов в профиле */
    const orderTabs = document.querySelectorAll('[data-order-filter]');
    if (orderTabs.length) {
        orderTabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                const filter = tab.dataset.orderFilter;
                orderTabs.forEach((other) => {
                    const active = other === tab;
                    other.classList.toggle('is-active', active);
                    other.setAttribute('aria-pressed', String(active));
                });
                document.querySelectorAll('[data-order-status]').forEach((card) => {
                    card.hidden = filter !== 'all' && card.dataset.orderStatus !== filter;
                });
            });
        });
    }

    /* Добавление в корзину */
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-wb-add-to-cart]');
        if (!button) return;
        event.preventDefault();
        const name = button.dataset.wbAddToCart || 'Товар';
        showToast(`${name} — в корзине`, 'success');
        const icon = button.querySelector('.bi');
        if (icon) {
            const previous = icon.className;
            icon.className = 'bi bi-check-lg';
            window.setTimeout(() => { icon.className = previous; }, 1100);
        }
    });

    /* Галерея товара */
    const mainImage = document.getElementById('productMainImage');
    document.querySelectorAll('[data-product-thumb]').forEach((thumb) => {
        thumb.addEventListener('click', () => {
            document.querySelectorAll('[data-product-thumb]').forEach((item) => item.classList.remove('is-active'));
            thumb.classList.add('is-active');
            if (mainImage && thumb.dataset.image) {
                mainImage.src = thumb.dataset.image;
            }
        });
    });

    /* Опции товара */
    document.querySelectorAll('[data-option-group]').forEach((group) => {
        group.querySelectorAll('[data-option]').forEach((option) => {
            option.addEventListener('click', () => {
                group.querySelectorAll('[data-option]').forEach((item) => item.classList.remove('is-active'));
                option.classList.add('is-active');
            });
        });
    });

    /* Показать/скрыть пароль */
    document.querySelectorAll('[data-toggle-password]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.querySelector(button.dataset.togglePassword);
            if (!input) return;
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.innerHTML = `<i class="bi ${show ? 'bi-eye-slash' : 'bi-eye'}"></i>`;
            button.setAttribute('aria-label', show ? 'Скрыть пароль' : 'Показать пароль');
        });
    });

    /* Подписка */
    document.querySelectorAll('[data-wb-subscribe]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            const input = form.querySelector('input[type="email"]');
            if (!input || !input.value.trim()) {
                showToast('Введите e-mail', 'error');
                return;
            }
            input.value = '';
            showToast('Спасибо! Проверьте почту', 'success');
        });
    });

    /* Демо-кнопки без бэкенда */
    document.querySelectorAll('[data-wb-demo]').forEach((element) => {
        element.addEventListener('click', (event) => {
            if (element.tagName === 'A') event.preventDefault();
            if (element.matches('.is-soon, .is-disabled, [aria-disabled="true"]')) {
                showToast('Раздел скоро появится');
                return;
            }
            showToast(element.dataset.wbDemo || 'Раздел в разработке');
        });
    });

    /* Копирование контактов продавца */
    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-wb-copy]');
        if (!button) return;
        event.preventDefault();

        const value = button.dataset.wbCopy || '';
        if (!value) return;

        const done = () => showToast('Скопировано: ' + value, 'success');
        const fallback = () => {
            const field = document.createElement('textarea');
            field.value = value;
            field.setAttribute('readonly', 'readonly');
            field.style.position = 'fixed';
            field.style.left = '-9999px';
            document.body.appendChild(field);
            field.select();

            try {
                document.execCommand('copy');
                done();
            } catch (error) {
                showToast('Не удалось скопировать', 'error');
            } finally {
                field.remove();
            }
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(value).then(done).catch(fallback);
        } else {
            fallback();
        }
    });

    /* Редактирование профиля продавца: личные данные и магазин — отдельно */
    const profileEditors = document.querySelectorAll('[data-profile-editor]');
    const openProfileEditor = (target) => {
        if (!profileEditors.length) return;

        profileEditors.forEach((editor) => {
            editor.hidden = editor.dataset.profileEditor !== target;
        });

        const editor = document.querySelector(`[data-profile-editor="${target}"]`);
        if (!editor) return;

        editor.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const firstInput = editor.querySelector('input:not([type="hidden"]), textarea');
        if (firstInput) window.setTimeout(() => firstInput.focus({ preventScroll: true }), 250);
    };

    document.querySelectorAll('[data-profile-edit-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            openProfileEditor(button.dataset.profileEditToggle || 'personal');
        });
    });

    document.querySelectorAll('[data-profile-edit-close]').forEach((button) => {
        button.addEventListener('click', () => {
            const editor = button.closest('[data-profile-editor]');
            if (editor) editor.hidden = true;
        });
    });

    /* Конфетти после сохранения профиля */
    const confettiHost = document.querySelector('[data-profile-confetti]');
    if (confettiHost) {
        const colors = ['#d4a373', '#4d6572', '#5c7864', '#f5c26b', '#ffffff'];

        for (let index = 0; index < 34; index += 1) {
            const piece = document.createElement('span');
            piece.className = 'wb-confetti';
            piece.style.left = Math.random() * 100 + '%';
            piece.style.background = colors[index % colors.length];
            piece.style.animationDelay = Math.random() * 0.25 + 's';
            piece.style.setProperty('--x', (Math.random() - 0.5) * 260 + 'px');
            piece.style.setProperty('--r', Math.random() * 720 - 360 + 'deg');
            confettiHost.appendChild(piece);
            window.setTimeout(() => piece.remove(), 2200);
        }
    }
});

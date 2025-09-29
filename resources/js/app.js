import './details.js';

class Modal {
    constructor(element) {
        this.element = element;
        this.isOpen = false;
        this.init();
    }

    init() {
        // Закрытие по клику на backdrop
        this.element.addEventListener('click', (e) => {
            if (e.target === this.element) {
                this.hide();
            }
        });

        // Закрытие по кнопке закрытия
        const closeBtn = this.element.querySelector('.modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.hide());
        }

        // Закрытие по Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.hide();
            }
        });
    }

    show() {
        this.element.classList.add('show');
        document.body.style.overflow = 'hidden';
        this.isOpen = true;

        this.element.dispatchEvent(new CustomEvent('modal:shown'));
    }

    hide() {
        this.element.classList.remove('show');
        document.body.style.overflow = '';
        this.isOpen = false;

        this.element.dispatchEvent(new CustomEvent('modal:hidden'));
    }

    static getInstance(element) {
        if (!element._modalInstance) {
            element._modalInstance = new Modal(element);
        }
        return element._modalInstance;
    }
}

class Dropdown {
    constructor(element) {
        this.element = element;
        this.toggle = element.querySelector('.dropdown-toggle');
        this.menu = element.querySelector('.dropdown-menu');
        this.init();
    }

    init() {
        this.toggle.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleDropdown();
        });

        document.addEventListener('click', (e) => {
            if (!this.element.contains(e.target)) {
                this.hide();
            }
        });
    }

    toggleDropdown() {
        this.element.classList.toggle('active');
    }

    hide() {
        this.element.classList.remove('active');
    }
}

// Инициализация при загрузке DOM
document.addEventListener('DOMContentLoaded', function () {
    console.log('BookLibrary app loaded');

    // Инициализация dropdown'ов
    document.querySelectorAll('.dropdown').forEach(dropdown => {
        new Dropdown(dropdown);
    });

    // Инициализация модальных окон
    document.querySelectorAll('.modal').forEach(modal => {
        Modal.getInstance(modal);
    });

    // Обработка кнопок для открытия модальных окон
    document.addEventListener('click', function(e) {
        const target = e.target.getAttribute('data-modal-target');
        if (target) {
            e.preventDefault();
            const modal = document.querySelector(target);
            if (modal) {
                Modal.getInstance(modal).show();
            }
        }

        // Обработка кнопок закрытия модальных окон
        if (e.target.hasAttribute('data-modal-close')) {
            const modal = e.target.closest('.modal');
            if (modal) {
                Modal.getInstance(modal).hide();
            }
        }
    });

    // Обработка алертов с кнопкой закрытия
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('alert-close')) {
            e.target.closest('.alert').remove();
        }
    });
});

// Делаем классы доступными глобально
window.Modal = Modal;
window.Dropdown = Dropdown;

// Функция для показа уведомлений (если нужно использовать глобально)
window.showNotification = function(message, type = 'success') {
    const alertContainer = document.querySelector('.alert-container') || createAlertContainer();

    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="alert-close">&times;</button>
    `;

    alertContainer.appendChild(alert);

    // Автоматическое удаление через 5 секунд
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
};

function createAlertContainer() {
    const container = document.createElement('div');
    container.className = 'alert-container';
    document.body.appendChild(container);
    return container;
}

// Общие утилиты
window.utils = {
    // Форматирование даты
    formatDate: function(dateString) {
        if (!dateString) return '';
        return new Date(dateString).toLocaleDateString('ru-RU');
    },

    // Экранирование HTML
    escapeHtml: function(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    },

    // Обрезка текста
    truncate: function(text, length = 50) {
        if (!text || text.length <= length) return text || '';
        return text.substring(0, length) + '...';
    }
};

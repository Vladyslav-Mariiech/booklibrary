document.addEventListener('DOMContentLoaded', function() {
    console.log('Details script loaded');

    // Добавляем обработчик для кнопок "Подробнее"
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('detailsBtn')) {
            const bookId = e.target.getAttribute('data-book-id');
            showBookDetails(bookId);
        }
    });
});

function showBookDetails(bookId) {
    const button = document.querySelector(`[data-book-id="${bookId}"]`);
    const modal = document.getElementById('bookDetailsModal');

    if (!button || !modal) {
        console.error('Button or modal not found');
        return;
    }

    // Заполняем модальное окно данными
    document.getElementById('modal-book-title').textContent = button.dataset.bookTitle;
    document.getElementById('modal-book-authors').textContent = button.dataset.bookAuthors || 'Не указаны';
    document.getElementById('modal-book-description').textContent = button.dataset.bookDescription || 'Описание отсутствует';

    // Обработка даты публикации
    const publishedWrapper = document.getElementById('modal-book-published-wrapper');
    if (button.dataset.bookPublished) {
        document.getElementById('modal-book-published').textContent = button.dataset.bookPublished;
        publishedWrapper.style.display = 'block';
    } else {
        publishedWrapper.style.display = 'none';
    }

    // Обработка изображения
    const modalImage = document.getElementById('modal-book-image');
    if (button.dataset.bookImage) {
        modalImage.src = button.dataset.bookImage;
        modalImage.alt = button.dataset.bookTitle;
        modalImage.style.display = 'block';
    } else {
        modalImage.style.display = 'none';
    }

    // Показываем модальное окно используя глобальный класс Modal
    if (window.Modal) {
        window.Modal.getInstance(modal).show();
    } else {
        // Fallback если Modal класс недоступен
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

// Закрытие модального окна
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal') ||
        e.target.classList.contains('modal-close') ||
        e.target.textContent === 'Закрыть') {

        const modal = document.getElementById('bookDetailsModal');
        if (modal) {
            if (window.Modal) {
                window.Modal.getInstance(modal).hide();
            } else {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        }
    }
});

// Закрытие по Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('bookDetailsModal');
        if (modal && modal.classList.contains('show')) {
            if (window.Modal) {
                window.Modal.getInstance(modal).hide();
            } else {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }
        }
    }
});

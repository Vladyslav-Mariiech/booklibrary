document.addEventListener('DOMContentLoaded', function () {
    const createForm = document.getElementById('createBookForm');
    if (createForm) {
        createForm.addEventListener('submit', handleCreateBook);
    }

    const editForm = document.getElementById('editBookForm');
    if (editForm) {
        editForm.addEventListener('submit', handleEditBook);
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('editBookBtn')) {
            const bookId = e.target.getAttribute('data-id');
            openEditModal(bookId);
        }
    });

    document.addEventListener('submit', function(e) {
        if (e.target.classList.contains('delete-book-form')) {
            e.preventDefault();
            handleDeleteBook(e.target);
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('alert-close')) {
            e.target.closest('.alert').remove();
        }
    });
});

// Функция создания книги
async function handleCreateBook(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            const modal = document.getElementById('bookModal');
            Modal.getInstance(modal).hide();

            form.reset();
            clearValidationErrors();

            showSuccessMessage(data.message || 'Book added.');

            addBookToTable(data.book);

        } else {
            if (data.errors) {
                displayValidationErrors(data.errors);
            } else if (data.message) {
                showErrorMessage(data.message);
            }
        }
    } catch (error) {
        console.error('Error creating book:', error);
        showErrorMessage('An error occurred while saving the book.');
    }
}

// Функция редактирования книги
async function handleEditBook(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const bookId = document.getElementById('edit-book-id').value;

    try {
        const response = await fetch(`/crud/books/${bookId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        console.log('Response from the server during update:', data);

        if (data.success) {
            const modal = document.getElementById('editBookModal');
            Modal.getInstance(modal).hide();

            showSuccessMessage(data.message || 'The book has been updated.');

            updateBookInTable(data.book);

            form.reset();
            clearValidationErrors();

        } else {
            if (data.errors) {
                displayValidationErrors(data.errors);
            } else if (data.message) {
                showErrorMessage(data.message);
            }
        }
    } catch (error) {
        console.error('Error updating book', error);
        showErrorMessage('An error occurred while updating the book.');
    }
}

// Функция открытия модального окна редактирования
async function openEditModal(bookId) {
    try {
        const response = await fetch(`/crud/books/${bookId}/edit`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            fillEditForm(data.book, data.authors);

            const modal = document.getElementById('editBookModal');
            Modal.getInstance(modal).show();
        } else {
            showErrorMessage('Error loading data');
        }
    } catch (error) {
        console.error('Error loading data:', error);
        showErrorMessage('Error loading book data.');
    }
}

// Заполнение формы редактирования
function fillEditForm(book, authors) {
    const form = document.getElementById('editBookForm');
    form.action = `/crud/books/${book.id}`;

    document.getElementById('edit-book-id').value = book.id;
    document.getElementById('edit-title').value = book.title || '';
    document.getElementById('edit-description').value = book.description || '';

    if (book.published_at) {
        const date = new Date(book.published_at);
        const formattedDate = date.toISOString().split('T')[0];
        document.getElementById('edit-published-at').value = formattedDate;
    } else {
        document.getElementById('edit-published-at').value = '';
    }

    // --------- Новый код для авторов (чекбоксы) ----------
    const container = document.getElementById('edit-authors-container');
    container.innerHTML = '';

    authors.forEach(author => {
        const id = author.id;
        const name = `${author.surname} ${author.name}`;
        const checked = book.authors && book.authors.some(a => a.id === id) ? 'checked' : '';

        const checkboxDiv = document.createElement('div');
        checkboxDiv.className = 'form-check';
        checkboxDiv.innerHTML = `
            <input class="form-check-input" type="checkbox" name="authors[]" value="${id}" id="author-${id}" ${checked}>
            <label class="form-check-label" for="author-${id}">${escapeHtml(name)}</label>
        `;
        container.appendChild(checkboxDiv);
    });

    clearValidationErrors();
}

function addBookToTable(book) {
    const tableBody = document.querySelector('table tbody');
    if (!tableBody) return;

    const authors = book.authors && Array.isArray(book.authors)
        ? book.authors.map(a => `${a.surname} ${a.name}`).join('<br>')
        : 'Authors not specified';

    const publishedDate = book.published_at ?
        new Date(book.published_at).toLocaleDateString('ru-RU') : '';

    let imageHtml = '';
    if (book.image) {
        imageHtml = `<img src="/storage/${book.image}" width="60" alt="Cover">`;
    }

    const row = document.createElement('tr');
    row.innerHTML = `
        <td>${escapeHtml(book.title)}</td>
        <td>${escapeHtml(book.description ? (book.description.length > 50 ? book.description.substring(0, 50) + '...' : book.description) : '')}</td>
        <td>${authors}</td>
        <td>${publishedDate}</td>
        <td>${imageHtml}</td>
        <td>
            <button class="btn btn-warning btn-sm editBookBtn" data-id="${book.id}">Edit</button>
            <form action="/crud/books/${book.id}" method="POST" style="display:inline;" class="delete-form">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete a book?')">Delete</button>
            </form>
        </td>
    `;
    tableBody.prepend(row);
}

function updateBookInTable(book) {
    const row = document.querySelector(`button[data-id="${book.id}"]`).closest('tr');
    if (!row) return;

    const authors = book.authors && Array.isArray(book.authors)
        ? book.authors.map(a => `${a.surname} ${a.name}`).join('<br>')
        : 'Авторы не указаны';

    const publishedDate = book.published_at ?
        new Date(book.published_at).toLocaleDateString('ru-RU') : '';

    let imageHtml = '';
    if (book.image) {
        const imagePath = book.image.startsWith('books/')
            ? `/storage/${book.image}`
            : `/storage/books/${book.image}`;
        imageHtml = `<img src="${imagePath}" width="60" alt="Cover">`;
    }

    row.innerHTML = `
        <td>${escapeHtml(book.title)}</td>
        <td>${escapeHtml(book.description ? (book.description.length > 50 ? book.description.substring(0, 50) + '...' : book.description) : '')}</td>
        <td>${authors}</td>
        <td>${publishedDate}</td>
        <td>${imageHtml}</td>
        <td>
            <button class="btn btn-warning btn-sm editBookBtn" data-id="${book.id}">Edit</button>
            <form action="/crud/books/${book.id}" method="POST" style="display:inline;" class="delete-form">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete a book?')">Delete</button>
            </form>
        </td>
    `;
}

function showSuccessMessage(message) {
    showAlert(message, 'success');
}

function showErrorMessage(message) {
    showAlert(message, 'danger');
}

function showAlert(message, type) {
    let alertContainer = document.querySelector('.alert-container');
    if (!alertContainer) {
        alertContainer = document.createElement('div');
        alertContainer.className = 'alert-container';
        document.body.appendChild(alertContainer);
    }

    const alert = document.createElement('div');
    alert.className = `alert alert-${type} alert-dismissible`;
    alert.innerHTML = `
        ${message}
        <button type="button" class="alert-close">&times;</button>
    `;

    alertContainer.appendChild(alert);

    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 5000);
}

async function handleDeleteBook(form) {
    if (!confirm('Are you sure you want to delete this book?')) {
        return;
    }

    const actionUrl = form.action;
    const formData = new FormData(form);

    try {
        const response = await fetch(actionUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (data.success) {
            showSuccessMessage(data.message || 'The book was deleted.');

            const row = form.closest('tr');
            if (row) {
                row.remove();
            }
        } else {
            showErrorMessage(data.message || 'Error deleting book.');
        }
    } catch (error) {
        console.error('Error deleting book:', error);
        showErrorMessage('An error occurred while deleting the book.');
    }
}

function displayValidationErrors(errors) {
    clearValidationErrors();

    Object.keys(errors).forEach(fieldName => {
        const field = document.querySelector(`[name="${fieldName}"], [name="${fieldName}[]"]`);
        if (field) {
            field.classList.add('is-invalid');

            const feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            feedback.textContent = errors[fieldName][0];
            field.parentNode.appendChild(feedback);
        }
    });
}

function clearValidationErrors() {
    document.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
    document.querySelectorAll('.invalid-feedback').forEach(el => {
        el.remove();
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

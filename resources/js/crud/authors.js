document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.modal').forEach(modal => {
        Modal.getInstance(modal);
    });

    const createForm = document.getElementById('createAuthorForm');
    if (createForm) {
        createForm.addEventListener('submit', handleCreateAuthor);
    }

    const editForm = document.getElementById('editAuthorForm');
    if (editForm) {
        editForm.addEventListener('submit', handleEditAuthor);
    }

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('editAuthorBtn')) {
            const authorId = e.target.getAttribute('data-id');
            openEditAuthorModal(authorId);
        }
    });

    document.addEventListener('submit', function(e) {
        if (e.target.classList.contains('delete-author-form')) {
            e.preventDefault();
            handleDeleteAuthor(e.target);
        }
    });
});

// Создание автора
async function handleCreateAuthor(e) {
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
            const modal = document.getElementById('authorModal');
            Modal.getInstance(modal).hide();

            form.reset();
            clearValidationErrors();

            showSuccessMessage(data.message || 'Автор успешно добавлен!');

            addAuthorToTable(data.author);

        } else {
            if (data.errors) {
                displayValidationErrors(data.errors);
            } else if (data.message) {
                showErrorMessage(data.message);
            }
        }
    } catch (error) {
        console.error('Ошибка при создании автора:', error);
        showErrorMessage('Произошла ошибка при сохранении автора');
    }
}

// Редактирование автора
async function handleEditAuthor(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const authorId = document.getElementById('edit-author-id').value;

    try {
        const response = await fetch(`/crud/authors/${authorId}`, {
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
            const modal = document.getElementById('editAuthorModal');
            Modal.getInstance(modal).hide();

            showSuccessMessage(data.message || 'Автор успешно обновлен!');

            updateAuthorInTable(data.author);

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
        console.error('Ошибка при обновлении автора:', error);
        showErrorMessage('Произошла ошибка при обновлении автора');
    }
}

// Открытие модального окна редактирования
async function openEditAuthorModal(authorId) {
    try {
        const response = await fetch(`/crud/authors/${authorId}/edit`, {
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
            fillEditAuthorForm(data.author);

            const modal = document.getElementById('editAuthorModal');
            Modal.getInstance(modal).show();
        } else {
            showErrorMessage('Ошибка при загрузке данных автора');
        }
    } catch (error) {
        console.error('Ошибка при загрузке данных:', error);
        showErrorMessage('Ошибка при загрузке данных автора');
    }
}

// Заполнение формы редактирования
function fillEditAuthorForm(author) {
    const form = document.getElementById('editAuthorForm');
    form.action = `/crud/authors/${author.id}`;

    document.getElementById('edit-author-id').value = author.id;
    document.getElementById('edit-surname').value = author.surname || '';
    document.getElementById('edit-name').value = author.name || '';
    document.getElementById('edit-patronymic').value = author.patronymic || '';

    clearValidationErrors();
}

// Удаление автора
async function handleDeleteAuthor(form) {
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new FormData(form)
        });

        let data = {};
        try {
            data = await response.json();
        } catch (_) {}

        if (response.ok && data.success) {
            showSuccessMessage(data.message || 'Author has been deleted.');
            form.closest('tr').remove();
        } else {
            showErrorMessage(data.message || `Error deleting author (code ${response.status})`);
        }
    } catch (error) {
        console.error('Error deleting author:', error);
        showErrorMessage(error.message || 'There was an error deleting the author.');
    }
}

// Добавление автора в таблицу
function addAuthorToTable(author) {
    const tableBody = document.querySelector('table tbody');
    if (!tableBody) return;

    const emptyRow = tableBody.querySelector('td[colspan="6"]');
    if (emptyRow) {
        emptyRow.closest('tr').remove();
    }

    const row = document.createElement('tr');
    row.innerHTML = `
        <td>${escapeHtml(author.surname)}</td>
        <td>${escapeHtml(author.name)}</td>
        <td>${escapeHtml(author.patronymic) || '-'}</td>
        <td><strong>${escapeHtml(author.surname)} ${escapeHtml(author.name)} ${escapeHtml(author.patronymic || '')}</strong></td>
        <td>0</td>
        <td>
            <button class="btn btn-warning btn-sm editAuthorBtn" data-id="${author.id}">Редактировать</button>
            <form action="/crud/authors/${author.id}" method="POST" style="display:inline;" class="delete-author-form">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Удалить автора ${escapeHtml(author.surname)} ${escapeHtml(author.name)}?')">Удалить</button>
            </form>
        </td>
    `;
    tableBody.prepend(row);
}

// Обновление строки в таблице
function updateAuthorInTable(author) {
    const row = document.querySelector(`button[data-id="${author.id}"]`).closest('tr');
    if (!row) {
        console.error('Строка для обновления не найдена, ID автора:', author.id);
        return;
    }

    const cells = row.querySelectorAll('td');
    cells[0].textContent = author.surname;
    cells[1].textContent = author.name;
    cells[2].textContent = author.patronymic || '-';
    cells[3].innerHTML = `<strong>${escapeHtml(author.surname)} ${escapeHtml(author.name)} ${escapeHtml(author.patronymic || '')}</strong>`;

    const deleteBtn = cells[5].querySelector('button[type="submit"]');
    if (deleteBtn) {
        deleteBtn.setAttribute('onclick', `return confirm('Удалить автора ${escapeHtml(author.surname)} ${escapeHtml(author.name)}?')`);
    }

    console.log('Строка автора успешно обновлена');
}

// Вспомогательные функции (используем те же, что и для книг)
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

function displayValidationErrors(errors) {
    clearValidationErrors();

    Object.keys(errors).forEach(fieldName => {
        const field = document.querySelector(`[name="${fieldName}"]`);
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

import { loadTasks } from './show.js';
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('edit-task-btn')) {
        const btn = e.target;
        document.getElementById('edit-task-id').value = btn.dataset.id;
        document.getElementById('edit-task-name').value = btn.dataset.name;
        document.getElementById('edit-task-description').value = btn.dataset.desc;
        document.getElementById('edit-task-completed').checked = btn.dataset.completed === 'true';

        document.getElementById('edit-modal').style.display = 'flex';
    }
});

// Закрытие модалки
document.getElementById('close-edit-modal').addEventListener('click', () => {
    document.getElementById('edit-modal').style.display = 'none';
});

document.getElementById('edit-task-form').addEventListener('submit', async function (e) {
    e.preventDefault();

    const id = document.getElementById('edit-task-id').value;
    const name = document.getElementById('edit-task-name').value;
    const description = document.getElementById('edit-task-description').value;
    const isCompleted = document.getElementById('edit-task-completed').checked;

    const token = localStorage.getItem('jwt_token');

    try {
        console.log({ name, description, isCompleted })
        const response = await fetch(`/api/task/${id}`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ name, description, isCompleted })
        });

        if (response.ok) {
            document.getElementById('edit-modal').style.display = 'none';
            await loadTasks(); // Обновим список
        } else {
            const error = await response.json();
            alert(error.message || 'Ошибка при обновлении');
        }

    } catch (err) {
        console.error('Ошибка при обновлении:', err);
    }
});
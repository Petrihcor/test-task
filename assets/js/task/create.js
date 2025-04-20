import { renderTask } from '../../app.js';
// Обработчик отправки формы
document.getElementById('task-form').addEventListener('submit', async function (e) {
    e.preventDefault();

    const name = document.getElementById('task_name').value;
    const description = document.getElementById('task_description').value;

    // Проверка на пустые поля
    if (!name) {
        alert('Task name is required');
        return;
    }

    try {
        // Получаем токен из localStorage
        const token = localStorage.getItem('jwt_token');

        if (!token) {
            alert('You must be logged in to add a task');
            return;
        }

        // Отправляем запрос на создание задачи
        const response = await fetch('/api/task', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ name, description })
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.error || 'Error adding task');
        }

        // После успешного добавления задачи, очищаем форму
        document.getElementById('task-form').reset();

        renderTask(data.task);

        alert('Task added successfully!');

    } catch (err) {
        console.error('Error:', err);
        alert('An error occurred while adding the task');
    }
});


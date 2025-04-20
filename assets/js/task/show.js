import { renderTask } from '../../app.js';
// Функция для загрузки задач с учетом выбранного статуса
export async function loadTasks() {
    const token = localStorage.getItem('jwt_token');
    if (!token) {
        console.warn('Пользователь не авторизован, задачи не загружаем');
        return;
    }

    // Получаем значение выбранного фильтра
    const allChecked = document.getElementById('filter-all').checked;
    const completedChecked = document.getElementById('filter-completed').checked;
    const incompleteChecked = document.getElementById('filter-incomplete').checked;

    let isCompleted = null;

    // Устанавливаем значение isCompleted в зависимости от выбранного фильтра
    if (completedChecked) {
        isCompleted = true;
    } else if (incompleteChecked) {
        isCompleted = false;
    } else if (allChecked) {
        isCompleted = null;  // Для "Все" передаем null
    }

    try {

        let url = '/api/task';
        if (isCompleted !== null) {
            url += `?isCompleted=${isCompleted}`;  // Передаем параметр фильтра
        }
        console.log("URL запроса:", url);
        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });
        console.log("Ответ от сервера:", response);
        const tasks = await response.json();
        console.log("Задачи:", tasks);
        const taskList = document.getElementById('task-list');
        taskList.innerHTML = ''; // Очищаем список задач

        tasks.forEach(task => {

            renderTask(task);
        });

    } catch (err) {
        console.error('Ошибка загрузки задач:', err);
    }
}

// Слушаем изменение состояния фильтров
document.getElementById('filter-all').addEventListener('change', loadTasks);
document.getElementById('filter-completed').addEventListener('change', loadTasks);
document.getElementById('filter-incomplete').addEventListener('change', loadTasks);

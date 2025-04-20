import './bootstrap.js';
import './styles/app.css';

import './js/auth/autoLogin.js';
import './js/auth/login.js';
import './js/auth/logout.js';
import './js/auth/register.js';

import './js/task/create.js';
import './js/task/show.js';
import './js/task/delete.js';
import './js/task/edit.js';
import { loadTasks } from './js/task/show.js';


export function showHome() {
    loadTasks();
    document.getElementById('task-container').style.display = 'block';
    document.getElementById('logout-btn').style.display = 'inline-block';
    document.getElementById('login_container').style.display = 'none'; // Скрываем весь контейнер
}


// Функция для переключения между формами (вход/регистрация)
export function showForm(formType) {
    const registerForm = document.getElementById('register-form');
    const loginForm = document.getElementById('login');

    if (formType === 'register') {
        registerForm.style.display = 'block';
        loginForm.style.display = 'none';
    } else {
        registerForm.style.display = 'none';
        loginForm.style.display = 'block';
    }

}

// Добавляем обработчики на кнопки для переключения форм
document.querySelector('.switch-button[data-form="register"]').addEventListener('click', () => {
    showForm('register');
});

document.querySelector('.switch-button[data-form="login"]').addEventListener('click', () => {
    showForm('login');
});

// Функция для очистки ошибок
export function clearErrorMessages(formId) {
    const form = document.getElementById(formId);
    const errorEl = form.querySelector('.form-error');
    if (errorEl) {
        errorEl.textContent = ''; // Очистить сообщение об ошибке
        errorEl.style.display = 'none'; // Скрыть ошибку
    }
}

export function showErrorMessage(formId, message) {
    const form = document.getElementById(formId);
    let errorEl = form.querySelector('.form-error');

    // Если элемент ошибки не существует, создаем его
    if (!errorEl) {
        errorEl = document.createElement('div');
        errorEl.classList.add('form-error');
        errorEl.style.color = 'red';
        form.appendChild(errorEl);
    }

    // Показываем сообщение об ошибке
    errorEl.textContent = message;
    errorEl.style.display = 'block';
}
//Показ формы создания
document.addEventListener("DOMContentLoaded", function () {
    const toggleHeader = document.getElementById("toggle-task-form");
    const formWrapper = document.getElementById("task-form-wrapper");

    toggleHeader.addEventListener("click", () => {
        if (formWrapper.style.display === "none") {
            formWrapper.style.display = "block";
        } else {
            formWrapper.style.display = "none";
        }
    });
});

export function formatDate(dateString) {
    const date = new Date(dateString);
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // месяц с 0!
    const year = date.getFullYear();

    return `${hours}:${minutes} ${day}.${month}.${year}`;
}

// Функция рендеринга задачи в DOM
export function renderTask(task) {
    const taskList = document.getElementById('task-list');
    console.log(task.createdAt);
    const li = document.createElement('li');
    li.classList.add('task-item');
    li.innerHTML = `                
        <div class="task-name">${task.name}</div>
    <div class="task-meta">${task.description || 'Без описания'}</div>
    <div class="update">Upd: ${formatDate(task.updatedAt)}</div>
    <div class="task-status">${task.isCompleted ? '✅ Выполнено' : '🕒 В процессе'}</div>
    <div class="task-actions">
        <button class="edit-task-btn"
            data-id="${task.id}"
            data-name="${task.name}"
            data-desc="${task.description || ''}"
            data-completed="${task.isCompleted}">
            Редактировать
        </button>
        <button class="delete-task-btn" data-id="${task.id}">Удалить</button>
    </div>
    `;

    taskList.appendChild(li);

}
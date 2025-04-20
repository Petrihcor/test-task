// Функция для автологине при загрузке страницы
import { showHome } from '../../app.js';

async function tryAutoLogin() {
    const token = localStorage.getItem('jwt_token');
    if (!token) {
        showLoginForm();
        return;
    }

    try {
        const response = await fetch('/api/profile', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok) {
            showHome(); // Показываем приветствие
        } else {
            localStorage.removeItem('jwt_token');
            showLoginForm();
        }

    } catch (err) {
        console.error('Ошибка автоавторизации:', err);
        showLoginForm();
    }
}

function showLoginForm() {
    document.getElementById('login_container').style.display = 'flex';
}

// Автологин при загрузке страницы
document.addEventListener('DOMContentLoaded', tryAutoLogin);
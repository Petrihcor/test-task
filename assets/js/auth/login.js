import { clearErrorMessages } from '../../app.js';
import { showErrorMessage } from '../../app.js';
import { showHome } from '../../app.js';
document.getElementById('login-form').addEventListener('submit', async function(e) {
    e.preventDefault();

    const username = e.target.username.value;
    const password = e.target.password.value;

    // Очищаем предыдущие ошибки
    clearErrorMessages('login-form');

    try {
        const loginResponse = await fetch('/api/login_check', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });

        const loginData = await loginResponse.json();

        if (!loginResponse.ok) {
            // Отображаем ошибку, если авторизация не удалась
            showErrorMessage('login-form', 'Неверное имя пользователя или пароль');
            return;
        }

        console.log('JWT Token:', loginData.token);
        localStorage.setItem('jwt_token', loginData.token);

        // Получаем защищённые данные
        const token = loginData.token;

        const profileResponse = await fetch('/api/profile', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Content-Type': 'application/json'
            }
        });

        const profileData = await profileResponse.json();

        if (profileResponse.ok) {
            console.log('Данные профиля:', profileData);
            showHome();
        } else {
            console.error('Ошибка получения данных профиля', profileData);
        }

    } catch (err) {
        console.error('Сетевая ошибка:', err);
    }
});

// Функция для отображения ошибки на форме
function showLoginError(message) {
    const errorEl = document.getElementById('login-error');
    if (!errorEl) {
        const errorElement = document.createElement('div');
        errorElement.id = 'login-error';
        errorElement.style.color = 'red';
        errorElement.style.marginTop = '10px';
        errorElement.textContent = message;
        document.getElementById('login-form').appendChild(errorElement);
    } else {
        errorEl.textContent = message;
        errorEl.style.display = 'block';
    }
}
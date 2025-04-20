//регистрация
import { showForm } from '../../app.js';
import { clearErrorMessages } from '../../app.js';
import { showErrorMessage } from '../../app.js';
document.getElementById('register-form-inner').addEventListener('submit', async function (e) {
    e.preventDefault();

    const username = document.querySelector('[name="registration_form[username]"]').value;
    const password = document.querySelector('[name="registration_form[plainPassword]"]').value;

    clearErrorMessages('register-form-inner');
    try {
        const response = await fetch('/api/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                username: username,
                password: password
            })
        });
        if (username.length < 3) {
            showErrorMessage('register-form-inner', 'Имя пользователя должно быть не менее 3 символов');
            return;
        }

        if (username.length > 20) {
            showErrorMessage('register-form-inner', 'Имя пользователя не должно превышать 20 символов');
            return;
        }

        const usernameRegex = /^[\p{L}][\p{L}0-9_]*$/u; // Имя должно начинаться с буквы и содержать только буквы, цифры и подчёркивания
        if (!usernameRegex.test(username)) {
            showErrorMessage('register-form-inner', 'Имя пользователя должно начинаться с буквы и содержать только буквы, цифры и подчёркивания');
            return;
        }

        // Валидация пароля
        if (password.length < 6) {
            showErrorMessage('register-form-inner', 'Пароль должен быть не менее 6 символов');
            return;
        }

        if (password.length > 20) {
            showErrorMessage('register-form-inner', 'Пароль не должен превышать 20 символов');
            return;
        }

        const data = await response.json();

        if (response.ok) {
            alert('Регистрация прошла успешно!');
            showForm('login'); // переключаемся на форму входа
        } else {
            alert(data.error || 'Ошибка регистрации');
        }
    } catch (err) {
        console.error('Ошибка запроса:', err);
    }
});
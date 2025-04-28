<template>
  <div class="page-wrapper">
    <div class="form-center-body">
      <div class="form-switcher">
        <button
            :class="['switch-button', { 'active-switch': activeForm === 'register' }]"
            @click="activeForm = 'register'"
        >
          Регистрация
        </button>
        <button
            :class="['switch-button', { 'active-switch': activeForm === 'login' }]"
            @click="activeForm = 'login'"
        >
          Вход
        </button>
      </div>

      <div v-show="activeForm === 'register'" class="form-container">
        <form id="register-form-inner" @submit.prevent="submitRegister">
          <div class="form-group">
            <label for="reg-username">Имя</label>
            <input v-model="registerForm.username" type="text" id="reg-username" class="form-control" required />
          </div>
          <div class="form-group">
            <label for="reg-password">Пароль</label>
            <input v-model="registerForm.password" type="password" id="reg-password" class="form-control" required />
          </div>
          <button type="submit" class="btn">Регистрация</button>
        </form>
      </div>

      <div v-show="activeForm === 'login'" class="form-container">
        <form id="login-form" @submit.prevent="submitLogin">
          <div v-if="loginError" style="color: red; margin-top: 10px;">{{ loginError }}</div>
          <div class="form-group">
            <label for="login-username">Имя</label>
            <input v-model="loginForm.username" type="text" id="login-username" class="form-control" required />
          </div>
          <div class="form-group">
            <label for="login-password">Пароль</label>
            <input v-model="loginForm.password" type="password" id="login-password" class="form-control" required />
          </div>
          <button type="submit" class="btn btn-lg btn-primary">Войти</button>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, defineEmits } from 'vue'

const emit = defineEmits();
const activeForm = ref('register')
const isLoggedIn = ref(false)
const username = ref('')

const registerForm = ref({
  username: '',
  password: ''
})

const loginForm = ref({
  username: '',
  password: ''
})
const loginError = ref('');


const submitRegister = async () => {
  // Здесь отправка на app_register, например через fetch
  console.log('Регистрация', registerForm.value)
  try {
    const response = await fetch('/api/register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username: registerForm.value.username,
        password: registerForm.value.password // если backend ждет plainPassword
      })
    });

    if (!response.ok) {
      const data = await response.json();
      throw new Error(data.message || 'Ошибка регистрации');
    }

    console.log('Успешная регистрация');
    activeForm.value = 'login'; // переключаемся на форму входа после регистрации
  } catch (error) {
    console.error('Ошибка при регистрации:', error.message);
    alert(error.message);
  }
}


const submitLogin = async () => {

  // Очищаем возможные старые ошибки (если хочешь обработать это красиво через ref)
  loginError.value = '';

  try {
    const loginResponse = await fetch('/api/login_check', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username: loginForm.value.username,
        password: loginForm.value.password
      })
    });

    const loginData = await loginResponse.json();

    if (!loginResponse.ok) {
      // Ошибка авторизации
      loginError.value = 'Неверное имя пользователя или пароль';
      return;
    }
    const token = loginData.token;
    console.log('JWT Token:', token);
    localStorage.setItem('jwt_token', loginData.token);

    // Теперь можно получить защищённые данные профиля
    const profileResponse = await fetch('/api/profile', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${loginData.token}`,
        'Content-Type': 'application/json'
      }
    });

    const data = await profileResponse.json();

    if (profileResponse.ok) {
      emit('login-success', { username: data.username, token: token });
    } else {
      console.error('Ошибка получения данных профиля', data);
    }

  } catch (err) {
    console.error('Сетевая ошибка:', err);
    loginError.value = 'Сетевая ошибка, попробуйте позже';
  }
}
</script>

<style scoped>
/* Для всей страницы */
html, body {
  height: 100%;
  margin: 0;
  padding: 0;
}

/* Обертка для центрирования */
.page-wrapper {
  display: flex;
  justify-content: center; /* по горизонтали */
  align-items: center;     /* по вертикали */
  min-height: 100vh;       /* минимум высота экрана */
  background-color: #f8f9fa; /* светлый фон */
}

/* Форма — компактная */
.form-center-body {
  width: 100%;
  max-width: 400px; /* ограничиваем ширину */
  padding: 30px;
  background-color: white;
  border-radius: 8px;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
}


h1, h3 {
  text-align: center;
  font-size: 24px;
  margin-bottom: 20px;
}

input.form-control, input[type="text"], input[type="password"], textarea {
  width: 100%;
  padding: 10px;
  margin: 10px 0;
  border-radius: 5px;
  border: 1px solid #ccc;
  box-sizing: border-box;
}

.btn {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
  width: 200px; /* фиксированная ширина */
  margin: 20px auto 0 auto; /* центрирование */
  display: block; /* нужно чтобы работало margin auto */
}

.btn:hover {
  background-color: #0056b3;
}

.form-switcher {
  display: flex;
  justify-content: space-around; /* поровну место */
  margin-bottom: 20px;
}

.switch-button {
  border: none;
  font-size: 16px;
  cursor: pointer;
  padding: 10px 20px;
  border-radius: 5px;
  transition: background-color 0.3s;
}

.switch-button:hover {
  background-color: #d6d8db;
}

.switch-button.active-switch {
  background-color: #007bff; /* активный — синий */
  color: #fff;
  font-weight: bold;
}

label {
  display: block;
  font-size: 14px;
  margin-bottom: 5px;
}
</style>
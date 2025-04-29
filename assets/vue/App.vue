<template>
  <div>
    <div v-if="isAuthenticated" class="header">
      <span>Добро пожаловать, {{ username }}!</span>
      <button @click="handleLogout" class="logout-button">Выйти</button>
    </div>
    <auth-form v-if="!isAuthenticated" @login-success="handleLogin" />
    <!-- Здесь можно добавлять другие компоненты, например, для отображения контента -->
    <taskList v-if="isAuthenticated" :username="username" @logout="handleLogout" />
  </div>
</template>

<script setup>
import { ref, watch  } from 'vue'
import AuthForm from './controllers/authorization.vue'
import taskList from './controllers/taskList.vue'

const isAuthenticated = ref(!!localStorage.getItem('jwt_token'));  // Проверка на аутентификацию
const username = ref(localStorage.getItem('username') || '');  // Получение имени из localStorage
// Отслеживаем изменения переменной isAuthenticated и выводим её в консоль
watch(isAuthenticated, (newValue) => {
  console.log('Значение isAuthenticated изменилось:', newValue);
});

const handleLogin = (userData) => {
  // Сохраняем токен в localStorage
  localStorage.setItem('username', userData.username);
  localStorage.setItem('jwt_token', userData.token);
  username.value = userData.username;
  // Обновляем состояние аутентификации
  isAuthenticated.value = true;
};

const handleLogout = () => {
  localStorage.removeItem('jwt_token');
  localStorage.removeItem('username');
  username.value = '';
  isAuthenticated.value = false; // Обновляем состояние аутентификации
}
</script>

<style scoped>
/* Стили для главного компонента */
/* Контейнер для приветствия и кнопки */
.header {
  position: absolute;
  top: 20px; /* Расстояние от верхнего края */
  right: 20px; /* Расстояние от правого края */
  display: flex;
  align-items: center;
  font-size: 16px;
  color: #333;
}

.header span {
  margin-right: 10px;
}

.logout-button {
  background-color: #dc3545; /* Красный */
  color: white;
  border: none;
  padding: 8px 15px;
  font-size: 14px;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.logout-button:hover {
  background-color: #c82333; /* Темно-красный */
}
</style>

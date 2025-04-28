<script setup>
const submitLogin = async () => {
  try {
    const response = await fetch('/api/login_check', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username: loginForm.value.username,
        password: loginForm.value.password
      })
    });

    if (!response.ok) {
      const data = await response.json();
      throw new Error(data.message || 'Ошибка входа');
    }

    const data = await response.json();
    localStorage.setItem('jwt_token', data.token); // сохраняем токен в localStorage

    console.log('Успешный вход! Токен сохранен');
    window.location.reload(); // можно заменить на роутинг если будет vue-router
  } catch (error) {
    console.error('Ошибка при входе:', error.message);
    alert(error.message); // показываем ошибку юзеру
  }
}
</script>

<template>

</template>

<style scoped>

</style>
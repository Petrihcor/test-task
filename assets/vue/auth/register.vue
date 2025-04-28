<script setup>
const submitRegister = async () => {
  try {
    const response = await fetch('/api/register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        username: registerForm.value.username,
        plainPassword: registerForm.value.password // если backend ждет plainPassword
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
</script>

<template>

</template>

<style scoped>

</style>
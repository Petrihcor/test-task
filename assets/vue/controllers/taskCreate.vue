<template>
  <div>
    <button @click="toggleForm" class="btn btn-primary">
      {{ showForm ? 'Скрыть форму' : 'Создать задачу' }}
    </button>

    <div v-if="showForm" id="task-form-wrapper" style="margin-top: 20px;">
      <form @submit.prevent="submitTask" id="task-form">
        <div class="form-group">
          <label for="task_name">Название задачи</label>
          <input v-model="task.name" type="text" id="task_name" class="form-control" required />
        </div>

        <div class="form-group">
          <label for="task_description">Описание задачи</label>
          <textarea v-model="task.description" id="task_description" class="form-control"></textarea>
        </div>
        <div class="form-group">
          <label for="task-image">Изображение:</label>
          <input type="file" id="task-image" @change="handleFileUpload" />
        </div>
        <button type="submit" class="btn btn-success">Создать</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, defineEmits } from 'vue'

const emit = defineEmits(['task-created'])

const showForm = ref(false)

const task = ref({
  name: '',
  description: '',
  imageBase64: ''  // Добавляем поле для изображения
})

const selectedFile = ref(null);

function handleFileUpload(event) {
  const file = event.target.files[0];
  if (file) {
    // Преобразуем файл в Base64
    const reader = new FileReader();
    reader.onloadend = () => {
      // Получаем MIME тип файла (например, image/png, image/jpeg)
      const fileType = file.type.split('/')[1]; // 'png', 'jpeg', 'gif' и т.д.

      // Добавляем префикс для Base64 строки
      task.value.imageBase64 = `data:image/${fileType};base64,${reader.result.split(',')[1]}`;
    };
    reader.readAsDataURL(file);
  }
}

const toggleForm = () => {
  showForm.value = !showForm.value
}

const submitTask = async () => {
  if (!task.value.name.trim()) {
    alert('Название обязательно');
    return;
  }

  try {
    const token = localStorage.getItem('jwt_token');

    if (!token) {
      alert('Для добавления задачи нужно пройти авторизацию');
      return;
    }

    const response = await fetch('/api/task', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`
      },
      body: JSON.stringify({
        name: task.value.name,
        description: task.value.description,
        imageBase64: task.value.imageBase64 // Добавляем изображение с префиксом
      })
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.error || 'Ошибка добавления');
    }

    // Очищаем форму
    task.value.name = '';
    task.value.description = '';
    task.value.imageBase64 = ''; // Очищаем изображение
    showForm.value = false;

    emit('task-created', data.task);

    alert('Задача успешно добавлена!');

  } catch (err) {
    console.error('Ошибка:', err);
    alert('Ошибка при добавлении задачи');
  }
}
</script>


<style scoped>
/* Общая обёртка формы */
#task-form-wrapper {
  background-color: #ffffff;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  max-width: 500px;
  margin: 20px auto;
}

/* Поля формы */
.form-group {
  margin-bottom: 15px;
  text-align: left;
}

/* Лейблы */
label {
  display: block;
  font-size: 14px;
  margin-bottom: 5px;
  font-weight: 600;
}

/* Инпуты и текстовые поля */
input.form-control,
textarea.form-control {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  box-sizing: border-box;
  font-size: 14px;
  resize: vertical;
}

/* Кнопка создания */
button.btn-success {
  width: 100%;
  padding: 10px;
  font-size: 16px;
  background-color: #007bff;
  border: none;
  border-radius: 5px;
  color: white;
  cursor: pointer;
  margin-top: 10px;
  transition: background-color 0.3s;
}

button.btn-success:hover {
  background-color: #0056b3;
}

/* Кнопка "Создать задачу"/"Скрыть форму" */
button.btn-primary {
  display: block;
  margin: 0 auto;
  margin-bottom: 20px;
  padding: 10px 20px;
  font-size: 16px;
  background-color: #007bff;
  border: none;
  border-radius: 5px;
  color: white;
  cursor: pointer;
  transition: background-color 0.3s;
}

button.btn-primary:hover {
  background-color: #0056b3;
}
</style>


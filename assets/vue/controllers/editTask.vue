<template>
  <div v-if="isVisible" class="modal">
    <div class="modal-content">
      <span class="close" @click="closeModal">&times;</span>
      <h2>Редактировать задачу</h2>
      <form @submit.prevent="submitEdit">
        <input type="hidden" v-model="task.id" />

        <label for="edit-task-name">Название:</label>
        <input type="text" id="edit-task-name" v-model="task.name" required />

        <label for="edit-task-description">Описание:</label>
        <textarea id="edit-task-description" v-model="task.description"></textarea>

        <label>
          <input type="checkbox" v-model="task.isCompleted" />
          Выполнено
        </label>

        <div v-if="task.image && !removeImage">
          <button type="button" @click="removeCurrentImage">Удалить текущее изображение</button>
        </div>

        <label for="edit-task-image">Новое изображение:</label>
        <input type="file" id="edit-task-image" @change="handleFileUpload" />

        <button type="submit">Сохранить</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, defineExpose, defineEmits } from 'vue'

const emit = defineEmits(['task-updated'])

const isVisible = ref(false)

const task = ref({
  id: '',
  name: '',
  description: '',
  isCompleted: false,
  image: '',
  imageBase64: ''
})
const selectedFile = ref(null);
const removeImage = ref(false);

function handleFileUpload(event) {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onloadend = () => {
      const fileType = file.type.split('/')[1];
      task.value.imageBase64 = `data:image/${fileType};base64,${reader.result.split(',')[1]}`;
      selectedFile.value = file;
    };
    reader.readAsDataURL(file);
  }
}

function removeCurrentImage() {
  removeImage.value = true;
}

function openModal(taskData) {
  task.value = {
    ...taskData,
    isCompleted: !!taskData.isCompleted,
    imageBase64: taskData.imageBase64 || ''
  };
  removeImage.value = false;
  selectedFile.value = null;
  isVisible.value = true;
}

function closeModal() {
  isVisible.value = false
}

async function submitEdit() {
  const token = localStorage.getItem('jwt_token');

  const formData = {
    name: task.value.name,
    description: task.value.description,
    isCompleted: !!task.value.isCompleted,
  };

  if (removeImage.value) {
    formData.removeImage = true;
  }

  if (selectedFile.value) {
    formData.imageBase64 = task.value.imageBase64;
  }

  try {
    const response = await fetch(`/api/task/${task.value.id}`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${token}`,
      },
      body: JSON.stringify(formData)
    });

    if (response.ok) {
      closeModal();
      emit('task-updated');
    } else {
      const error = await response.json();
      alert(error.message || 'Ошибка при обновлении');
    }
  } catch (err) {
    console.error('Ошибка при обновлении:', err);
  }
}

defineExpose({ openModal })
</script>

<style scoped>
.modal-content form,
.modal-content input,
.modal-content textarea,
.modal-content button {
  box-sizing: border-box;
}

/* Модалка */
.modal {
  display: flex;
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  justify-content: center;
  align-items: center;
  background-color: rgba(0, 0, 0, 0.5);
  opacity: 0;
  animation: fadeIn 0.3s forwards;
}

/* Контейнер модалки */
.modal-content {
  background: #fff;
  padding: 2rem;
  border-radius: 10px;
  position: relative;
  width: 100%;
  max-width: 600px;
  box-sizing: border-box;
  overflow: auto;
}

/* Заголовок */
h2 {
  font-size: 1.5rem;
  margin-bottom: 1rem;
}

/* Стили для формы */
label {
  display: block;
  margin: 0.5rem 0 0.3rem;
  font-size: 1rem;
}

input, textarea {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 5px;
  font-size: 1rem;
  margin-bottom: 1rem;
}

/* Чекбокс */
input[type="checkbox"] {
  width: auto;
  margin-right: 0.5rem;
}

/* Кнопка */
button {
  background-color: #007bff;
  color: white;
  padding: 0.8rem 1.5rem;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1rem;
}

button:hover {
  background-color: #0056b3;
}

/* Кнопка закрытия */
.close {
  position: absolute;
  right: 10px;
  top: 10px;
  cursor: pointer;
  font-size: 1.5rem;
}

/* Анимация появления */
@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}
</style>

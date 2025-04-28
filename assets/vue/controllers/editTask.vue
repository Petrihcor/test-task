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

        <button type="submit">Сохранить</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, defineExpose, defineEmits } from 'vue'

const emit = defineEmits(['task-updated']) // объявляем событие

const isVisible = ref(false)

const task = ref({
  id: '',
  name: '',
  description: '',
  isCompleted: false
})

function openModal(taskData) {
  task.value = { ...taskData }
  isVisible.value = true
}

function closeModal() {
  isVisible.value = false
}

async function submitEdit() {
  const token = localStorage.getItem('jwt_token')
  try {
    const response = await fetch(`/api/task/${task.value.id}`, {
      method: 'PUT',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        name: task.value.name,
        description: task.value.description,
        isCompleted: task.value.isCompleted
      })
    })

    if (response.ok) {
      closeModal()
      emit('task-updated') // ЭМИТИМ СОБЫТИЕ!
    } else {
      const error = await response.json()
      alert(error.message || 'Ошибка при обновлении')
    }
  } catch (err) {
    console.error('Ошибка при обновлении:', err)
  }
}

defineExpose({ openModal })
</script>

<style scoped>
.modal {
  display: flex;
  position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  justify-content: center;
  align-items: center;
  background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 10px;
  position: relative;
}

.close {
  position: absolute;
  right: 10px;
  top: 10px;
  cursor: pointer;
}
</style>

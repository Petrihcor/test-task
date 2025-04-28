<template>
  <task-create @task-created="loadTasks" />
  <div>
    <div class="status-filters">
      <label class="status-option">
        <input type="radio" name="status" value="all" v-model="filterStatus" /> Все
      </label>
      <label class="status-option">
        <input type="radio" name="status" value="true" v-model="filterStatus" /> Выполнено
      </label>
      <label class="status-option">
        <input type="radio" name="status" value="false" v-model="filterStatus" /> В процессе
      </label>
    </div>

    <h2>Ваши задачи</h2>
    <div v-for="task in tasks" :key="task.id" class="task-item">
      <h3>{{ task.name }}</h3>
      <p>{{ task.description }}</p>
      <p>Статус: {{ task.isCompleted ? 'Выполнено' : 'В процессе' }}</p>

      <button @click="openEditModal(task)">Редактировать</button>
      <button class="delete" @click="deleteTask(task.id)">Удалить</button>
    </div>
    <edit-task ref="editModal" @task-updated="loadTasks" />
    <div v-if="tasks.length === 0">
      <p>Нет задач для отображения.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import EditTask from "./editTask.vue";
import TaskCreate from "./taskCreate.vue";

const tasks = ref([]);
const filterStatus = ref('all'); // по умолчанию - все

const loadTasks = async () => {
  const token = localStorage.getItem('jwt_token');
  if (!token) {
    console.warn('Пользователь не авторизован');
    tasks.value = [];
    return;
  }

  let url = '/api/task';
  if (filterStatus.value !== 'all') {
    url += `?isCompleted=${filterStatus.value}`;
  }

  try {
    console.log('Запрашиваем задачи с URL:', url);
    const response = await fetch(url, {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });

    if (!response.ok) {
      throw new Error('Ошибка загрузки задач');
    }

    const data = await response.json();
    console.log('Полученные задачи:', data);
    tasks.value = data;

  } catch (err) {
    console.error('Ошибка при загрузке задач:', err);
    tasks.value = [];
  }
};

// Загружаем задачи при первом рендере
onMounted(() => {
  loadTasks();
});

const editModal = ref(null); // создаём ссылку на модалку
const openEditModal = (task) => {
  if (editModal.value) {
    editModal.value.openModal(task);
  }
};
// Следим за изменением фильтра и перезагружаем задачи
watch(filterStatus, (newStatus, oldStatus) => {
  console.log('Фильтр изменился:', newStatus);
  loadTasks();
});

const deleteTask = async (taskId) => {
  if (!confirm('Удалить эту задачу?')) return;

  const token = localStorage.getItem('jwt_token');
  if (!token) {
    alert('Авторизуйтесь для удаления');
    return;
  }

  try {
    const response = await fetch(`/api/task/${taskId}`, {
      method: 'DELETE',
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.error || 'Ошибка удаления');
    }

    // Удаляем задачу из массива локально
    tasks.value = tasks.value.filter(task => task.id !== taskId);

    alert('Задача удалена');

  } catch (err) {
    console.error('Ошибка при удалении задачи:', err);
    alert('Ошибка при удалении задачи');
  }
};

</script>

<style scoped>
/* Обёртка для списка задач */
div {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

/* Заголовок */
h2 {
  font-size: 24px;
  margin-bottom: 20px;
  font-weight: bold;
  color: #333;
}

/* Стиль фильтров */
.status-filters {
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
  border-bottom: 1px solid #ddd;
  padding-bottom: 10px;
}

/* Опции фильтров */
.status-option {
  font-size: 16px;
  font-weight: normal;
}

/* Элементы задач */
.task-item {
  background-color: #ffffff;
  padding: 15px;
  margin-bottom: 15px;
  border-radius: 8px;
  box-shadow: 0 2px 18px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.task-item:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.task-item h3 {
  font-size: 18px;
  color: #333;
  margin-bottom: 10px;
  font-weight: bold;
}

.task-item p {
  font-size: 14px;
  color: #555;
}

.task-item p:last-child {
  font-weight: bold;
  color: #333;
}

/* Кнопки */
button {
  padding: 8px 15px;
  font-size: 14px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s;
}

button:hover {
  opacity: 0.8;
}

/* Кнопка редактирования */
button:not(.delete) {
  background-color: #007bff;
  color: white;
}

button:not(.delete):hover {
  background-color: #0056b3;
}

/* Кнопка удаления */
button.delete {
  background-color: #dc3545;
  color: white;
  margin-left: 10px;
}

button.delete:hover {
  background-color: #c82333;
}

/* Пустой список */
div[v-if="tasks.length === 0"] {
  text-align: center;
  color: #aaa;
  font-size: 16px;
}
</style>

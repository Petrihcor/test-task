// Логика выхода
document.getElementById('logout-btn').addEventListener('click', function () {
    localStorage.removeItem('jwt_token');
    document.getElementById('task-container').style.display = 'none';
    document.getElementById('task-form').reset();
    document.getElementById('logout-btn').style.display = 'none';
    document.getElementById('login_container').style.display = 'flex'; // Показываем контейнер

    const taskList = document.getElementById('task-list');
    if (taskList) {
        taskList.innerHTML = '';
    }

});
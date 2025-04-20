document.getElementById('task-list').addEventListener('click', async function (e) {
    if (e.target.classList.contains('delete-task-btn')) {
        const taskId = e.target.getAttribute('data-id');
        const token = localStorage.getItem('jwt_token');

        try {
            const response = await fetch(`/api/task/${taskId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${token}`,
                }
            });

            const result = await response.json();

            if (response.ok) {
                e.target.closest('li').remove();
            } else {
                alert(result.error || 'Ошибка удаления');
            }
        } catch (err) {
            console.error('Ошибка при удалении задачи:', err);
        }
    }
});
<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
use App\Service\Base64ImageSaver;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

final class TaskController extends AbstractController
{
    private Base64ImageSaver $imageSaver;

    // Symfony автоматически инжектирует зависимость через конструктор
    public function __construct(Base64ImageSaver $imageSaver)
    {
        $this->imageSaver = $imageSaver;
    }
    #[Route('api/task', name: 'app_task', methods: ['GET'])]
    public function getTasks(TaskRepository $repository, Request $request, LoggerInterface $logger): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        // Получаем параметр фильтра из запроса (по умолчанию показываем все задачи)
        $page = max((int) $request->query->get('page', 1), 1);
        $limit = min((int) $request->query->get('limit', 10), 100); // например, 10 по умолчанию
        $offset = ($page - 1) * $limit;

        $status = $request->query->get('isCompleted', null);

        $criteria = ["creator" => $user];
        if ($status === "true") {
            $criteria["isCompleted"] = true;
        } elseif ($status === "false") {
            $criteria["isCompleted"] = false;
        }

        $tasks = $repository->findBy(
            $criteria,
            ["id" => "DESC"],
            $limit,
            $offset
        );

        $totalTasks = $repository->count($criteria);
//я хотел передавать массив с объектами, но почему-то на фронте isCompleted преобразовывалось в completed
        $data = array_map(fn($task) => [
            'id' => $task->getId(),
            'name' => $task->getName(),
            'createdAt' => $task->getCreatedAt()->format(\DateTime::ATOM),
            'updatedAt' => $task->getUpdatedAt()->format(\DateTime::ATOM),
            'isCompleted' => $task->isCompleted(),
            'description' => $task->getDescription(),
            'image' => $task->getImage()
        ], $tasks);

        return $this->json([
            'tasks' => $data,
            'total' => $totalTasks,
            'page' => $page,
            'limit' => $limit,
            'pages' => ceil($totalTasks / $limit),
            ]);
    }

    #[Route('api/task', name: 'api_create_task', methods: ['POST'])]
    public function createTask(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger): JsonResponse
    {
        // Получаем данные из запроса
        $data = json_decode($request->getContent(), true);
        $user = $this->getUser(); // Получаем пользователя через Symfony Security
        if (!$user instanceof User) {
            throw new \LogicException('User must be of type \App\Entity\User');
        }
        // Проверяем, что name не пустой
        if (empty($data['name'])) {
            return new JsonResponse(['error' => 'Name is required'], 400);
        }

        // Создаём задачу
        $task = new Task();
        if (!empty($data['imageBase64'])) {
            $imagePath = $this->imageSaver->saveBase64Image($data['imageBase64'], $logger);

            if ($imagePath) {
                $task->setImage($imagePath);
            }
        }
        $task
            ->setName($data['name'])
            ->setDescription($data['description'] ?? null)
            ->setCreator($user)// Привязываем задачу к текущему пользователю
            ->setIsCompleted(false)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable())
        ;
        // Сохраняем задачу в базе данных
        $entityManager->persist($task);
        $entityManager->flush();

        // Возвращаем успешный ответ
        return new JsonResponse([
            'message' => 'Task created successfully',
            'task' => [
                'name' => $task->getName(),
                'description' => $task->getDescription(),
                'id' => $task->getId(),
                'isCompleted' => $task->isCompleted(),
                'createdAt' => $task->getCreatedAt()->format(\DateTime::ATOM),
                'updatedAt' => $task->getUpdatedAt()->format(\DateTime::ATOM),
            ]
        ], 201);
    }

    #[Route('/api/task/{id}', name: 'api_edit_task', methods: ['PUT'])]
    public function editTask(Request $request, TaskRepository $taskRepository, EntityManagerInterface $entityManager, $id, LoggerInterface $logger, Base64ImageSaver $imageSaver): JsonResponse
    {

        // Получаем задачу из базы
        $task = $taskRepository->find($id);
        $user = $this->getUser();
        if ($task->getCreator() !== $user) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        } elseif (!$task) {
            return new JsonResponse(['error' => 'Task not found'], 404);
        }

        // Получаем данные из запроса
        $data = json_decode($request->getContent(), true);
        if (!empty($data['removeImage']) && $data['removeImage'] === true && $task->getImage()) {
            $imageSaver->deleteImage($task->getImage());
            $task->setImage(null);
        }

        if (!empty($data['imageBase64'])) {
            if ($task->getImage()) {
                $imageSaver->deleteImage($task->getImage());
            }
            $newPath = $imageSaver->saveBase64Image($data['imageBase64']);
            if ($newPath) {
                $task->setImage($newPath);
            }
        }
        $logger->info('Edit task request data', $data);
        // Обновляем поля задачи
        $task->setName($data['name']);
        $task->setDescription($data['description'] ?? null);
        $task->setIsCompleted($data['isCompleted']);
        $task->setUpdatedAt(new \DateTimeImmutable());

        // Сохраняем изменения в базе
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Task updated successfully',
            'task' => [
                'id' => $task->getId(),
                'name' => $task->getName(),
                'createdAt' => $task->getCreatedAt(),
                'updatedAt' => $task->getUpdatedAt()->format(\DateTime::ATOM),
                'isCompleted' => $task->isCompleted(),
                'description' => $task->getDescription(),
            ]
        ]);
    }

    #[Route('/api/task/{id}', name: 'delete_task', methods: ['DELETE'])]
    public function deleteTask(Task $task, Security $security, EntityManagerInterface $em): JsonResponse
    {
        $user = $security->getUser();

        if ($task->getCreator() !== $user) {
            return new JsonResponse(['error' => 'Недостаточно прав'], 403);
        }

        $em->remove($task);
        $em->flush();

        return new JsonResponse(['message' => 'Задача удалена']);
    }
}

<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Repository\TaskRepository;
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
    #[Route('api/task', name: 'app_task', methods: ['GET'])]
    public function getTasks(TaskRepository $repository, Request $request, LoggerInterface $logger): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        // Получаем параметр фильтра из запроса (по умолчанию показываем все задачи)
        $status = $request->query->get('isCompleted', null);
        $logger->info("status is: $status");
        if ($status === "true") {
            $tasks = $repository->findBy([
                "creator" => $this->getUser(),
                "isCompleted" => true
            ]);
        } elseif ($status === "false") {
            $tasks = $repository->findBy([
                "creator" => $this->getUser(),
                "isCompleted" => false
            ]);
        } else {
            $tasks = $repository->findBy(["creator" => $this->getUser()]);
        }

        $data = array_map(fn($task) => [
            'id' => $task->getId(),
            'name' => $task->getName(),
            'createdAt' => $task->getCreatedAt()->format(\DateTime::ATOM),
            'updatedAt' => $task->getUpdatedAt()->format(\DateTime::ATOM),
            'isCompleted' => $task->isCompleted(),
            'description' => $task->getDescription(),
        ], $tasks);
        return $this->json($data);
    }

    #[Route('api/task', name: 'api_create_task', methods: ['POST'])]
    public function createTask(Request $request, EntityManagerInterface $entityManager): JsonResponse
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
    public function editTask(Request $request, TaskRepository $taskRepository, EntityManagerInterface $entityManager, $id, LoggerInterface $logger): JsonResponse
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

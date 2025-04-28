<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationController extends AbstractController
{
    private $entityManager;
    private $userPasswordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->entityManager = $entityManager;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    #[Route('/api/register', name: 'app_register')]
    public function register(Request $request, UserRepository $userRepository, ValidatorInterface $validator, LoggerInterface $logger): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Валидация структуры данных
        $constraints = new Assert\Collection([
            'username' => [
                new Assert\NotBlank(['message' => 'Имя пользователя обязательно']),
                new Assert\Length([
                    'min' => 3,
                    'max' => 20,
                    'minMessage' => 'Имя пользователя должно быть не менее {{ limit }} символов',
                    'maxMessage' => 'Имя пользователя не должно превышать {{ limit }} символов'
                ]),
                new Assert\Regex([
                    'pattern' => '/^[\p{L}][\p{L}0-9_]*$/u',
                    'message' => 'Имя пользователя должно начинаться с буквы и содержать только буквы, цифры и подчёркивания'
                ])
            ],
            'password' => [
                new Assert\NotBlank(['message' => 'Пароль обязателен']),
                new Assert\Length([
                    'min' => 6,
                    'max' => 20,
                    'minMessage' => 'Пароль должен быть не менее {{ limit }} символов',
                    'maxMessage' => 'Пароль не должен превышать {{ limit }} символов'
                ])
            ],
        ]);

        $violations = $validator->validate($data, $constraints);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }
            return new JsonResponse(['errors' => $errors], 400);
        }

        // Проверка на существование пользователя с таким email
        $existingUser = $userRepository->findOneBy(['username' => $data['username']]);
        if ($existingUser) {
            return new JsonResponse(['error' => 'Пользователь уже существует'], 400);
        }

        // Создание нового пользователя
        $user = new User();
        $user->setUsername($data['username']);
        $hashedPassword = $this->userPasswordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return new JsonResponse(['message' => 'User created successfully'], 201);
    }
}

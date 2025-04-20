<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {

        $user = new User();
        $registerForm = $this->createForm(RegistrationFormType::class, $user, [
            'action' => $this->generateUrl('app_register')
        ]);
        $task = new Task(); // Предполагается, что у тебя есть сущность Task
        $taskForm = $this->createForm(TaskType::class, $task);
        //Вывод шаблона для входа
        return $this->render('home/index.html.twig', [
            'registrationForm' => $registerForm,
            'taskForm' => $taskForm->createView(),
        ]);
    }

}

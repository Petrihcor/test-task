<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LoginController extends AbstractController
{
    #[Route('/api/profile', name: 'api_profile')]
    public function loginFormProxy(): Response
    {
        $user = $this->getUser();
        return $this->json([
            'username' => $user->getUserIdentifier()
        ]);
    }

}

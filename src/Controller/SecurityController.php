<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    /**
     * Symfony's logout listener intercepts this route — the controller is never executed.
     */
    #[Route(path: '/logout', name: 'app_logout', methods: ['GET'])]
    public function logout(): never
    {
        throw new \LogicException('Intercepted by the logout listener.');
    }

    #[Route(path: '/cabinet', name: 'app_cabinet', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function cabinet(): Response
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        return $this->render('cabinet/index.html.twig', [
            'user' => $user,
            'orders' => $user->getOrders(),
        ]);
    }
}

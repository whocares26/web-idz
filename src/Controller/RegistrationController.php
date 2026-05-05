<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\RegistrationInput;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;

final class RegistrationController extends AbstractController
{
    #[Route(path: '/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $users,
        UserAuthenticatorInterface $userAuthenticator,
        FormLoginAuthenticator $formAuthenticator,
    ): Response {
        if ($this->getUser() !== null) {
            return $this->redirectToRoute('app_home');
        }

        $input = new RegistrationInput();
        $form = $this->createForm(RegistrationFormType::class, $input);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = new User();
            $user->setUsername($input->username)->setEmail($input->email);
            $user->setPassword($passwordHasher->hashPassword($user, $input->plainPassword));

            $users->save($user, true);

            return $userAuthenticator->authenticateUser($user, $formAuthenticator, $request)
                ?? $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}

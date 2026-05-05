<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Component\HttpFoundation\Response;

final class SecurityTest extends AbstractWebTestCase
{
    public function testAnonymousUserIsRedirectedToLoginFromHome(): void
    {
        $this->client->request('GET', '/');

        self::assertResponseRedirects('/login');
    }

    public function testLoginPageIsPublic(): void
    {
        $this->client->request('GET', '/login');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Вход');
    }

    public function testLoginWithValidCredentialsRedirectsHome(): void
    {
        $this->createUser('alice', 'alice123');

        $this->client->request('GET', '/login');
        $this->client->submitForm('Войти', [
            'username' => 'alice',
            'password' => 'alice123',
        ]);

        self::assertResponseRedirects('/');
        $this->client->followRedirect();
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Магазин');
    }

    public function testLoginWithInvalidCredentialsFailsWithMessage(): void
    {
        $this->createUser('alice', 'alice123');

        $this->client->request('GET', '/login');
        $this->client->submitForm('Войти', [
            'username' => 'alice',
            'password' => 'wrong-password',
        ]);

        // Symfony's form_login redirects to the login_path with the error in session.
        $this->client->followRedirect();
        self::assertSelectorExists('.errors');
    }

    public function testLogoutReturnsAnonymousState(): void
    {
        $user = $this->createUser();
        $this->loginAs($user);

        $this->client->request('GET', '/logout');
        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }
}

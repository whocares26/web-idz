<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Repository\UserRepository;

final class RegistrationTest extends AbstractWebTestCase
{
    public function testRegistrationFormIsPublic(): void
    {
        $this->client->request('GET', '/register');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Регистрация');
    }

    public function testSuccessfulRegistrationCreatesUserAndLogsIn(): void
    {
        $this->client->request('GET', '/register');
        $this->client->submitForm('Зарегистрироваться', [
            'registration_form[username]' => 'bob',
            'registration_form[email]' => 'bob@example.com',
            'registration_form[plainPassword][first]' => 'secret123',
            'registration_form[plainPassword][second]' => 'secret123',
        ]);

        self::assertResponseRedirects('/');

        /** @var UserRepository $users */
        $users = self::getContainer()->get(UserRepository::class);
        $user = $users->findOneByUsername('bob');
        self::assertNotNull($user);
        self::assertSame('bob@example.com', $user->getEmail());
        self::assertNotEmpty($user->getPassword(), 'Password should be hashed and stored.');
    }

    public function testMismatchedPasswordsTriggerAValidationError(): void
    {
        $this->client->request('GET', '/register');
        $this->client->submitForm('Зарегистрироваться', [
            'registration_form[username]' => 'bob',
            'registration_form[email]' => 'bob@example.com',
            'registration_form[plainPassword][first]' => 'secret123',
            'registration_form[plainPassword][second]' => 'different',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('.form-error-message, .errors, body', 'не совпадают', 'Form should show password mismatch error.');
    }

    public function testDuplicateUsernameIsRejected(): void
    {
        $this->createUser('bob', 'secret123');

        $this->client->request('GET', '/register');
        $this->client->submitForm('Зарегистрироваться', [
            'registration_form[username]' => 'bob',
            'registration_form[email]' => 'bob2@example.com',
            'registration_form[plainPassword][first]' => 'secret123',
            'registration_form[plainPassword][second]' => 'secret123',
        ]);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('body', 'уже существует');
    }
}

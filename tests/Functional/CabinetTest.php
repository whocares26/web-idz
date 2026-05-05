<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;

final class CabinetTest extends AbstractWebTestCase
{
    public function testAnonymousIsRedirected(): void
    {
        $this->client->request('GET', '/cabinet');

        self::assertResponseRedirects('/login');
    }

    public function testCabinetShowsUserDetails(): void
    {
        $user = $this->createUser('alice');
        $this->loginAs($user);

        $this->client->request('GET', '/cabinet');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Личный кабинет');
        self::assertSelectorTextContains('body', 'alice');
        self::assertSelectorTextContains('body', 'Пользователь');
    }

    public function testAdminBadgeIsShown(): void
    {
        $admin = $this->createUser('admin', 'pw', [User::ROLE_ADMIN]);
        $this->loginAs($admin);

        $this->client->request('GET', '/cabinet');

        self::assertSelectorTextContains('body', 'Администратор');
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testRolesAlwaysIncludeRoleUser(): void
    {
        $user = new User();

        self::assertContains(User::ROLE_USER, $user->getRoles());
    }

    public function testCustomRolesArePreservedAndDeduplicated(): void
    {
        $user = (new User())->setRoles([User::ROLE_ADMIN, User::ROLE_USER]);

        self::assertSame([User::ROLE_ADMIN, User::ROLE_USER], $user->getRoles());
    }

    public function testIsAdminReflectsRoleMembership(): void
    {
        $user = new User();
        self::assertFalse($user->isAdmin());

        $user->setRoles([User::ROLE_ADMIN]);
        self::assertTrue($user->isAdmin());
    }

    public function testUserIdentifierIsTheUsername(): void
    {
        $user = (new User())->setUsername('alice');

        self::assertSame('alice', $user->getUserIdentifier());
    }

    public function testEraseCredentialsIsANoOp(): void
    {
        $user = (new User())->setUsername('alice')->setPassword('hash');
        $user->eraseCredentials();

        self::assertSame('hash', $user->getPassword());
    }
}

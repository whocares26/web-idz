<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Common setup for functional tests:
 *   - boots a kernel client,
 *   - rebuilds the SQLite schema from Doctrine metadata before every test.
 */
abstract class AbstractWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();
        $this->client->disableReboot();

        $this->resetSchema();
    }

    protected function resetSchema(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $schemaTool = new SchemaTool($em);
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        $schemaTool->dropDatabase();
        if (!empty($metadata)) {
            $schemaTool->createSchema($metadata);
        }
    }

    protected function createUser(
        string $username = 'alice',
        string $plainPassword = 'alice123',
        array $roles = [],
        string $email = null,
    ): User {
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = self::getContainer()->get(UserPasswordHasherInterface::class);
        /** @var UserRepository $users */
        $users = self::getContainer()->get(UserRepository::class);

        $user = new User();
        $user
            ->setUsername($username)
            ->setEmail($email ?? $username.'@example.com')
            ->setRoles($roles)
            ->setPassword($hasher->hashPassword($user, $plainPassword));
        $users->save($user, true);

        return $user;
    }

    protected function loginAs(User $user): void
    {
        $this->client->loginUser($user);
    }
}

<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class AppFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $hasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setUsername('admin')->setEmail('admin@example.com')
            ->setRoles([User::ROLE_ADMIN])
            ->setPassword($this->hasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        $user = new User();
        $user->setUsername('alice')->setEmail('alice@example.com')
            ->setPassword($this->hasher->hashPassword($user, 'alice123'));
        $manager->persist($user);

        $order = new Order();
        $order
            ->setUser($user)
            ->setFirstName('Alice')->setLastName('Liddell')
            ->setPhone('+7 (000) 000-00-00')
            ->setCity('Москва')->setAddress('Тверская, 1')
            ->setDelivery('Курьер')->setPayment('Картой онлайн')
            ->setTotalSum(2_592_000);

        $item = new OrderItem();
        $item->setCategory('Сумка Hermes Birkin 30 Black Togo PHW')->setSize('30')->setQuantity(1);
        $order->addItem($item);

        $manager->persist($order);
        $manager->flush();
    }
}

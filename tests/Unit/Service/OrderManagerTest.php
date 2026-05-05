<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Dto\OrderInput;
use App\Dto\OrderItemInput;
use App\Entity\Order;
use App\Entity\User;
use App\Repository\OrderRepository;
use App\Service\OrderManager;
use PHPUnit\Framework\TestCase;

final class OrderManagerTest extends TestCase
{
    public function testCreateFromInputBuildsOrderAndPersistsIt(): void
    {
        $user = (new User())->setUsername('alice')->setEmail('alice@example.com');
        $repo = $this->createMock(OrderRepository::class);
        $repo->expects(self::once())
            ->method('save')
            ->with(self::isInstanceOf(Order::class), true);

        $manager = new OrderManager($repo);

        $input = new OrderInput();
        $input->firstName = 'Alice';
        $input->lastName = 'Liddell';
        $input->phone = '+70000000000';
        $input->city = 'Москва';
        $input->address = 'Тверская, 1';
        $input->delivery = 'Курьер';
        $input->payment = 'Картой онлайн';
        $input->totalSum = 1234;
        $input->items = [
            $this->itemInput('Bag', 'M', 2),
            $this->itemInput('Coat', 'L', 1),
        ];

        $order = $manager->createFromInput($input, $user);

        self::assertSame($user, $order->getUser());
        self::assertSame('Alice', $order->getFirstName());
        self::assertSame(1234, $order->getTotalSum());
        self::assertCount(2, $order->getItems());
        self::assertSame('Bag', $order->getItems()->first()->getCategory());
        self::assertSame($order, $order->getItems()->first()->getOrder());
    }

    public function testListVisibleToReturnsAllOrdersForAdmins(): void
    {
        $admin = (new User())->setRoles([User::ROLE_ADMIN]);
        $repo = $this->createMock(OrderRepository::class);
        $repo->expects(self::once())->method('findAllOrdered')->willReturn([new Order()]);
        $repo->expects(self::never())->method('findAllForUser');

        $manager = new OrderManager($repo);

        self::assertCount(1, $manager->listVisibleTo($admin));
    }

    public function testListVisibleToReturnsUserOrdersForRegularUsers(): void
    {
        $user = new User();
        $repo = $this->createMock(OrderRepository::class);
        $repo->expects(self::never())->method('findAllOrdered');
        $repo->expects(self::once())
            ->method('findAllForUser')
            ->with($user)
            ->willReturn([]);

        $manager = new OrderManager($repo);

        self::assertSame([], $manager->listVisibleTo($user));
    }

    private function itemInput(string $category, string $size, int $quantity): OrderItemInput
    {
        $item = new OrderItemInput();
        $item->category = $category;
        $item->size = $size;
        $item->quantity = $quantity;

        return $item;
    }
}

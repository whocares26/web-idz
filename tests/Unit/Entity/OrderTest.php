<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    public function testNewOrderHasEmptyItemCollectionAndTimestamp(): void
    {
        $order = new Order();

        self::assertCount(0, $order->getItems());
        self::assertEqualsWithDelta(time(), $order->getCreatedAt()->getTimestamp(), 5);
    }

    public function testAddItemEstablishesBidirectionalRelation(): void
    {
        $order = new Order();
        $item = new OrderItem();
        $item->setCategory('Bag')->setSize('M')->setQuantity(1);

        $order->addItem($item);

        self::assertSame($order, $item->getOrder(), 'Item should reference the owning order.');
        self::assertCount(1, $order->getItems());
    }

    public function testAddItemIsIdempotent(): void
    {
        $order = new Order();
        $item = (new OrderItem())->setCategory('Bag')->setSize('M')->setQuantity(1);

        $order->addItem($item);
        $order->addItem($item);

        self::assertCount(1, $order->getItems());
    }

    public function testRemoveItemDetachesItFromCollection(): void
    {
        $order = new Order();
        $item = (new OrderItem())->setCategory('Bag')->setSize('M')->setQuantity(1);
        $order->addItem($item);

        $order->removeItem($item);

        self::assertCount(0, $order->getItems());
    }

    public function testFullNameTrimsSurroundingWhitespace(): void
    {
        $order = (new Order())->setFirstName('  Alice ')->setLastName(' Liddell ');

        self::assertSame('Alice  Liddell', trim($order->getFullName()));
    }

    public function testUserAssociationCanBeNull(): void
    {
        $order = new Order();

        self::assertNull($order->getUser());

        $user = new User();
        $order->setUser($user);
        self::assertSame($user, $order->getUser());

        $order->setUser(null);
        self::assertNull($order->getUser());
    }
}

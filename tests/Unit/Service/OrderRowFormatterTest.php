<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Service\Report\OrderRowFormatter;
use PHPUnit\Framework\TestCase;

final class OrderRowFormatterTest extends TestCase
{
    public function testHeadersAreStable(): void
    {
        self::assertCount(12, (new OrderRowFormatter())->headers());
    }

    public function testRowsFlattenOrdersAndItemsToOneRowPerItem(): void
    {
        $order = (new Order())
            ->setFirstName('Alice')->setLastName('Liddell')
            ->setPhone('+7000')->setCity('Москва')->setAddress('Тверская, 1')
            ->setDelivery('Курьер')->setPayment('Картой онлайн')
            ->setTotalSum(500);

        $order->addItem((new OrderItem())->setCategory('Bag')->setSize('M')->setQuantity(2));
        $order->addItem((new OrderItem())->setCategory('Coat')->setSize('L')->setQuantity(1));

        $rows = (new OrderRowFormatter())->rows([$order]);

        self::assertCount(2, $rows);
        self::assertSame('Alice Liddell', $rows[0][1]);
        self::assertSame('Bag', $rows[0][7]);
        self::assertSame('Coat', $rows[1][7]);
        self::assertSame(500, $rows[0][10]);
    }

    public function testRowsAreEmptyForOrdersWithoutItems(): void
    {
        $order = new Order();

        self::assertSame([], (new OrderRowFormatter())->rows([$order]));
    }
}

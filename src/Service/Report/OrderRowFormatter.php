<?php

declare(strict_types=1);

namespace App\Service\Report;

use App\Entity\Order;

/**
 * Flattens orders into a tabular structure shared by every report format.
 */
final class OrderRowFormatter
{
    /**
     * @return list<string>
     */
    public function headers(): array
    {
        return [
            '№', 'ФИО', 'Телефон', 'Город', 'Адрес', 'Доставка',
            'Оплата', 'Товар', 'Размер', 'Кол-во', 'Сумма (руб)', 'Дата',
        ];
    }

    /**
     * @param iterable<Order> $orders
     *
     * @return list<list<string|int>>
     */
    public function rows(iterable $orders): array
    {
        $rows = [];
        foreach ($orders as $order) {
            foreach ($order->getItems() as $item) {
                $rows[] = [
                    (int) $order->getId(),
                    $order->getFullName(),
                    $order->getPhone(),
                    $order->getCity(),
                    $order->getAddress(),
                    $order->getDelivery(),
                    $order->getPayment(),
                    $item->getCategory(),
                    $item->getSize(),
                    $item->getQuantity(),
                    $order->getTotalSum(),
                    $order->getCreatedAt()->format('d.m.Y H:i'),
                ];
            }
        }

        return $rows;
    }
}

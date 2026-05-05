<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\OrderInput;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\User;
use App\Repository\OrderRepository;

/**
 * Encapsulates business operations on orders.
 *
 * Keeps the controllers thin and isolates persistence concerns from HTTP
 * concerns, which makes the logic straightforward to unit-test.
 */
final readonly class OrderManager
{
    public function __construct(
        private OrderRepository $orders,
    ) {
    }

    /**
     * Build a domain Order from a validated DTO and persist it.
     */
    public function createFromInput(OrderInput $input, User $user): Order
    {
        $order = new Order();
        $order
            ->setUser($user)
            ->setFirstName($input->firstName)
            ->setLastName($input->lastName)
            ->setPhone($input->phone)
            ->setCity($input->city)
            ->setAddress($input->address)
            ->setDelivery($input->delivery)
            ->setPayment($input->payment)
            ->setTotalSum($input->totalSum);

        foreach ($input->items as $itemInput) {
            $item = new OrderItem();
            $item
                ->setCategory($itemInput->category)
                ->setSize($itemInput->size)
                ->setQuantity($itemInput->quantity);
            $order->addItem($item);
        }

        $this->orders->save($order, true);

        return $order;
    }

    /**
     * Returns orders the given user is allowed to see.
     *
     * @return list<Order>
     */
    public function listVisibleTo(User $user): array
    {
        return $user->isAdmin()
            ? $this->orders->findAllOrdered()
            : $this->orders->findAllForUser($user);
    }
}

<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Order;
use Symfony\Component\Validator\Constraints as Assert;

class OrderInput
{
    #[Assert\NotBlank(message: 'Имя не может быть пустым.')]
    #[Assert\Length(max: 100)]
    public string $firstName = '';

    #[Assert\NotBlank(message: 'Фамилия не может быть пустой.')]
    #[Assert\Length(max: 100)]
    public string $lastName = '';

    #[Assert\NotBlank(message: 'Телефон не может быть пустым.')]
    #[Assert\Regex(pattern: '/^[0-9+\-()\s]+$/', message: 'Телефон содержит недопустимые символы.')]
    #[Assert\Length(max: 50)]
    public string $phone = '';

    #[Assert\NotBlank(message: 'Выберите город из списка.')]
    #[Assert\Choice(choices: Order::CITIES, message: 'Выберите город из предложенного списка.')]
    public string $city = '';

    #[Assert\NotBlank(message: 'Адрес доставки не может быть пустым.')]
    #[Assert\Length(max: 255)]
    public string $address = '';

    #[Assert\NotBlank(message: 'Выберите способ доставки.')]
    #[Assert\Choice(choices: Order::DELIVERY_METHODS, message: 'Выберите способ доставки.')]
    public string $delivery = '';

    #[Assert\NotBlank(message: 'Выберите способ оплаты.')]
    #[Assert\Choice(choices: Order::PAYMENT_METHODS, message: 'Выберите способ оплаты.')]
    public string $payment = '';

    #[Assert\PositiveOrZero]
    public int $totalSum = 0;

    /**
     * @var list<OrderItemInput>
     */
    #[Assert\Count(min: 1, minMessage: 'Добавьте хотя бы один товар.')]
    #[Assert\Valid]
    public array $items = [];
}

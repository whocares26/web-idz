<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class OrderItemInput
{
    #[Assert\NotBlank(message: 'Категория товара не может быть пустой.')]
    #[Assert\Length(max: 255)]
    public string $category = '';

    #[Assert\NotBlank(message: 'Размер не может быть пустым.')]
    #[Assert\Length(max: 50)]
    public string $size = '';

    #[Assert\Positive(message: 'Количество должно быть больше нуля.')]
    #[Assert\LessThanOrEqual(value: 99, message: 'Количество не может превышать 99.')]
    public int $quantity = 1;
}

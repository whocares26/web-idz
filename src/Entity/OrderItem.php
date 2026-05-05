<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrderItemRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderItemRepository::class)]
#[ORM\Table(name: 'order_items')]
class OrderItem
{
    public const SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '25', '30'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Order::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Order $order = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Категория товара не может быть пустой.')]
    #[Assert\Length(max: 255)]
    private string $category = '';

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Размер не может быть пустым.')]
    #[Assert\Length(max: 50)]
    private string $size = '';

    #[ORM\Column]
    #[Assert\Positive(message: 'Количество должно быть больше нуля.')]
    #[Assert\LessThanOrEqual(value: 99, message: 'Количество не может превышать 99.')]
    private int $quantity = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): self
    {
        $this->order = $order;

        return $this;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function setSize(string $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }
}

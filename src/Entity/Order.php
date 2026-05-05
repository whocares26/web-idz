<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: 'orders')]
class Order
{
    public const CITIES = ['Москва', 'Санкт-Петербург', 'Липецк', 'Воронеж', 'Тамбов'];
    public const DELIVERY_METHODS = ['Курьер', 'СДЭК', 'Почта России', 'Самовывоз'];
    public const PAYMENT_METHODS = ['Картой онлайн', 'Наличными при получении', 'Картой при получении'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'orders')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(name: 'first_name', length: 100)]
    #[Assert\NotBlank(message: 'Имя не может быть пустым.')]
    #[Assert\Length(max: 100)]
    private string $firstName = '';

    #[ORM\Column(name: 'last_name', length: 100)]
    #[Assert\NotBlank(message: 'Фамилия не может быть пустой.')]
    #[Assert\Length(max: 100)]
    private string $lastName = '';

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: 'Телефон не может быть пустым.')]
    #[Assert\Regex(
        pattern: '/^[0-9+\-()\s]+$/',
        message: 'Телефон содержит недопустимые символы.'
    )]
    #[Assert\Length(max: 50)]
    private string $phone = '';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Выберите город из списка.')]
    #[Assert\Choice(choices: self::CITIES, message: 'Выберите город из предложенного списка.')]
    private string $city = '';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Адрес доставки не может быть пустым.')]
    #[Assert\Length(max: 255)]
    private string $address = '';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Выберите способ доставки.')]
    #[Assert\Choice(choices: self::DELIVERY_METHODS, message: 'Выберите способ доставки из списка.')]
    private string $delivery = '';

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Выберите способ оплаты.')]
    #[Assert\Choice(choices: self::PAYMENT_METHODS, message: 'Выберите способ оплаты из списка.')]
    private string $payment = '';

    #[ORM\Column(name: 'total_sum', type: 'bigint', options: ['default' => 0])]
    #[Assert\PositiveOrZero]
    private int $totalSum = 0;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    /**
     * @var Collection<int, OrderItem>
     */
    #[ORM\OneToMany(mappedBy: 'order', targetEntity: OrderItem::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[Assert\Count(min: 1, minMessage: 'Добавьте хотя бы один товар.')]
    #[Assert\Valid]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFullName(): string
    {
        return trim($this->firstName.' '.$this->lastName);
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getDelivery(): string
    {
        return $this->delivery;
    }

    public function setDelivery(string $delivery): self
    {
        $this->delivery = $delivery;

        return $this;
    }

    public function getPayment(): string
    {
        return $this->payment;
    }

    public function setPayment(string $payment): self
    {
        $this->payment = $payment;

        return $this;
    }

    public function getTotalSum(): int
    {
        return $this->totalSum;
    }

    public function setTotalSum(int $totalSum): self
    {
        $this->totalSum = $totalSum;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, OrderItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(OrderItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items->add($item);
            $item->setOrder($this);
        }

        return $this;
    }

    public function removeItem(OrderItem $item): self
    {
        $this->items->removeElement($item);

        return $this;
    }
}

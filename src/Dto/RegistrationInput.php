<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class RegistrationInput
{
    #[Assert\NotBlank(message: 'Введите имя пользователя.')]
    #[Assert\Length(min: 3, max: 100)]
    public string $username = '';

    #[Assert\NotBlank(message: 'Введите email.')]
    #[Assert\Email(message: 'Введите корректный email.')]
    #[Assert\Length(max: 255)]
    public string $email = '';

    #[Assert\NotBlank(message: 'Введите пароль.')]
    #[Assert\Length(min: 6, max: 4096, minMessage: 'Пароль должен быть не менее 6 символов.')]
    public string $plainPassword = '';
}

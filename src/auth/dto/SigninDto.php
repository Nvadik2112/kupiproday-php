<?php

namespace App\Auth\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class SigninDto {
    /**
     * @Assert\NotBlank(message="Имя пользователя обязательно")
     * @Assert\Length(
     *     min=3,
     *     max=50,
     *     minMessage="Имя пользователя должно быть не менее {{ limit }} символов",
     *     maxMessage="Имя пользователя должно быть не более {{ limit }} символов"
     * )
     */
    public string $username;

    /**
     * @Assert\NotBlank(message="Пароль обязателен")
     * @Assert\Length(
     *     min=6,
     *     minMessage="Пароль должен быть не менее {{ limit }} символов"
     * )
     */
    public string $password;
}
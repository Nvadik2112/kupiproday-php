<?php

namespace App\Users\Dto;

use App\Users\Entities\User;

class CreateUserDto {
    public function __construct(
        public string $username,
        public string $email,
        public string $password,
        public string $about = 'Пока ничего не рассказал о себе',
        public string $avatar = 'https://i.pravatar.cc/300'
    ) {
        User::validateUsername($this->username);
        User::validateEmail($this->email);
        User::validatePassword($this->password);
        User::validateAbout($this->about);
        User::validateAvatar($this->avatar);
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['username'] ?? '',
            $data['email'] ?? '',
            $data['password'] ?? '',
            $data['about'] ?? 'Пока ничего не рассказал о себе',
            $data['avatar'] ?? 'https://i.pravatar.cc/300'
        );
    }
}
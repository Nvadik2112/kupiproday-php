<?php

namespace App\Users\Dto;

use App\Users\Entities\User;

class CreateUserDto 
{
    public function __construct(
        public string $username,
        public string $email,
        public string $password,
        public string $about = 'Пока ничего не рассказал о себе',
        public string $avatar = 'https://i.pravatar.cc/300'
    ) {
        $this->validate();
    }

    public static function fromArray(array $data): self 
    {
        return new self(
            $data['username'] ?? '',
            $data['email'] ?? '',
            $data['password'] ?? '',
            $data['about'] ?? 'Пока ничего не рассказал о себе',
            $data['avatar'] ?? 'https://i.pravatar.cc/300'
        );
    }

    private function validate(): void
    {
        User::validateUsername($this->username);
        User::validateEmail($this->email);
        User::validatePassword($this->password);
        User::validateAbout($this->about);
        User::validateAvatar($this->avatar);
    }

    public function toArray(): array
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'about' => $this->about,
            'avatar' => $this->avatar
        ];
    }

    public function getUserData(): array
    {
        return [
            'username' => $this->username,
            'email' => $this->email,
            'about' => $this->about,
            'avatar' => $this->avatar
        ];
    }
}
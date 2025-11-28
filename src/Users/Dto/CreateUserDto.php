<?php

namespace App\Users\Dto;

use App\Users\Entities\UserEntity;

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
        UserEntity::validateUsername($this->username);
        UserEntity::validateEmail($this->email);
        UserEntity::validatePassword($this->password);
        UserEntity::validateAbout($this->about);
        UserEntity::validateAvatar($this->avatar);
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
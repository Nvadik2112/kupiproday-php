<?php

namespace App\Users\Dto;

use App\Users\Entities\UserEntity;

class UpdateUserDto {
    public function __construct(
        public ?string $username = null,
        public ?string $email = null,
        public ?string $password = null,
        public ?string $about = null,
        public ?string $avatar = null
    ) {
        if ($this->username !== null) UserEntity::validateUsername($this->username);
        if ($this->email !== null) UserEntity::validateEmail($this->email);
        if ($this->password !== null) UserEntity::validatePassword($this->password);
        if ($this->about !== null) UserEntity::validateAbout($this->about);
        if ($this->avatar !== null) UserEntity::validateAvatar($this->avatar);
    }

    public static function fromArray(array $data): self {
        return new self(
            $data['username'] ?? null,
            $data['email'] ?? null,
            $data['password'] ?? null,
            $data['about'] ?? null,
            $data['avatar'] ?? null
        );
    }

    public function hasChanges(): bool {
        return $this->username !== null ||
            $this->email !== null ||
            $this->password !== null ||
            $this->about !== null ||
            $this->avatar !== null;
    }
}
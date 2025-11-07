<?php

namespace App\Users\Dto;

use App\Users\Entities\User;

class UpdateUserDto {
    public function __construct(
        public ?string $username = null,
        public ?string $email = null,
        public ?string $password = null,
        public ?string $about = null,
        public ?string $avatar = null
    ) {
        if ($this->username !== null) User::validateUsername($this->username);
        if ($this->email !== null) User::validateEmail($this->email);
        if ($this->password !== null) User::validatePassword($this->password);
        if ($this->about !== null) User::validateAbout($this->about);
        if ($this->avatar !== null) User::validateAvatar($this->avatar);
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
<?php

namespace App\Users\Dto;

class FindUserDto {
    public function __construct(
        public ?int $id = null,
        public ?string $username = null,
        public ?string $email = null
    ) {}

    public static function fromArray(array $data): self {
        return new self(
            isset($data['id']) ? (int)$data['id'] : null,
            $data['username'] ?? null,
            $data['email'] ?? null
        );
    }

    public static function fromParams(array $params): self {
        return new self(
            isset($params['id']) ? (int)$params['id'] : null,
            $params['username'] ?? null,
            null
        );
    }

    public function toArray(): array {
        $result = [];

        if ($this->id !== null) {
            $result['id'] = $this->id;
        }

        if ($this->username !== null) {
            $result['username'] = $this->username;
        }

        if ($this->email !== null) {
            $result['email'] = $this->email;
        }

        return $result;
    }

    public function isEmpty(): bool {
        return $this->id === null &&
            $this->username === null &&
            $this->email === null;
    }
}
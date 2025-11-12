<?php

namespace App\Users\Entities;

class User {
    private ?int $id = null;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;
    private string $username;
    private string $about;
    private string $avatar;
    private string $email;
    private string $password;

    public function __construct(
        string $username,
        string $email,
        string $password,
        string $about = 'Пока ничего не рассказал о себе',
        string $avatar = 'https://i.pravatar.cc/300'
    ) {
        $this->setUsername($username);
        $this->setEmail($email);
        $this->setPassword($password);
        $this->setAbout($about);
        $this->setAvatar($avatar);

        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public static function validateUsername(string $username): void {
        if (strlen($username) < 2 || strlen($username) > 30) {
            throw new \InvalidArgumentException('Username must be between 2 and 30 characters');
        }
    }

    public static function validateEmail(string $email): void {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
    }

    public static function validatePassword(string $password): void {
        if (strlen($password) < 3) {
            throw new \InvalidArgumentException('Password must be at least 3 characters');
        }
    }

    public static function validateAbout(string $about): void {
        if (strlen($about) < 2 || strlen($about) > 200) {
            throw new \InvalidArgumentException('About must be between 2 and 200 characters');
        }
    }

    public static function validateAvatar(string $avatar): void {
        if (!filter_var($avatar, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid avatar URL');
        }
    }

    public function getId(): ?int { return $this->id; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function getUsername(): string { return $this->username; }
    public function getAbout(): string { return $this->about; }
    public function getAvatar(): string { return $this->avatar; }
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }

    public function setUsername(string $username): void {
        self::validateUsername($username);
        $this->username = $username;
        $this->updatedAt = new \DateTime();
    }

    public function setAbout(string $about): void {
        self::validateAbout($about);
        $this->about = $about;
        $this->updatedAt = new \DateTime();
    }

    public function setAvatar(string $avatar): void {
        self::validateAvatar($avatar);
        $this->avatar = $avatar;
        $this->updatedAt = new \DateTime();
    }

    public function setEmail(string $email): void {
        self::validateEmail($email);
        $this->email = $email;
        $this->updatedAt = new \DateTime();
    }

    public function setPassword(string $password): void {
        self::validatePassword($password);
        $this->password = $password;
        $this->updatedAt = new \DateTime();
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public static function fromArray(array $data): self {
        $user = new self(
            $data['username'],
            $data['email'],
            $data['password'],
            $data['about'],
            $data['avatar']
        );

        if (isset($data['id'])) {
            $user->setId((int)$data['id']);
        }

        if (isset($data['createdAt']) && is_string($data['createdAt'])) {
            $user->createdAt = \DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $data['createdAt']);
        }

        if (isset($data['updatedAt']) && is_string($data['updatedAt'])) {
            $user->updatedAt = \DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $data['updatedAt']);
        }

        return $user;
    }
   
    public function toArray(): array {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'about' => $this->about,
            'avatar' => $this->avatar,
            'email' => $this->email,
            'createdAt' => $this->createdAt->format('Y-m-d\TH:i:s.u\Z'),
            'updatedAt' => $this->updatedAt->format('Y-m-d\TH:i:s.u\Z'),
        ];
    }

    public function clearPassword(): void {
        unset($this->password);
    }
}

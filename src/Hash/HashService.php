<?php

namespace App\Hash;

class HashService
{

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    public function comparePassword(string $password, string $hashedPassword): bool
    {
        return password_verify($password, $hashedPassword);
    }
}
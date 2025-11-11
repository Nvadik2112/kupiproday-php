<?php

namespace App\Users;

use AllowDynamicProperties;
use App\Hash\HashService;
use App\Users\Entities\User;
use PDO;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AllowDynamicProperties]
class UsersService
{     public function __construct(HashService $hashService)
    {
        $this->hashService = $hashService;
    }
    public function create(array $data): User
    {
        $this->checkDuplicate($data['email'], $data['username']);

        $data['password'] = $this->hashService->hashPassword($data['password']);
        
        $sql = "INSERT INTO users (username, email, password, created_at, updated_at) 
                VALUES (:username, :email, :password, NOW(), NOW())";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        $userId = (int)$this->connection->lastInsertId();
        
        if ($userId === 0) {
            throw new BadRequestException('Не удалось создать пользователя');
        }

        return $this->findById($userId);
    }

    public function findById(int $id): User
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            throw new NotFoundException('Пользователь не найден');
        }

        return User::fromArray($data);
    }

    public function findByEmail(string $email): ?User
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data ? User::fromArray($data) : null;
    }

    public function search(string $query): array
    {
        if (empty($query)) {
            throw new BadRequestException('Параметр поиска не должен быть пустым');
        }

        $sql = "SELECT * FROM users WHERE username LIKE :query OR email LIKE :query";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['query' => "%{$query}%"]);
        
        $usersData = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($usersData)) {
            throw new NotFoundException('Пользователи не найдены');
        }

        return array_map(fn($data) => User::fromArray($data), $usersData);
    }

    public function update(int $userId, array $data): User
    {
        if (isset($data['email']) || isset($data['username'])) {
            $currentUser = $this->findById($userId);
            $email = $data['email'] ?? $currentUser->getEmail();
            $username = $data['username'] ?? $currentUser->getUsername();
            
            $this->checkDuplicate($email, $username, $userId);
        }

        if (isset($data['password'])) {
            $data['password'] = $this->hashService->hashPassword($data['password']);
        }

        $setFields = [];
        $params = ['id' => $userId];

        foreach ($data as $key => $value) {
            $setFields[] = "{$key} = :{$key}";
            $params[$key] = $value;
        }

        $setFields[] = "updated_at = NOW()";
        $setClause = implode(', ', $setFields);

        $sql = "UPDATE users SET {$setClause} WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);

        $user = $this->findById($userId);
        $user->clearPassword();
        
        return $user;
    }

    public function findWishesByUser(int $userId): array
    {
        $sql = "SELECT w.* FROM wishes w 
                WHERE w.user_id = :userId";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['userId' => $userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function checkDuplicate(string $email, string $username, ?int $excludeUserId = null): void
    {
        $sql = "SELECT COUNT(*) as count FROM users 
                WHERE (email = :email OR username = :username)";
        
        $params = [
            'email' => $email,
            'username' => $username
        ];

        if ($excludeUserId) {
            $sql .= " AND id != :excludeId";
            $params['excludeId'] = $excludeUserId;
        }

        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && $result['count'] > 0) {
            throw new ForbiddenException('Email или username с таким именем существует');
        }
    }
}
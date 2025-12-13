<?php

namespace App\Users;

use App\Exceptions\Domain\BadRequestException;
use App\Exceptions\Domain\ForbiddenException;
use App\Exceptions\Domain\NotFoundException;
use App\Hash\HashService;
use App\Users\Entities\UserEntity;
use PDO;

class UsersService
{
    private HashService $hashService;
    private PDO $connection;

    public function __construct(PDO $connection, HashService $hashService)
    {
        $this->connection = $connection;
        $this->hashService = $hashService;
    }

    /**
     * @throws NotFoundException
     * @throws ForbiddenException
     * @throws BadRequestException
     */
    public function create($data): UserEntity
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

    /**
     * @throws NotFoundException
     */
    public function findById(int $id): UserEntity
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['id' => $id]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$data) {
            throw new NotFoundException('Пользователь не найден');
        }

        return UserEntity::fromArray($data);
    }

    public function findByEmail(string $email): ?UserEntity
    {
        $sql = "SELECT * FROM users WHERE email = :email";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['email' => $email]);
        
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $data ? UserEntity::fromArray($data) : null;
    }

    /**
     * @throws NotFoundException
     * @throws BadRequestException
     */
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

        return array_map(fn($data) => UserEntity::fromArray($data), $usersData);
    }

    public function findByEmailOrUsername(string $identifier): ?UserEntity
    {
        $sql = "SELECT * FROM users WHERE email = :identifier OR username = :identifier LIMIT 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute(['identifier' => $identifier]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? UserEntity::fromArray($data) : null;
    }

    /**
     * @throws NotFoundException
     * @throws ForbiddenException
     */
    public function update(int $userId, array $data): UserEntity
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

    //public function findWishesByUser(int $userId): array
    //{
       // $sql = "SELECT w.* FROM wishes w
       //         WHERE w.user_id = :userId";
        
       //$stmt = $this->connection->prepare($sql);
       // $stmt->execute(['userId' => $userId]);
        
     //   return $stmt->fetchAll(PDO::FETCH_ASSOC);
    //}

    /**
     * @throws ForbiddenException
     */
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
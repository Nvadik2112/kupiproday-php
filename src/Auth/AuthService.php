<?php

namespace App\Auth;

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Exceptions\Domain\UnauthorizedException;
use App\Hash\HashService;
use App\Users\UsersService;
use Firebase\JWT\JWT;

class AuthService {
    private HashService $hashService;
    private UsersService $usersService;
    private string $secretKey;
    private string $algorithm = 'HS256';

    public function __construct(HashService $hashService, UsersService $usersService) {
        $this->hashService = $hashService;
        $this->usersService = $usersService;
        $this->secretKey = getenv('JWT_SECRET') ?: 'your-fallback-secret-key';
    }

    public function auth($user): array
    {
        $payload = [
            'sub' => $user['id'],
            'username' => $user['username'],
            'iat' => time(),
            'exp' => time() + (7 * 24 * 60 * 60) // 7 дней
        ];

        return [
            'access_token' => JWT::encode($payload, $this->secretKey, $this->algorithm),
            'token_type' => 'Bearer',
            'expires_in' => 7 * 24 * 60 * 60
        ];
    }

    /**
     * @throws UnauthorizedException
     */
    public function validatePassword($identifier, $password): array
    {
        $user = $this->usersService->findByEmailOrUsername($identifier);

        if (!$user) {
            throw new UnauthorizedException('Учетная запись не найдена');
        }

        if (!$user->verifyPassword($password, $this->hashService)) {
            throw new UnauthorizedException('Неверный пароль');
        }

        return $user->toArray();
    }
}
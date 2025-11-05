<?php

namespace App\Auth;

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Exceptions\UnauthorizedException;
use App\Hash\HashService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService {
    private HashService $hashService;
    private $userService;
    private string|array|false $secretKey;
    private string $algorithm = 'HS256';

    public function __construct(HashService $hashService) {
        $this->hashService = $hashService;
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
    public function validatePassword($username, $password) {
        $user = $this->userService->findByUsername($username);

        if (!$user) {
            throw new UnauthorizedException('Учетная запись не найдена');
        }

        $isPasswordValid = $this->hashService->comparePassword(
            $password,
            $user['password']
        );

        if (!$isPasswordValid) {
            throw new UnauthorizedException('Неверный пароль');
        }

        unset($user['password']);

        return $user;
    }
}
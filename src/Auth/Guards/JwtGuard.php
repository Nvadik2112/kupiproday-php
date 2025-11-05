<?php

namespace App\Auth\Guards;

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Auth\JwtStrategy;
use App\Exceptions\UnauthorizedException;
use Exception;
use Firebase\JWT\JWT;

class JwtGuard
{
    private JwtStrategy $JwtStrategy;

    public function __construct()
    {
        $this->JwtStrategy = new JwtStrategy();
    }

    public function canActivate(array $request): bool {
        try {
            $authenticatedRequest = $this->JwtStrategy->handle($request);

            $GLOBALS['user'] = $authenticatedRequest['user'] ?? null;

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @throws UnauthorizedException
     */
    public function validate(Request $request): User {
        $token = $this->extractToken($request);

        if (!$token) {
            throw new UnauthorizedException('Token not provided');
        }

        try {
            // Декодируем JWT токен используя библиотеку
            $payload = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            $payloadArray = (array)$payload;

            // Создаем пользователя из payload
            return new User([
                'id' => $payloadArray['userId'] ?? $payloadArray['sub'] ?? null,
                'username' => $payloadArray['username'] ?? '',
                'email' => $payloadArray['email'] ?? ''
            ]);

        } catch (\Exception $e) {
            throw new UnauthorizedException('Invalid token: ' . $e->getMessage());
        }
    }

    private function extractToken(Request $request): ?string {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader) {
            // Проверяем cookie или query parameter как fallback
            return $request->cookies->get('auth_token') ?? $request->query->get('token');
        }

        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    // Метод для генерации токена с библиотекой
    public function generateToken(User $user): string {
        $payload = [
            'userId' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24 * 7) // 7 days
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }
}
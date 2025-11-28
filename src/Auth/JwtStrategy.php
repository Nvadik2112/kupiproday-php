<?php

namespace App\Auth;

use App\Exceptions\Domain\NotFoundException;
use App\Users\UsersService;
use App\Config\ConfigService;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtStrategy {
    private UsersService $usersService;
    private string $secretKey;

    public function __construct(UsersService $usersService) {
        $this->usersService = $usersService;
        $configService = new ConfigService();
        $this->secretKey = $configService->get('JWT_KEY') ?? 'default_secret_key';
    }

    /**
     * @throws Exception
     */
    public function validate($request): array {
        $token = $this->extractJwtFromRequest($request);

        if (!$token) {
            throw new Exception('Токен не предоставлен', 401);
        }

        try {
            $jwtPayload = $this->verifyToken($token);

            // findById либо вернет User, либо выбросит NotFoundException
            $user = $this->usersService->findById($jwtPayload->sub);

            // Конвертируем User в массив
            return $user->toArray();

        } catch (NotFoundException $e) {
            // Пользователь не найден в БД
            throw new Exception('Пользователь не найден', 401);
        } catch (Exception $e) {
            throw new Exception('Ошибка аутентификации: ' . $e->getMessage(), 401);
        }
    }

    private function extractJwtFromRequest($request): ?string
    {
        // Для Symfony Request
        if (is_object($request) && method_exists($request, 'headers')) {
            $authHeader = $request->headers->get('Authorization') ?? '';
        } else {
            // Для массива
            $authHeader = $request['headers']['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        }

        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * @throws Exception
     */
    private function verifyToken($token): \stdClass
    {
        try {
            return JWT::decode($token, new Key($this->secretKey, 'HS256'));
        } catch (Exception $e) {
            throw new Exception('Невалидный токен: ' . $e->getMessage());
        }
    }

    public function handle($request): array
    {
        try {
            $user = $this->validate($request);

            // Добавляем пользователя в запрос
            if (is_object($request)) {
                $request->attributes->set('user', $user);
            } else {
                $request['user'] = $user;
            }

            return $request;

        } catch (Exception $e) {
            http_response_code($e->getCode() ?: 401);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    private function extractToken($request): ?string {
        if (is_object($request) && method_exists($request, 'headers')) {
            $authHeader = $request->headers->get('Authorization');

            if (!$authHeader) {
                return $request->cookies->get('auth_token') ?? $request->query->get('token');
            }
        } else {
            $authHeader = $request['headers']['authorization'] ?? '';
        }

        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function generateToken($user): string {
        $payload = [
            'sub' => $user['id'], // используем 'sub' для совместимости
            'username' => $user['username'],
            'email' => $user['email'],
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24 * 7) // 7 days
        ];

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }
}
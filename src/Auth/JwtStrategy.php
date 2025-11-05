<?php

namespace App\Auth;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../users/UsersService.php';
require_once __DIR__ . '/../Config/ConfigService.php';

use App\Exceptions\UnauthorizedException;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtStrategy {
    private $configService;
    private $userService;
    private $secretKey;

    public function __construct() {
        $this->userService = new UserService();
        $this->secretKey = $this->configService->get('JWT_KEY');
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

            $user = $this->userService->findOne(['id' => $jwtPayload->sub]);

            if (!$user) {
                throw new Exception('Необходимо авторизоваться', 401);
            }

            return $user;

        } catch (Exception $e) {
            throw new Exception('Ошибка аутентификации: ' . $e->getMessage(), 401);
        }
    }

    private function extractJwtFromRequest($request) {
        $authHeader = $request['headers']['authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? '';

        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        $tokenFromQuery = $_GET['token'] ?? null;

        if ($tokenFromQuery) {
            return $tokenFromQuery;
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

    /**
     * @throws Exception
     */
    public function handle($request): array
    {
        try {
            $user = $this->validate($request);

            $request['user'] = $user;
            return $request;

        } catch (UnauthorizedException $e) {
            http_response_code($e->getCode() ?: 401);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
}
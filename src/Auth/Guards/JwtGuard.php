<?php


namespace App\Auth\Guards;

use App\Auth\JwtStrategy;
use App\Exceptions\UnauthorizedException;
use App\Users\Entities\User;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\HttpFoundation\Request;


class JwtGuard
{
    private string $secretKey;
    private JwtStrategy $jwtStrategy;

    public function __construct()
    {
        $this->secretKey = $_ENV['JWT_SECRET'] ?? 'fallback-secret-key';
        $this->jwtStrategy = new JwtStrategy();
    }

    public function canActivate(array $request): bool {
        try {
            $authenticatedRequest = $this->jwtStrategy->handle($request);

            $GLOBALS['user'] = $authenticatedRequest['user'] ?? null;

            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * @throws UnauthorizedException
     */
    public function validate(Request $request): User
    {
        $token = $this->extractToken($request);

        if (!$token) {
            throw new UnauthorizedException('Token not provided');
        }

        try {
            // Декодируем JWT токен используя библиотеку
            $payload = JWT::decode($token, new Key($this->secretKey, 'HS256'));
            $payloadArray = (array)$payload;

            // Создаем пользователя из payload
            return new User(
                $payloadArray['username'] ?? '',
                $payloadArray['email'] ?? '',
                $payloadArray['about'] ?? 'Пока ничего не рассказал о себе',
                $payloadArray['avatar'] ?? 'https://i.pravatar.cc/300'
            );

        } catch (\Exception $e) {
            throw new UnauthorizedException('Invalid token: ' . $e->getMessage());
        }
    }

    private function extractToken(Request $request): ?string {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader) {
            return $request->cookies->get('auth_token') ?? $request->query->get('token');
        }

        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

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
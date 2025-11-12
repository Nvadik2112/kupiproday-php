<?php

namespace App\Auth\Guards;

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Auth\AuthService;
use App\Auth\LocalStrategy;
use App\Auth\Exceptions\ValidationException;
use App\Exceptions\Domain\UnauthorizedException;
use App\Hash\HashService;
use Symfony\Component\HttpFoundation\Request;

class LocalGuard {
    private AuthService $authService;
    private LocalStrategy $localStrategy;

    public function __construct() {
        $hashService = new HashService();
        $this->authService = new AuthService($hashService);
        $this->localStrategy = new LocalStrategy($this->authService);
    }

    /**
     * @throws ValidationException
     * @throws UnauthorizedException
     */
    public function validate(Request $request) {
        // Получаем данные из запроса
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        return $this->localStrategy->validate($username, $password);
    }

    public function canActivate(Request $request): bool {
        return $request->getMethod() === 'POST' &&
            str_contains($request->getPathInfo(), '/signin');
    }
}

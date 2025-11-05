<?php

namespace Auth\Guards;

require_once __DIR__ . '/../../../vendor/autoload.php';

use Auth\AuthService;
use Auth\Dto\SigninDto;
use Auth\Exceptions\ValidationException;
use Auth\LocalStrategy;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

class LocalGuard {
    private AuthService $authService;
    private LocalStrategy $localStrategy;

    public function __construct() {
        $this->authService = new AuthService();
        $this->localStrategy = new LocalStrategy($this->authService);
    }

    /**
     * @throws ValidationException
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

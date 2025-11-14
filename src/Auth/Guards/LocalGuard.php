<?php

namespace App\Auth\Guards;

require_once __DIR__ . '/../../../vendor/autoload.php';

use AllowDynamicProperties;
use App\Auth\AuthService;
use App\Auth\LocalStrategy;
use App\Auth\Exceptions\ValidationException;
use App\Hash\HashService;
use App\Users\UsersModule;
use Symfony\Component\HttpFoundation\Request;

#[AllowDynamicProperties]
class LocalGuard {
    private AuthService $authService;
    private LocalStrategy $localStrategy;

    public function __construct() {
        $hashService = new HashService();
        $usersModule = UsersModule::getInstance();
        $this->authService = new AuthService($hashService, $usersModule->getUserService());
        $this->localStrategy = new LocalStrategy($this->authService);
    }

    /**
     * @throws ValidationException
     */
    public function validate(Request $request): ?array
    {
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

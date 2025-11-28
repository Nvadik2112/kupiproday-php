<?php

namespace App\Auth;

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Auth\Guards\LocalGuard;
use App\Users\UsersModule;
use App\Hash\HashService;
use App\Config\ConfigService;

class AuthModule {
    private static ?AuthModule $instance = null;
    private array $services = [];

    private function __construct() {
        $this->initialize();
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initialize(): void {
        $configService = new ConfigService();
        $usersModule = UsersModule::getInstance();

        $hashService = new HashService();

        $jwtConfig = [
            'secret' => $configService->get('JWT_KEY', 'fallback-secret-key'),
            'signOptions' => ['expiresIn' => '7d']
        ];

        $this->services['authService'] = new AuthService($hashService, $usersModule->getUserService());
        $this->services['jwtStrategy'] = new JwtStrategy($usersModule->getUserService());
        $this->services['localStrategy'] = new LocalStrategy($this->services['authService']);
        $this->services['localGuard'] = new LocalGuard();

        $this->services['authController'] = new AuthController(
            $usersModule->getUserService(),
            $this->services['authService'],
            $this->services['localGuard']
        );

        $this->services['jwtConfig'] = $jwtConfig;
        $this->services['hashService'] = $hashService;
    }

    public function get(string $serviceName) {
        return $this->services[$serviceName] ?? null;
    }

    public function getAuthService(): AuthService {
        return $this->services['authService'];
    }

    public function getJwtStrategy(): JwtStrategy {
        return $this->services['jwtStrategy'];
    }

    public function getLocalStrategy(): LocalStrategy {
        return $this->services['localStrategy'];
    }

    public function getAuthController(): AuthController {
        return $this->services['authController'];
    }

    public function getJwtConfig(): array {
        return $this->services['jwtConfig'];
    }

    public function getHashService(): HashService {
        return $this->services['hashService'];
    }
}
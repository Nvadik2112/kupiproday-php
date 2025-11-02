<?php

namespace App\Auth;

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Users\UsersModule;
use App\Hash\HashModule;
use App\Config\ConfigService;

class AuthModule {
    private static AuthModule $instance;
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

    private function initialize(): void  {
        $configService = new ConfigService();
        $usersModule = UsersModule::getInstance();
        $hashModule = HashModule::getInstance();

        $jwtConfig = [
            'secret' => $configService->get('JWT_KEY', 'fallback-secret-key'),
            'signOptions' => ['expiresIn' => '7d']
        ];

        // Создаем сервисы (аналог providers)
        $this->services['authService'] = new AuthService();
        $this->services['jwtStrategy'] = new JwtStrategy($configService, $usersModule->getUserService());
        $this->services['localStrategy'] = new LocalStrategy($this->services['authService']);

        // Контроллер (аналог controllers)
        $this->services['authController'] = new AuthController(
            $usersModule->getUserService(),
            $this->services['authService']
        );

        // Сохраняем конфиг
        $this->services['jwtConfig'] = $jwtConfig;
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
}
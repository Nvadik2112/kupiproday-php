<?php

namespace App\Bootstrap;

use App\AppModule;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Application {
    private AppModule $appModule;
    private string $environment;

    public function __construct() {
        $this->environment = $_ENV['APP_ENV'] ?? 'production';
        $this->initialize();
    }

    private function initialize(): void {
        // Инициализируем модуль приложения
        $this->appModule = new AppModule();

        // Настраиваем CORS
        $this->setupCors();
    }

    private function setupCors(): void {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        header('Access-Control-Allow-Credentials: true');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    public function run(): void {
        $request = Request::createFromGlobals();
        $response = $this->handleRequest($request);
        $response->send();
    }

    private function handleRequest(Request $request): Response {
        return $this->appModule->handle($request);
    }

    public function getAppModule(): AppModule {
        return $this->appModule;
    }

    public function getEnvironment(): string {
        return $this->environment;
    }
}
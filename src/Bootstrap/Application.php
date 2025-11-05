<?php

namespace App\Bootstrap;

use App\AppModule;
use App\Exceptions\ApiErrorHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Application {
    private AppModule $appModule {
        get {
            return $this->appModule;
        }
    }
    private string $environment;

    public function __construct() {
        $this->environment = $_ENV['APP_ENV'] ?? 'production';
        $this->initialize();
    }

    private function initialize(): void {
        // Регистрируем обработчик исключений
        ApiErrorHandler::register();

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

        // Handle preflight OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    public function run(): void {
        try {
            $request = Request::createFromGlobals();
            $response = $this->handleRequest($request);
            $response->send();
        } catch (\Throwable $e) {
            // Fallback error handler
            error_log("Application error: " . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Internal Server Error']);
        }
    }

    private function handleRequest(Request $request): Response {
        return $this->appModule->handle($request);
    }

}
<?php

namespace App\Bootstrap;

use App\Kernel;
use App\Constants\Status;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Application 
{
    private Kernel $kernel;
    private string $environment;

    public function __construct() {
        $this->environment = $_ENV['APP_ENV'] ?? 'production';
        $this->initialize();
    }

    private function initialize(): void {
        // Ваша кастомная инициализация
        $this->setupCors();
        $this->setupErrorHandling();
        
        // Создаем Symfony Kernel
        $this->kernel = new Kernel($this->environment, $this->environment === 'dev');
    }

    private function setupCors(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(Status::DEFAULT_ERR);
            exit;
        }
    }

    private function setupErrorHandling(): void {
        if ($this->environment === 'production') {
            set_exception_handler(function (\Throwable $e) {
                error_log("Unhandled exception: " . $e->getMessage());
                http_response_code(Status::DEFAULT_ERR);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Internal Server Error']);
            });
        }
    }

    public function run(): void {
        try {
            $request = Request::createFromGlobals();
            $response = $this->kernel->handle($request);
            $response->send();
            $this->kernel->terminate($request, $response);
        } catch (\Throwable $e) {
            error_log("Application error: " . $e->getMessage());
            http_response_code(Status::DEFAULT_ERR);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Internal Server Error']);
        }
    }

    public function getKernel(): Kernel {
        return $this->kernel;
    }

    public function getEnvironment(): string {
        return $this->environment;
    }
}
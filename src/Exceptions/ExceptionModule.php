<?php

namespace App\Exceptions;

use Symfony\Component\ErrorHandler\ErrorHandler;

class ExceptionModule {
    private static ExceptionModule $instance;
    private ApiExceptionHandler $apiHandler {
        get {
            return $this->apiHandler;
        }
    }
    private bool $isInitialized = false;

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function initialize(string $environment = 'production'): void {
        if ($this->isInitialized) {
            return;
        }

        $this->apiHandler = new ApiExceptionHandler();

        if ($environment === 'production') {
            $this->setupProductionHandlers();
        } else {
            $this->setupDevelopmentHandlers();
        }

        $this->isInitialized = true;
    }

    private function setupProductionHandlers(): void {
        // Symfony ErrorHandler для базовой обработки
        ErrorHandler::register();

        // Устанавливаем глобальный обработчик для API
        set_exception_handler([$this->apiHandler, 'handleException']);
        set_error_handler([$this->apiHandler, 'handleError']);
    }

    private function setupDevelopmentHandlers(): void {
        ErrorHandler::register();
        set_exception_handler([$this->apiHandler, 'handleException']);
    }

}

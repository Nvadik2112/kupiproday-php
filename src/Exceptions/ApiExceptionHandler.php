<?php

namespace App\Exceptions;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiExceptionHandler {
    public function handleException(\Throwable $exception): void {
        $response = $this->createResponseFromException($exception);
        $response->send();
        exit;
    }

    public function handleError(int $level, string $message, string $file = '', int $line = 0): bool {
        $exception = new \ErrorException($message, 0, $level, $file, $line);
        $this->handleException($exception);
        return true;
    }

    private function createResponseFromException(\Throwable $e): JsonResponse {
        $statusCode = $this->determineStatusCode($e);
        $errorType = $this->getErrorType($statusCode);

        $responseData = [
            'error' => [
                'type' => $errorType,
                'message' => $this->getUserMessage($e),
                'code' => $statusCode
            ]
        ];

        // В development добавляем debug информацию
        if ($this->isDevelopment()) {
            $responseData['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ];
        }

        return new JsonResponse($responseData, $statusCode);
    }

    private function determineStatusCode(\Throwable $e): int {
        // Если исключение уже имеет HTTP код
        if ($e instanceof HttpException) {
            return $e->getCode();
        }

        // Определяем по сообщению
        $message = $e->getMessage();

        if (str_contains($message, 'Невалидный токен') ||
            str_contains($message, 'Токен не предоставлен') ||
            str_contains($message, 'Необходима авторизация') ||
            str_contains($message, 'Учетная запись не найдена') ||
            str_contains($message, 'Неверный пароль')) {
            return 401;
        }

        if (str_contains($message, 'Email или username с таким именем существует')) {
            return 409;
        }

        if (str_contains($message, 'Validation failed')) {
            return 400;
        }

        return 500;
    }

    private function getErrorType(int $statusCode): string {
        return match($statusCode) {
            400 => 'VALIDATION_ERROR',
            401 => 'AUTHENTICATION_ERROR',
            403 => 'AUTHORIZATION_ERROR',
            404 => 'NOT_FOUND',
            409 => 'CONFLICT',
            default => 'INTERNAL_ERROR'
        };
    }

    private function getUserMessage(\Throwable $e): string {
        // Для пользовательских исключений используем их сообщение
        if ($e instanceof HttpException) {
            return $e->getMessage();
        }

        // Для системных исключений - обобщенные сообщения
        $statusCode = $this->determineStatusCode($e);

        return match($statusCode) {
            401 => 'Требуется авторизация',
            403 => 'Доступ запрещен',
            404 => 'Ресурс не найден',
            409 => 'Конфликт данных',
            500 => 'Внутренняя ошибка сервера',
            default => 'Произошла ошибка'
        };
    }

    private function isDevelopment(): bool {
        return ($_ENV['APP_ENV'] ?? 'production') === 'development';
    }
}
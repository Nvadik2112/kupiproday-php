<?php

namespace App\Exceptions;

use ErrorException;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiErrorHandler {
    public static function register(): void {
        set_exception_handler([self::class, 'handleException']);
        set_error_handler([self::class, 'handleError']);
    }

    /**
     * @throws ErrorException
     */
    public static function handleError($level, $message, $file = '', $line = 0): bool {
        throw new ErrorException($message, 0, $level, $file, $line);
    }

    public static function handleException(\Throwable $exception): void {
        $response = self::createErrorResponse($exception);
        $response->send();
        exit;
    }

    private static function createErrorResponse(\Throwable $e): JsonResponse {
        $statusCode = self::determineStatusCode($e);

        $data = [
            'error' => [
                'code' => $statusCode,
                'message' => $e->getMessage(),
                'type' => self::getErrorType($statusCode)
            ]
        ];

        if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
            $data['debug'] = [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ];
        }

        return new JsonResponse($data, $statusCode);
    }

    private static function determineStatusCode(\Throwable $e): int {
        $message = $e->getMessage();

        if (str_contains($message, 'Невалидный токен') ||
            str_contains($message, 'Токен не предоставлен') ||
            str_contains($message, 'Учетная запись не найдена') ||
            str_contains($message, 'Неверный пароль') ||
            str_contains($message, 'Необходима авторизация')) {
            return 401;
        }

        if (str_contains($message, 'Email или username с таким именем существует')) {
            return 409;
        }

        return $e->getCode() ?: 500;
    }

    private static function getErrorType(int $statusCode): string {
        return match($statusCode) {
            400 => 'BAD_REQUEST',
            401 => 'UNAUTHORIZED',
            403 => 'FORBIDDEN',
            404 => 'NOT_FOUND',
            409 => 'CONFLICT',
            422 => 'VALIDATION_ERROR',
            default => 'INTERNAL_ERROR'
        };
    }
}

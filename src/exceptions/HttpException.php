<?php

namespace App\Exceptions;

class HttpException extends \Exception {
    public function __construct(string $message = "", int $code = 500, \Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}

class UnauthorizedException extends HttpException {
    public function __construct(string $message = "Требуется авторизация") {
        parent::__construct($message, 401);
    }
}

class ValidationException extends HttpException {
    private $errors;

    public function __construct(array $errors, string $message = "Ошибка валидации") {
        $this->errors = $errors;
        parent::__construct($message, 400);
    }

    public function getErrors(): array {
        return $this->errors;
    }
}

class ConflictException extends HttpException {
    public function __construct(string $message = "Конфликт данных") {
        parent::__construct($message, 409);
    }
}
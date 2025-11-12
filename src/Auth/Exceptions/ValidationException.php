<?php

namespace App\Auth\Exceptions;

class ValidationException extends \Exception {
    private array $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct('Validation failed');
    }

    public function getResponse(): false|string
    {
        http_response_code($this->getCode());
        header('Content-Type: application/json');

        return json_encode([
            'statusCode' => $this->getCode(),
            'message' => $this->errors,
            'error' => 'Bad Request'
        ]);
    }
}
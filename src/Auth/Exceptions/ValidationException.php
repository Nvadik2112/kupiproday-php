<?php

namespace Auth\Exceptions;

use Exception;

class ValidationException extends Exception {
    private $errors;

    public function __construct(array $errors, $code = 400)
    {
        $this->errors = $errors;
        parent::__construct('Validation failed', $code);
    }

    public function getResponse()
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
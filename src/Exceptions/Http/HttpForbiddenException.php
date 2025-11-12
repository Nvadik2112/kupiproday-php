<?php

namespace App\Exceptions\Http;
use App\Exceptions\HttpException;

class HttpForbiddenException extends HttpException
{
    public function __construct(string $message = "Forbidden")
    {
        parent::__construct($message, 403);
    }
}
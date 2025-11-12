<?php

namespace App\Exceptions\Http;
use App\Exceptions\HttpException;

class HttpBadRequestException extends HttpException
{
    public function __construct(string $message = "Bad Request")
    {
        parent::__construct($message, 400);
    }
}
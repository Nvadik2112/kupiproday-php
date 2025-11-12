<?php

namespace App\Exceptions\Http;
use App\Exceptions\HttpException;

class HttpUnauthorizedException extends HttpException
{
    public function __construct(string $message = "Unauthorized")
    {
        parent::__construct($message, 401);
    }
}
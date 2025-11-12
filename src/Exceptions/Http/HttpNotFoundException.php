<?php

namespace App\Exceptions\Http;
use App\Exceptions\HttpException;

class HttpNotFoundException extends HttpException
{
    public function __construct(string $message = "Not Found")
    {
        parent::__construct($message, 404);
    }
}
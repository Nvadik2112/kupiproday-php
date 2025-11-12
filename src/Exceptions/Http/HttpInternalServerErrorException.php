<?php

namespace App\Exceptions\Http;
use App\Exceptions\HttpException;

class HttpInternalServerErrorException extends HttpException
{
    public function __construct(string $message = "Internal Server Error")
    {
        parent::__construct($message, 500);
    }
}
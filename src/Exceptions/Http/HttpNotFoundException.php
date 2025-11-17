<?php

namespace App\Exceptions\Http;
use App\Exceptions\HttpException;
use App\Constants\Status;

class HttpNotFoundException extends HttpException
{
    public function __construct(string $message = "Not Found")
    {
        parent::__construct($message, Status::NOT_FOUND);
    }
}
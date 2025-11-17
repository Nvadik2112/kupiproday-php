<?php

namespace App\Exceptions\Http;
use App\Exceptions\HttpException;
use App\Constants\Status;

class HttpForbiddenException extends HttpException
{
    public function __construct(string $message = "Forbidden")
    {
        parent::__construct($message, Status::FORBIDDEN);
    }
}
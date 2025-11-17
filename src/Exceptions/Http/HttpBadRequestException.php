<?php

namespace App\Exceptions\Http;
use App\Exceptions\HttpException;
use App\Constants\Status;

class HttpBadRequestException extends HttpException
{
    public function __construct(string $message = "Bad Request")
    {
        parent::__construct($message, Status::BAD_REQUEST);
    }
}
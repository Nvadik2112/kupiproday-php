<?php

namespace App\Exceptions\Http;
use App\Constants\Status;
use App\Exceptions\HttpException;

class HttpInternalServerErrorException extends HttpException
{
    public function __construct(string $message = "Internal Server Error")
    {
        parent::__construct($message, Status::DEFAULT_ERR);
    }
}
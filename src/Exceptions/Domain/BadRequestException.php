<?php

namespace App\Exceptions\Domain;
use App\Exceptions\DomainException;
class BadRequestException extends DomainException
{
    public function __construct(string $message = "Bad Request")
    {
        parent::__construct($message);
    }
}
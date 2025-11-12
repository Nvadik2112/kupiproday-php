<?php

namespace App\Exceptions\Domain;
use App\Exceptions\DomainException;
class UnauthorizedException extends DomainException
{
    public function __construct(string $message = "Unauthorized")
    {
        parent::__construct($message);
    }
}
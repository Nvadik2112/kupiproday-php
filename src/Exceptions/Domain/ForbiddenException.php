<?php

namespace App\Exceptions\Domain;
use App\Exceptions\DomainException;

class ForbiddenException extends DomainException
{
    public function __construct(string $message = "Forbidden")
    {
        parent::__construct($message);
    }
}
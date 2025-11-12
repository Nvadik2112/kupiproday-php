<?php

namespace App\Exceptions\Domain;
use App\Exceptions\DomainException;

class InternalServerErrorException extends DomainException
{
    public function __construct(string $message = "Internal Server Error")
    {
        parent::__construct($message);
    }
}
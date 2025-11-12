<?php

namespace App\Exceptions\Domain;
use App\Exceptions\DomainException;

class NotFoundException extends DomainException
{
    public function __construct(string $resource = "Not Found")
    {
        parent::__construct($resource);
    }
}
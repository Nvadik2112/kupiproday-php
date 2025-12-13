<?php

namespace App\Exceptions;

use App\Constants\Status;
use App\Exceptions\Domain\NotFoundException;
use App\Exceptions\Domain\BadRequestException;
use App\Exceptions\Domain\ForbiddenException;
use App\Exceptions\Domain\UnauthorizedException;
use App\Exceptions\Http\HttpNotFoundException;
use App\Exceptions\Http\HttpBadRequestException;
use App\Exceptions\Http\HttpForbiddenException;
use App\Exceptions\Http\HttpInternalServerErrorException;
use App\Exceptions\Http\HttpUnauthorizedException;

class ExceptionHandler
{
    public function handle(\Throwable $e): void
    {
        $httpException = $this->mapToHttpException($e);
        $this->sendResponse($httpException);
    }

    private function mapToHttpException(\Throwable $e): \Throwable
    {
        return match (get_class($e)) {
            NotFoundException::class => new HttpNotFoundException($e->getMessage()),
            BadRequestException::class => new HttpBadRequestException($e->getMessage()),
            ForbiddenException::class => new HttpForbiddenException($e->getMessage()),
            UnauthorizedException::class => new HttpUnauthorizedException($e->getMessage()),

            default => $e instanceof \DomainException
                ? new HttpInternalServerErrorException($e->getMessage())
                : $e
        };
    }

    private function sendResponse(\Throwable $e): void    {

        $code = $e->getCode() ?: Status::DEFAULT_ERR;

        if ($code < 100 || $code >= 600) {
            $code = Status::DEFAULT_ERR;
        }

        http_response_code($code);
        header('Content-Type: application/json');

        $response = [
            'error' => $e->getMessage(),
            'code' => $e->getCode()
        ];

        echo json_encode($response, JSON_PRETTY_PRINT);
    }
}
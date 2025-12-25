<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Bootstrap\Application;
use App\Exceptions\ExceptionHandler;

set_exception_handler(function ($e) {
    $handler = new ExceptionHandler();
    $handler->handle($e);
});

set_error_handler(/**
 * @throws ErrorException
 */ function ($errno, $errstr, $errfile, $errline) {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

$app = new Application();
$app->run();

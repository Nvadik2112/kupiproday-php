<?php

// Простой роутер
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Убираем query string
$path = parse_url($requestUri, PHP_URL_PATH);

set_exception_handler([new App\Exceptions\ExceptionHandler(), 'handle']);
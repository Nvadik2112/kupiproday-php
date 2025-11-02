<?php
require_once __DIR__ . '/routes/web.php';

// Простой роутер
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Убираем query string
$path = parse_url($requestUri, PHP_URL_PATH);

// Вызываем соответствующий маршрут
route($requestMethod, $path);
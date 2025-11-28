<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

// Создаем тестовый запрос
$request = Request::create('/signup', 'POST', [], [], [], [], json_encode([
    'email' => 'test@test.com',
    'password' => 'test123',
    'username' => 'testuser'
]));

// Тестируем напрямую
$appModule = new App\AppModule();
$response = $appModule->handle($request);

echo "Status: " . $response->getStatusCode() . "\n";
echo "Content: " . $response->getContent() . "\n";
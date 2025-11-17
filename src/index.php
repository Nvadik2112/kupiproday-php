<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Bootstrap\Application;

if (file_exists(dirname(__DIR__).'/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();
}

$app = new Application();
$app->run();

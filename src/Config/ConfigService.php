<?php

namespace App\Config;

require_once 'vendor/autoload.php';

use Dotenv\Dotenv;

class ConfigService {
    private Dotenv $dotenv;

    public function __construct()
    {
        $this->dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $this->dotenv->load();
    }

    public function get($key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}
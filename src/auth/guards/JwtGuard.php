<?php

namespace App\Auth\Guards;

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Auth\JwtStrategy;
use Exception;

class JwtGuard
{
    private JwtStrategy $JwtStrategy;

    public function __construct()
    {
        $this->JwtStrategy = new JwtStrategy();
    }

    /**
     * Основной метод guard'а - аналог canActivate в Nest.js
     */
    public function canActivate(array $request): bool {
        try {
            $authenticatedRequest = $this->JwtStrategy->handle($request);

            $GLOBALS['user'] = $authenticatedRequest['user'] ?? null;

            return true;

        } catch (Exception $e) {
            return false;
        }
    }
}
<?php

namespace App\Users;

use App\Auth\Guards\JwtGuard;
use App\Hash\HashService;
use App\Database\DataBaseModule;

class UsersModule {
    private static ?UsersModule $instance = null;
    private array $services = [];

    private function __construct() {
        $this->initialize();
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initialize(): void {
        $this->services['hashService'] = new HashService();

        $this->services['pdo'] = DataBaseModule::getInstance();

        $this->services['usersService'] = new UsersService(
            $this->services['pdo'],
            $this->services['hashService']
        );

        $this->services['jwtGuard'] = new JwtGuard();

        $this->services['usersController'] = new UsersController(
            $this->services['usersService'],
            $this->services['jwtGuard']
        );
    }

    public function getUserService(): UsersService {
        return $this->services['usersService'];
    }

    public function getUsersController(): UsersController {
        return $this->services['usersController'];
    }

    public function getHashService(): HashService {
        return $this->services['hashService'];
    }

    public function getJwtGuard(): JwtGuard {
        return $this->services['jwtGuard'];
    }
}
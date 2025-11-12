<?php

namespace App\Users;

use App\Auth\AuthService;
use App\Hash\HashService;
use App\Auth\Guards\JwtGuard;
use PDO;
// use App\Wishes\WishesService;
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
        $this->services['jwtGuard'] = new JwtGuard();
        $this->services['pdo'] = $this->createPDOConnection();



        $this->services['usersService'] = new UsersService($this->services['pdo'], $this->services['hashService']);

        $this->services['usersController'] = new UsersController(
            $this->services['usersService'],
            $this->services['jwtGuard']
        );

        // $wishesService = WishesModule::getInstance()->getWishesService(); // WishesModule
        // $this->services['wishesService'] = $wishesService;
    }

    private function createPDOConnection(): PDO {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'myapp';
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? '';

        return new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }

    // Аналог exports: [UsersService]
    public function getUserService(): UsersService {
        return $this->services['usersService'];
    }

    public function getUsersController(): UsersController {
        return $this->services['usersController'];
    }

    public function getHashService(): HashService {
        return $this->services['hashService'];
    }

    // public function getWishesService(): WishesService {
    //     return $this->services['wishesService'];
    // }
}

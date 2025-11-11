<?php

namespace App\Users;

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
        $hashService = new HashService();
        $jwtGuard = new JwtGuard();

        $pdo = $this->createPDOConnection();
        // $wishesService = WishesModule::getInstance()->getWishesService(); // WishesModule

        $this->services['usersService'] = new UsersService($pdo, $hashService);

        $this->services['usersController'] = new UsersController(
            $this->services['usersService'],
            $jwtGuard
        );

        $this->services['hashService'] = $hashService;
        $this->services['pdo'] = $pdo;
        $this->services['jwtGuard'] = $jwtGuard;
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

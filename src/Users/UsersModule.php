<?php

namespace App\Users;

use App\Hash\HashService;
use App\Wishes\WishesService;

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
        $hashService = new HashService(); // HashModule
        // $wishesService = WishesModule::getInstance()->getWishesService(); // WishesModule

        $this->services['usersService'] = new UsersService($hashService);


        $this->services['usersController'] = new UsersController($this->services['usersService']);

        $this->services['hashService'] = $hashService;
        // $this->services['wishesService'] = $wishesService;
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
<?php

namespace App\Auth\Guards;

use App\Auth\JwtStrategy;
use App\Database\DataBaseModule;
use App\Hash\HashService;
use App\Users\UsersService;
use Exception;
use Symfony\Component\HttpFoundation\Request;

class JwtGuard
{
    private JwtStrategy $jwtStrategy;

    public function __construct()
    {
        $connection = DataBaseModule::getInstance();
        $hashService = new HashService();
        $usersService = new UsersService($connection, $hashService);

        $this->jwtStrategy = new JwtStrategy($usersService);
    }

    /**
     * @throws Exception
     */
    public function validate(Request $request): array
    {
        return $this->jwtStrategy->validate($request);
    }

    public function handle($request): array
    {
        return $this->jwtStrategy->handle($request);
    }
}
<?php

namespace App;

use App\Auth\AuthModule;
use App\Auth\Exceptions\ValidationException;
use App\Database\DataBaseModule;
use App\Exceptions\Domain\BadRequestException;
use App\Exceptions\Domain\ForbiddenException;
use App\Exceptions\Domain\NotFoundException;
use App\Exceptions\Domain\UnauthorizedException;
use App\Users\UsersModule;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AppModule
{
    private AuthModule $authModule;
    private UsersModule $usersModule;

    public function __construct()
    {
        DataBaseModule::getInstance();

        if (($_ENV['APP_ENV'] ?? 'production') === 'development') {
            DataBaseModule::runMigrations();
        }

        $this->authModule = AuthModule::getInstance();
        $this->usersModule = UsersModule::getInstance();
    }

    /**
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws ForbiddenException
     * @throws BadRequestException
     * @throws ValidationException
     */
    public function handle(?Request $request = null): Response
    {
        $request = $request ?? Request::createFromGlobals();
        $path = $request->getPathInfo();
        $method = $request->getMethod();

        return $this->route($request, $path, $method);
    }

    /**
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws ForbiddenException
     * @throws BadRequestException
     * @throws ValidationException
     */
    private function route(Request $request, string $path, string $method): JsonResponse
    {
        $authController = $this->authModule->getAuthController();
        $usersController = $this->usersModule->getUsersController();

        if ($method === 'GET') {
            if ($path === '/users/me') {
                return $usersController->getProfile($request);
            }

            if (preg_match('#^/users/(\d+)$#', $path, $matches)) {
                return $usersController->getUser((int)$matches[1]);
            }
        }

        if ($method === 'POST') {
            if ($path === '/signin') {
                return $authController->signin($request);
            }
            if ($path === '/signup') {
                return $authController->signup($request);
            }
            if ($path === '/users/find') {
                return $usersController->searchUsers($request);
            }
        }

        if ($method === 'PATCH') {
            if ($path === '/users/me') {
                return $usersController->updateMyProfile($request);
            }
        }

        return new JsonResponse(['error' => 'Not Found'], 404);
    }
}

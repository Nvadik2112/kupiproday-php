<?php

namespace App;

use App\Auth\AuthModule;
use App\Users\UsersModule;
use App\Database\DataBaseModule;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AppModule {
    private AuthModule $authModule;
    private $usersModule;

    public function __construct() {
        DataBaseModule::getInstance();

        $this->authModule = AuthModule::getInstance();
        $this->usersModule = UsersModule::getInstance();
    }

    public function handle(?Request $request = null): void {
        $request = $request ?? Request::createFromGlobals();
        $path = $request->getPathInfo();
        $method = $request->getMethod();

        $response = $this->route($request, $path, $method);
        $response->send();
    }

    private function route(Request $request, string $path, string $method): JsonResponse {
        $authController = $this->authModule->getAuthController();

        $routes = [
            'POST' => [
                '/signin' => fn() => $authController->signin($request),
                '/signup' => fn() => $authController->signup($request),
            ],
        ];

        $handler = $routes[$method][$path] ?? null;

        if ($handler) {
            return $handler();
        }

        return new JsonResponse(['error' => 'Not Found'], 404);
    }
}

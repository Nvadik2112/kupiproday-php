<?php

namespace App\Auth;

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Users\UserService;
use App\Auth\Guards\LocalGuard;
use App\Users\Dto\CreateUserDto;
use App\Types\RequestUser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthController {
    private UserService $usersService;
    private AuthService $authService;

    public function __construct() {
        $this->usersService = new UserService();
        $this->authService = new AuthService();
    }

    /**
     * @Post('/signin')
     * @UseGuards(LocalGuard)
     */
    public function signin(Request $request): JsonResponse
    {
        try {
            $localGuard = new LocalGuard();
            $user = $localGuard->validate($request);

            $tokens = $this->authService->auth($user);

            return new JsonResponse($tokens);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 401);
        }
    }

    /**
     * @Post('/signup')
     */
    public function signup(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            $createUserDto = CreateUserDto::fromArray($data);

            $this->usersService->findSameUser($createUserDto);

            $user = $this->usersService->create($createUserDto);

            unset($user['password']);

            return new JsonResponse($user, 201);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }
}
<?php

namespace App\Auth;

use App\Users\UsersService;
use App\Auth\Guards\LocalGuard;
use App\Users\Dto\CreateUserDto;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AuthController {
    private UsersService $usersService;
    private AuthService $authService;
    private LocalGuard $localGuard;

    public function __construct(
        UsersService $usersService,
        AuthService $authService,
        LocalGuard $localGuard
    ) {
        $this->usersService = $usersService;
        $this->authService = $authService;
        $this->localGuard = $localGuard;
    }

    #[Route('/signin', methods: ['POST'])]
    public function signin(Request $request): JsonResponse
    {
        try {
            $user = $this->localGuard->validate($request);
            $tokens = $this->authService->auth($user);

            return new JsonResponse($tokens);

        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 401);
        }
    }

    #[Route('/signup', methods: ['POST'])]
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
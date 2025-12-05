<?php

namespace App\Auth;

use App\Auth\Exceptions\ValidationException;
use App\Exceptions\Domain\BadRequestException;
use App\Exceptions\Domain\ForbiddenException;
use App\Exceptions\Domain\NotFoundException;
use App\Exceptions\Domain\UnauthorizedException;
use App\Users\UsersService;
use App\Auth\Guards\LocalGuard;
use App\Users\Dto\CreateUserDto;
use App\Constants\Status;
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

    /**
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    public function signin(Request $request): JsonResponse
    {
        $user = $this->localGuard->validate($request);
        $tokens = $this->authService->auth($user);

        return new JsonResponse($tokens);
    }

    /**
     * @throws ForbiddenException
     * @throws NotFoundException
     * @throws BadRequestException
     */
    public function signup(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $createUserDto = CreateUserDto::fromArray($data);
        $user = $this->usersService->create($createUserDto->toArray());

        return new JsonResponse($user, Status::CREATED);
    }
}
<?php

namespace App\Users;


use App\Types\RequestUser;
use App\Users\Dto\UpdateUserDto;
use App\Users\Dto\FindUserDto;
use App\Auth\Guards\JwtGuard;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UsersController {
    private UsersService $usersService;
    private JwtGuard $jwtGuard;

    public function __construct(
        UsersService $usersService,
        JwtGuard $jwtGuard
    ) {
        $this->usersService = $usersService;
        $this->jwtGuard = $jwtGuard;
    }

    /**
     * @Route("/users/me", methods={"GET"})
     */
    public function getProfile(Request $request): JsonResponse {
        try {
            $user = $this->jwtGuard->validate($request);
            $userData = $this->usersService->findUser(['id' => $user->getId()]);

            return new JsonResponse($userData->toArray());
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 401);
        }
    }

    /**
     * @Route("/users/me/wishes", methods={"GET"})
     */
    public function getProfileWishes(Request $request): JsonResponse {
        try {
            $user = $this->jwtGuard->validate($request);
            $wishes = $this->usersService->findWishesByUser(['id' => $user->getId()]);

            return new JsonResponse($wishes);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 401);
        }
    }

    /**
     * @Route("/users/{username}", methods={"GET"})
     */
    public function getUser(string $username): JsonResponse {
        try {
            $user = $this->usersService->findUser(['username' => $username]);

            return new JsonResponse($user->toArray());
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 404);
        }
    }

    /**
     * @Route("/users/{username}/wishes", methods={"GET"})
     */
    public function getUserWishes(string $username): JsonResponse {
        try {
            $wishes = $this->usersService->findWishesByUser(['username' => $username]);

            return new JsonResponse($wishes);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 404);
        }
    }

    /**
     * @Route("/users/me", methods={"PATCH"})
     */
    public function updateMyProfile(Request $request): JsonResponse {
        try {
            $user = $this->jwtGuard->validate($request);
            $data = json_decode($request->getContent(), true);

            $updateUserDto = UpdateUserDto::fromArray($data);
            $updatedUser = $this->usersService->updateOne(
                ['id' => $user->getId()],
                $updateUserDto
            );

            return new JsonResponse($updatedUser->toArray());
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    /**
     * @Route("/users/find", methods={"POST"})
     */
    public function searchUsers(Request $request): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);
            $query = $data['query'] ?? '';

            $users = $this->usersService->findMany($query);

            $usersArray = array_map(function($user) {
                return $user->toArray();
            }, $users);

            return new JsonResponse($usersArray, 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }
}
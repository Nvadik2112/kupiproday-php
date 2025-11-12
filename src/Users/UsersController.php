<?php

namespace App\Users;

use App\Users\UsersService;
use App\Auth\Guards\JwtGuard;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class UsersController 
{
    private UsersService $usersService;
    private JwtGuard $jwtGuard;

    public function __construct(
        UsersService $usersService,
        JwtGuard $jwtGuard
    ) {
        $this->usersService = $usersService;
        $this->jwtGuard = $jwtGuard;
    }

    #[Route('/users/me', methods: ['GET'])]
    public function getProfile(Request $request): JsonResponse
    {
        try {
            $user = $this->jwtGuard->validate($request);
            $userData = $this->usersService->findById($user->getId());

            return new JsonResponse($userData->toArray());
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 401);
        }
    }

    // #[Route('/users/me/wishes', methods: ['GET'])]
    // public function getProfileWishes(Request $request): JsonResponse
    // {
    //     try {
    //         $user = $this->jwtGuard->validate($request);
    //         $wishes = $this->usersService->findWishesByUser($user->getId());

    //         return new JsonResponse($wishes);
    //    } catch (\Exception $e) {
    //         return new JsonResponse([
    //             'error' => $e->getMessage()
    //         ], $e->getCode() ?: 401);
    //     }
    // }

    #[Route('/users/{id}', methods: ['GET'])]
    public function getUser(int $id): JsonResponse
    {
        try {
            $user = $this->usersService->findById($id);

            return new JsonResponse($user->toArray());
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 404);
        }
    }

    // #[Route('/users/{id}/wishes', methods: ['GET'])]
    // public function getUserWishes(int $id): JsonResponse
    // {
    //     try {
    //         $wishes = $this->usersService->findWishesByUser($id);

    //         return new JsonResponse($wishes);
    //     } catch (\Exception $e) {
    //         return new JsonResponse([
    //             'error' => $e->getMessage()
    //         ], $e->getCode() ?: 404);
    //     }
    // }

    #[Route('/users/me', methods: ['PATCH'])]
    public function updateMyProfile(Request $request): JsonResponse
    {
        try {
            $user = $this->jwtGuard->validate($request);
            $data = json_decode($request->getContent(), true);

            $updatedUser = $this->usersService->update(
                $user->getId(),
                $data
            );

            return new JsonResponse($updatedUser->toArray());
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }

    #[Route('/users/find', methods: ['POST'])]
    public function searchUsers(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $query = $data['query'] ?? '';

            $users = $this->usersService->search($query);

            $usersArray = array_map(function($user) {
                return $user->toArray();
            }, $users);

            return new JsonResponse($usersArray);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }
}
<?php

namespace App\Users;

use App\Auth\Guards\JwtGuard;
use App\Constants\Status;
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

    public function getProfile(Request $request): JsonResponse
    {
        try {
            $user = $this->jwtGuard->validate($request);
            $userData = $this->usersService->findById($user['id']);

            return new JsonResponse($userData->toArray());
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 401);
        }
    }

    /*#[Route('/users/me/wishes', methods: ['GET'])]
    public function getProfileWishes(Request $request): JsonResponse
    {
        try {
            $user = $this->jwtGuard->validate($request);
            $wishes = $this->usersService->findWishesByUser($user->getId());

            return new JsonResponse($wishes);
       } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?: 401);
        }
    }*/

    public function getUser(int $id): JsonResponse
    {
        try {
            $user = $this->usersService->findById($id);

            return new JsonResponse($user->toArray());
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?: Status::NOT_FOUND);
        }
    }
   /*#[Route('/users/{id}/wishes', methods: ['GET'])]
     public function getUserWishes(int $id): JsonResponse
     {
         try {
             $wishes = $this->usersService->findWishesByUser($id);

             return new JsonResponse($wishes);
         } catch (\Exception $e) {
             return new JsonResponse([
                 'error' => $e->getMessage()
             ], $e->getCode() ?: Status::NOT_FOUND);
         }
     }*/

    public function updateMyProfile(Request $request): JsonResponse
    {
        try {
            $user = $this->jwtGuard->validate($request);
            $data = json_decode($request->getContent(), true);

            $updatedUser = $this->usersService->update(
                $user['id'],
                $data
            );

            return new JsonResponse($updatedUser->toArray());
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => $e->getMessage()
            ], $e->getCode() ?: Status::BAD_REQUEST);
        }
    }

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
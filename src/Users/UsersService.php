<?php

namespace App\Users;

use App\Users\Entities\User;
use App\Users\Dto\CreateUserDto;
use App\Users\Dto\UpdateUserDto;
use App\Users\Dto\FindUserDto;
use App\Hash\HashService;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class UsersService {
    private UserRepository $usersRepository;
    private HashService $hashService;

    public function __construct(
        HashService $hashService,
        UserRepository $usersRepository
    ) {
        $this->usersRepository = $usersRepository;
        $this->hashService = $hashService;
    }

    public function create(CreateUserDto $createUserDto): User {
        $hashedPassword = $this->hashService->hashPassword(
            $createUserDto->password
        );

        $user = $this->usersRepository->create([
            'username' => $createUserDto->username,
            'email' => $createUserDto->email,
            'password' => $hashedPassword,
        ]);

        if (!$user) {
            throw new BadRequestException('Не удалось создать пользователя');
        }

        return $this->usersRepository->save($user);
    }

    public function findMany(string $search): array {
        if (empty($search)) {
            throw new BadRequestException('Параметр поиска не должен быть пустым');
        }

        $users = $this->usersRepository->findBySearch($search);

        if (empty($users)) {
            throw new NotFoundException('Пользователи не найдены');
        }

        return $users;
    }

    public function findOne(
        $where,
        array $select = [],
        array $relations = []
    ): ?User {
        return $this->usersRepository->findOne(
            $where,
            $select,
            $relations
        );
    }

    public function findUser(
        $filter,
        array $select = [],
        array $relations = []
    ): User {
        $user = $this->findOne($filter, $select, $relations);

        if (!$user) {
            throw new NotFoundException('Пользователь не найден');
        }

        return $user;
    }

    public function updateOne(FindUserDto $filter, UpdateUserDto $updateUserDto): User {
        if ($updateUserDto->email || $updateUserDto->username) {
            $this->findSameUser($updateUserDto);
        }

        if ($updateUserDto->password) {
            $updateUserDto->password = $this->hashService->hashPassword(
                $updateUserDto->password
            );
        }

        $user = $this->findUser($filter);

        // Обновляем поля пользователя
        if ($updateUserDto->username) {
            $user->setUsername($updateUserDto->username);
        }
        if ($updateUserDto->email) {
            $user->setEmail($updateUserDto->email);
        }
        if ($updateUserDto->password) {
            $user->setPassword($updateUserDto->password);
        }

        $updatedUser = $this->usersRepository->save($user);

        $updatedUser->clearPassword();

        return $updatedUser;
    }

    public function findWishesByUser(FindUserDto $filter): array {
        $user = $this->findUser($filter, [], ['wishes']);

        return $user->getWishes();
    }

    public function findSameUser($userDto): void {
        $email = $userDto->email ?? null;
        $username = $userDto->username ?? null;

        $foundUser = $this->findOne([
            ['email', '=', $email],
            ['username', '=', $username]
        ]);

        if ($foundUser) {
            throw new ForbiddenException(
                'Email или username с таким именем существует'
            );
        }
    }
}
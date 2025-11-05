<?php

namespace App\Auth;

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Exceptions\UnauthorizedException;
use Auth\Dto\SigninDto;
use Auth\Exceptions\ValidationException;
use App\Constants\Status;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LocalStrategy {
    private AuthService $authService;
    private ValidatorInterface $validator;

    public function __construct($authService) {
        $this->authService = $authService;
        $this->validator = Validation::createValidator();
    }

    /**
     * @throws UnauthorizedException
     * @throws ValidationException
     */
    public function validate($username, $password) {
        // Создаем DTO
        $signinDto = new SigninDto();
        $signinDto->username = $username;
        $signinDto->password = $password;

        // Валидируем данные
        $violations = $this->validator->validate($signinDto);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }

            throw new ValidationException($errors, Status::BAD_REQUEST);
        }

        $user = $this->authService->validatePassword($username, $password);

        if (!$user) {
            http_response_code(Status::UNAUTHORIZED);
            echo json_encode([
                'error' => 'Unauthorized',
                'message' => 'Username and password is required'
            ]);
            exit;
        }

        return $user;
    }
}
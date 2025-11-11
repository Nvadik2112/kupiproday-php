<?php

namespace App\Auth\Guards;

require_once __DIR__ . '/../../../vendor/autoload.php';

use App\Auth\AuthService;
use App\Auth\LocalStrategy;
use App\Auth\Dto\SigninDto;
use App\Auth\Exceptions\ValidationException;
use App\Hash\HashService;
use App\Users\UsersService;
use PDO;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

class LocalGuard {
    private AuthService $authService;
    private LocalStrategy $localStrategy;

    public function __construct() {
        $pdo = $this->createPDOConnection();
        $hashService = new HashService();
        $usersService = new UsersService($pdo, $hashService); // Или другой способ получить UsersService
        $secretKey = $_ENV['JWT_SECRET'] ?? 'your-default-secret-key';
        $this->authService = new AuthService($hashService);
        $this->localStrategy = new LocalStrategy($this->authService);
    }

    private function createPDOConnection(): PDO {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'myapp';
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASSWORD'] ?? '';

        return new PDO(
            "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    }

    /**
     * @throws ValidationException
     */
    public function validate(Request $request) {
        // Получаем данные из запроса
        $data = json_decode($request->getContent(), true);
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        return $this->localStrategy->validate($username, $password);
    }

    public function canActivate(Request $request): bool {
        return $request->getMethod() === 'POST' &&
            str_contains($request->getPathInfo(), '/signin');
    }
}

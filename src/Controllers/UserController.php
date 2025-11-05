<?php

class UserController {
    public function index() {
        header('Content-Type: application/json');

        // Пример данных (замени на работу с БД)
        $users = [
            ['id' => 1, 'name' => 'John'],
            ['id' => 2, 'name' => 'Jane']
        ];

        echo json_encode($users);
    }

    public function show() {
        // Получаем ID из URL
        $path = $_SERVER['REQUEST_URI'];
        $parts = explode('/', $path);
        $id = end($parts);

        header('Content-Type: application/json');
        echo json_encode(['id' => $id, 'name' => 'Users ' . $id]);
    }

    public function store() {
        $input = json_decode(file_get_contents('php://input'), true);

        header('Content-Type: application/json');
        echo json_encode([
            'message' => 'Users created',
            'data' => $input
        ]);
    }
}
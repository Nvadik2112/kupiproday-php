<?php

function route($method, $path) {
    $path = ltrim($path, '/');

    $routes = [
        'GET' => [
            '' => 'HomeController@index',
            'users' => 'UserController@index',
            'users/{id}' => 'UserController@show',
        ],
        'POST' => [
            'users' => 'UserController@store',
        ]
    ];

    if (isset($routes[$method][$path])) {
        $handler = $routes[$method][$path];
        list($controller, $action) = explode('@', $handler);

        require_once __DIR__ . "/../src/Controllers/{$controller}.php";
        $controllerInstance = new $controller();
        $controllerInstance->$action();
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
    }
}
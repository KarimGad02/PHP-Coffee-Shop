<?php

// Simple routing system for API endpoints
class Router {
    private $db;
    private $routes = [];

    public function __construct($db) {
        $this->db = $db;
    }

    // Register a route
    public function register($method, $path, $controller, $action) {
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'controller' => $controller,
            'action' => $action
        ];
    }

    // Dispatch request
    public function dispatch($method, $path) {
        foreach ($this->routes as $route) {
            if ($route['method'] === $method && $this->matchPath($route['path'], $path, $params)) {
                return $this->execute($route['controller'], $route['action'], $params);
            }
        }

        return $this->notFound();
    }

    // Match path with params
    private function matchPath($pattern, $path, &$params) {
        $params = [];
        
        // Convert pattern to regex: /users/:id -> /users/(\d+)
        $regex = preg_replace('/:([a-zA-Z_]+)/', '(?P<$1>[^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $path, $matches)) {
            // Extract named parameters
            foreach ($matches as $key => $value) {
                if (!is_numeric($key)) {
                    $params[$key] = $value;
                }
            }
            return true;
        }

        return false;
    }

    // Execute controller action
    private function execute($controllerName, $action, $params) {
        require_once __DIR__ . '/../controllers/' . $controllerName . '.php';
        $controller = new $controllerName($this->db);
        
        if (method_exists($controller, $action)) {
            try {
                $requestParams = $GLOBALS['_REQUEST_PARAMS'] ?? [];
                $method = new ReflectionMethod($controller, $action);
                $args = [];

                foreach ($method->getParameters() as $parameter) {
                    $name = $parameter->getName();

                    if (array_key_exists($name, $params)) {
                        $args[] = $params[$name];
                    } elseif (array_key_exists($name, $requestParams)) {
                        $args[] = $requestParams[$name];
                    } elseif ($parameter->isDefaultValueAvailable()) {
                        $args[] = $parameter->getDefaultValue();
                    } else {
                        return [
                            'success' => false,
                            'message' => 'Missing required parameter: ' . $name,
                            'code' => 422
                        ];
                    }
                }

                return $method->invokeArgs($controller, $args);
            } catch (ReflectionException $e) {
                return ['success' => false, 'message' => 'Route execution failed', 'code' => 500];
            }
        }

        return $this->notFound();
    }

    // 404 Not Found
    private function notFound() {
        return ['success' => false, 'message' => 'Route not found', 'code' => 404];
    }
}

// Define routes
function defineRoutes($router) {
    // Authentication routes
    $router->register('POST', '/auth/login', 'AuthController', 'login');
    $router->register('POST', '/auth/logout', 'AuthController', 'logout');
    $router->register('POST', '/auth/register', 'AuthController', 'register');
    $router->register('POST', '/auth/forgot-password', 'AuthController', 'forgotPassword');
    $router->register('POST', '/auth/reset-password', 'AuthController', 'resetPassword');
    $router->register('GET', '/auth/me', 'AuthController', 'me');

    // Customer self CRUD
    $router->register('GET', '/users/me', 'UserController', 'getMe');
    $router->register('PUT', '/users/me', 'UserController', 'updateMe');
    $router->register('DELETE', '/users/me', 'UserController', 'deleteMe');

    // Admin users CRUD
    $router->register('GET', '/admin/users', 'AdminUserController', 'getAllUsers');
    $router->register('POST', '/admin/users', 'AdminUserController', 'createUser');
    $router->register('GET', '/admin/users/:id', 'AdminUserController', 'getUserById');
    $router->register('PUT', '/admin/users/:id', 'AdminUserController', 'updateUser');
    $router->register('DELETE', '/admin/users/:id', 'AdminUserController', 'deleteUser');
}

// FILE: /app/core/Router.php
<?php

/**
 * Router Class
 *
 * Handles URL routing and dispatches to appropriate controllers
 */
class Router
{
    private $routes = [];
    private $currentRoute = null;

    /**
     * Add a GET route
     */
    public function get($path, $controller, $method)
    {
        $this->routes['GET'][$path] = ['controller' => $controller, 'method' => $method];
    }

    /**
     * Add a POST route
     */
    public function post($path, $controller, $method)
    {
        $this->routes['POST'][$path] = ['controller' => $controller, 'method' => $method];
    }

    /**
     * Add a route for both GET and POST
     */
    public function any($path, $controller, $method)
    {
        $this->get($path, $controller, $method);
        $this->post($path, $controller, $method);
    }

    /**
     * Dispatch the request to the appropriate controller
     */
    public function dispatch()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        // Remove base path if application is in subdirectory
        $basePath = str_replace('/public/index.php', '', $_SERVER['SCRIPT_NAME']);
        $requestUri = str_replace($basePath, '', $requestUri);
        $requestUri = '/' . trim($requestUri, '/');

        // Try exact match first
        if (isset($this->routes[$requestMethod][$requestUri])) {
            $route = $this->routes[$requestMethod][$requestUri];
            $this->executeRoute($route, []);
            return;
        }

        // Try pattern matching with parameters
        foreach ($this->routes[$requestMethod] as $path => $route) {
            $pattern = $this->convertToRegex($path);
            if (preg_match($pattern, $requestUri, $matches)) {
                array_shift($matches); // Remove full match
                $this->executeRoute($route, $matches);
                return;
            }
        }

        // 404 Not Found
        http_response_code(404);
        echo "404 - Page Not Found";
    }

    /**
     * Convert route path to regex pattern
     */
    private function convertToRegex($path)
    {
        // Convert :param to regex capture group
        $pattern = preg_replace('/\/:([a-zA-Z0-9_]+)/', '/([a-zA-Z0-9_-]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Execute the route
     */
    private function executeRoute($route, $params)
    {
        $controllerName = $route['controller'];
        $methodName = $route['method'];

        $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';

        if (!file_exists($controllerFile)) {
            die("Controller not found: $controllerName");
        }

        require_once $controllerFile;

        if (!class_exists($controllerName)) {
            die("Controller class not found: $controllerName");
        }

        $controller = new $controllerName();

        if (!method_exists($controller, $methodName)) {
            die("Method not found: $methodName in $controllerName");
        }

        call_user_func_array([$controller, $methodName], $params);
    }
}

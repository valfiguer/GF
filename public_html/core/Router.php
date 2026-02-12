<?php
/**
 * Simple regex router (GET/POST).
 */
class Router {
    private array $routes = [];

    /** Register a route. $handler = [ControllerClass, 'method'] */
    public function add(string $method, string $pattern, array $handler): void {
        $this->routes[] = [
            'method'  => strtoupper($method),
            'pattern' => $pattern,
            'handler' => $handler,
        ];
    }

    public function get(string $pattern, array $handler): void {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, array $handler): void {
        $this->add('POST', $pattern, $handler);
    }

    /** Dispatch the current request. Returns true if matched. */
    public function dispatch(): bool {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri    = '/' . trim($uri, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) continue;

            $regex = '#^' . $route['pattern'] . '$#';
            if (preg_match($regex, $uri, $matches)) {
                array_shift($matches); // remove full match
                [$class, $action] = $route['handler'];
                $controller = new $class();
                call_user_func_array([$controller, $action], $matches);
                return true;
            }
        }
        return false;
    }
}

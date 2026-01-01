<?php

declare(strict_types=1);

namespace App\Http;

class Router
{
    private array $routes = [];

    public function get(string $pattern, callable $handler): self
    {
        return $this->addRoute('GET', $pattern, $handler);
    }

    public function post(string $pattern, callable $handler): self
    {
        return $this->addRoute('POST', $pattern, $handler);
    }

    public function delete(string $pattern, callable $handler): self
    {
        return $this->addRoute('DELETE', $pattern, $handler);
    }

    private function addRoute(string $method, string $pattern, callable $handler): self
    {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler
        ];

        return $this;
    }

    public function dispatch(Request $request): void
    {
        $method = $request->getMethod();
        $uri = $request->getUri();

        if ($method === 'OPTIONS') {
            Response::handleOptions();
        }

        Response::setCorsHeaders();

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchRoute($route['pattern'], $uri);

            if ($params !== null) {
                call_user_func($route['handler'], $request, $params);
                return;
            }
        }

        Response::error('NOT_FOUND', 'Endpoint not found', 404);
    }

    private function matchRoute(string $pattern, string $uri): ?array
    {
        $regex = preg_replace('/\{([^}]+)\}/', '([^/]+)', $pattern);
        $regex = '#^' . $regex . '$#';

        if (preg_match($regex, $uri, $matches)) {
            preg_match_all('/\{([^}]+)\}/', $pattern, $paramNames);

            $params = [];
            foreach ($paramNames[1] as $index => $name) {
                $params[$name] = $matches[$index + 1];
            }

            return $params;
        }

        return null;
    }
}

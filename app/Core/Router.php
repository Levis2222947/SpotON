<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Koppelt een route (de ?r= parameter) en HTTP-methode aan een controller-actie.
 * Er wordt bewust geen mod_rewrite gebruikt, zodat de app op elke server werkt.
 */
final class Router
{
    /** @var array<string, array<string, array{0: class-string, 1: string}>> */
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $route, array $handler): void
    {
        $this->routes['GET'][$route] = $handler;
    }

    public function post(string $route, array $handler): void
    {
        $this->routes['POST'][$route] = $handler;
    }

    public function dispatch(string $method, string $route): void
    {
        $handler = $this->routes[$method][$route] ?? null;

        if ($handler === null) {
            $allowedElsewhere = array_filter($this->routes, static fn (array $r) => isset($r[$route]));
            throw $allowedElsewhere !== []
                ? new HttpException(405, 'Deze actie is op deze manier niet toegestaan.')
                : HttpException::notFound();
        }

        if ($method === 'POST' && !Csrf::isValid($_POST['_csrf'] ?? null)) {
            throw new HttpException(419, 'Het formulier is verlopen. Ga terug, vernieuw de pagina en probeer het opnieuw.');
        }

        [$class, $action] = $handler;
        (new $class())->$action();
    }
}

<?php

namespace app\Core;

use app\Core\Middleware\MiddlewareInterface;

class Router
{
    public Request $request;
    public Response $response;

    protected array $routes = [
        'get' => [],
        'post' => [],
    ];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    public function get(string $path, mixed $callback, array $middlewares = []): void
    {
        $this->routes['get'][$this->normalizePath($path)] = [
            'callback' => $callback,
            'middlewares' => $middlewares,
        ];
    }

    public function post(string $path, mixed $callback, array $middlewares = []): void
    {
        $this->routes['post'][$this->normalizePath($path)] = [
            'callback' => $callback,
            'middlewares' => $middlewares,
        ];
    }

    public function resolve(): string
    {
        $path = $this->request->getPath();
        $method = $this->request->getMethod();

        $resolved = $this->matchRoute($method, $path);

        if ($resolved === null) {
            $this->response->setStatusCode(404);
            return $this->renderNotFound($path);
        }

        $route = $resolved['route'];
        $this->request->setRouteParams($resolved['params']);

        $csrfExemptLogout = $method === 'post' && in_array($path, ['/admin/users/logout', '/users/logout'], true);
        if ($method === 'post' && !$csrfExemptLogout && !$this->request->isJson() && !Csrf::validate($this->request->input('_csrf'))) {
            $this->response->setStatusCode(419);
            return $this->renderView('_419', null, ['message' => 'La sesión del formulario expiró. Intenta nuevamente.']);
        }

        foreach ($route['middlewares'] ?? [] as $middlewareClass) {
            $middleware = new $middlewareClass();

            if (!$middleware instanceof MiddlewareInterface) {
                throw new \RuntimeException("{$middlewareClass} no implementa MiddlewareInterface");
            }

            $middleware->handle($this->request);
        }

        $callback = $route['callback'] ?? null;

        if (is_string($callback)) {
            return $this->renderView($callback);
        }

        if (is_array($callback)) {
            $callback[0] = new $callback[0]();
        }

        return (string) call_user_func($callback, $this->request);
    }


    protected function renderNotFound(string $path): string
    {
        $normalizedPath = $this->normalizePath($path);

        if (str_starts_with($normalizedPath, '/admin')) {
            return $this->renderView('_404_admin', 'adminUserLayout', [
                'page_title' => 'Página no encontrada',
                'page_subtitle' => 'La ruta solicitada no existe dentro del panel administrativo.',
                'active' => '',
            ]);
        }

        return $this->renderView('_404_public', 'mainUserLayout', [
            'title' => 'Página no encontrada | Over Alestur',
        ]);
    }

    public function renderView(string $view, ?string $layout = 'mainUserLayout', array $params = []): string
    {
        $viewContent = $this->renderOnlyView($view, $params);

        if ($layout === null) {
            return $viewContent;
        }

        $layoutContent = $this->layoutContent($layout, $params);
        return str_replace('{{content}}', $viewContent, $layoutContent);
    }

    protected function layoutContent(string $layout, array $params = []): string
    {
        foreach ($params as $key => $value) {
            $$key = $value;
        }

        $layoutFile = Application::$ROOT_DIR . '/views/layouts/' . basename($layout) . '.php';

        if (!is_file($layoutFile)) {
            throw new \RuntimeException('Layout no encontrado: ' . $layout);
        }

        ob_start();
        include_once $layoutFile;
        return ob_get_clean();
    }

    protected function renderOnlyView(string $view, array $params): string
    {
        foreach ($params as $key => $value) {
            $$key = $value;
        }

        $view = ltrim($view, '/');
        $view = str_replace('\\', '/', $view);
        $view = preg_replace('/\.php$/', '', $view);

        if ($view === null || str_contains($view, '..')) {
            $this->response->setStatusCode(400);
            return 'Invalid view path';
        }

        $viewFile = Application::$ROOT_DIR . '/views/' . $view . '.php';

        if (!is_file($viewFile)) {
            $this->response->setStatusCode(404);
            if ($view !== '_404') {
                return $this->renderOnlyView('_404', []);
            }
            return '404';
        }

        ob_start();
        include $viewFile;
        return ob_get_clean();
    }

    protected function matchRoute(string $method, string $path): ?array
    {
        $routes = $this->routes[$method] ?? [];
        $path = $this->normalizePath($path);

        if (isset($routes[$path])) {
            return [
                'route' => $routes[$path],
                'params' => [],
            ];
        }

        foreach ($routes as $routePath => $route) {
            if (!str_contains($routePath, '{')) {
                continue;
            }

            $pattern = preg_replace_callback('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', function (array $matches): string {
                return '(?P<' . $matches[1] . '>[^/]+)';
            }, $routePath);

            if ($pattern === null) {
                continue;
            }

            if (preg_match('#^' . $pattern . '$#', $path, $matches) !== 1) {
                continue;
            }

            $params = [];
            foreach ($matches as $key => $value) {
                if (!is_int($key)) {
                    $params[$key] = $value;
                }
            }

            return [
                'route' => $route,
                'params' => $params,
            ];
        }

        return null;
    }

    protected function normalizePath(string $path): string
    {
        $path = rawurldecode($path);
        $path = '/' . ltrim($path, '/');
        return rtrim($path, '/') ?: '/';
    }
}

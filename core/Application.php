<?php

namespace app\Core;

use Throwable;

class Application
{
    public static string $ROOT_DIR;
    public Router $router;
    public Request $request;
    public Response $response;
    public static Application $app;

    public function __construct(string $rootpath)
    {
        self::$app = $this;
        self::$ROOT_DIR = rtrim($rootpath, DIRECTORY_SEPARATOR);

        $this->loadEnvironment();
        $this->bootstrapRuntimeDirectories();
        $this->bootstrapErrorHandling();
        secure_session_start();

        $this->request = new Request();
        $this->response = new Response();
        $this->response->applySecurityHeaders();
        $this->router = new Router($this->request, $this->response);
    }

    public function run(): void
    {
        try {
            echo $this->router->resolve();
        } catch (Throwable $exception) {
            $this->handleThrowable($exception);
        }
    }

    protected function loadEnvironment(): void
    {
        $envFile = self::$ROOT_DIR . DIRECTORY_SEPARATOR . '.env';

        if (!is_file($envFile) || !is_readable($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = array_map('trim', explode('=', $line, 2));

            if ($name === '') {
                continue;
            }

            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
            putenv($name . '=' . $value);
        }
    }

    protected function bootstrapRuntimeDirectories(): void
    {
        $directories = [
            self::$ROOT_DIR . '/runtime',
            self::$ROOT_DIR . '/runtime/logs',
            self::$ROOT_DIR . '/runtime/cache',
        ];

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                @mkdir($directory, 0755, true);
            }
        }
    }

    protected function bootstrapErrorHandling(): void
    {
        $debug = env_bool('APP_DEBUG', false);

        ini_set('display_errors', $debug ? '1' : '0');
        ini_set('log_errors', '1');

        set_error_handler(function (int $severity, string $message, string $file, int $line): void {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        set_exception_handler(function (Throwable $exception): void {
            $this->handleThrowable($exception);
        });
    }

    protected function handleThrowable(Throwable $exception): void
    {
        $message = sprintf(
            '%s in %s:%d | %s',
            get_class($exception),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getMessage()
        );

        app_log('app', $message . PHP_EOL . $exception->getTraceAsString());

        if (!isset($this->response)) {
            $this->response = new Response();
        }

        if (!headers_sent()) {
            $this->response->setStatusCode(500);
            $this->response->applySecurityHeaders();
        }

        if (env_bool('APP_DEBUG', false)) {
            echo '<pre style="padding:16px;white-space:pre-wrap">' . e($message . PHP_EOL . PHP_EOL . $exception->getTraceAsString()) . '</pre>';
            return;
        }

        $errorView = self::$ROOT_DIR . '/views/_500.php';
        if (is_file($errorView)) {
            include $errorView;
            return;
        }

        echo 'Ha ocurrido un error interno.';
    }
}

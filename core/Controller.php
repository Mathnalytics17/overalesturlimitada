<?php


namespace app\Core;
use app\Core\Application;


class Controller
{
    public function render(string $view, array $params = [], ?string $layout = 'mainUserLayout')
    {
        return Application::$app->router->renderView($view, $layout, $params);
    }
}
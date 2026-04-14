<?php

namespace app\Controllers\web\politica;

use app\Core\Controller;

class PoliticaController extends Controller
{
    public function index()
    {
        return $this->render('politica/index', [
            'titulo' => 'Política de datos',
            'pageCss' => '/styles/home.css',
        ], 'mainUserLayout');
    }
}
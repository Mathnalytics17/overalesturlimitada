<?php

namespace app\Controllers\admin\packageTour;

use app\Core\Controller;
use app\Core\Csrf;
use app\Core\Flash;
use app\Models\Currency;

class CurrencyController extends Controller
{
    public function index()
    {
        $filters = [
            'q' => trim((string)($_GET['q'] ?? '')),
            'status' => trim((string)($_GET['status'] ?? '')),
        ];

        return $this->render('admin/packageTour/currencies/list', [
            'page_title' => 'Monedas',
            'page_subtitle' => 'Gestiona las monedas usadas en paquetes turísticos.',
            'active' => 'currencies',
            'currencies' => Currency::adminList($filters),
            'usage' => Currency::usageCounts(),
            'filters' => $filters,
        ], 'adminUserLayout');
    }

    public function create()
    {
        return $this->render('admin/packageTour/currencies/create', [
            'page_title' => 'Crear moneda',
            'page_subtitle' => 'Agrega una moneda para usarla en paquetes turísticos.',
            'active' => 'currencies',
            'errors' => [],
            'old' => [
                'is_active' => '1',
            ],
        ], 'adminUserLayout');
    }

    public function store()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $data = $this->normalize($_POST);
        $errors = $this->validate($data);

        if (Currency::findByCode($data['code'])) {
            $errors['code'][] = 'Ya existe una moneda con este código.';
        }

        if ($errors !== []) {
            return $this->render('admin/packageTour/currencies/create', [
                'page_title' => 'Crear moneda',
                'page_subtitle' => 'Agrega una moneda para usarla en paquetes turísticos.',
                'active' => 'currencies',
                'errors' => $errors,
                'old' => $data,
            ], 'adminUserLayout');
        }

        $created = Currency::create($data);

        if (!$created) {
            Flash::error('No se pudo crear la moneda.');
            \redirect('/admin/currencies/create');
            exit;
        }

        Flash::success('Moneda creada correctamente. Ya puedes usarla en los paquetes.');
        \redirect('/admin/currencies');
        exit;
    }

    public function edit()
    {
        $id = (int)($_GET['id'] ?? 0);
        $currency = Currency::find($id);

        if (!$currency) {
            Flash::error('La moneda no fue encontrada.');
            \redirect('/admin/currencies');
            exit;
        }

        return $this->render('admin/packageTour/currencies/edit', [
            'page_title' => 'Editar moneda',
            'page_subtitle' => 'Actualiza la información de la moneda.',
            'active' => 'currencies',
            'currency' => $currency,
            'errors' => [],
            'old' => $currency->toArray(),
        ], 'adminUserLayout');
    }

    public function update()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $id = (int)($_POST['id'] ?? 0);
        $currency = Currency::find($id);

        if (!$currency) {
            Flash::error('La moneda no fue encontrada.');
            \redirect('/admin/currencies');
            exit;
        }

        $data = $this->normalize($_POST);
        $errors = $this->validate($data);
        $existing = Currency::findByCode($data['code']);

        if ($existing && (int)$existing->id !== (int)$currency->id) {
            $errors['code'][] = 'Ya existe una moneda con este código.';
        }

        if ($errors !== []) {
            return $this->render('admin/packageTour/currencies/edit', [
                'page_title' => 'Editar moneda',
                'page_subtitle' => 'Actualiza la información de la moneda.',
                'active' => 'currencies',
                'currency' => $currency,
                'errors' => $errors,
                'old' => array_merge($currency->toArray(), $data),
            ], 'adminUserLayout');
        }

        $currency->update($data);
        Flash::success('Moneda actualizada correctamente.');
        \redirect('/admin/currencies');
        exit;
    }

    public function toggle()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $id = (int)($_POST['id'] ?? 0);
        $currency = Currency::find($id);

        if (!$currency) {
            Flash::error('La moneda no fue encontrada.');
            \redirect('/admin/currencies');
            exit;
        }

        $currency->update([
            'is_active' => !empty($currency->is_active) ? 0 : 1,
        ]);

        Flash::success(!empty($currency->is_active) ? 'Moneda desactivada.' : 'Moneda activada.');
        \redirect('/admin/currencies');
        exit;
    }

    public function seed()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $created = Currency::seedDefaults();
        Flash::success($created > 0 ? "Se crearon {$created} moneda(s)." : 'Las monedas base ya existían.');
        \redirect('/admin/currencies');
        exit;
    }

    protected function normalize(array $input): array
    {
        return [
            'code' => strtoupper(trim((string)($input['code'] ?? ''))),
            'name' => trim((string)($input['name'] ?? '')),
            'symbol' => trim((string)($input['symbol'] ?? '')),
            'is_active' => !empty($input['is_active']) ? 1 : 0,
            'sort_order' => max(0, (int)($input['sort_order'] ?? 0)),
        ];
    }

    protected function validate(array $data): array
    {
        $errors = [];

        if (!preg_match('/^[A-Z]{3}$/', $data['code'])) {
            $errors['code'][] = 'El código debe tener 3 letras. Ejemplo: COP, USD, EUR.';
        }

        if ($data['name'] === '') {
            $errors['name'][] = 'El nombre de la moneda es obligatorio.';
        }

        if ($data['symbol'] === '') {
            $errors['symbol'][] = 'El símbolo es obligatorio.';
        }

        return $errors;
    }
}

<?php

namespace app\Controllers\web\users;

use app\Core\Controller;
use app\Core\Csrf;
use app\Core\CustomerAuth;
use app\Models\Customer;
use app\Models\CustomerTravelPreference;
use app\Models\Lead;
use app\Models\TourPackageTag;
use app\Models\TourPackageImage;
use app\Services\Web\Users\CustomerProfileService;
use app\Services\Package\CustomerPackageService;
use app\Core\Flash;

class ProfileController extends Controller
{
    public function index()
    {
        $account = CustomerAuth::user();

        if (!$account) {
            \redirect('/users/login');
        }

        $customer = Customer::find((int) $account->customer_id);
        $packageService = new CustomerPackageService();
        $favorites = $packageService->favoritePackages((int) $account->customer_id);
        $recommendations = $packageService->recommendations((int) $account->customer_id, 6);

        foreach (array_merge($favorites, $recommendations) as $package) {
            $package->cover_image = TourPackageImage::coverByPackage((int) $package->id);
        }

        return $this->render('users/user', [
            'account' => $account,
            'customer' => $customer,
            'favorites' => $favorites,
            'recommendations' => $recommendations,
            'inquiries' => Lead::byCustomer((int) $account->customer_id, 10),
            'preference' => CustomerTravelPreference::findByCustomer((int) $account->customer_id),
            'preferenceTags' => TourPackageTag::activeList(),
            'preferenceErrors' => [],
            'preferenceMessage' => null,
        ], 'mainUserLayout');
    }

    public function updatePreferences()
    {
        $account = CustomerAuth::user();
        if (!$account) {
            \redirect('/users/login');
        }

        $tagSlugs = array_values(array_unique(array_filter(array_map(
            static fn($value): string => trim((string) $value),
            (array) ($_POST['preferred_tag_slugs'] ?? [])
        ))));
        $allowedTagSlugs = array_map(
            static fn($tag): string => (string) $tag->slug,
            TourPackageTag::activeList()
        );
        $tagSlugs = array_values(array_intersect($tagSlugs, $allowedTagSlugs));

        $destinations = trim((string) ($_POST['desired_destinations'] ?? ''));
        $budgetMin = trim((string) ($_POST['budget_min'] ?? ''));
        $budgetMax = trim((string) ($_POST['budget_max'] ?? ''));
        $travelers = trim((string) ($_POST['usual_travelers'] ?? ''));
        $notifyNewPackages = !empty($_POST['notify_new_packages']) ? 1 : 0;
        $notifyRecommendations = !empty($_POST['notify_recommendations']) ? 1 : 0;
        $errors = [];

        if (mb_strlen($destinations) > 500) {
            $errors['desired_destinations'][] = 'Usa máximo 500 caracteres para tus destinos deseados.';
        }

        if ($budgetMin !== '' && (!is_numeric($budgetMin) || (float) $budgetMin < 0)) {
            $errors['budget_min'][] = 'El presupuesto mínimo debe ser un valor positivo.';
        }

        if ($budgetMax !== '' && (!is_numeric($budgetMax) || (float) $budgetMax < 0)) {
            $errors['budget_max'][] = 'El presupuesto máximo debe ser un valor positivo.';
        }

        if ($budgetMin !== '' && $budgetMax !== '' && (float) $budgetMax < (float) $budgetMin) {
            $errors['budget_max'][] = 'El presupuesto máximo no puede ser menor al mínimo.';
        }

        if ($travelers !== '' && (!ctype_digit($travelers) || (int) $travelers < 1 || (int) $travelers > 99)) {
            $errors['usual_travelers'][] = 'Indica una cantidad de viajeros entre 1 y 99.';
        }

        if ($errors === []) {
            CustomerTravelPreference::saveForCustomer((int) $account->customer_id, [
                'preferred_tag_slugs_json' => json_encode($tagSlugs, JSON_UNESCAPED_UNICODE),
                'desired_destinations' => $destinations !== '' ? $destinations : null,
                'budget_min' => $budgetMin !== '' ? (float) $budgetMin : null,
                'budget_max' => $budgetMax !== '' ? (float) $budgetMax : null,
                'usual_travelers' => $travelers !== '' ? (int) $travelers : null,
                'notify_new_packages' => $notifyNewPackages,
                'notify_recommendations' => $notifyRecommendations,
            ]);

            \redirect('/users/user?preferences=saved');
        }

        $customerId = (int) $account->customer_id;
        $packageService = new CustomerPackageService();
        $favorites = $packageService->favoritePackages($customerId);
        $recommendations = $packageService->recommendations($customerId, 6);

        foreach (array_merge($favorites, $recommendations) as $package) {
            $package->cover_image = TourPackageImage::coverByPackage((int) $package->id);
        }

        return $this->render('users/user', [
            'account' => $account,
            'customer' => Customer::find($customerId),
            'favorites' => $favorites,
            'recommendations' => $recommendations,
            'inquiries' => Lead::byCustomer($customerId, 10),
            'preference' => new CustomerTravelPreference([
                'preferred_tag_slugs_json' => $tagSlugs,
                'desired_destinations' => $destinations,
                'budget_min' => $budgetMin,
                'budget_max' => $budgetMax,
                'usual_travelers' => $travelers,
                'notify_new_packages' => $notifyNewPackages,
                'notify_recommendations' => $notifyRecommendations,
            ]),
            'preferenceTags' => TourPackageTag::activeList(),
            'preferenceErrors' => $errors,
            'preferenceMessage' => 'Revisa los datos de tus preferencias.',
        ], 'mainUserLayout');
    }

    public function clearPersonalization()
    {
        $account = CustomerAuth::user();
        if (!$account) {
            \redirect('/users/login');
        }

        if (($_POST['confirm_clear_personalization'] ?? '') !== '1') {
            \redirect('/users/user?privacy=confirm');
        }

        (new CustomerPackageService())->clearPersonalization((int) $account->customer_id);

        \redirect('/users/user?privacy=cleared');
    }

    public function showChangePassword()
    {
        return $this->render('users/changePassword', [
            'errors' => [],
            'message' => null,
        ], 'mainUserLayout');
    }

    public function changePassword()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $account = CustomerAuth::user();

        if (!$account) {
            \redirect('/users/login');
        }

        $service = new CustomerProfileService();

        $result = $service->changePassword(
            accountId: (int) $account->id,
            currentPassword: $_POST['current_password'] ?? '',
            newPassword: $_POST['new_password'] ?? '',
            newPasswordConfirmation: $_POST['new_password_confirmation'] ?? ''
        );

        if (!empty($result['success']) && !empty($result['data']['force_relogin'])) {
            CustomerAuth::logout(
                ipAddress: $_SERVER['REMOTE_ADDR'] ?? null,
                userAgent: $_SERVER['HTTP_USER_AGENT'] ?? null
            );

            return $this->render('users/login', [
                'errors' => [],
                'message' => 'Tu contrasena fue actualizada. Inicia sesion nuevamente.',
                'old' => [],
            ]);
        }

        return $this->render('users/changePassword', [
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
        ], 'mainUserLayout');
    }

     public function showEditProfile()
    {
        $account = CustomerAuth::user();

        if (!$account) {
            \redirect('/users/login');
        }

        $customer = Customer::find((int) $account->customer_id);

        return $this->render('users/editUser', [
            'account' => $account,
            'customer' => $customer,
            'errors' => [],
            'message' => null,
            'old' => [],
        ], 'mainUserLayout');
    }

    public function updateProfile()
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            exit('CSRF inválido');
        }

        $account = CustomerAuth::user();

        if (!$account) {
            \redirect('/users/login');
        }

        $service = new CustomerProfileService();

        $result = $service->updateProfile(
            customerId: (int) $account->customer_id,
            input: $_POST,
            files: $_FILES
        );

        $customer = Customer::find((int) $account->customer_id);

        return $this->render('users/editUser', [
            'account' => $account,
            'customer' => $customer,
            'errors' => $result['errors'] ?? [],
            'message' => $result['message'] ?? null,
            'old' => $_POST,
        ], 'mainUserLayout');
    }
}

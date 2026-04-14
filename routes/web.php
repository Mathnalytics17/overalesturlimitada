<?php

use app\Controllers\SiteController;
use app\Controllers\web\packageTour\TourPackageController;
use app\Controllers\web\users\AuthController as WebAuthController;
use app\Controllers\web\users\EmailVerificationController as WebEmailVerificationController;
use app\Controllers\web\users\PasswordController as WebPasswordController;
use app\Controllers\web\users\RegisterController as WebRegisterController;
use app\Controllers\web\leads\LeadCaptureController;
use app\Core\Middleware\AuthCustomerMiddleware;
use app\Core\Middleware\GuestCustomerMiddleware;
use app\Controllers\web\pqrs\PqrsController as WebPqrsController;
use app\Controllers\web\services\ExtraServicesController;
use app\Controllers\web\experience\ExperienceController;
use app\Controllers\web\politica\PoliticaController;


/* home */
$app->router->get('/', [SiteController::class, 'home']);

/* contact */
$app->router->get('/contact', [SiteController::class, 'contact']);
$app->router->post('/contact', [LeadCaptureController::class, 'submitContact']);


/* about */
$app->router->get('/aboutUs', [SiteController::class, 'about']);

/* pqrs */
$app->router->get('/pqrs', [SiteController::class, 'pqrs']);
$app->router->post('/pqrs', [LeadCaptureController::class, 'submitPqrs']);

/* extraServices */
$app->router->get('/extra-services', [ExtraServicesController::class, 'index']);
$app->router->get('/extra-services/show', [ExtraServicesController::class, 'show']);
$app->router->post('/extra-services/contact', [ExtraServicesController::class, 'submitExtraServices']);
/* paquetes publicos */
$app->router->get('/packagesTourist', [TourPackageController::class, 'index']);
$app->router->get('/packagesTourist/package', [TourPackageController::class, 'show']);
$app->router->post('/packagesTourist/inquiry', [LeadCaptureController::class, 'submitPackage']);
$app->router->get('/packagesTourist/{id}', [SiteController::class, 'packageTouristDetail']);
$app->router->get('/pqrs', [WebPqrsController::class, 'index']);
$app->router->post('/pqrs', [WebPqrsController::class, 'submit']);
/* users privadas */
$app->router->get('/users/user', [SiteController::class, 'UserShow'], [AuthCustomerMiddleware::class]);
$app->router->get('/users/editUser', [\app\Controllers\web\users\ProfileController::class, 'showEditProfile'], [AuthCustomerMiddleware::class]);
$app->router->post('/users/editUser', [\app\Controllers\web\users\ProfileController::class, 'updateProfile'], [AuthCustomerMiddleware::class]);
$app->router->get('/users/changePassword', [\app\Controllers\web\users\ProfileController::class, 'showChangePassword'], [AuthCustomerMiddleware::class]);
$app->router->post('/users/changePassword', [\app\Controllers\web\users\ProfileController::class, 'changePassword'], [AuthCustomerMiddleware::class]);

/* users publicas */
$app->router->get('/users/confirmUser', [WebEmailVerificationController::class, 'confirm']);
$app->router->get('/users/resendVerification', [WebEmailVerificationController::class, 'showResendForm']);
$app->router->post('/users/resendVerification', [WebEmailVerificationController::class, 'resend']);
$app->router->get('/users/confirmPassword', [SiteController::class, 'UserConfirmPassword']);
$app->router->get('/users/forgotPassword', [WebPasswordController::class, 'showForgotPassword']);
$app->router->post('/users/forgotPassword', [WebPasswordController::class, 'sendResetLink']);
$app->router->get('/users/resetPassword', [WebPasswordController::class, 'showResetPassword']);
$app->router->post('/users/resetPassword', [WebPasswordController::class, 'resetPassword']);

/* users auth */
$app->router->get('/users/login', [WebAuthController::class, 'showLogin'], [GuestCustomerMiddleware::class]);
$app->router->post('/users/login', [WebAuthController::class, 'login'], [GuestCustomerMiddleware::class]);
$app->router->post('/users/logout', [WebAuthController::class, 'logout'], [AuthCustomerMiddleware::class]);
$app->router->get('/users/register', [WebRegisterController::class, 'showRegister'], [GuestCustomerMiddleware::class]);
$app->router->post('/users/register', [WebRegisterController::class, 'register'], [GuestCustomerMiddleware::class]);

$app->router->get('/tickets', [\app\Controllers\SiteController::class, 'tickets']);
$app->router->post('/tickets', [LeadCaptureController::class, 'submitTickets']);



$app->router->get('/experiences', [ExperienceController::class, 'index']);
$app->router->get('/experiences/share', [ExperienceController::class, 'share']);
$app->router->post('/experiences/share', [ExperienceController::class, 'store']);

$app->router->get('/politica-datos', [PoliticaController::class, 'index']);
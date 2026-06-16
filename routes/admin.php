<?php

require_once __DIR__ . '/../services/ChatbotCrmApiClient.php';
require_once __DIR__ . '/../controllers/admin/chatbot/ChatbotController.php';

use app\Controllers\admin\packageTour\PackageTourController;
use app\Controllers\admin\packageTour\PackageTagController;
use app\Controllers\admin\packageTour\CurrencyController;
use app\Controllers\admin\leads\LeadController;
use app\Controllers\admin\users\AuthController as AdminAuthController;
use app\Controllers\admin\users\EmailVerificationController as AdminEmailVerificationController;
use app\Controllers\admin\users\PasswordController as AdminPasswordController;
use app\Controllers\admin\users\ProfileController as AdminProfileController;
use app\Controllers\admin\users\RegisterController as AdminRegisterController;
use app\Controllers\SiteController;
use app\Controllers\admin\users\UserManagementController;

use app\Core\Middleware\AuthAdminMiddleware;
use app\Core\Middleware\GuestAdminMiddleware;
use app\Controllers\admin\pqrs\PqrsController as AdminPqrsController;


/* admin protegidas */
$app->router->get(
    '/admin',
    [\app\Controllers\admin\dashboard\DashboardController::class, 'index'],
    [\app\Core\Middleware\AuthAdminMiddleware::class]
);
$app->router->get('/admin/leads', [LeadController::class, 'index'], [AuthAdminMiddleware::class]);
$app->router->get('/admin/leads/show', [LeadController::class, 'show'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/leads/status', [LeadController::class, 'updateStatus'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/leads/take', [\app\Controllers\admin\leads\LeadController::class, 'take'], [
    \app\Core\Middleware\AuthAdminMiddleware::class
]);
$app->router->post('/admin/leads/release', [\app\Controllers\admin\leads\LeadController::class, 'release'], [
    \app\Core\Middleware\AuthAdminMiddleware::class
]);

$app->router->get('/admin/pqrs', [AdminPqrsController::class, 'index'], [
    \app\Core\Middleware\AuthAdminMiddleware::class
]);

$app->router->get('/admin/pqrs/show', [AdminPqrsController::class, 'show'], [
    \app\Core\Middleware\AuthAdminMiddleware::class
]);

$app->router->post('/admin/pqrs/take', [AdminPqrsController::class, 'take'], [
    \app\Core\Middleware\AuthAdminMiddleware::class
]);

$app->router->post('/admin/pqrs/release', [AdminPqrsController::class, 'release'], [
    \app\Core\Middleware\AuthAdminMiddleware::class
]);

$app->router->get('/admin/pqrs/attachment', [\app\Controllers\admin\pqrs\PqrsController::class, 'attachment'], [
    \app\Core\Middleware\AuthAdminMiddleware::class
]);
$app->router->post('/admin/leads/note', [LeadController::class, 'note'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/leads/task', [LeadController::class, 'task'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/leads/task/complete', [LeadController::class, 'completeTask'], [AuthAdminMiddleware::class]);

// Chatbot WhatsApp / CRM
$app->router->get('/admin/chatbot', [\app\Controllers\admin\chatbot\ChatbotController::class, 'index'], [AuthAdminMiddleware::class]);
$app->router->get('/admin/chatbot/show', [\app\Controllers\admin\chatbot\ChatbotController::class, 'show'], [AuthAdminMiddleware::class]);
$app->router->get('/admin/chatbot/export', [\app\Controllers\admin\chatbot\ChatbotController::class, 'export'], [AuthAdminMiddleware::class]);
$app->router->get('/admin/chatbot/export-messages', [\app\Controllers\admin\chatbot\ChatbotController::class, 'exportMessages'], [AuthAdminMiddleware::class]);

// Ruta legacy mantenida por compatibilidad: redirige al módulo real de seguimiento de ventas.
$app->router->get('/admin/trazabilitySells', [\app\Controllers\admin\sales\SalesOpportunityController::class, 'index'], [AuthAdminMiddleware::class]);

/* admin auth */
$app->router->get('/admin/users/login', [AdminAuthController::class, 'showLogin'], [GuestAdminMiddleware::class]);
$app->router->post('/admin/users/login', [AdminAuthController::class, 'login'], [GuestAdminMiddleware::class]);
$app->router->post('/admin/users/logout', [AdminAuthController::class, 'logout'], [AuthAdminMiddleware::class]);

/* admin verify email */
$app->router->get('/admin/users/confirmUser', [AdminEmailVerificationController::class, 'confirm']);
$app->router->get('/admin/users/resendVerification', [AdminEmailVerificationController::class, 'showResendForm']);
$app->router->post('/admin/users/resendVerification', [AdminEmailVerificationController::class, 'resend']);

/* admin password reset */
$app->router->get('/admin/users/forgotPassword', [AdminPasswordController::class, 'showForgotPassword']);
$app->router->post('/admin/users/forgotPassword', [AdminPasswordController::class, 'sendResetLink']);
$app->router->get('/admin/users/resetPassword', [AdminPasswordController::class, 'showResetPassword']);
$app->router->post('/admin/users/resetPassword', [AdminPasswordController::class, 'resetPassword']);

/* admin profile */
$app->router->get('/admin/users/editUser', [AdminProfileController::class, 'showEditProfile'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/users/editUser', [AdminProfileController::class, 'updateProfile'], [AuthAdminMiddleware::class]);
$app->router->get('/admin/users/changePassword', [AdminProfileController::class, 'showChangePassword'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/users/changePassword', [AdminProfileController::class, 'changePassword'], [AuthAdminMiddleware::class]);


/* admin profile */
$app->router->get('/admin/profile', [AdminProfileController::class, 'index'], [AuthAdminMiddleware::class]);
$app->router->get('/admin/profile/edit', [AdminProfileController::class, 'showEditProfile'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/profile/edit', [AdminProfileController::class, 'updateProfile'], [AuthAdminMiddleware::class]);
$app->router->get('/admin/profile/change-password', [AdminProfileController::class, 'showChangePassword'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/profile/change-password', [AdminProfileController::class, 'changePassword'], [AuthAdminMiddleware::class]);


/* paquetes admin */
$app->router->get('/admin/packageTour/tags', [PackageTagController::class, 'index'], [AuthAdminMiddleware::class]);
$app->router->get('/admin/packageTour/tags/create', [PackageTagController::class, 'create'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/packageTour/tags/create', [PackageTagController::class, 'store'], [AuthAdminMiddleware::class]);
$app->router->get('/admin/packageTour/tags/edit', [PackageTagController::class, 'edit'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/packageTour/tags/edit', [PackageTagController::class, 'update'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/packageTour/tags/delete', [PackageTagController::class, 'delete'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/packageTour/tags/seed', [PackageTagController::class, 'seed'], [AuthAdminMiddleware::class]);


$app->router->get('/admin/currencies', [CurrencyController::class, 'index'], [AuthAdminMiddleware::class]);
$app->router->get('/admin/currencies/create', [CurrencyController::class, 'create'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/currencies/create', [CurrencyController::class, 'store'], [AuthAdminMiddleware::class]);
$app->router->get('/admin/currencies/edit', [CurrencyController::class, 'edit'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/currencies/edit', [CurrencyController::class, 'update'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/currencies/toggle', [CurrencyController::class, 'toggle'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/currencies/seed', [CurrencyController::class, 'seed'], [AuthAdminMiddleware::class]);

$app->router->get('/admin/packageTour', [PackageTourController::class, 'index'], [AuthAdminMiddleware::class]);
$app->router->get('/admin/packageTour/create', [PackageTourController::class, 'create'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/packageTour/create', [PackageTourController::class, 'store'], [AuthAdminMiddleware::class]);
$app->router->get('/admin/packageTour/edit', [PackageTourController::class, 'edit'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/packageTour/edit', [PackageTourController::class, 'update'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/packageTour/delete', [PackageTourController::class, 'delete'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/packageTour/status', [PackageTourController::class, 'toggleStatus'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/packageTour/featured', [PackageTourController::class, 'toggleFeatured'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/packageTour/notifications/process', [PackageTourController::class, 'processNotifications'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/packageTour/notifications/queue-recommendations', [PackageTourController::class, 'queueWeeklyRecommendations'], [AuthAdminMiddleware::class]);
$app->router->get('/admin/packageTour/analytics', [\app\Controllers\admin\packageTour\PackageAnalyticsController::class, 'index'], [AuthAdminMiddleware::class]);


$app->router->get('/admin/users', [UserManagementController::class, 'index'], [
    \app\Core\Middleware\AuthAdminMiddleware::class
]);

$app->router->post('/admin/users/create', [UserManagementController::class, 'create'], [
    \app\Core\Middleware\AuthAdminMiddleware::class
]);

$app->router->get('/admin/users/edit', [UserManagementController::class, 'edit'], [
    \app\Core\Middleware\AuthAdminMiddleware::class
]);

$app->router->post('/admin/users/update', [UserManagementController::class, 'update'], [
    \app\Core\Middleware\AuthAdminMiddleware::class
]);

$app->router->post('/admin/users/delete', [UserManagementController::class, 'delete'], [
    \app\Core\Middleware\AuthAdminMiddleware::class
]);

$app->router->post('/admin/users/status', [UserManagementController::class, 'changeStatus'], [
    \app\Core\Middleware\AuthAdminMiddleware::class
]);


//Ventas
$app->router->get('/admin/sales', [\app\Controllers\admin\sales\SalesOpportunityController::class, 'index'], [AuthAdminMiddleware::class]);
$app->router->get('/admin/sales/show', [\app\Controllers\admin\sales\SalesOpportunityController::class, 'show'], [AuthAdminMiddleware::class]);

$app->router->post('/admin/sales/create-from-lead', [\app\Controllers\admin\sales\SalesOpportunityController::class, 'createFromLead'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/sales/assign', [\app\Controllers\admin\sales\SalesOpportunityController::class, 'assign'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/sales/change-stage', [\app\Controllers\admin\sales\SalesOpportunityController::class, 'changeStage'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/sales/schedule-followup', [\app\Controllers\admin\sales\SalesOpportunityController::class, 'scheduleFollowUp'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/sales/add-note', [\app\Controllers\admin\sales\SalesOpportunityController::class, 'addNote'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/sales/mark-won', [\app\Controllers\admin\sales\SalesOpportunityController::class, 'markWon'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/sales/mark-lost', [\app\Controllers\admin\sales\SalesOpportunityController::class, 'markLost'], [AuthAdminMiddleware::class]);

$app->router->post('/admin/sales/payment/report', [\app\Controllers\admin\sales\SalesPaymentController::class, 'store'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/sales/payment/verify', [\app\Controllers\admin\sales\SalesPaymentController::class, 'verify'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/sales/payment/reject', [\app\Controllers\admin\sales\SalesPaymentController::class, 'reject'], [AuthAdminMiddleware::class]);

$app->router->get('/admin/sales/kanban', [\app\Controllers\admin\sales\SalesOpportunityController::class, 'kanban'], [AuthAdminMiddleware::class]);

$app->router->get('/admin/sales/create', [\app\Controllers\admin\sales\SalesOpportunityController::class, 'create'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/sales/create', [\app\Controllers\admin\sales\SalesOpportunityController::class, 'store'], [AuthAdminMiddleware::class]);

$app->router->get('/admin/sales/edit', [\app\Controllers\admin\sales\SalesOpportunityController::class, 'edit'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/sales/edit', [\app\Controllers\admin\sales\SalesOpportunityController::class, 'update'], [AuthAdminMiddleware::class]);

$app->router->post('/admin/sales/quote/create', [\app\Controllers\admin\sales\SalesQuoteController::class, 'store'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/sales/quote/mark-sent', [\app\Controllers\admin\sales\SalesQuoteController::class, 'markSent'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/sales/quote/mark-accepted', [\app\Controllers\admin\sales\SalesQuoteController::class, 'markAccepted'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/sales/quote/mark-rejected', [\app\Controllers\admin\sales\SalesQuoteController::class, 'markRejected'], [AuthAdminMiddleware::class]);

$app->router->get('/admin/sales-orders', [\app\Controllers\admin\sales\SalesOrderController::class, 'index'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/sales-orders/create-from-opportunity', [\app\Controllers\admin\sales\SalesOrderController::class, 'createFromOpportunity'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/sales-orders/update-operational-status', [\app\Controllers\admin\sales\SalesOrderController::class, 'updateOperationalStatus'], [AuthAdminMiddleware::class]);
$app->router->get('/admin/sales-orders/show', [\app\Controllers\admin\sales\SalesOrderController::class, 'show'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/sales-orders/update-notes', [\app\Controllers\admin\sales\SalesOrderController::class, 'updateNotes'], [AuthAdminMiddleware::class]);




$app->router->get('/admin/experiences', [\app\Controllers\admin\experience\AdminExperienceController::class, 'index'], [AuthAdminMiddleware::class]);
$app->router->get('/admin/experiences/show', [\app\Controllers\admin\experience\AdminExperienceController::class, 'show'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/experiences/approve', [\app\Controllers\admin\experience\AdminExperienceController::class, 'approve'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/experiences/reject', [\app\Controllers\admin\experience\AdminExperienceController::class, 'reject'], [AuthAdminMiddleware::class]);
$app->router->post('/admin/experiences/update', [\app\Controllers\admin\experience\AdminExperienceController::class, 'update'], [AuthAdminMiddleware::class]);

$app->router->post('/admin/pqrs/status', [AdminPqrsController::class, 'updateStatus'], [
    \app\Core\Middleware\AuthAdminMiddleware::class
]);

$app->router->post('/admin/pqrs/note', [AdminPqrsController::class, 'note'], [
    \app\Core\Middleware\AuthAdminMiddleware::class
]);

$app->router->post('/admin/pqrs/task', [AdminPqrsController::class, 'task'], [
    \app\Core\Middleware\AuthAdminMiddleware::class
]);

$app->router->post('/admin/pqrs/task/complete', [AdminPqrsController::class, 'completeTask'], [
    \app\Core\Middleware\AuthAdminMiddleware::class
]);

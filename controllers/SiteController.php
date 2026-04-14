<?php


namespace app\Controllers;

use app\Core\Application;
use app\Core\Controller;
use app\Core\Request;
use app\Core\CustomerAuth;
use app\Models\Customer;
use app\Services\Mail\InboxMailService;
use app\Models\TravelExperience;
use app\Models\TravelExperienceImage;

class SiteController extends Controller{

    public function home(){

    $experiences = TravelExperience::featured(3);

foreach ($experiences as $exp) {
    $exp->cover_image = TravelExperienceImage::coverByExperience((int)$exp->id);
}
    return $this->render('home', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css',
        'experiences' =>  $experiences ,
    ], 'mainUserLayout');
    }




    /*contact*/

   public function contact(){
    return $this->render('contact', [
        'pageCss' => '/public/styles/contact.css'
    ], 'mainUserLayout');
}

    public function handleContact(Request $request){
        $payload = $request->getBody();

        $name = trim((string) ($payload['firstLastName'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $phone = trim((string) ($payload['telefono'] ?? ''));

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $phone === '') {
            return $this->render('contact', [
                'pageCss' => '/public/styles/contact.css',
                'message' => 'Completa nombre, correo y teléfono con datos válidos.',
                'messageType' => 'error',
            ], 'mainUserLayout');
        }

        if (empty($payload['acepta'])) {
            return $this->render('contact', [
                'pageCss' => '/public/styles/contact.css',
                'message' => 'Debes aceptar la política de tratamiento de datos.',
                'messageType' => 'error',
            ], 'mainUserLayout');
        }

        $mail = new InboxMailService();
        $result = $mail->sendWebsiteFormSubmission(isset($payload['country']) || isset($payload['departureDate']) ? 'tickets' : 'contact', $payload);

        if (!$result['success']) {
            app_log('forms', 'Contacto web no enviado por correo: ' . ($result['message'] ?? 'error desconocido'));
        }

        return $this->render('contact', [
            'pageCss' => '/public/styles/contact.css',
            'message' => 'Tu solicitud fue recibida correctamente. Te responderemos pronto.',
            'messageType' => 'success',
        ], 'mainUserLayout');
    }




    /*tickets*/
    public function tickets(){
    return $this->render('tickets', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'mainUserLayout');
    }

    /*package tourist*/
    public function packageTourist(){
    return $this->render('packagesTourist/list', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'mainUserLayout');
    }

    public function packageTouristDetail(){
    return $this->render('packageTourist/package', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'mainUserLayout');
    }



    /*about*/

    public function about(){
        return $this->render('about', [
            'name' => 'Usuario',
            'pageCss' => '/public/styles/home.css'
        ], 'mainUserLayout');
        }


    /*pqrs*/
    public function pqrs(){
        return $this->render('pqrs', [
            'name' => 'Usuario',
            'pageCss' => '/public/styles/pqrs.css'
        ], 'mainUserLayout');
        }

    public function handlePqrs(Request $request){
        $payload = $request->getBody();

        $name = trim((string) ($payload['name'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $type = trim((string) ($payload['type'] ?? ''));
        $subject = trim((string) ($payload['subject'] ?? ''));
        $message = trim((string) ($payload['message'] ?? ''));

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $type === '' || $subject === '' || $message === '') {
            return $this->render('pqrs', [
                'name' => 'Usuario',
                'pageCss' => '/public/styles/pqrs.css',
                'message' => 'Completa todos los campos obligatorios de la PQRS.',
                'messageType' => 'error',
            ], 'mainUserLayout');
        }

        if (empty($payload['acepta'])) {
            return $this->render('pqrs', [
                'name' => 'Usuario',
                'pageCss' => '/public/styles/pqrs.css',
                'message' => 'Debes aceptar la política de tratamiento de datos.',
                'messageType' => 'error',
            ], 'mainUserLayout');
        }

        $mail = new InboxMailService();
        $result = $mail->sendWebsiteFormSubmission('pqrs', $payload);

        if (!$result['success']) {
            app_log('forms', 'PQRS web no enviada por correo: ' . ($result['message'] ?? 'error desconocido'));
        }

        return $this->render('pqrs', [
            'name' => 'Usuario',
            'pageCss' => '/public/styles/pqrs.css',
            'message' => 'Tu solicitud PQRS fue recibida correctamente.',
            'messageType' => 'success',
        ], 'mainUserLayout');
    }





    /*users*/
        public function UserLogin(){
    return $this->render('users/login', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'mainUserLayout');
    }
     public function UserRegister(){
    return $this->render('users/register', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'mainUserLayout');
    }
     public function UserConfirmPassword(){
    return $this->render('users/confirmPassword', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'mainUserLayout');
    }
     public function UserResetPassword(){
    return $this->render('users/newPassword', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'mainUserLayout');
    }
     public function UserConfirm(){
    return $this->render('users/confirmAccount', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'mainUserLayout');
    }
     public function UserForgotPassword(){
    return $this->render('users/forgotPassword', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'mainUserLayout');
    }
     public function UserEdit(){
    return $this->render('users/editUser', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'mainUserLayout');
    }


    public function UserShow() {
        $account = CustomerAuth::user();

        if (!$account) {
            \redirect('/users/login');
        }

        $customer = Customer::find((int) $account->customer_id);

        return $this->render('users/user', [
            'account' => $account,
            'customer' => $customer,
        ], 'mainUserLayout');
    }

    public function UserList(){
    return $this->render('users/userList', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'mainUserLayout');
    }


    /*admin*/
     public function admin(){
    return $this->render('admin/home', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'adminUserLayout');
    }


    /* /admin/leads */


    public function adminLeads(){
    return $this->render('admin/leads', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'adminUserLayout');
    }




    /* /admin/users */




     public function adminUserLogin(){
    return $this->render('admin/users/login', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'adminUserLayout');
    }
     public function adminUserRegister(){
    return $this->render('admin/users/register', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'adminUserLayout');
    }
     public function adminUserConfirmPassword(){
    return $this->render('admin/users/confirmPassword', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'adminUserLayout');
    }
     public function adminUserResetPassword(){
    return $this->render('admin/users/newPassword', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'adminUserLayout');
    }
     public function adminUserConfirm(){
    return $this->render('admin/users/confirmAccount', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'adminUserLayout');
    }
     public function adminUserForgotPassword(){
    return $this->render('admin/users/forgotPassword', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'adminUserLayout');
    }
     public function adminUserEdit(){
    return $this->render('admin/users/editUser', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'adminUserLayout');
    }


    public function adminUserShow(){
    return $this->render('admin/users/user', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'adminUserLayout');
    }

    public function adminUserList(){
    return $this->render('admin/users/userList', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'adminUserLayout');
    }










    
    /*extraServices*/



    public function extraServices(){
    return $this->render('/extraServices/extraServicesInfo', [
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'mainUserLayout');
    }

    public function extraServiceDetails(){
    return $this->render('/extraServices/extraServiceDescription', [    
        'name' => 'Usuario',
        'pageCss' => '/public/styles/home.css'
    ], 'mainUserLayout');
    }
}
<?php declare(strict_types=1);

namespace app\web\controllers;

use app\models\Users;

class UserController extends AbstractController
{
    public function registerAction() : void
    {
        if(isset($_POST['register']) && !($errors = $this->models->usersRegister->exec($_POST))) {
            $this->services->flashes->addInfo('Your registration is success. Check your E-mail for confirmation.');
            $this->goBack();
        }

        $this->renderView('user/register', ['data' => $_POST, 'errors' => $errors ?? []]);
    }

    public function confirmEmailAction()
    {
        if($error = $this->models->usersConfirmEmailAndAuth->exec($this->request)) {
            $this->services->flashes->addError($error);
            $this->redirect($this->config['routes_for_roles'][Users::ROLE_GUEST]);
        } else {
            $this->services->flashes->addInfo('Your e-mail address has been confirmed');
            $this->redirect($this->config['routes_for_roles'][Users::ROLE_USER]);
        }
    }

    public function loginAction() : void
    {
        $this->renderView('user/login');
    }

    public function profileAction() : void
    {
        $this->renderView('user/profile');
    }

    public function captchaAction() : void
    {
        $this->services->captcha()->buildAndOutput();
        exit();
    }
}
<?php declare(strict_types=1);

namespace app\web\controllers;

use Gregwar\Captcha\CaptchaBuilder;
use ParagonIE\AntiCSRF\AntiCSRF;

class UserController extends AbstractController
{
    public function registerAction() : void
    {
        $errors = [];
        if(isset($_POST['register'])) {
            if(!(new AntiCSRF())->validateRequest()) {
                $this->services->flashes->addError('Form data has been expired');
                $this->goBack();
            }

            $errors = $this->models->users->validate($_POST);
            if(!$_POST['repassword']) {
                $errors['repassword'] = 'Repeat password is required';
            } elseif($_POST['password'] != $_POST['repassword']) {
                $errors['repassword'] = 'Passwords is not match';
            }
            if(!$_POST['captcha']) {
                $errors['captcha'] = 'Captcha is required';
            } elseif(!$this->services->captcha()->validate($_POST['captcha'])) {
                $errors['captcha'] = 'Captcha is not match';
            }

            if(!$errors) {
                $this->models->users->register($_POST);

                $this->services->flashes->addInfo('Your registration is success.
                    Check your E-mail for confirmation.');

                $this->goBack();
            }
        }

        $this->renderView('user/register', ['data' => $_POST, 'errors' => $errors]);
    }

    public function loginAction() : void
    {
        $this->renderView('user/login');
    }

    public function captchaAction() : void
    {
        $this->services->captcha()->buildAndOutput();
        exit();
    }
}
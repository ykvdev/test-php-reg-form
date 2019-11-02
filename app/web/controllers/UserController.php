<?php declare(strict_types=1);

namespace app\web\controllers;

use Gregwar\Captcha\CaptchaBuilder;
use ParagonIE\AntiCSRF\AntiCSRF;

class UserController extends AbstractController
{
    public function registerAction() : void
    {
        if($this->models->users->getAuthorised()) {
            $this->redirect('/profile');
        }

        if(isset($_POST['register'])) {
            if(!(new AntiCSRF())->validateRequest()) {
                $this->services->flashes->addError('Form data has been expired');
                $this->goBack();
            }

            if(!($errors = $this->validateRegisterForm())) {
                $this->models->users->register($_POST);

                $this->services->flashes->addInfo('Your registration is success.
                    Check your E-mail for confirmation.');

                $this->goBack();
            }
        } else {
            $errors = [];
        }

        $this->renderView('user/register', ['data' => $_POST, 'errors' => $errors]);
    }

    private function validateRegisterForm() : array
    {
        $errors = [];

        if($error = $this->models->users->validateLogin($_POST['login'])) {
            $errors['login'] = $error;
        }

        if($error = $this->models->users->validateEmail($_POST['email'])) {
            $errors['email'] = $error;
        }

        if($error = $this->models->users->validatePassword($_POST['password'])) {
            $errors['password'] = $error;
        }

        if(!$_POST['repassword']) {
            $errors['repassword'] = 'Repeat password is required';
        } elseif($_POST['password'] != $_POST['repassword']) {
            $errors['repassword'] = 'Passwords is not match';
        }

        if($error = $this->models->users->validateFullName($_POST['full_name'])) {
            $errors['full_name'] = $error;
        }

        if(!$_POST['captcha']) {
            $errors['captcha'] = 'Captcha is required';
        } elseif(!$this->services->captcha()->validate($_POST['captcha'])) {
            $errors['captcha'] = 'Captcha is not match';
        }

        return $errors;
    }

    public function confirmEmailAction()
    {
        if($this->models->users->getAuthorised()) {
            $this->redirect('/profile');
        }

        if($error = $this->models->users->validateEmailConfirmToken(
            $this->request['token'] ?? null,
            $this->request['email'] ?? null
        )) {
            $this->services->flashes->addError($error);
            $this->redirect('/');
        }

        $this->models->users->activate($this->request['email']);
        $this->models->users->login($this->request['email']);
        $this->services->flashes->addInfo('Your e-mail address has been confirmed');
        $this->redirect('/profile');
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
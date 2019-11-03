<?php declare(strict_types=1);

namespace app\controllers;

use app\models\Users;

class UserController extends AbstractController
{
    protected function registerAction() : void
    {
        if(isset($_POST['register']) && !($errors = $this->models->usersRegister->exec($_POST))) {
            $this->services->flashes->addInfo('Your registration is success. Check your E-mail for confirmation.');
            $this->goBack();
        }

        $this->renderView('user/register', ['data' => $_POST, 'errors' => $errors ?? []]);
    }

    protected function confirmEmailAction()
    {
        if($error = $this->models->usersConfirmEmail->exec($this->request)) {
            $this->services->flashes->addError($error);
            $this->redirect($this->config['routes_for_roles'][Users::ROLE_GUEST]);
        } else {
            $this->services->flashes->addInfo('Your e-mail address has been confirmed');
            $this->redirect($this->config['routes_for_roles'][Users::ROLE_USER]);
        }
    }

    protected function loginAction() : void
    {
        if(isset($_POST['login']) && !($errors = $this->models->usersLogin->exec($_POST))) {
            $this->redirect($this->config['routes_for_roles'][Users::ROLE_USER]);
        }

        $this->renderView('user/login', [
            'data' => $_POST,
            'errors' => $errors ?? [],
            'is_need_captcha' => $this->models->usersLogin->isNeedCaptcha()
        ]);
    }

    protected function passwordRestoreRequestAction() : void
    {
        if(isset($_POST['restore']) && !($errors = $this->models->usersPasswordRestoreRequest->exec($_POST))) {
            $this->services->flashes->addInfo('Check your E-mail for password restore link.');
            $this->goBack();
        }

        $this->renderView('user/password-restore-request', ['data' => $_POST, 'errors' => $errors ?? []]);
    }

    protected function passwordRestoreAction() : void
    {
        if($error = $this->models->usersPasswordRestore->validateToken($this->request)) {
            $this->services->flashes->addError($error);
            $this->redirect($this->config['routes_for_roles'][Users::ROLE_GUEST]);
        } elseif(isset($_POST['restore']) && !($errors = $this->models->usersPasswordRestore->exec($_POST))) {
            $this->services->flashes->addInfo('Your new password has been success saved');
            $this->redirect($this->config['routes_for_roles'][Users::ROLE_USER]);
        }

        $this->renderView('user/password-restore', [
            'data' => $_POST,
            'errors' => $errors ?? [],
            'token' => $this->request['token'] ?? null
        ]);
    }

    protected function profileAction() : void
    {
        $this->renderView('user/profile', ['user' => $this->models->users->getAuthorised()]);
    }

    protected function profileEditAction() : void
    {
        if(isset($_POST['save']) && !($errors = $this->models->usersProfileEdit->exec($_POST))) {
            $this->redirect('/profile');
        }

        $this->renderView('user/profile-edit', [
            'data' => array_replace($this->models->users->getAuthorised(), $_POST),
            'errors' => $errors ?? []
        ]);
    }

    protected function passwordChangeAction() : void
    {
        if(isset($_POST['save']) && !($errors = $this->models->usersPasswordChange->exec($_POST))) {
            $this->services->flashes->addInfo('Your password has been success changed');
            $this->redirect('/profile');
        }

        $this->renderView('user/password-change', ['data' => $_POST, 'errors' => $errors ?? []]);
    }

    protected function logoutAction()
    {
        if($this->models->users->logout()) {
            $this->redirect($this->config['routes_for_roles'][Users::ROLE_GUEST]);
        } else {
            $this->services->flashes->addError('Internal error');
            $this->goBack();
        }
    }

    protected function captchaAction() : void
    {
        $this->services->captcha()->buildAndOutput();
        exit();
    }
}
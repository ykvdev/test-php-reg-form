<?php declare(strict_types=1);

namespace app\controllers;

use app\models\Users;

class UserController extends AbstractController
{
    protected function registerAction() : void
    {
        if(isset($this->post['register']) && !($errors = $this->models->usersRegister->exec($this->post))) {
            $this->services->flashes->addInfo('Your registration is success. Check your E-mail for confirmation.');
            $this->goBack();
        }

        $this->renderView('user/register', ['data' => $this->post, 'errors' => $errors ?? []]);
    }

    protected function confirmEmailAction()
    {
        if($error = $this->models->usersConfirmEmail->exec($this->get)) {
            $this->services->flashes->addError($error);
            $this->redirect($this->config['routes_for_roles'][Users::ROLE_GUEST]);
        } else {
            $this->services->flashes->addInfo('Your e-mail address has been confirmed');
            $this->redirect($this->config['routes_for_roles'][Users::ROLE_USER]);
        }
    }

    protected function loginAction() : void
    {
        if(isset($this->post['login']) && !($errors = $this->models->usersLogin->exec($this->post))) {
            $this->redirect($this->config['routes_for_roles'][Users::ROLE_USER]);
        }

        $this->renderView('user/login', [
            'data' => $this->post,
            'errors' => $errors ?? [],
            'is_need_captcha' => $this->models->usersLogin->isNeedCaptcha()
        ]);
    }

    protected function passwordRestoreRequestAction() : void
    {
        if(isset($this->post['restore']) && !($errors = $this->models->usersPasswordRestoreRequest->exec($this->post))) {
            $this->services->flashes->addInfo('Check your E-mail for password restore link.');
            $this->goBack();
        }

        $this->renderView('user/password-restore-request', ['data' => $this->post, 'errors' => $errors ?? []]);
    }

    protected function passwordRestoreAction() : void
    {
        if($error = $this->models->usersPasswordRestore->validateToken($this->get)) {
            $this->services->flashes->addError($error);
            $this->redirect($this->config['routes_for_roles'][Users::ROLE_GUEST]);
        } elseif(isset($this->post['restore']) && !($errors = $this->models->usersPasswordRestore->exec($this->post))) {
            $this->services->flashes->addInfo('Your new password has been success saved');
            $this->redirect($this->config['routes_for_roles'][Users::ROLE_USER]);
        }

        $this->renderView('user/password-restore', [
            'data' => $this->post,
            'errors' => $errors ?? [],
            'token' => $this->get['token'] ?? null
        ]);
    }

    protected function profileAction() : void
    {
        $this->renderView('user/profile', ['user' => $this->models->users->getAuthorised()]);
    }

    protected function profileEditAction() : void
    {
        if(isset($this->post['save']) && !($errors = $this->models->usersProfileEdit->exec($this->post))) {
            $this->redirect('/profile');
        }

        $this->renderView('user/profile-edit', [
            'data' => array_replace($this->models->users->getAuthorised(), $this->post),
            'errors' => $errors ?? []
        ]);
    }

    protected function passwordChangeAction() : void
    {
        if(isset($this->post['save']) && !($errors = $this->models->usersPasswordChange->exec($this->post))) {
            $this->services->flashes->addInfo('Your password has been success changed');
            $this->redirect('/profile');
        }

        $this->renderView('user/password-change', ['data' => $this->post, 'errors' => $errors ?? []]);
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
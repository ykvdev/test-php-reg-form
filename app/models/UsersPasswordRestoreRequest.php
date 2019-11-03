<?php declare(strict_types=1);

namespace app\models;

class UsersPasswordRestoreRequest extends Users
{
    public $dbFields = ['pw_restore_token', 'pw_token_sent_at'];

    /** @var array */
    private $data;

    /** @var array */
    private $user;

    /** @var array */
    private $errors = [];

    public function exec(array $data) : array
    {
        $this->data = $data;
        if($this->validate()) {
            $this->saveToken();
            $this->sendMail();
        }

        return $this->errors;
    }

    private function validate() : bool
    {
        $isIdentityLogin = strstr($this->data['identity'], '@') === false;
        if($error = $isIdentityLogin
            ? $this->validateLogin($this->data['identity'])
            : $this->validateEmail($this->data['identity'])) {
            $this->errors['identity'] = $error;
        } else {
            $this->user = $this->getRow($isIdentityLogin
                ? ['login' => $this->data['identity']]
                : ['email' => $this->data['identity']]);
            if (!$this->user) {
                $this->errors['identity'] = ($isIdentityLogin ? 'Login' : 'E-mail') . ' not found';
            } elseif(!$this->user['email_confirmed_at']) {
                $this->errors['identity'] = 'Your e-mail'
                    . ($isIdentityLogin ? ' ' . $this->user['email'] : '')
                    . ' is not confirmed';
            }
        }

        if(!isset($this->data['captcha']) || !$this->data['captcha']) {
            $this->errors['captcha'] = 'Captcha is required';
        } elseif(!$this->services->captcha()->validate($this->data['captcha'])) {
            $this->errors['captcha'] = 'Captcha is not match';
        }

        return !$this->errors;
    }

    private function saveToken() : void
    {
        $this->user['pw_restore_token'] = $this->makePasswordRestoreToken();
        $this->user['pw_token_sent_at'] = date('Y-m-d H:i:s');
        $this->update([
            'pw_restore_token' => $this->user['pw_restore_token'],
            'pw_token_sent_at' => $this->user['pw_token_sent_at'],
        ], $this->user['id']);
    }

    private function sendMail() : void
    {
        $this->services->mailer()->send(
            $this->user['full_name'], $this->user['email'],
            'Password restore', 'mails/password-restore',
            [
                'full_name' => $this->user['full_name'],
                'url' => $this->services->getBaseUrl() . '/password-restore/' . $this->user['pw_restore_token'],
                'token_ttl' => $this->config['password_restore_token_ttl_hours']
            ]
        );
    }
}
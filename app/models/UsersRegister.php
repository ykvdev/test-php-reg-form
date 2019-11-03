<?php declare(strict_types=1);

namespace app\models;

class UsersRegister extends Users
{
    public $dbFields = ['login', 'email', 'password', 'full_name', 'registered_at'];

    /** @var array */
    private $data;

    /** @var array */
    private $errors = [];

    public function exec(array $data) : array
    {
        $this->data = $data;
        if($this->validate()) {
            $this->save();
            $this->sendMail();
        }

        return $this->errors;
    }

    private function validate() : bool
    {
        if($error = $this->validateLogin($this->data['login'])) {
            $this->errors['login'] = $error;
        }

        if($error = $this->isExists(['login' => $this->data['login']])) {
            $this->errors['login'] = 'Login already exists';
        }

        if($error = $this->validateEmail($this->data['email'])) {
            $this->errors['email'] = $error;
        }

        if($error = $this->isExists(['email' => $this->data['email']])) {
            $this->errors['email'] = 'E-mail already exists';
        }

        if($error = $this->validatePassword($this->data['password'])) {
            $this->errors['password'] = $error;
        } elseif(in_array($this->data['password'], [$this->data['login'], $this->data['email']])) {
            $this->errors['password'] = 'Password can\'t equal to login or e-mail';
        }

        if(!isset($this->data['repassword']) || !$this->data['repassword']) {
            $this->errors['repassword'] = 'Repeat password is required';
        } elseif($this->data['password'] && $this->data['password'] != $this->data['repassword']) {
            $this->errors['repassword'] = 'Passwords is not match';
        }

        if($error = $this->validateFullName($this->data['full_name'])) {
            $this->errors['full_name'] = $error;
        }

        if(!isset($this->data['captcha']) || !$this->data['captcha']) {
            $this->errors['captcha'] = 'Captcha is required';
        } elseif(!$this->services->captcha()->validate($this->data['captcha'])) {
            $this->errors['captcha'] = 'Captcha is not match';
        }

        return empty($this->errors);
    }
    
    private function save() : void
    {
        $this->data['password'] = $this->passwordHash($this->data['password']);
        $this->data['registered_at'] = date('Y-m-d H:i:s');
        $this->insert($this->data);
    }
    
    private function sendMail() : void
    {
        $this->services->mailer()->send(
            $this->data['full_name'], $this->data['email'],
            'Confirm your E-mail', 'mails/register',
            [
                'full_name' => $this->data['full_name'],
                'url' => $this->services->getBaseUrl() . '/confirm-email/'
                    . urlencode($this->data['email']) . '/'
                    . $this->makeEmailConfirmToken($this->data['email'], $this->data['registered_at'])
            ]
        );
    }
}